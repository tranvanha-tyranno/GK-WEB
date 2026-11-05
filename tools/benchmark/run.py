"""Reproducible polling versus WebSocket experiment, isolated test stack only."""
from __future__ import annotations
import sys, json, time, math, threading, statistics, argparse, platform
from pathlib import Path
sys.path.insert(0, str(Path(__file__).resolve().parents[1]))
from integration import Client, join, send, ROOT, BASE
import websocket

OUT=ROOT/'evidence/raw'

def percentile(values, p):
    if not values:return None
    values=sorted(values);return values[max(0,math.ceil(len(values)*p)-1)]

def run(transport, number, duration, count, interval):
    sender, receiver=Client('alice'),Client('charlie')
    tx=sender.socket();join(tx,2)
    rx=receiver.socket() if transport=='websocket' else None
    if rx:join(rx,2)
    marker=f'bench-{transport}-{number}-{time.time_ns()}'
    lock=threading.Lock();stopping=threading.Event();sent={};observed={};errors=[]
    metrics={'httpRequests':0,'applicationJsonBytesReceived':0,'websocketMessagesReceived':0}
    before=receiver.http.get('/api/rooms/2/messages').json()['data']
    after_id=max((m['id'] for m in before),default=0)
    def capture(message):
        text=message.get('content','')
        if not text.startswith(marker):return
        now=time.perf_counter()
        with lock:
            if text in sent and text not in observed:observed[text]={'messageId':message['id'],'elapsedMs':(now-sent[text])*1000}
    def ws_read(ws, observe):
        ws.settimeout(.25)
        while not stopping.is_set():
            try:
                raw=ws.recv()
                if not raw:break
                event=json.loads(raw)
                if observe:
                    metrics['websocketMessagesReceived']+=1
                    metrics['applicationJsonBytesReceived']+=len(raw.encode('utf-8'))
                if observe and event['type']=='chat:new':capture(event['payload'])
                if event['type']=='system:error':errors.append(event['payload'])
            except websocket.WebSocketTimeoutException:continue
            except Exception as error:
                if not stopping.is_set():errors.append(str(error))
                break
    def poll_read():
        nonlocal after_id
        next_poll=time.perf_counter()+.55
        while not stopping.is_set():
            delay=next_poll-time.perf_counter()
            if delay>0 and stopping.wait(delay):break
            try:
                response=receiver.http.get(f'/api/benchmark/rooms/2/messages?afterId={after_id}')
                metrics['httpRequests']+=1;metrics['applicationJsonBytesReceived']+=len(response.content)
                response.raise_for_status()
                for message in response.json()['data']:
                    after_id=max(after_id,message['id']);capture(message)
            except Exception as error:errors.append(str(error))
            next_poll+=1
    readers=[threading.Thread(target=ws_read,args=(tx,False),daemon=True)]
    readers.append(threading.Thread(target=ws_read,args=(rx,True),daemon=True) if rx else threading.Thread(target=poll_read,daemon=True))
    for reader in readers:reader.start()
    started=time.perf_counter()
    try:
        for index in range(count):
            target=started+.17+index*interval
            if target>time.perf_counter():time.sleep(target-time.perf_counter())
            text=f'{marker}:{index:02d}'
            with lock:sent[text]=time.perf_counter()
            send(tx,'chat:send',{'roomId':2,'content':text})
        remaining=started+duration-time.perf_counter()
        if remaining>0:stopping.wait(remaining)
    finally:
        stopping.set()
        for reader in readers:reader.join(timeout=2)
        sender.close();receiver.close()
    rows=[]
    for text,stamp in sent.items():
        result=observed.get(text)
        rows.append({'marker':text,'transport':transport,'run':number,'status':'received' if result else 'timeout',**(result or {'messageId':None,'elapsedMs':None})})
    values=[r['elapsedMs'] for r in rows if r['status']=='received']
    summary={'transport':transport,'run':number,'durationSeconds':duration,'intervalSeconds':interval,'sent':len(sent),'received':len(observed),'timeouts':len(sent)-len(observed),'medianMs':statistics.median(values) if values else None,'p95Ms':percentile(values,.95),**metrics,'errors':errors}
    OUT.mkdir(parents=True,exist_ok=True)
    (OUT/f'{transport}-run-{number}.json').write_text(json.dumps({'summary':summary,'messages':rows},indent=2),encoding='utf-8')
    print(json.dumps(summary),flush=True)
    return summary

def idle(transport, duration=60):
    c=Client('charlie');requests=0;events=0
    try:
        if transport=='websocket':
            ws=c.socket();join(ws,2);ws.settimeout(.2)
            # Flush initial presence/typing snapshots before counting application events at idle.
            try:
                while True:ws.recv()
            except websocket.WebSocketTimeoutException:pass
            end=time.perf_counter()+duration
            while time.perf_counter()<end:
                try:
                    raw=ws.recv()
                    if raw:events+=1
                except websocket.WebSocketTimeoutException:pass
        else:
            latest=c.http.get('/api/rooms/2/messages').json()['data'];cursor=max((m['id'] for m in latest),default=0)
            for _ in range(duration):
                start=time.perf_counter();r=c.http.get(f'/api/benchmark/rooms/2/messages?afterId={cursor}');r.raise_for_status();requests+=1
                time.sleep(max(0,1-(time.perf_counter()-start)))
        result={'transport':transport,'durationSeconds':duration,'httpRequests':requests,'websocketApplicationEvents':events,'note':'Protocol heartbeat ping/pong is not included in application event count.'}
        (OUT/f'idle-{transport}.json').write_text(json.dumps(result,indent=2),encoding='utf-8');print('IDLE',json.dumps(result),flush=True)
        return result
    finally:c.close()

def main():
    parser=argparse.ArgumentParser();parser.add_argument('--quick',action='store_true',help='Smoke only, not final report evidence');args=parser.parse_args()
    if ':8088' not in BASE:raise SystemExit('Use the isolated default test stack at port 8088.')
    OUT.mkdir(parents=True,exist_ok=True)
    metadata={'python':platform.python_version(),'os':platform.platform(),'baseUrl':BASE,'generatedAt':time.strftime('%Y-%m-%dT%H:%M:%SZ',time.gmtime()),'quickSmokeOnly':args.quick,'measurement':'Same Python perf_counter at WS sender and HTTP/WS receiver; delay includes delivery, storage, polling wait and scheduling. JSON bytes exclude framing, HTTP headers and TLS.'}
    summaries=[]
    for number in range(1,2 if args.quick else 4):
        order=['polling','websocket'] if number%2 else ['websocket','polling']
        for transport in order:summaries.append(run(transport,number,12 if args.quick else 120,2 if args.quick else 20,6))
    idles=[] if args.quick else [idle('polling'),idle('websocket')]
    (OUT/'benchmark-summary.json').write_text(json.dumps({'environment':metadata,'runs':summaries,'idle':idles},indent=2),encoding='utf-8')
    print('Benchmark saved.',flush=True)
    return int(any(s['errors'] or s['timeouts'] for s in summaries))

if __name__=='__main__':raise SystemExit(main())
