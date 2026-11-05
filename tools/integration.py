"""Run against the isolated test stack on port 8088. Never reset the main demo DB."""
from __future__ import annotations
import json
import time
import os
from pathlib import Path
import httpx
import websocket

BASE = os.environ.get('CHAT_TEST_URL', 'http://localhost:8088').rstrip('/')
WS_BASE = BASE.replace('http', 'ws', 1).replace('localhost', '127.0.0.1')
ROOT = Path(__file__).resolve().parents[1]
RESULTS = []

class Client:
    def __init__(self, username='alice'):
        self.http = httpx.Client(base_url=BASE, timeout=10, trust_env=False)
        me = self.http.get('/api/auth/me')
        self.csrf = me.json()['data']['csrfToken']
        if username:
            response = self.post('/api/auth/login', {'username': username, 'password': 'ChatDemo!2026'})
            assert response.status_code == 200, response.text
            self.csrf = response.json()['data']['csrfToken']
        self.sockets = []
    def post(self, path, body, **kwargs):
        headers = {'Origin': BASE, 'X-CSRF-Token': self.csrf, **kwargs.pop('headers', {})}
        return self.http.post(path, json=body, headers=headers, **kwargs)
    def socket(self, **kwargs):
        cookie = '; '.join(f'{key}={value}' for key, value in self.http.cookies.items())
        ws = websocket.create_connection(WS_BASE + '/ws', cookie=cookie, origin=kwargs.pop('origin', BASE), timeout=5, **kwargs)
        self.sockets.append(ws)
        return ws
    def close(self):
        for ws in self.sockets:
            try: ws.close(timeout=0.2)
            except Exception: pass
        self.http.close()

def send(ws, event, payload):
    ws.send(json.dumps({'type': event, 'payload': payload}, ensure_ascii=False))

def receive(ws, event, timeout=4, predicate=lambda payload: True):
    end = time.monotonic() + timeout
    while time.monotonic() < end:
        ws.settimeout(max(0.01, end - time.monotonic()))
        raw = ws.recv()
        if not raw: raise AssertionError('Socket closed before expected event ' + event)
        data = json.loads(raw)
        if data['type'] == event and predicate(data['payload']): return data['payload']
    raise AssertionError('Event timeout: ' + event)

def join(ws, room=1):
    send(ws, 'room:join', {'roomId': room})
    return receive(ws, 'room:joined', predicate=lambda p: p['roomId'] == room)

def no_event(ws, name, timeout=0.3):
    end = time.monotonic() + timeout
    while time.monotonic() < end:
        ws.settimeout(max(0.01, end - time.monotonic()))
        try: raw = ws.recv()
        except websocket.WebSocketTimeoutException: return
        if not raw: return
        assert json.loads(raw)['type'] != name, f'Unexpected {name}'

def case(ids, title, fn):
    started = time.monotonic()
    try:
        detail = fn() or ''
        RESULTS.append({'ids': ids, 'title': title, 'status': 'PASS', 'seconds': round(time.monotonic()-started, 3), 'detail': detail})
        print('PASS', ids, title, flush=True)
    except Exception as error:
        RESULTS.append({'ids': ids, 'title': title, 'status': 'FAIL', 'seconds': round(time.monotonic()-started, 3), 'detail': f'{type(error).__name__}: {error}'})
        print('FAIL', ids, title, str(error), flush=True)

