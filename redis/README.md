Redis & Email helpers (Sell Shop SPU)

Mô tả nhanh
- Thư mục `redis/` chứa helper và endpoint phục vụ xác thực email (verify) và OTP đặt lại mật khẩu (forgot/reset).
- Hỗ trợ Redis nếu extension có, ngược lại fallback lưu file tạm trong thư mục tạm hệ thống.

File chính
- `redis_helper.php` — wrapper cho Redis với fallback file (sys_get_temp_dir()).
- `email_helper.php` — helper gửi mail; dùng `redis/email_smtp_config.php` để gửi qua SMTP, fallback về `mail()` nếu tắt.
- `email_smtp_config.php` — cấu hình SMTP (host, port, username, password, encryption, from). Chứa credentials — KHÔNG commit.
- `verify.php` — xử lý link xác thực từ email.
- `forgot.php` — form yêu cầu gửi OTP.
- `request_otp.php` — tạo OTP, lưu key `otp:<md5(email)>` và gửi email.
- `reset_password.php` — form + handler đặt lại mật khẩu bằng OTP.
- `_resend_otp_cli.php` — tiện ích CLI để tạo/gửi OTP nhanh (dev).

Cấu hình
- Mở `redis/email_smtp_config.php` và đặt `username` + `password` (App Password cho Gmail) và `use_smtp` = `true` để gửi qua SMTP.
- Đặt `use_smtp` = `false` nếu muốn fallback về `mail()` (không khuyến nghị trên XAMPP).

TTL và key
- OTP: `otp:<md5(lowercase(email))>` — TTL mặc định 10 phút.
- Verify token: `verify:<token>` — TTL mặc định 24 giờ.

Kiểm thử (dev)
1) CLI: chạy `c:\xampp\php\php.exe redis\_resend_otp_cli.php` để tạo và gửi OTP tới email trong config.
2) Web: sign-in -> Quên mật khẩu -> nhập email -> hệ thống gọi `request_otp.php`.

Bảo mật
- KHÔNG commit file chứa mật khẩu SMTP. Thêm `redis/email_smtp_config.php` vào `.gitignore`.
- Dùng App Password cho Gmail (khuyên dùng khi bật 2FA).

Gợi ý khắc phục lỗi gửi mail
- Nếu không nhận mail: kiểm tra `email_smtp_config.php` (username/password), port và encryption, hoặc dùng MailHog để debug.
- Khi dùng fallback file, OTP lưu tại file `sys_get_temp_dir()/redis_fallback_<md5(key)>`.

Tiếp theo đề xuất
- Thêm `resend_verify.php` để gửi lại link xác thực.
- Thêm rate-limit cho `request_otp.php` để chống lạm dụng.

Ghi chú
- Đây là helper đơn giản cho môi trường dev/staging; đối với production, cân nhắc queue + thư viện mail chuyên dụng.
