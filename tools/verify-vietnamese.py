"""Chạy kiểm tra PHP và tổng hợp minh chứng cho bản tiếng Việt."""
from datetime import datetime
from pathlib import Path
from zoneinfo import ZoneInfo
import hashlib,json,subprocess,sys
ROOT=Path(__file__).resolve().parents[1]
commands={
    'phpLint':['docker','compose','exec','-T','app','composer','lint'],
    'phpUnit':['docker','compose','exec','-T','app','composer','test'],
    'composerManifest':['docker','compose','exec','-T','app','composer','validate','--strict'],
}
def execute(item):
    name,command=item
    result=subprocess.run(command,cwd=ROOT,capture_output=True,text=True,encoding='utf-8',errors='replace')
    return name,{'status':'PASS' if result.returncode==0 else 'FAIL','command':command,'exitCode':result.returncode,'output':(result.stdout+'\n'+result.stderr).strip()}
def main():
    checks=dict(execute(item) for item in commands.items())
    for name,filename in {'ui':'vietnamese-ui.json','login':'vietnamese-login.json','browserRegression':'vietnamese-browser.json','integration':'vietnamese-integration.json'}.items():
        result=json.loads((ROOT/'evidence/logs'/filename).read_text(encoding='utf-8'))
        cases=result.get('checks',result.get('results',[]))
        passed=result.get('status')=='PASS' or bool(cases) and all(not isinstance(row,dict) or row.get('status')=='PASS' for row in cases)
        checks[name]={'status':'PASS' if passed else 'FAIL','evidence':'evidence/logs/'+filename,'cases':len(cases),'checkedAt':result.get('checkedAt',result.get('generatedAt'))}
    source={p.relative_to(ROOT).as_posix():hashlib.sha256(p.read_bytes()).hexdigest() for folder in ['public','resources','src','bin','database'] for p in (ROOT/folder).rglob('*') if p.is_file()}
    report={'completedAt':datetime.now(ZoneInfo('Asia/Saigon')).isoformat(),'timezone':'Asia/Saigon','status':'PASS' if all(row['status']=='PASS' for row in checks.values()) else 'FAIL','checks':checks,'sourceSha256':source,'limits':['Một worker và SQLite ghi đồng bộ','Khôi phục tối đa 50 tin mới nhất','Tìm và xuất chỉ tin đã tải','Chưa kiểm chứng TLS công khai','Video thành viên thật và nộp chính thức chưa thực hiện']}
    (ROOT/'evidence/logs/vietnamese-validation.json').write_text(json.dumps(report,ensure_ascii=False,indent=2)+'\n',encoding='utf-8')
    for name,row in checks.items():print(name+': '+row['status'])
    return int(report['status']!='PASS')
if __name__=='__main__':raise SystemExit(main())