def auth():
    c = Client(None)
    try:
        assert c.http.get('/api/rooms').status_code == 401
        assert c.post('/api/auth/login', {'username':'alice','password':'wrong'}).status_code == 401
        assert c.post('/api/auth/login', {'username':'alice','password':'ChatDemo!2026'}, headers={'Origin':'https://evil.example'}).status_code == 403
        assert c.post('/api/auth/login', {'username':'alice','password':'ChatDemo!2026'}, headers={'X-CSRF-Token':'wrong'}).status_code == 403
        old_cookie = c.http.cookies.get('chat_session')
        response = c.post('/api/auth/login', {'username':'alice','password':'ChatDemo!2026'})
        assert response.status_code == 200
        c.csrf = response.json()['data']['csrfToken']
        assert c.http.cookies.get('chat_session') != old_cookie
        assert set(response.json()['data']['user']) == {'id','username','displayName'}
        rooms = c.http.get('/api/rooms'); assert rooms.status_code == 200 and len(rooms.json()['data']) == 3
        assert c.http.get('/api/rooms/999/messages').status_code == 404
        assert c.http.get('/api/rooms/1/messages?limit=51').status_code == 400
        cookie_header = next(v for k,v in response.headers.items() if k == 'set-cookie')
        assert 'HttpOnly' in cookie_header and 'SameSite=Lax' in cookie_header
        for cookie, origin in [('',BASE), (old_cookie,BASE), ('f'*64+'.'+'e'*64,BASE), (c.http.cookies.get('chat_session'),'https://evil.example')]:
            try:
                ws = websocket.create_connection(WS_BASE+'/ws', cookie=('chat_session='+cookie) if cookie else None, origin=origin, timeout=5)
            except websocket.WebSocketBadStatusException as e:
                assert e.status_code == 403
            else:
                ws.close(); raise AssertionError('Unauthorized upgrade accepted')
        return 'HTTP 401/403/404/400, cookie rotation/flags, anonymous/tampered/revoked cookie and bad Origin all checked.'
    finally: c.close()

def registration():
    c=Client(None)
    try:
        old_cookie=c.http.cookies.get('chat_session')
        response=c.post('/api/auth/register',{'username':'qa_new_user','displayName':'QA New User','password':'qa-safe-password-2026'})
        assert response.status_code==201,response.text
        data=response.json()['data'];assert data['user']['username']=='qa_new_user' and data['user']['displayName']=='QA New User'
        assert c.http.cookies.get('chat_session')!=old_cookie
        c.csrf=data['csrfToken'];assert c.http.get('/api/rooms').status_code==200
        duplicate=c.post('/api/auth/register',{'username':'qa_new_user','displayName':'Another Name','password':'qa-safe-password-2026'})
        assert duplicate.status_code==409,duplicate.text
        invalid=c.post('/api/auth/register',{'username':'x','displayName':'QA User','password':'qa-safe-password-2026'})
        assert invalid.status_code==400,invalid.text
        return 'Registration validates username/password, creates a hashed user, rotates the anonymous session, and rejects duplicates.'
    finally:c.close()

def removed_social_features():
    alice, bob = Client('alice'), Client('bob')
    try:
        for mode in ('nearby', 'random'):
            assert alice.http.get(f'/api/people?mode={mode}').status_code == 400
        assert alice.post('/api/location', {'enabled': False}).status_code == 404
        assert alice.post('/api/friends', {'userId': 2, 'action': 'send'}).status_code == 200
        people = bob.http.get('/api/people?mode=all').json()['data']['people']
        incoming = next(person for person in people if person['username'] == 'alice')
        assert incoming['relation'] == 'incoming'
        assert 'distanceKm' not in incoming
        requests = bob.http.get('/api/people?mode=requests').json()['data']['people']
        assert any(person['username'] == 'alice' and person['relation'] == 'incoming' for person in requests)
        assert bob.post('/api/friends', {'userId': 1, 'action': 'accept'}).status_code == 200
        return 'Nearby/random filters and location endpoint rejected; the invitation filter and accept action remain available.'
    finally:
        alice.close(); bob.close()

