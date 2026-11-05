"""Synchronize actual evidence, task status and the handoff checklist."""
from pathlib import Path
import json, re

ROOT=Path(__file__).resolve().parents[1]
done={
'A02':['composer.json','Dockerfile','docs/contracts.md'],
'A03':['evidence/logs/phpunit.xml','evidence/logs/integration.json'],
'A04':['evidence/logs/integration.json','src/Http/Controllers/RoomController.php'],
'A05':['evidence/logs/clean-install.json','evidence/logs/runtime-verification.json','deploy/README.md'],
'A06':['README.md','tools/benchmark/run.py','evidence/logs/clean-install.json'],
'A07':['docs/report.md','docs/references.md','submission/report.docx'],
'A08':['submission/report.docx','submission/report.pdf'],
'B01':['resources/views/chat.php','public/assets/css/app.css','docs/report.md'],
'B02':['evidence/logs/browser-acceptance.json','public/assets/js/api.js'],
'B03':['evidence/logs/integration.json','evidence/logs/browser-acceptance.json'],
'B04':['evidence/logs/browser-acceptance.json','evidence/screenshots/EV-07-chat-mobile.png'],
'B05':['docs/comparison.md','evidence/raw/benchmark-summary.json'],
'B06':['docs/report.md','evidence/charts/delivery-delay.png'],
'B07':['submission/report.pdf','docs/report-review.md'],
'C01':['database/schema.sql','database/seed.php','evidence/logs/clean-install.json'],
'C02':['evidence/logs/phpunit.xml','src/Repositories/MessageRepository.php'],
'C03':['evidence/logs/integration.json','src/Realtime/Gateway.php'],
'C04':['evidence/logs/integration.json','src/Realtime/RoomHub.php'],
'C05':['evidence/logs/runtime-verification.json','evidence/logs/integration.json'],
'C06':['evidence/raw/benchmark-summary.json','evidence/logs/concurrent.json'],
'C07':['docs/report.md','evidence/diagrams/architecture.png','evidence/diagrams/erd.png'],
}
pending={
'A01':'Thông tin lớp/cơ sở đào tạo để trống theo yêu cầu; người dùng cần xác nhận hạn và đăng ký trình bày. Đã chuẩn bị kế hoạch, nguồn và bố cục.',
'A09':'Đã có ghi chú và câu hỏi tập luyện; thành viên thật cần thực hiện thuyết trình theo quy định môn học.',
'A10':'Đã có gói nguồn và danh mục; nộp chính thức và biên nhận là thao tác của người dùng, chưa thực hiện.',
'B08':'Đã có 12 slide chỉnh sửa được, PDF và lời trình bày; còn video có âm thanh ba thành viên thật.',
'B09':'Đã kiểm tra trình duyệt/bố cục; còn trình bày thật, video cuối và rà tệp đã tải lên.',
'C08':'Đã chuẩn bị cài sạch, test, bảng minh chứng và danh mục; xác nhận đóng góp phải do thành viên thật điền.',
'C09':'Đã có ghi chú thiết kế/test và kiểm tra kỹ thuật; còn trình bày thật và rà video cuối.',
}

def main():
 p=ROOT/'PHAN_CONG_PHP_3_NGUOI.md';s=p.read_text(encoding='utf-8')
 for task in done:
  assert all((ROOT/f).exists() for f in done[task]),(task,'missing evidence')
  s=re.sub(r'\| '+task+r'(?: \*\*\[x\] ĐÃ XONG\*\*)? \|','| '+task+' **[x] ĐÃ XONG** |',s)
 for task in pending:
  s=re.sub(r'\| '+task+r'(?: \*\*\[x\] ĐÃ XONG\*\*)? \|','| '+task+' **[ ] CÒN PHẦN NGƯỜI THẬT/THÔNG TIN LỚP** |',s)
 # A concrete README/runtime verification now supports the original checklist.
 for key in ['Tạo Dockerfile, Compose, Nginx proxy `/ws`','Viết test repository, validation, giới hạn gửi, broadcast/cô lập phòng;']:
  s=s.replace('- [ ] '+key,'- [x] '+key)
 s=s.replace('Template chính thức vẫn cần A lấy và kiểm tra; không tự khẳng định `Template_VN.dotx` là đúng bộ mẫu hiện hành của lớp.','**Theo chỉ dẫn mới của người dùng: dùng bố cục tự tạo, để trống thông tin nhóm/lớp/giảng viên để người dùng tự điền/chỉnh mẫu sau.** Không khẳng định bản này đã theo template chính thức của Khoa.')
 summary='\n\n### Trạng thái bàn giao thực tế\n\n'+f'**{len(done)}/28 gói đã hoàn thành và kiểm tra kỹ thuật/tài liệu.** Các gói còn lại có sản phẩm hỗ trợ nhưng giữ `[ ]` vì còn thông tin lớp, người thật trình bày/ghi hình, xác nhận đóng góp hoặc nộp bài. Người dùng đã yêu cầu để trống thông tin cá nhân và tự chỉnh template sau; không hỏi lại. Xem `docs/task-status.md`, `submission/manifest.md`.\n'
 if '### Trạng thái bàn giao thực tế' not in s:s+=summary
 p.write_text(s,encoding='utf-8')
 statuses={t:{'status':'done','verifiedBy':'Codex kiểm tra tự động và trực quan; xem ngày trong log gốc','evidence':ev} for t,ev in done.items()}
 statuses.update({t:{'status':'pending-human-or-institutional','reason':reason} for t,reason in pending.items()})
 (ROOT/'docs/task-status.json').write_text(json.dumps(statuses,indent=2,ensure_ascii=False),encoding='utf-8')
 lines=['# Theo dõi hoàn thành công việc','',f'{len(done)} /28 gói đã có minh chứng kỹ thuật/tài liệu. Bảy gói còn cần người thật hoặc thông tin lớp; các sản phẩm đã chuẩn bị vẫn được giữ.','', 'Thông tin cá nhân, lớp, giảng viên để trống theo yêu cầu. Người dùng tự chỉnh bố cục. Không tự tạo chữ ký, tham dự, thuyết trình hoặc xác nhận nộp.','', '| Gói | Trạng thái | Minh chứng hoặc phần còn thiếu |','| --- | --- | --- |']
 for t in sorted(statuses):
  row=statuses[t];content=', '.join('`'+f+'`' for f in row.get('evidence',[])) or row['reason'];lines.append(f'| {t} | {"[x] ĐÃ CÓ MINH CHỨNG" if t in done else "[ ] CẦN NGƯỜI THẬT"} | {content} |')
 (ROOT/'docs/task-status.md').write_text('\n'.join(lines)+'\n',encoding='utf-8')
 print(f'Đã đồng bộ {len(done)} gói có minh chứng và {len(pending)} gói còn phần người thật.')

if __name__=='__main__':main()
