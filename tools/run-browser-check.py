"""Capture Playwright CLI's structured result without storing browser secrets."""
from pathlib import Path
import argparse,json,subprocess,shutil,sys

ROOT=Path(__file__).resolve().parents[1]
parser=argparse.ArgumentParser();parser.add_argument('script');parser.add_argument('--session',default='relay-bob');parser.add_argument('--output',default='evidence/logs/browser-acceptance.json');args=parser.parse_args()
command=[shutil.which('npx.cmd') or shutil.which('npx'),'--yes','--package','@playwright/cli','playwright-cli',f'-s={args.session}','run-code',f'--filename={args.script}']
r=subprocess.run(command,cwd=ROOT,capture_output=True,text=True,encoding='utf-8',errors='replace')
if r.returncode or '### Error' in r.stdout:
 print(r.stdout);print(r.stderr);raise SystemExit(1)
result_text=r.stdout.split('### Result\n',1)[1].lstrip()
data,_=json.JSONDecoder().raw_decode(result_text)
p=ROOT/args.output;p.parent.mkdir(parents=True,exist_ok=True);p.write_text(json.dumps(data,indent=2),encoding='utf-8')
print(json.dumps(data,indent=2))
