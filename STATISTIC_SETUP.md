# Hướng Dẫn Setup Hệ Thống Thống Kê

## ✅ Các Tính Năng Mới

### 1. **Thống Kê Doanh Thu** (`/admin/analystic_general.php`)
- Hiển thị tổng doanh thu tháng hiện tại
- So sánh % thay đổi với tháng trước
- Biểu đồ doanh thu cả năm
- Đơn hàng, sản phẩm, khách hàng

### 2. **Chi Tiết Doanh Thu** (`/admin/revenue_detail.php`)
- Chọn tháng/năm xem chi tiết
- Top 10 sản phẩm bán chạy
- Thống kê theo từng sản phẩm
- Xếp hạng sản phẩm (🥇 🥈 🥉)

### 3. **Quản Lý User Online** (`/admin/users_online.php`)
- Xem danh sách user đang online (30 phút gần đây)
- Thời gian đăng nhập & hoạt động gần đây
- Địa chỉ IP
- Làm mới tự động (auto-refresh)

---

## 🔧 Cách Setup

### Bước 1: Tạo Bảng Database
Trước tiên, chạy file setup để tạo các bảng cần thiết:

```
1. Mở trình duyệt
2. Truy cập: http://your-domain/sell-shop-SPU/setup_statistic.php
3. Sẽ hiển thị thông báo "✅ Thành công!" khi hoàn thành
```

**Sau khi chạy xong, bạn có thể xóa file `setup_statistic.php` để bảo mật.**

### Bước 2: Cấp Quyền Truy Cập
Các trang này chỉ admin mới có thể truy cập:
- Kiểm tra bảng `Account` (cột `MaLV`, giá trị = 1 là admin)

### Bước 3: Update Link Menu
Thêm link vào sidebar admin nếu muốn:

```html
<!-- Doanh Thu -->
<li><a href="analystic_general.php">📊 Thống Kê Chung</a></li>
<li><a href="revenue_detail.php">💰 Chi Tiết Doanh Thu</a></li>

<!-- User Online -->
<li><a href="users_online.php">👥 Người Online</a></li>
```

---

## 📊 Sơ Đồ Dữ Liệu

### Bảng `users_online`
```
- id: INT (Primary Key)
- username: VARCHAR(100) UNIQUE
- session_id: VARCHAR(255)
- ip_address: VARCHAR(50)
- login_time: TIMESTAMP
- last_activity: TIMESTAMP (tự cập nhật)
```

### Bảng `page_views`
```
- id: INT (Primary Key)
- page_slug: VARCHAR(255)
- ip_address: VARCHAR(50)
- timestamp: TIMESTAMP
```

---

## 🔄 Cách Hoạt Động

### Tracking User Online
- Mỗi lần user truy cập trang, `user_tracking.php` sẽ ghi nhận
- `last_activity` được cập nhật tự động
- User được coi là "online" nếu hoạt động trong 30 phút

**Được bao gồm tự động trong:** `template/script_footer.php`

### Tracking Page Views (Optional)
- Nhận xét dòng `$stat->trackPageView($pageSlug);` nếu muốn bật
- Sẽ ghi nhận tất cả page view

---

## 🔌 Sử Dụng Model `M_statistic`

### Lấy Doanh Thu
```php
$stat = new M_statistic();

// Doanh thu tháng hiện tại
$revenue = $stat->getMonthlyRevenue();

// Doanh thu tháng cụ thể
$revenue = $stat->getMonthlyRevenue(3, 2026); // Tháng 3 năm 2026

// Doanh thu cả năm (array)
$yearly = $stat->getYearlyRevenue(2026);
```

### Quản Lý User Online
```php
// Ghi nhận user online
$stat->registerUserOnline($username);

// Lấy danh sách user online
$users = $stat->getOnlineUsers(); // MySQLi Result

// Số user online
$count = $stat->getOnlineUserCount(); // Int

// Xóa user offline
$stat->cleanupOfflineUsers();
```

### Top Sản Phẩm
```php
// Top 10 sản phẩm bán chạy
$result = $stat->getTopProducts(10);
while ($row = $result->fetch_assoc()) {
    echo $row['TenSP'] . ": " . $row['total_qty'] . " cái";
}
```

---

## ✨ Tính Năng Nâng Cao

### 1. Auto-Refresh Users Online
Trên trang `/admin/users_online.php`, click nút "Bật làm mới tự động" để tự động làm mới danh sách

### 2. Lọc Doanh Thu Theo Tháng/Năm
Trên trang `/admin/revenue_detail.php`, chọn tháng/năm rồi click "Cập nhật"

### 3. So Sánh Doanh Thu
Tự động so sánh với tháng trước → hiển thị % tăng/giảm

---

## 🛡️ Bảo Mật

- ✅ Chỉ admin (MaLV = 1) mới truy cập được thống kê
- ✅ Session check tự động
- ✅ SQL Injection protection: Sử dụng `$model->real_escape_string()`
- ✅ User offline tự động xóa sau 30 phút không hoạt động

---

## 🐛 Troubleshooting

### "Table doesn't exist" Error
→ Chạy `setup_statistic.php` để tạo bảng

### User online không hiển thị
→ Kiểm tra `last_activity` >= 30 phút trước
→ Chạy `$stat->cleanupOfflineUsers()` để xóa offline

### Doanh thu hiển thị 0
→ Kiểm tra dữ liệu trong bảng `LS_Mua` & `Products`
→ Kiểm tra cột tên: `MaHD`, `MaTK`, `MaSP`, `SoLuong`, `NgayMua`, `GiaTien`

---

## 📞 Liên Hệ

Nếu gặp vấn đề, kiểm tra:
1. File database logs (trong `/logs/`)
2. Quyền truy cập database
3. Tên cột trong bảng

---

**Chúc bạn sử dụng vui vẻ! 🚀**
