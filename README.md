# Sell-Shop-SPU — Hệ thống bán hàng (Project README)

Tài liệu này mô tả kiến trúc, lý thuyết, cách cài đặt và vận hành dự án Sell-Shop-SPU (PHP + MySQL). Hướng dẫn phù hợp cho môi trường phát triển local (XAMPP/WAMP) và staging.

## Mục tiêu hệ thống
- Trang bán hàng đơn giản: danh sách sản phẩm, giỏ hàng, wishlist, voucher, thanh toán (VNPAY), bảng quản trị cơ bản.
- Thiết kế không dùng framework nặng — dễ đọc, mở rộng nhanh cho mục đích học tập và thử nghiệm.

## Kiến trúc & luồng dữ liệu (high level)

- Trình duyệt (Client)
  - UI HTML/PHP (trong `template/`) + CSS/JS từ `public/`.
  - Thực hiện các thao tác: thêm/xóa giỏ hàng (AJAX), đăng nhập, áp voucher, gọi trang thanh toán.
- Webserver (PHP)
  - Controllers (`controller/`) nhận request từ client, xử lý logic nghiệp vụ, gọi các model.
  - Models (`model/`) truy xuất MySQL thông qua `model/m_database.php`.
  - Templates (`template/`) render HTML, bao gồm component như header/footer. Chatbot UI nằm trong `template/chatBubble.php`.
- Database (MySQL)
  - Lưu sản phẩm, khách hàng, đơn hàng, voucher, wishlist, các bảng liên quan.
- External services
  - VNPAY (thanh toán) — các file trong `vnpay_php/` xử lý redirect/notify.
  - Chatbot proxy (Node) — `chatbot/` (tùy chọn) để gọi model AI an toàn.

Luồng đơn hàng ngắn gọn: Client → `controller/c_thanhToan.php` (tính toán server-side) → tạo hoá đơn vào DB → nếu trả VNPAY thì redirect/confirm.

## Lý thuyết & quyết định thiết kế
- Server-side authoritative: tất cả tính toán quan trọng (tổng tiền, phí ship, voucher) PHẢI được tính trên server, không tin dữ liệu từ client.
- Hạn chế SQL injection: hiện code dùng chuỗi SQL ở một số nơi — cần chuyển sang prepared statements `mysqli_prepare` trong `model/*`.
- XSS: escape mọi dữ liệu user-driven trước khi echo ra HTML (use `htmlspecialchars($s, ENT_QUOTES, 'UTF-8')`).
- Uploads: lưu file an toàn, validate MIME, không cho thực thi mã upload.

## Thư mục chính
- `controller/` — các script xử lý request (ví dụ `c_thanhToan.php`, `c_signUp.php`)
- `model/` — truy vấn DB và logic dữ liệu
- `template/` — layout và component (header/footer/chat bubble)
- `public/` — `CSS/`, `JS/`, `image/`
- `vnpay_php/` — tích hợp VNPAY
- `install/` — các script SQL và `install.bat`

## Cài đặt (Windows/XAMPP)

1) Chuẩn bị môi trường
   - Cài XAMPP (Apache + MySQL + PHP). Khởi chạy Apache và MySQL.
   - Đặt project vào `c:\xampp\htdocs\sell-shop-SPU`.

2) Cài Database tự động (script)
   - Mở `install/install.bat` (Windows) và chạy nó. Script sẽ import các file SQL cần thiết.
   - Nếu muốn chạy thủ công: import `install/full_schema.sql` hoặc `install/create_db.sql` bằng phpMyAdmin hoặc MySQL client.

3) Cấu hình kết nối DB
   - Mở file `model/m_database.php` và kiểm tra cấu hình host/user/password/dbName. Mặc định thường là `localhost`, `root`, `""`.

4) Cấu hình VNPAY (nếu dùng)
   - Mở `vnpay_php/config.php` và cập nhật `vnp_TmnCode`, `vnp_HashSecret`, `vnp_Returnurl` theo thông tin sandbox/merchant.

5) (Tùy chọn) Chatbot proxy
   - Nếu bạn muốn bật chatbot, vào folder `chatbot/`, chỉnh `.env` (set `GEMINI_API_KEY`, `GEMINI_MODEL`), chạy `npm install` rồi `node server.js`.

6) Mở site
   - Truy cập `http://localhost/sell-shop-SPU/` trong trình duyệt.

## `install/install.bat` (mô tả)
- Script này thực thi lệnh MySQL import cho các file SQL trong `install/`. Trước khi chạy, điều chỉnh thông tin kết nối (user/password) nếu không dùng root không password.
- Nếu script không chạy (quyền admin), bạn có thể import SQL bằng phpMyAdmin.

## Các lệnh hữu ích
- Import SQL thủ công:
```powershell
mysql -u root -p < install\\full_schema.sql
```
- Chạy server Node chatbot (nếu dùng):
```bash
cd chatbot
npm install
node server.js
```

## Kiểm tra an ninh cơ bản (check-list)
- Chuyển query nhạy cảm sang prepared statements (`mysqli_prepare`).
- Thực hiện `htmlspecialchars()` khi echo các giá trị từ user.
- Thêm CSRF token cho form admin và endpoint thay đổi dữ liệu.
- Kiểm tra upload path và disable PHP execution trong thư mục upload.

## Triển khai & vận hành
- Không commit file cấu hình chứa mật khẩu hoặc key.
- Thiết lập backup DB định kỳ.
- Giới hạn quyền DB của tài khoản ứng dụng (không dùng root cho ứng dụng production).

## Test & Dev notes
- Có một số file test trong `tests/` (ví dụ `test_wishlist_model_only.php`) — dùng để kiểm tra model wishlist độc lập.
- Logs DB được ghi trong `logs/` — kiểm tra khi gặp lỗi SQL.

## Các bước ưu tiên tiếp theo (gợi ý)
- 1) Xây dựng migration/convert toàn bộ query sang prepared statements.
- 2) Thêm escape đầu ra và kiểm tra XSS.
- 3) Thêm xác thực/CSRF cho admin controllers.
- 4) Thêm unit/integration tests cho checkout + voucher flows.

---
Nếu bạn muốn, tôi sẽ: tạo script migration để thay các query sang prepared statements, hoặc bắt đầu vá file `product_detail.php` trước vì nơi đó có dấu hiệu bị tấn công SQLi — bạn muốn tôi làm bước nào tiếp theo?