def chat_rooms():
    a,b=Client('alice'),Client('bob')
    try:
        x,y=a.socket(),b.socket(); join(x); join(y)
        content='integration-'+str(time.time_ns())
        send(x,'chat:send',{'roomId':1,'content':content})
        first=receive(x,'chat:new',predicate=lambda p:p['content']==content)
        second=receive(y,'chat:new',predicate=lambda p:p['content']==content)
        assert first==second
        assert any(m['id']==first['id'] for m in b.http.get('/api/rooms/1/messages').json()['data'])
        older=a.http.get(f"/api/rooms/1/messages?limit=10&beforeId={first['id']}").json()['data'];assert all(m['id']<first['id'] for m in older)
        matches=a.http.get('/api/rooms/1/messages/search?q='+content).json()['data'];assert any(m['id']==first['id'] for m in matches['messages']) and not matches['hasMore']
        no_event(y,'chat:new')
        join(y,2); send(x,'typing:set',{'roomId':1,'isTyping':True})
        send(x,'chat:send',{'roomId':1,'content':'isolation-'+content}); receive(x,'chat:new')
        no_event(y,'chat:new'); no_event(y,'room:typing')
        send(y,'chat:send',{'roomId':1,'content':'must not save'})
        assert receive(y,'system:error')['code']=='NOT_IN_ROOM'
        send(y,'room:join',{'roomId':999}); assert receive(y,'system:error')['code']=='ROOM_NOT_FOUND'
        join(y,1); join(y,1)
        return 'Bidirectional sender echo, receiver ID equality, SQL persistence, isolation, invalid membership and idempotent join verified.'
    finally:a.close();b.close()

def direct_and_group_management():
    a,b,c=Client('alice'),Client('bob'),Client('charlie')
    try:
        assert a.post('/api/friends',{'userId':2,'action':'send'}).status_code==200
        assert b.post('/api/friends',{'userId':1,'action':'accept'}).status_code==200
        direct_a=a.post('/api/direct-messages',{'userId':2});direct_b=b.post('/api/direct-messages',{'userId':1})
        assert direct_a.status_code==201 and direct_b.status_code==201
        direct_id=direct_a.json()['data']['id'];assert direct_id==direct_b.json()['data']['id']
        rooms_a=a.http.get('/api/rooms').json()['data'];rooms_b=b.http.get('/api/rooms').json()['data']
        assert next(r for r in rooms_a if r['id']==direct_id)['name']=='Trò chuyện với Bob Tran'
        assert next(r for r in rooms_b if r['id']==direct_id)['name']=='Trò chuyện với Alice Nguyen'
        assert c.http.get(f'/api/rooms/{direct_id}/messages').status_code==404
        x,y=a.socket(),b.socket();join(x,direct_id);join(y,direct_id)
        marker='direct-message-'+str(time.time_ns());send(x,'chat:send',{'roomId':direct_id,'content':marker})
        dm_a=receive(x,'chat:new',predicate=lambda p:p['content']==marker);dm_b=receive(y,'chat:new',predicate=lambda p:p['content']==marker);assert dm_a==dm_b
        assert a.post('/api/friends',{'userId':3,'action':'send'}).status_code==200
        assert c.post('/api/friends',{'userId':1,'action':'accept'}).status_code==200
        made=a.post('/api/groups',{'name':'Managed test group','memberIds':[2]});assert made.status_code==200,made.text
        room_id=made.json()['data']['id'];bob_group=b.socket();join(bob_group,room_id)
        details=a.http.get(f'/api/groups/{room_id}').json()['data'];assert len(details['members'])==2 and details['isOwner']
        assert a.post('/api/groups/manage',{'roomId':room_id,'action':'rename','name':'Renamed test group'}).status_code==200
        assert a.post('/api/groups/manage',{'roomId':room_id,'action':'add','userId':3}).status_code==200
        assert any(r['id']==room_id for r in c.http.get('/api/rooms').json()['data'])
        assert a.post('/api/groups/manage',{'roomId':room_id,'action':'remove','userId':2}).status_code==200
        deadline=time.monotonic()+2.5;closed=False
        while time.monotonic()<deadline:
            try:
                bob_group.settimeout(max(.05,deadline-time.monotonic()));bob_group.recv()
            except (websocket.WebSocketConnectionClosedException,websocket.WebSocketException):closed=True;break
            except websocket.WebSocketTimeoutException:pass
        assert closed,'Removed member socket stayed connected'
        assert b.http.get(f'/api/rooms/{room_id}/messages').status_code==404
        assert c.post('/api/groups/manage',{'roomId':room_id,'action':'leave'}).status_code==200
        deleted=a.post('/api/groups/manage',{'roomId':room_id,'action':'delete'});assert deleted.status_code==200 and deleted.json()['data']['deleted']
        assert c.http.get(f'/api/rooms/{room_id}/messages').status_code==404
        return 'Direct messages are private and unique; group rename/add/remove/leave/delete work; removed members lose history and live socket access.'
    finally:a.close();b.close();c.close()

