"""Build a Word-importable HTML report from the reviewed Vietnamese source.

DOCX/PDF export is performed by native Word in export-report.ps1 on Windows.
"""
from pathlib import Path
import re,json,html,statistics

ROOT=Path(__file__).resolve().parents[1]
OUT=ROOT/'tmp/report-build';OUT.mkdir(parents=True,exist_ok=True)

def inline(text):
 text=html.escape(text)
 text=re.sub(r'`([^`]+)`',r'<code>\1</code>',text)
 text=re.sub(r'\*\*([^*]+)\*\*',r'<strong>\1</strong>',text)
 return text

def block(markdown):
 lines=markdown.strip().splitlines();out=[];paragraph=[];i=0
 def flush():
  if paragraph:out.append('<p>'+inline(' '.join(paragraph))+'</p>');paragraph.clear()
 while i<len(lines):
  line=lines[i].strip()
  if not line:flush();i+=1;continue
  if line.startswith('#'):
   flush();m=re.match(r'(#+) (.*)',line);level=min(len(m[1]),3);out.append(f'<h{level}>'+inline(m[2])+f'</h{level}>');i+=1;continue
  image=re.fullmatch(r'!\[(.*?)\]\((.*?)\)',line)
  if image:
   flush();src=(ROOT/'docs'/image[2]).resolve()
   from PIL import Image
   with Image.open(src) as im:w,h=im.size
   # A deliberate print size preserves readability and leaves space for analysis.
   max_width,max_height=(620,420) if 'sequence' in src.name or 'message-flow' in src.name else (620,360)
   if 'mobile' in src.name:max_height=410
   scale=min(max_width/w,max_height/h)
   out.append(f'<p class="figure"><img src="{src.as_uri()}" width="{round(w*scale)}" height="{round(h*scale)}"></p><p class="caption">{inline(image[1])}</p>')
   i+=1;continue
  if line.startswith('|'):
   flush();rows=[]
   while i<len(lines) and lines[i].strip().startswith('|'):
    cells=[x.strip() for x in lines[i].strip().strip('|').split('|')]
    if not all(re.fullmatch(r':?-+:?',c) for c in cells):rows.append(cells)
    i+=1
   out.append('<table>')
   for n,row in enumerate(rows):
    tag='th' if n==0 else 'td';out.append('<tr>'+''.join(f'<{tag}>'+inline(c)+f'</{tag}>' for c in row)+'</tr>')
   out.append('</table>');continue
  paragraph.append(line);i+=1
 flush();return '\n'.join(out)

def bench():
 p=ROOT/'evidence/raw/benchmark-summary.json'
 if not p.exists():return '<p>Chưa có kết quả đo hoàn chỉnh; không tự tạo số liệu.</p>'
 data=json.loads(p.read_text());runs=data['runs']
 md='| Kênh / lượt | Nhận / gửi | Trung vị ms | p95 ms | Yêu cầu HTTP | Byte JSON nhận |\n| --- | --- | --- | --- | --- | --- |\n'
 for r in runs:md+=f"| {r['transport']} / {r['run']} | {r['received']} / {r['sent']} | {r['medianMs']:.2f} | {r['p95Ms']:.2f} | {r['httpRequests']} | {r['applicationJsonBytesReceived']} |\n"
 md+='\nCả sáu lượt nhận đủ 20 tin, không có lỗi máy chủ hoặc timeout được ghi. Mỗi cửa sổ đo hoạt động dài 120 giây. Byte JSON không gồm khung/tiêu đề; WS nhận thêm trạng thái phòng nên chưa phải so sánh băng thông cùng nội dung.\n\n'
 if data.get('idle'):
  md+='| Kênh nhàn rỗi | Thời lượng giây | Yêu cầu HTTP | Sự kiện ứng dụng WS |\n| --- | --- | --- | --- |\n'
  for r in data['idle']:md+=f"| {r['transport']} | {r['durationSeconds']} | {r['httpRequests']} | {r['websocketApplicationEvents']} |\n"
 return block(md)

