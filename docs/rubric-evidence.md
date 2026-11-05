# Đối chiếu tiêu chí và minh chứng

Thang điểm gốc chia báo cáo 6 điểm, demo 4 điểm. Bảng là vị trí minh chứng, không phải điểm đã chấm. Sau Việt hóa dùng mục/chương thay cho số trang cũ. Thông tin cá nhân/cơ sở đào tạo để trống theo yêu cầu.

| Tiêu chí | Điểm | Vị trí báo cáo | Minh chứng kỹ thuật | Slide | Video cuối |
| --- | --- | --- | --- | --- | --- |
| R1 Hiểu lý thuyết | 2 | Chương 2, thiết kế chương 3 | references.md, sơ đồ, protocol-capture.json | 2, 3, 5, 6 | Chờ lời giải thích/mốc thật |
| R2 Phân tích so sánh | 1 | 2.8, 4.8–4.9, 5.2 | benchmark-summary.json, sáu lượt, dữ liệu nhàn rỗi, biểu đồ | 4, 10, 11 | Chờ giải thích đã ghi |
| R3 Ứng dụng/triển khai | 1 | Chương 3–4 | Hợp đồng, schema, mã nguồn, JUnit, tích hợp, trình duyệt, runtime, cài sạch | 5–9 | Chờ demo trực tiếp |
| R4 Cấu trúc/trình bày báo cáo | 2 | Toàn bộ Word/PDF | report-review.md, bản xuất/kết xuất hình | Bộ slide hỗ trợ | Điền bìa thật và chỉnh mẫu khi cần |
| D1 Phạm vi/chi tiết | 2 | Chương 4 | Nguồn + README, 28 PHPUnit, 7 nhóm tích hợp cũ, kiểm tra giao diện mới riêng | 7–11 | Chờ video nhóm có âm thanh |
| D2 Demo rõ ràng | 1 | Hình, trình tự chương 2–4 | Ảnh ứng dụng thật, chụp giao thức đã lược bí mật, bộ ghi hình | 1–12 | Điền mốc sau thu thật |
| D3 Kỹ năng trình bày/video | 1 | Không chứng minh bằng báo cáo | Lời nói/câu hỏi đã chuẩn bị | Ghi chú A/B/C | Chờ ba thành viên thật, tối đa 20 phút |

## Kết quả so sánh đã ghi ngày 22/10/2026

Mỗi kênh nhận có ba lượt 120 giây, mỗi lượt nhận 20/20 tin, không timeout/lỗi được ghi. Trung vị polling một giây: 452,95–504,52 ms; WS: 17,63–23,73 ms. Tổng 360 yêu cầu nhận HTTP trong giai đoạn hoạt động. Byte chỉ tính JSON, hai bên khác lớp sự kiện; máy chạy chung phát triển. Không suy rộng thành hiệu năng, băng thông hoặc sức chứa production.

## Điều kiện còn thiếu

Nguồn/báo cáo/slide không thay video có âm thanh. Theo rubric gốc, thiếu video demo có lời hoặc nguồn đầy đủ kèm hướng dẫn cài thì phần demo có thể nhận 0. Video im lặng chỉ là tư liệu phụ. Tài liệu tiếng Việt được tạo theo yêu cầu người dùng; cần đối chiếu quy định tiếng Anh trong đề gốc trước khi nộp video chính thức.