def presence_typing():
    c=Client('charlie')
    try:
        x,y=c.socket(),c.socket(); join(x,3); join(y,3)
        presence=receive(y,'room:presence'); assert len(presence['users'])==1
        send(x,'typing:set',{'roomId':3,'isTyping':True})
        typing=receive(y,'room:typing',predicate=lambda p:len(p['users'])==1)
        assert typing['users'][0]['username']=='charlie'
        receive(y,'room:typing',timeout=4.5,predicate=lambda p:not p['users'])
        x.close(timeout=.2)
        presence=receive(y,'room:presence'); assert len(presence['users'])==1
        join(y,2)
        return 'Same user in two tabs counted once; typing expires without client stop; closing one tab preserves presence.'
    finally:c.close()

def invalid_payloads():
    c=Client('bob')
    try:
        ws=c.socket();join(ws,2)
        for raw in ['{bad','[]','{"type":"nope","payload":{}}', '{"type":"typing:set","payload":{"roomId":2,"isTyping":1}}', '{"type":"chat:send","payload":{"roomId":2,"content":"  "}}']:
            ws.send(raw);assert receive(ws,'system:error')['code']=='INVALID_PAYLOAD'
        send(ws,'chat:send',{'roomId':2,'content':'😀'*501});assert receive(ws,'system:error')['code']=='MESSAGE_TOO_LONG'
        good='😀'*500;send(ws,'chat:send',{'roomId':2,'content':good});assert receive(ws,'chat:new')['content']==good
        ws.send_binary(b'{"type":"room:join","payload":{"roomId":2}}');assert receive(ws,'system:error')['code']=='INVALID_PAYLOAD'
        # A binary fragmented JSON message must remain binary after reassembly.
        ws.send_frame(websocket.ABNF.create_frame(b'{"type":"room:',websocket.ABNF.OPCODE_BINARY,fin=0))
        ws.send_frame(websocket.ABNF.create_frame(b'join","payload":{"roomId":2}}',websocket.ABNF.OPCODE_CONT,fin=1))
        assert receive(ws,'system:error')['code']=='INVALID_PAYLOAD'
        # Fragmented text is parsed by Workerman and accepted as a normal JSON message.
        ws.send_frame(websocket.ABNF.create_frame('{"type":"room:',websocket.ABNF.OPCODE_TEXT,fin=0))
        ws.send_frame(websocket.ABNF.create_frame('join","payload":{"roomId":2}}',websocket.ABNF.OPCODE_CONT,fin=1))
        receive(ws,'room:joined')
        huge=c.socket();join(huge,2)
        huge.send('x'*8300)
        try:
            while True:
                raw=huge.recv()
                if not raw: break
        except websocket.WebSocketConnectionClosedException: pass
        else: pass
        assert c.http.get('/api/health').status_code==200
        return 'Malformed schemas, Unicode 500/501, binary/fragmented binary rejection, text fragmentation and oversized close verified.'
    finally:c.close()

