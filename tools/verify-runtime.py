"""Live persistence and dead-socket cleanup checks on the local demo stack."""
import os
os.environ['CHAT_TEST_URL']='http://localhost:8080'
import json,time,subprocess
from pathlib import Path
from integration import Client,join,send,receive,ROOT

results=[]
c=Client('charlie')
try:
 ws=c.socket();join(ws,3);marker='restart-proof-'+str(time.time_ns())
 send(ws,'chat:send',{'roomId':3,'content':marker});saved=receive(ws,'chat:new',predicate=lambda p:p['content']==marker)
 ws.close(timeout=.2)
 restarted=subprocess.run(['docker','compose','restart','ws','app'],cwd=ROOT,capture_output=True,text=True)
 assert restarted.returncode==0,restarted.stderr
 deadline=time.monotonic()+20
 while True:
  try:
   response=c.http.get('/api/rooms/3/messages');response.raise_for_status();break
  except Exception:
   if time.monotonic()>deadline:raise
   time.sleep(.5)
 assert any(m['id']==saved['id'] and m['content']==marker for m in response.json()['data'])
 ws=c.socket();join(ws,3)
 results.append({'id':'TC-16','status':'PASS','detail':'Stored marker survived real PHP-FPM/Workerman restart in named SQLite volume; session remained valid; new socket rejoined.','messageId':saved['id']})
 print('PASS TC-16 persistence/restart',flush=True)
finally:c.close()

c=Client('charlie')
try:
 ws=c.socket();join(ws,3)
 # Read raw TCP bytes without invoking WebSocket.recv(), so no automatic pong is sent.
 # This intentionally simulates an unresponsive peer; no application frame parser is implemented.
 ws.sock.settimeout(.5);started=time.monotonic();closed=False
 while time.monotonic()-started<60:
  try:
   if ws.sock.recv(65536)==b'':closed=True;break
  except TimeoutError:continue
  except ConnectionResetError:closed=True;break
 elapsed=time.monotonic()-started
 assert closed and elapsed<60,elapsed
 results.append({'id':'TC-12-heartbeat','status':'PASS','elapsedSeconds':round(elapsed,3),'detail':'Client intentionally never sent pong; server closed raw TCP connection within 60 seconds.'})
 print('PASS TC-12 dead peer cleanup',round(elapsed,3),flush=True)
finally:c.close()

p=ROOT/'evidence/logs/runtime-verification.json';p.write_text(json.dumps(results,indent=2),encoding='utf-8')
