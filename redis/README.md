# Redis & Email helpers (Sell Shop SPU)

## Mục đích
- Thư mục `redis/` chứa các helper và endpoint nhỏ phục vụ 2 tính năng chính:
	1) Xác thực email khi đăng ký (verify)
	2) Đặt lại mật khẩu qua mã OTP (forgot / request_otp / reset_password)

## Đặc tính
- Hỗ trợ Redis thật nếu PHP `Redis` extension có; nếu không có, fallback lưu tạm dưới dạng file JSON trong thư mục tạm hệ thống (`sys_get_temp_dir()`).
```
Các file quan trọng
- `redis_helper.php` — wrapper cho Redis với fallback file.
- `email_helper.php` — helper gửi email, đọc cấu hình từ `email_smtp_config.php` (SMTP) và fallback về `mail()` nếu cần.
- `email_smtp_config.php` — file cấu hình SMTP (host, port, username, password, encryption, from). *KHÔNG lưu file này vào Git.*
- `verify.php` — endpoint xử lý link xác thực token.
- `forgot.php` — giao diện yêu cầu gửi OTP (UI).
- `request_otp.php` — tạo OTP, lưu key `otp:<md5(email)>` và gửi email.
- `reset_password.php` — giao diện + xử lý đặt lại mật khẩu bằng OTP.
- `resend_verify.php` — resend link xác thực (đã thêm).
- `_resend_otp_cli.php` — tiện ích CLI để nhanh chóng tạo/gửi OTP (mục đích dev).
```
## Cấu hình nhanh
1) Mở `redis/email_smtp_config.php` và điền thông tin SMTP của bạn (host, port, username, password, encryption). Sử dụng App Password cho Gmail nếu bật 2FA.
2) Đặt `'use_smtp' => true` để bật gửi qua SMTP. Nếu gặp khó khăn trên XAMPP, có thể tạm đặt `false` để dùng `mail()` (không khuyến nghị).

Key & TTL
- OTP key: `otp:<md5(lowercase(email))>` — TTL mặc định 10 phút.
- Verify token: `verify:<token>` — TTL mặc định 24 giờ.

## Kiểm thử (dev)
```
- Từ CLI (Windows):
	- `c:\xampp\php\php.exe redis\_resend_otp_cli.php` — tạo và gửi OTP đến email trong config, in OTP ra console.
- Từ web:
	- Truy cập `signIn.php` → "Quên mật khẩu" → nhập email → hệ thống gọi `request_otp.php` → bạn sẽ nhận mã OTP.
	- Đăng ký thử và kiểm tra email xác thực; nếu chưa nhận, dùng `resend_verify.php?email=you@example.com` để gửi lại.
```
## Bảo mật & vận hành
```
- Tuyệt đối **không commit** `redis/email_smtp_config.php` chứa mật khẩu lên SCM. Thêm file vào `.gitignore`.
- Dùng App Password cho Gmail (khi bật 2FA). Tránh dùng mật khẩu chính.
- Trên môi trường production, cân nhắc dùng queue (RabbitMQ, Redis + worker) và thư viện mail chuyên dụng (PHPMailer/Symfony Mailer).
```
## Khắc phục sự cố gửi mail
```
- Nếu email không đến:
	- Kiểm tra `redis/email_smtp_config.php` (username/password, port, encryption).
	- Kiểm tra kết nối tới SMTP server từ máy chủ (telnet smtp.gmail.com 587).
	- Tạm thời bật `use_smtp=false` để kiểm tra behavior của code.
	- Xem file tạm fallback: các file `redis_fallback_*` trong thư mục tạm hệ thống chứa giá trị OTP (trong môi trường dev).
```
## Hướng phát triển tiếp
```
- Thêm rate-limit cho `request_otp.php` (ví dụ: key `rl:otp:<ip>` trong Redis).
- Thêm `resend_verify` UI/flow (đã có endpoint).
- Ghi log chi tiết gửi mail để dễ debug (hoặc bật `mail.log` trong `php.ini`).
```
