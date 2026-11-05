"""Build a source archive without dependencies, secrets or runtime databases."""
from pathlib import Path
import zipfile,hashlib,json,argparse

ROOT=Path(__file__).resolve().parents[1]
EXCLUDE={'.git','.env','vendor','node_modules','storage','tmp','output','submission','github-handoff','.playwright-cli','.phpunit.cache','__pycache__'}

def source_archive(target):
 files=[]
 with zipfile.ZipFile(target,'w',zipfile.ZIP_DEFLATED) as out:
  for path in sorted(ROOT.rglob('*')):
   if not path.is_file():continue
   rel=path.relative_to(ROOT)
   if any(part in EXCLUDE for part in rel.parts):continue
   if path.suffix.lower() in ['.sqlite','.pyc','.zip','.dotx'] or path.name.endswith(('.sqlite-wal','.sqlite-shm')):continue
   if path.name=='ProjectPlan-WebSockets-Redesigned.docx':continue
   out.write(path,rel.as_posix());files.append(rel.as_posix())
  out.writestr('storage/.gitkeep','')
 return files

def main():
 parser=argparse.ArgumentParser();parser.add_argument('--output',default='submission/source.zip');args=parser.parse_args()
 target=ROOT/args.output;target.parent.mkdir(parents=True,exist_ok=True)
 files=source_archive(target)
 print(f'Source package: {target}; {len(files)} files; sha256 {hashlib.sha256(target.read_bytes()).hexdigest()}')

if __name__=='__main__':main()
