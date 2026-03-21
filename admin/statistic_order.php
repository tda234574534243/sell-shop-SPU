<?php include "../template/sidebar.php"; ?>
<?php require_once "../controller/c_donhang.php"; ?>
<div class="bg-light flex-fill">
    <div id="mainContent" class="p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold">Quản lý đơn hàng</h4>
        </div>

        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-dark text-white">
                <i class="fa-solid fa-filter"></i> Bộ lọc
            </div>
            <div class="card-body">
                <form class="row g-3" method="GET" action="">
                    <!-- Mã đơn hàng -->
                    <div class="col-md-3">
                        <label class="form-label">Mã đơn hàng</label>
                        <input type="text" name="ma_hd" placeholder="Mã đơn hàng..." value="<?= $_GET['ma_hd'] ?? '' ?>" class="form-control">
                    </div>
                    <!-- Tên tài khoản -->
                    <div class="col-md-3">
                        <label class="form-label">Tài khoản</label>
                        <input type="text" name="ten_tk" placeholder="Tên tài khoản..." value="<?= $_GET['ten_tk'] ?? '' ?>" class="form-control">
                    </div>

                    <!-- Tên sản phẩm -->
                    <div class="col-md-3">
                        <label class="form-label">Sản phẩm</label>
                        <input type="text" name="ten_sp" placeholder="Tên sản phẩm..." value="<?= $_GET['ten_sp'] ?? '' ?>" class="form-control">
                    </div>

                    <!-- Ngày mua (Từ ngày - Đến ngày) -->
                    <div class="col-md-3">
                        <label class="form-label">Từ ngày</label>
                        <input type="date" name="ngay_tu" value="<?= $_GET['ngay_tu'] ?? '' ?>" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Đến ngày</label>
                        <input type="date" name="ngay_den" value="<?= $_GET['ngay_den'] ?? '' ?>" class="form-control">
                    </div>

                    <!-- Nút lọc -->
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-dark w-100">
                            <i class="fa fa-filter"></i> Áp dụng
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Nội dung bảng -->
        <div class="table-responsive" style="border-radius: 10px;">
            <table class="table table-striped table-hover table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Mã đơn hàng</th>
                        <th>Tên tài khoản</th>
                        <th>Tên sản phẩm</th>
                        <th>Số lượng</th>
                        <th>Tổng tiền</th>
                        <th>Ngày mua</th>
                    </tr>
                </thead>
                <tbody>
                    <tbody>
                        <?php while ($row = $lich_su->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['MaHD'] ?></td>
                                <td><?= htmlspecialchars($row['TenTK']) ?></td>
                                <td><?= htmlspecialchars($row['TenSP']) ?></td>
                                <td><?= $row['SoLuong'] ?></td>
                                <td><?= number_format($row['TongTien'], 0, ',', '.') ?> ₫</td>
                                <td><?= $row['NgayMua'] ?></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>

                </tbody>
            </table>
        </div>

       <!-- Phân trang -->
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= ($current_page == 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=1&ten_tk=<?= $_GET['ten_tk'] ?? '' ?>&ten_sp=<?= $_GET['ten_sp'] ?? '' ?>&ngay_tu=<?= $_GET['ngay_tu'] ?? '' ?>&ngay_den=<?= $_GET['ngay_den'] ?? '' ?>" aria-label="First">
                        <span aria-hidden="true">&laquo;&laquo;</span>
                    </a>
                </li>
                <li class="page-item <?= ($current_page == 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= max(1, $current_page - 1) ?>&ten_tk=<?= $_GET['ten_tk'] ?? '' ?>&ten_sp=<?= $_GET['ten_sp'] ?? '' ?>&ngay_tu=<?= $_GET['ngay_tu'] ?? '' ?>&ngay_den=<?= $_GET['ngay_den'] ?? '' ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&ten_tk=<?= $_GET['ten_tk'] ?? '' ?>&ten_sp=<?= $_GET['ten_sp'] ?? '' ?>&ngay_tu=<?= $_GET['ngay_tu'] ?? '' ?>&ngay_den=<?= $_GET['ngay_den'] ?? '' ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= ($current_page == $total_pages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= min($total_pages, $current_page + 1) ?>&ten_tk=<?= $_GET['ten_tk'] ?? '' ?>&ten_sp=<?= $_GET['ten_sp'] ?? '' ?>&ngay_tu=<?= $_GET['ngay_tu'] ?? '' ?>&ngay_den=<?= $_GET['ngay_den'] ?? '' ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
                <li class="page-item <?= ($current_page == $total_pages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $total_pages ?>&ten_tk=<?= $_GET['ten_tk'] ?? '' ?>&ten_sp=<?= $_GET['ten_sp'] ?? '' ?>&ngay_tu=<?= $_GET['ngay_tu'] ?? '' ?>&ngay_den=<?= $_GET['ngay_den'] ?? '' ?>" aria-label="Last">
                        <span aria-hidden="true">&raquo;&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>


    </div>
</div>
<?php include "../template/script_footer.php"; ?>