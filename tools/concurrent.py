"""Ten-message functional fanout at each connection level; isolated test stack."""
import json,time,sys
from integration import Client,join,send,receive,ROOT

results=[]
for count,username in [(2,'alice'),(10,'bob'),(20,'charlie')]:
 c=Client(username)
 try:
  sockets=[c.socket() for _ in range(count)]
  for ws in sockets:join(ws,3)
  deliveries=0;ids=[]
  for n in range(10):
   marker=f'fanout-{count}-{n}-{time.time_ns()}'
   send(sockets[0],'chat:send',{'roomId':3,'content':marker})
   got=[receive(ws,'chat:new',timeout=8,predicate=lambda p:p['content']==marker)['id'] for ws in sockets]
   assert len(set(got))==1
   ids.append(got[0]);deliveries+=len(got)
  assert len(set(ids))==10
  result={'connections':count,'sent':10,'expectedDeliveries':count*10,'actualDeliveries':deliveries,'uniqueStoredIds':len(set(ids)),'status':'PASS'}
  results.append(result);print(json.dumps(result),flush=True)
 finally:c.close()
(ROOT/'evidence/logs/concurrent.json').write_text(json.dumps(results,indent=2),encoding='utf-8')
