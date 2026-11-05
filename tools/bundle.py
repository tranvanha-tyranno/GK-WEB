"""Đóng gói bản bàn giao với tài liệu tiếng Việt và mã băm thực tế."""
from pathlib import Path
import hashlib,json,re,subprocess,zipfile
from package import source_archive,ROOT
OUT=ROOT/'submission'

def main():
    source=OUT/'source.zip';source_archive(source)
    required=['report.docx','report.pdf','slides.pptx','slides.pdf','source.zip','reference-walkthrough.webm']
    for name in required:
        assert (OUT/name).is_file() and (OUT/name).stat().st_size>0,name
    info=subprocess.run(['pdfinfo',str(OUT/'report.pdf')],capture_output=True,text=True,check=True).stdout
    pages=int(re.search(r'^Pages:\s+(\d+)',info,re.M)[1])
    files=[{'file':name,'bytes':(OUT/name).stat().st_size,'sha256':hashlib.sha256((OUT/name).read_bytes()).hexdigest()} for name in required]
    manifest={'version':'vietnamese-ui-2','startedDate':'2026-10-28','date':'2026-10-29','timezone':'Asia/Saigon','language':'vi','reportPages':pages,'slides':12,'completedTaskPackages':21,'totalTaskPackages':28,'finalTeamVideo':'CHƯA GHI HÌNH','referenceVideo':{'file':'reference-walkthrough.webm','seconds':34.52,'audio':False,'isFinalSubmissionVideo':False,'description':'Tư liệu giao diện cũ, giữ nguyên làm tham khảo; không phải video mới hoặc video nhóm cuối.'},'officialUpload':'CHƯA THỰC HIỆN','identities':'ĐỂ TRỐNG THEO YÊU CẦU','layout':'Bố cục chỉnh sửa được; người dùng có thể áp dụng mẫu lớp sau','validation':'evidence/logs/vietnamese-validation.json','files':files}
    (OUT/'manifest.json').write_text(json.dumps(manifest,ensure_ascii=False,indent=2)+'\n',encoding='utf-8')
    lines=['# Danh mục bàn giao tiếng Việt','','Bản cập nhật bắt đầu 28/10 và hoàn tất 29/10/2026 (Asia/Saigon). Tài liệu và ứng dụng đã cập nhật; nộp chính thức và video ba thành viên thật vẫn cần người dùng thực hiện.','','| Sản phẩm | Trạng thái | Nội dung |','| --- | --- | --- |',f'| report.docx / report.pdf | ĐÃ TẠO | {pages} trang tiếng Việt, 5 chương, tài liệu tham khảo và phụ lục; bìa để trống thông tin thật |','| slides.pptx / slides.pdf | ĐÃ TẠO | 12 trang, văn bản/bảng chỉnh sửa được, ghi chú tiếng Việt, ảnh giao diện mới |','| source.zip | ĐÃ TẠO | Mã, schema/seed, khóa thư viện, Docker, README tiếng Việt, test và minh chứng; không có .env, vendor hoặc DB thực |','| recording-kit | ĐÃ TẠO | Lời nói, thao tác và câu hỏi tiếng Việt; đối chiếu ngôn ngữ theo đề gốc khi nộp |','| reference-walkthrough.webm | TƯ LIỆU CŨ BỔ TRỢ | 34,52 giây, im lặng; giao diện bản cũ, không thay video nhóm cuối |','| demo.mp4 có ba thành viên thật | CHƯA GHI HÌNH | Kiểm tra âm thanh, quy định ngôn ngữ và thời lượng tối đa 20 phút |','| Họ tên / mã sinh viên / lớp / giảng viên | NGƯỜI DÙNG ĐIỀN | Để trống có chủ đích, chỉnh theo mẫu thật khi cần |','| Nộp chính thức / biên nhận / xác nhận đóng góp | CHƯA THỰC HIỆN | Không tự tạo sự tham gia hoặc xác nhận |','','## Kiểm chứng','','Xem `evidence/logs/vietnamese-validation.json`: kết quả PHP, tích hợp HTTP/WS, giao diện, đăng nhập, hồi quy trình duyệt và kiểm tra tài liệu. Log ngày 22/10/2026 được giữ là lịch sử, không gắn thành phép chạy mới. Sáu lượt đo và dữ liệu thô cũ giữ nguyên; không chạy lại chỉ để đổi ngôn ngữ.','','Giới hạn: một worker, SQLite đồng bộ, khôi phục 50 tin, tìm/xuất chỉ tin đã tải, chưa kiểm chứng TLS công khai. Bản nháp trong thẻ trình duyệt không phải lưu trữ máy chủ.','','## Toàn vẹn tệp','','| Tệp | Byte | SHA256 |','| --- | --- | --- |']
    for f in files:lines.append(f'| `{f["file"]}` | {f["bytes"]} | `{f["sha256"]}` |')
    (OUT/'manifest.md').write_text('\n'.join(lines)+'\n',encoding='utf-8')
    bundle=OUT/'relay-handoff.zip'
    with zipfile.ZipFile(bundle,'w',zipfile.ZIP_DEFLATED) as out:
        for name in ['README.md','manifest.md','manifest.json']:out.write(OUT/name,name)
        for name in ['report.docx','report.pdf']:out.write(OUT/name,'report/'+name)
        for name in ['slides.pptx','slides.pdf']:out.write(OUT/name,'slides/'+name)
        out.write(OUT/'reference-walkthrough.webm','video/reference-walkthrough.webm')
        for p in (OUT/'recording-kit').rglob('*'):
            if p.is_file():out.write(p,p.relative_to(OUT).as_posix())
        with zipfile.ZipFile(source) as src:
            for name in src.namelist():out.writestr('source/'+name,src.read(name))
    with zipfile.ZipFile(bundle) as check:
        assert check.testzip() is None
        assert not any(p.endswith('/.env') or '/vendor/' in p or p.endswith('.sqlite') for p in check.namelist())
    print(f'Gói bàn giao: {bundle}; {bundle.stat().st_size} byte; báo cáo {pages} trang.')

if __name__=='__main__':main()
