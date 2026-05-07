# Sell-Shop-SPU

Một hệ thống cửa hàng bán hàng điện tử (sales page) đơn giản bằng PHP, sử dụng MySQL làm cơ sở dữ liệu và một số thư viện frontend phổ biến. Dự án được thiết kế để chạy trên môi trường local (XAMPP / LAMP / WAMP).

## Tổng quan
- Ngôn ngữ: PHP (code thuần, không dùng framework MVC lớn)
- Cơ sở dữ liệu: MySQL (kết nối bằng `mysqli`)
- Mục đích: website bán hàng (danh sách sản phẩm, giỏ hàng, thanh toán VNPAY, quản trị đơn giản)

## Tính năng chính
- Trang chủ với sản phẩm nổi bật và khu vực widget (page builder)
- Thêm/xóa/cập nhật giỏ hàng (session + AJAX)
- Danh sách yêu thích (wishlist)
- Hệ thống voucher
- Quản trị cơ bản (thống kê, quản lý vouchers, thông báo, page-builder)
- Tích hợp thanh toán VNPAY (thư mục `vnpay_php`)

## Công nghệ & Thư viện sử dụng

- Backend
  - PHP (sử dụng extension `mysqli` trong `model/m_database.php`)
  - cURL: dùng trong các file VNPAY (`vnpay_querydr.php`, `vnpay_refund.php`)

- Frontend
  - Bootstrap (CDN + local `public/CSS/bootstrap.min1.css`, `public/JS/bootstrap.min.js`)
  - jQuery (có `public/JS/jquery-3.5.1.slim.min.js`, nhưng nhiều mã JS dùng Fetch API thuần)
  - Font Awesome (qua CDN)
  - SweetAlert2 (qua CDN)
  - Google Fonts (Roboto)
  - Vanilla JS: `public/JS/main.js` chứa nhiều xử lý UI/AJAX

- Thanh toán
  - VNPAY sandbox (cấu hình tại `vnpay_php/config.php`)

## Cấu trúc thư mục chính

- `controller/` - xử lý form/endpoint (các hành động như thêm giỏ hàng, đăng nhập, wishlist...)
- `model/` - lớp truy cập dữ liệu (ví dụ `m_database.php`, `m_sanpham.php`, `m_account.php`...)
- `template/` - layout, header, footer, component (head.php, header.php,...)
- `public/` - tài nguyên tĩnh: `CSS/`, `JS/`, `DATA/`
- `vnpay_php/` - mã tích hợp VNPAY
- `install/` - tập tin SQL để tạo database (`full_schema.sql` ...)

## Cấu hình nhanh để chạy local

1. Cài XAMPP (Apache + MySQL + PHP) hoặc môi trường tương tự.
2. Đặt thư mục dự án vào `htdocs` (ví dụ `c:\xampp\htdocs\sell-shop-SPU`).
3. Tạo database: import `install/full_schema.sql` hoặc `create_db.sql` bằng phpMyAdmin hoặc mysql client.
4. Kiểm tra và tùy chỉnh cấu hình database (nếu cần):
   - Mặc định kết nối DB xem `model/m_database.php` (server=localhost, user=root, password="", dbName=`salespage`).
5. Cấu hình VNPAY (nếu dùng): cập nhật `vnpay_php/config.php` với `vnp_TmnCode`, `vnp_HashSecret`, `vnp_Returnurl` tương ứng (hiện đang để sandbox/origin là localhost).
6. Mở trình duyệt truy cập `http://localhost/sell-shop-SPU/`.

## Lưu ý vận hành & an ninh
- Input/output chưa có lớp ORM hay prepared statements đầy đủ; cần kiểm tra và chuyển sang prepared statements để tránh SQL injection.
- Một số file log lỗi DB được ghi vào thư mục `logs/`.
- Kiểm tra kỹ các endpoint AJAX (controller) trước khi đưa vào môi trường production.

## Tài liệu & tham khảo
- Mã liên quan VNPAY: `vnpay_php/`
- Cấu hình DB: `model/m_database.php`
- Tài nguyên frontend: `public/CSS/`, `public/JS/`

