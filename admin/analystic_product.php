<?php 
    ob_start();
    include "../template/sidebar.php"; 
    require_once "../controller/c_sanpham.php"; 
?>

<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../public/CSS/style.css">
    <link rel="stylesheet" href="../public/CSS/base.css">
</head>
<?php include('../template/toastMess.php') ?>

<div class="bg-light flex-fill">
    <div id="mainContent" class="p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold">Quản lý sản phẩm</h4>
        </div>
        <!-- Thêm sản phẩm -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-dark text-white">
                <i class="fa-solid"></i> Tạo sản phẩm mới
            </div>
            <div class="card-body">
                <form class="row g-3" method="POST" enctype="multipart/form-data">
                    <!-- Mã sản phẩm -->
                    <div class="col-md-3">
                        <label class="form-label">Mã sản phẩm</label>
                        <input type="text" class="form-control" name="masp" placeholder="Mã sản phẩm..." required>
                    </div>

                    <!-- Tên sản phẩm -->
                    <div class="col-md-3">
                        <label class="form-label">Tên sản phẩm</label>
                        <input type="text" class="form-control" name="tensp" placeholder="Tên sản phẩm..." required>
                    </div>

                    <!-- Nhà sản xuất -->
                    <div class="col-md-3">
                        <label class="form-label">Ngày sản xuất</label>
                        <input type="text" class="form-control" name="nsx" placeholder="Ngày sản xuất..." required>
                    </div>

                    <!-- Phân loại -->
                    <div class="col-md-3">
                        <label class="form-label">Phân loại</label>
                        <input type="text" class="form-control" name="phanloai" placeholder="Phân loại..." required>
                    </div>

                    <!-- Số lượng -->
                    <div class="col-md-3">
                        <label class="form-label">Số lượng</label>
                        <input type="number" class="form-control" name="soluong" placeholder="Số lượng..." required>
                    </div>

                    <!-- Giá -->
                    <div class="col-md-3">
                        <label class="form-label">Giá</label>
                        <input type="number" class="form-control" name="giatien" placeholder="Giá..." required>
                    </div>

                    <!-- Mô tả -->
                    <div class="col-md-3">
                        <label class="form-label">Mô tả</label>
                        <input type="text" class="form-control" name="mota" placeholder="Mô tả sản phẩm..." required>
                    </div>

                    <!-- Bảo hành -->
                    <div class="col-md-3">
                        <label class="form-label">Bảo hành</label>
                        <input type="text" class="form-control" name="baohanh" placeholder="Bảo hành..." required>
                    </div>

                    <!-- Ảnh sản phẩm -->
                    <div class="col-md-3">
                        <label class="form-label">Ảnh sản phẩm</label>
                        <input type="file" class="form-control" name="image" required>
                    </div>

                    <!-- Nút thêm -->
                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-success w-100" type="submit" name="add_product">
                            <i class="fa fa-plus-circle"></i> Tạo
                        </button>

                    </div>
                </form>
            </div>
        </div>


        <!-- Bộ lọc nâng cao -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-dark text-white">
                <i class="fa-solid fa-filter"></i> Bộ lọc
            </div>
            <div class="card-body">
                <form class="row g-3" method="GET" action="">
                    <!-- Mã sản phẩm -->
                    <div class="col-md-3">
                        <label class="form-label">Mã sản phẩm</label>
                        <input type="text" class="form-control" name="masp" placeholder="Mã sản phẩm..."
                            value="<?= $_GET['masp'] ?? '' ?>">
                    </div>

                    <!-- Tên sản phẩm -->
                    <div class="col-md-3">
                        <label class="form-label">Từ khóa</label>
                        <input type="text" class="form-control" name="keyword" placeholder="Tên sản phẩm..."
                            value="<?= $_GET['keyword'] ?? '' ?>">
                    </div>

                    <!-- Trạng thái -->
                    <div class="col-md-3">
                        <label class="form-label">Trạng thái</label>
                        <select class="form-select" name="status">
                            <option value="">-- Tất cả --</option>
                            <option value="1">Còn hàng</option>
                            <option value="0">Hết hàng</option>
                        </select>
                    </div>
                    <!-- Giá -->
                    <div class="col-md-3">
                        <label class="form-label">Giá</label>
                        <select class="form-select" name="sort_gia">
                            <option value="">Không sắp xếp</option>
                            <option value="gia_desc">Giá cao → thấp</option>
                            <option value="gia_asc">Giá thấp → cao</option>
                        </select>
                    </div>

                    <!-- Số lượng -->
                    <div class="col-md-3">
                        <label class="form-label">Số lượng</label>
                        <select class="form-select" name="sort_so_luong">
                            <option value="">Không sắp xếp</option>
                            <option value="so_luong_desc">Nhiều → ít</option>
                            <option value="so_luong_asc">Ít → nhiều</option>
                        </select>
                    </div>

                    <!-- Nút lọc -->
                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-dark w-100" type="submit">
                            <i class="fa fa-filter"></i> Áp dụng
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bảng dữ liệu -->
        <div class="table-responsive" style="border-radius: 10px;">
            <table class="table table-striped table-hover table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Mã sản phẩm</th> <!-- Thêm cột Mã sản phẩm -->
                        <th>Hình ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th>Giá</th>
                        <th>Số lượng</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ds_tren_trang as $sp): ?>
                    <tr>
                        <td><?= $sp['MaSP'] ?></td> <!-- Thêm hiển thị Mã sản phẩm -->
                        <td><img src=".<?= $sp['ImageSP'] ?>" width="60"></td>
                        <td><?= $sp['TenSP'] ?></td>
                        <td><?= number_format($sp['GiaTien'], 0, ',', '.') ?> VNĐ</td>
                        <td><?= $sp['SoLuong'] ?></td>
                        <td>
                            <?php if ($sp['SoLuong'] > 0): ?>
                            <span class="badge bg-success">Còn hàng</span>
                            <?php else: ?>
                            <span class="badge bg-danger">Hết hàng</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <!-- Nút sửa sản phẩm -->
                            <!-- Nút sửa sản phẩm (trong bảng) -->
                            <a href="?edit_masp=<?= $sp['MaSP'] ?>" class="btn btn-sm btn-warning">sửa</a>

                            <!-- Nút xóa sản phẩm -->
                            <form method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa sản phẩm này?');"
                                style="display:inline;">
                                <input type="hidden" name="delete_masp" value="<?= $sp['MaSP'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">xóa</button>
                            </form>

                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Phân trang -->
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= ($page == 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=1" aria-label="First">
                            <span aria-hidden="true">&laquo;&laquo;</span>
                        </a>
                    </li>
                    <li class="page-item <?= ($page == 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($page == $total_pages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                    <li class="page-item <?= ($page == $total_pages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $total_pages ?>" aria-label="Last">
                            <span aria-hidden="true">&raquo;&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Phần sửa sản phẩm (thay thế phần modal cũ) -->
            <?php if (isset($sanpham_can_sua)): ?>
            <div class="card mb-4 shadow-sm border-0" id="editFormSection">
                <div class="card-header bg-primary text-white">
                    <i class="fa-solid fa-pen-to-square"></i> Sửa sản phẩm
                </div>
                <div class="card-body">
                    <form class="row g-3" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="masp" value="<?= $sanpham_can_sua['MaSP'] ?>">

                        <div class="col-md-3">
                            <label class="form-label">Mã sản phẩm</label>
                            <input type="text" class="form-control" value="<?= $sanpham_can_sua['MaSP'] ?>" readonly>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Tên sản phẩm</label>
                            <input type="text" class="form-control" name="tensp"
                                value="<?= $sanpham_can_sua['TenSP'] ?>" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Nhà sản xuất</label>
                            <input type="text" class="form-control" name="nsx" value="<?= $sanpham_can_sua['NSX'] ?>"
                                required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Phân loại</label>
                            <input type="text" class="form-control" name="phanloai"
                                value="<?= $sanpham_can_sua['PhanLoai'] ?>" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Số lượng</label>
                            <input type="number" class="form-control" name="soluong"
                                value="<?= $sanpham_can_sua['SoLuong'] ?>" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Giá</label>
                            <input type="number" class="form-control" name="giatien"
                                value="<?= $sanpham_can_sua['GiaTien'] ?>" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Mô tả</label>
                            <input type="text" class="form-control" name="mota" value="<?= $sanpham_can_sua['MoTa'] ?>"
                                required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Bảo hành</label>
                            <input type="text" class="form-control" name="baohanh"
                                value="<?= $sanpham_can_sua['BaoHanh'] ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Ảnh sản phẩm</label>
                            <input type="file" class="form-control" name="image">
                            <small class="text-muted">Để trống nếu không muốn thay đổi ảnh</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Ảnh hiện tại</label>
                            <img src=".<?= $sanpham_can_sua['ImageSP'] ?>" width="100" class="d-block">
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary" name="update_product">Lưu thay đổi</button>
                            <a href="?" class="btn btn-secondary">Hủy</a>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
// Tự động cuộn xuống form sửa khi có sản phẩm cần sửa
document.addEventListener('DOMContentLoaded', function() {
    if (window.location.href.includes('edit_masp')) {
        const editSection = document.getElementById('editFormSection');
        if (editSection) {
            editSection.scrollIntoView({
                behavior: 'smooth'
            });
        }
    }
});
</script>
<?php include "../template/script_footer.php"; ?>