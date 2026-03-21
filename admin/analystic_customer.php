<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../public/CSS/style.css">
    <link rel="stylesheet" href="../public/CSS/base.css">
</head>
<?php
require_once '../controller/c_khachhang.php';
?>
<?php include ('../template/toastMess.php') ?>
<?php include "../template/sidebar.php"; ?>
<div class="bg-light flex-fill">
    <div id="mainContent" class="p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold">Quản lý khách hàng</h4>
        </div>

        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-dark text-white">
                <i class="fa-solid fa-filter"></i> Bộ lọc
            </div>
            <div class="card-body">
                <form class="row g-3" method="GET" action="">
                    <div class="col-md-3">
                        <label class="form-label">Tên tài khoản</label>
                        <input type="text" class="form-control" name="ten_khach" placeholder="Tên tài khoản..." value="<?= $_GET['ten_khach'] ?? '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" placeholder="Email..." value="<?= $_GET['email'] ?? '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Số điện thoại</label>
                        <input type="text" class="form-control" name="sdt" placeholder="Số điện thoại..." value="<?= $_GET['sdt'] ?? '' ?>">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-dark w-100" type="submit"><i class="fa fa-filter"></i> Áp dụng</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bảng dữ liệu khách hàng -->
        <div class="table-responsive" style="border-radius: 10px;">
            <table class="table table-striped table-hover table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Mã tài khoản</th>
                        <th>Tên tài khoản</th>
                        <th>Email</th>
                        <th>Số điện thoại</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($khachHangs['khachHangs'])): ?>
                        <?php foreach ($khachHangs['khachHangs'] as $khachHang): ?>
                            <tr>
                                <td><?= $khachHang['MaTK'] ?></td>
                                <td><?= $khachHang['TenTK'] ?></td>
                                <td><?= $khachHang['Email'] ?></td>
                                <td><?= $khachHang['SDT'] ?></td>
                                <td>
                                    <!-- <a href="sua_khach_hang.php?id=<?= $khachHang['MaTK'] ?>" class="btn btn-warning btn-sm">Sửa</a> -->
                                    <a href="?action=xoa&id=<?= $khachHang['MaTK'] ?>&ten_khach=<?= $_GET['ten_khach'] ?? '' ?>&email=<?= $_GET['email'] ?? '' ?>&sdt=<?= $_GET['sdt'] ?? '' ?>&page=<?= $page ?>" 
                                    class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa khách hàng này?');">
                                        Xóa
                                    </a>

                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">Không có khách hàng nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Phân trang -->
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= ($page == 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=1&ten_khach=<?= $_GET['ten_khach'] ?? '' ?>&email=<?= $_GET['email'] ?? '' ?>&sdt=<?= $_GET['sdt'] ?? '' ?>" aria-label="First">
                        <span aria-hidden="true">&laquo;&laquo;</span>
                    </a>
                </li>
                <li class="page-item <?= ($page == 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page - 1 ?>&ten_khach=<?= $_GET['ten_khach'] ?? '' ?>&email=<?= $_GET['email'] ?? '' ?>&sdt=<?= $_GET['sdt'] ?? '' ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <?php for ($i = 1; $i <= $khachHangs['total_pages']; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&ten_khach=<?= $_GET['ten_khach'] ?? '' ?>&email=<?= $_GET['email'] ?? '' ?>&sdt=<?= $_GET['sdt'] ?? '' ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= ($page == $khachHangs['total_pages']) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page + 1 ?>&ten_khach=<?= $_GET['ten_khach'] ?? '' ?>&email=<?= $_GET['email'] ?? '' ?>&sdt=<?= $_GET['sdt'] ?? '' ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
                <li class="page-item <?= ($page == $khachHangs['total_pages']) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $khachHangs['total_pages'] ?>&ten_khach=<?= $_GET['ten_khach'] ?? '' ?>&email=<?= $_GET['email'] ?? '' ?>&sdt=<?= $_GET['sdt'] ?? '' ?>" aria-label="Last">
                        <span aria-hidden="true">&raquo;&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>

    </div>
</div>
<script src="main.js"></script>
<?php include "../template/script_footer.php"; ?>
