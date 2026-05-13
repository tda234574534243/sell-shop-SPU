# VNPay PHP Integration

Bản này mô tả cách cài đặt và sử dụng thư mục `vnpay_php` để tích hợp thanh toán VNPay trong dự án PHP.

## Mục lục
- Giới thiệu
- Yêu cầu
- Cài đặt
- Cấu hình
- Sử dụng (mẫu)
- Kiểm thử
- Gỡ lỗi & hỗ trợ

## Giới thiệu
Thư mục `vnpay_php` chứa mã mẫu và helper để gửi yêu cầu thanh toán đến cổng VNPay và xử lý callback/return. README này cung cấp hướng dẫn nhanh để bạn tích hợp với hệ thống đặt hàng của mình.

## Yêu cầu
- PHP 7.2+
- Mở `curl` extension
- Tài khoản VNPay (lấy `vnp_TmnCode`, `vnp_HashSecret`, và URL endpoint) để chạy môi trường thật.

## Cài đặt
1. Sao chép thư mục `vnpay_php` vào dự án (nếu chưa có).
2. Đặt các biến cấu hình VNPay trong file cấu hình của bạn hoặc tạo file `vnp_config.php`:

```php
return [
    'vnp_TmnCode'   => 'YOUR_TMNCODE',
    'vnp_HashSecret' => 'YOUR_HASH_SECRET',
    'vnp_Url'        => 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html',
    'vnp_ReturnUrl'  => 'https://your-domain.com/vnpay_return.php',
];
```

## Cấu hình
- `vnp_TmnCode`: Mã website do VNPay cấp.
- `vnp_HashSecret`: Khóa bí mật để ký dữ liệu.
- `vnp_Url`: Endpoint VNPay (sandbox hoặc production).
- `vnp_ReturnUrl`: URL xử lý kết quả giao dịch (do bạn định nghĩa).

## Sử dụng (mẫu)
1. Khi tạo đơn hàng, xây dựng tham số yêu cầu theo tài liệu VNPay, ký bằng `vnp_HashSecret`, và chuyển hướng người dùng tới `vnp_Url`.

Ví dụ đơn giản (gợi ý):

```php
$config = include __DIR__ . '/vnp_config.php';
$data = [
    'vnp_Version' => '2.1.0',
    'vnp_TmnCode' => $config['vnp_TmnCode'],
    'vnp_Amount' => $amount * 100,
    'vnp_Command' => 'pay',
    'vnp_CreateDate' => date('YmdHis'),
    'vnp_CurrCode' => 'VND',
    'vnp_TxnRef' => $orderId,
    'vnp_OrderInfo' => 'Payment for order ' . $orderId,
    'vnp_ReturnUrl' => $config['vnp_ReturnUrl'],
    // ... các tham số khác
];

// 1) Sắp xếp param theo key, tạo chuỗi để ký
// 2) Sinh `vnp_SecureHash` bằng HMAC SHA512 với `vnp_HashSecret`
// 3) Chuyển hướng tới `$config['vnp_Url'] . '?' . http_build_query($data)` (bao gồm `vnp_SecureHash`)
```

2. Xử lý trả về (Return/Callback):
- Kiểm tra `vnp_SecureHash` nhận được so với giá trị tính lại.
- Kiểm tra `vnp_ResponseCode` để xác định trạng thái giao dịch.
- Cập nhật trạng thái đơn hàng nếu giao dịch hợp lệ.

## Kiểm thử
- Sử dụng endpoint sandbox của VNPay và thông tin test do VNPay cung cấp.
- Kiểm tra signatures (HMAC SHA512) luôn đúng để tránh chấp nhận giao dịch giả mạo.

## Gỡ lỗi & hỗ trợ
- Logs: lưu các tham số nhận được từ VNPay để so sánh khi debug.
- Nếu chữ ký không hợp lệ: kiểm tra `vnp_HashSecret` và thứ tự/sort key khi tạo chuỗi ký.
- Tham khảo tài liệu chính thức của VNPay để biết chi tiết các tham số và mã lỗi.

## Ghi chú
- Không commit `vnp_HashSecret` vào kho công khai.
- Ở môi trường production, sử dụng HTTPS và kiểm soát chặt chẽ URL callback.

---

Nếu bạn muốn, tôi có thể: thêm file mẫu `vnpay_create_payment.php` và `vnpay_return.php` trong `vnpay_php/` để triển khai mẫu hoàn chỉnh.