def main():
 source=(ROOT/'docs/report.md').read_text(encoding='utf-8')
 parts=source.split('<!-- PAGE -->')
 intro=parts.pop(0)
 abstract=intro.split('## Tóm tắt',1)[1]
 cover='''<div class="cover"><p class="course">LẬP TRÌNH WEB VÀ ỨNG DỤNG 503073</p><h1>Relay Trò chuyện bằng PHP và WebSocket</h1><h2>WebSocket và giao tiếp thời gian thực</h2><p>Học kỳ 1<br>Năm học 2026 2027</p><p class="cover-space">Thành viên nhóm __________________________________<br><br>Mã sinh viên _____________________________________<br><br>Lớp và nhóm _________________________________<br><br>Giảng viên ______________________________________</p><p>Tháng 11 năm 2026</p></div>'''
 toc='<h1>Mục lục</h1><p>Tóm tắt</p>'+''.join(f'<p>Chương {n} {title}</p>' for n,title in [(1,'Giới thiệu'),(2,'Cơ sở lý thuyết và phân tích so sánh'),(3,'Yêu cầu và thiết kế hệ thống'),(4,'Triển khai kiểm thử và đánh giá'),(5,'Kết luận và hướng phát triển')])+'<p>Tài liệu tham khảo</p><p>Phụ lục A Tái lập và minh chứng</p><h2>Danh mục hình</h2>'
 for title in re.findall(r'!\[(.*?)\]',source):toc+='<p>'+inline(title)+'</p>'
 units=[cover,'<h1>Tóm tắt</h1>'+block(abstract),toc]
 for part in parts:
  rendered=block(part.replace('<!-- BENCHMARK_RESULTS -->','BENCHMARK_PLACEHOLDER'))
  rendered=rendered.replace('<p>BENCHMARK_PLACEHOLDER</p>',bench())
  units.append(rendered)
 css='''@page{size:A4;margin:19mm 20mm 18mm 22mm}body{font-family:"Times New Roman",serif;font-size:13pt;line-height:1.5;color:#181818}h1{font-size:18pt;line-height:1.12;margin:0 0 14pt;color:black;page-break-after:avoid}h2{font-size:14pt;line-height:1.15;margin:6pt 0 12pt;color:black;page-break-after:avoid}h3{font-size:12pt;color:black}p{margin:0 0 9pt;text-align:justify}table{width:100%;border-collapse:collapse;font-size:10.5pt;line-height:1.25;margin:10pt 0 12pt}td,th{border:1px solid #d9d9d9;padding:6pt 5pt;vertical-align:middle}th{background:#edf0f6;text-align:left;font-weight:bold}tr{page-break-inside:avoid}code{font-family:Consolas,monospace;font-size:9pt;overflow-wrap:anywhere}.unit{page-break-before:always}.first{page-break-before:auto}.figure{text-align:center;margin:7pt 0;page-break-inside:avoid}.figure img{max-width:100%}.caption{font-size:8.5pt;text-align:center;color:#555;margin:4pt 0 12pt;page-break-before:avoid}.cover{text-align:center;padding-top:60pt}.cover h1{font-size:30pt;margin:45pt 0 18pt}.cover h2{font-size:19pt}.cover p{text-align:center;line-height:1.6}.cover .course{font-size:12pt}.cover-space{margin:55pt 0 50pt}'''
 # Word HTML import propagates div page-break rules to child paragraphs. Put
 # the rule on only the first heading of each unit to avoid spurious pages.
 chunks=[]
 for i,unit in enumerate(units):
  if 0 < i <= 3:unit=re.sub(r'<h([123])>',r'<h\1 style="page-break-before:always">',unit,count=1)
  chunks.append(unit)
 doc='<!doctype html><html lang="vi"><head><meta charset="utf-8"><title>Relay Trò chuyện bằng PHP và WebSocket</title><style>'+css+'</style></head><body>'+''.join(chunks)+'</body></html>'
 (OUT/'report.html').write_text(doc,encoding='utf-8')
 print('Report HTML:',len(units),'designed page units;',len(source.split()),'source words.')

if __name__=='__main__':main()