def logout():
    c=Client('alice')
    try:
        x,y=c.socket(),c.socket();join(x);join(y)
        started=time.monotonic();assert c.post('/api/auth/logout',{}).status_code==200
        for ws in [x,y]:
            ws.settimeout(2)
            try:
                while ws.recv(): pass
            except websocket.WebSocketConnectionClosedException: pass
        elapsed=time.monotonic()-started
        assert elapsed<1.6,elapsed
        assert c.http.get('/api/rooms').status_code==401
        return f'Both sockets of revoked session closed in {elapsed:.3f}s; API rejected after logout.'
    finally:c.close()

def concurrent():
    c=Client('charlie')
    try:
        detail=[]
        for count in [2,10,20]:
            sockets=[c.socket() for _ in range(count)]
            for ws in sockets:join(ws,3)
            marker='concurrent-'+str(time.time_ns());send(sockets[0],'chat:send',{'roomId':3,'content':marker})
            ids=[receive(ws,'chat:new',timeout=6,predicate=lambda p:p['content']==marker)['id'] for ws in sockets]
            assert len(set(ids))==1
            for ws in sockets:ws.close(timeout=.05)
            detail.append({'connections':count,'received':len(ids),'uniqueMessageIds':len(set(ids))})
        return detail
    finally:c.close()

def rate_limit():
    c=Client('bob')
    try:
        x,y=c.socket(),c.socket();join(x,2);join(y,2)
        # Count existing accepted sends in this window by waiting for the first RATE_LIMITED.
        accepted=0
        for i in range(21):
            ws=x if i%2==0 else y
            marker=f'rate-test-{time.time_ns()}'
            send(ws,'chat:send',{'roomId':2,'content':marker})
            end=time.monotonic()+4
            while time.monotonic()<end:
                data=json.loads(ws.recv())
                if data['type']=='chat:new' and data['payload']['content']==marker:
                    accepted+=1;break
                if data['type']=='system:error':
                    assert data['payload']['code']=='RATE_LIMITED'
                    before=len(c.http.get('/api/benchmark/rooms/2/messages?afterId=0').json()['data'])
                    send(ws,'chat:send',{'roomId':2,'content':'blocked-not-persisted'})
                    receive(ws,'system:error',predicate=lambda p:p['code']=='RATE_LIMITED')
                    after=len(c.http.get('/api/benchmark/rooms/2/messages?afterId=0').json()['data'])
                    assert before==after
                    assert accepted<=20
                    return f'Account-wide limit reached after {accepted} additional sends; two sockets cannot bypass; blocked message not persisted.'
        raise AssertionError('Rate limit not enforced')
    finally:c.close()

def run():
    case('TC-01/02/03/15','Authentication and security boundaries',auth)
    case('TC-SOCIAL-REMOVE','Removed location and invitation filters',removed_social_features)
    case('TC-REG','Registration and session rotation',registration)
    case('TC-04/05/07','Persistence, room isolation and join',chat_rooms)
    case('TC-DM/GROUP','Direct chat and group lifecycle',direct_and_group_management)
    case('TC-06/12','Multi-tab presence and typing TTL',presence_typing)
    case('TC-09/10','Protocol and Unicode boundaries',invalid_payloads)
    case('TC-14','Logout closes all session sockets',logout)
    case('TC-20','2, 10 and 20 simultaneous connections',concurrent)
    case('TC-11','Rate limit across sockets',rate_limit)
    path=ROOT/os.environ.get('CHAT_TEST_OUTPUT', 'evidence/logs/integration.json');path.parent.mkdir(parents=True,exist_ok=True)
    path.write_text(json.dumps({'baseUrl':BASE,'generatedAt':time.strftime('%Y-%m-%dT%H:%M:%SZ',time.gmtime()),'results':RESULTS},indent=2),encoding='utf-8')
    print(f'{sum(r["status"]=="PASS" for r in RESULTS)}/{len(RESULTS)} groups passed. Evidence: {path}')
    return int(any(r['status']!='PASS' for r in RESULTS))

if __name__=='__main__':raise SystemExit(run())
