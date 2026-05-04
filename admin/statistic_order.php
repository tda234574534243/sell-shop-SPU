<?php include "../template/sidebar.php"; ?>
<?php require_once "../controller/c_donhang.php"; ?>
<?php
function orderStatusBadgeClass($status) {
    if ($status === 'Đã giao hàng') return 'bg-success';
    if ($status === 'Đang giao hàng') return 'bg-primary';
    if ($status === 'Đang chuẩn bị hàng') return 'bg-warning text-dark';
    return 'bg-secondary';
}
?>
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
                        <input type="text" name="ma_hd" placeholder="Mã đơn hàng..." value="<?= htmlspecialchars($_GET['ma_hd'] ?? '') ?>" class="form-control">
                    </div>
                    <!-- Tên tài khoản -->
                    <div class="col-md-3">
                        <label class="form-label">Tài khoản</label>
                        <input type="text" name="ten_tk" placeholder="Tên tài khoản..." value="<?= htmlspecialchars($_GET['ten_tk'] ?? '') ?>" class="form-control">
                    </div>

                    <!-- Tên sản phẩm -->
                    <div class="col-md-3">
                        <label class="form-label">Sản phẩm</label>
                        <input type="text" name="ten_sp" placeholder="Tên sản phẩm..." value="<?= htmlspecialchars($_GET['ten_sp'] ?? '') ?>" class="form-control">
                    </div>

                    <!-- Ngày mua (Từ ngày - Đến ngày) -->
                    <div class="col-md-3">
                        <label class="form-label">Từ ngày</label>
                        <input type="date" name="ngay_tu" value="<?= htmlspecialchars($_GET['ngay_tu'] ?? '') ?>" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Đến ngày</label>
                        <input type="date" name="ngay_den" value="<?= htmlspecialchars($_GET['ngay_den'] ?? '') ?>" class="form-control">
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
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                        <?php while ($row = $lich_su->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['MaHD'] ?></td>
                                <td><?= htmlspecialchars($row['TenTK']) ?></td>
                                <td><?= htmlspecialchars($row['TenSP']) ?></td>
                                <td><?= $row['SoLuong'] ?></td>
                                <td><?= number_format($row['TongTien'], 0, ',', '.') ?> ₫</td>
                                <td><?= $row['NgayMua'] ?></td>
                                <td>
                                    <span class="badge <?= orderStatusBadgeClass($row['State']) ?>"><?= htmlspecialchars($row['State']) ?></span>
                                </td>
                                <td>
                                    <form method="POST" action="../controller/c_donhang.php" class="d-flex gap-2 align-items-center">
                                        <input type="hidden" name="action" value="updateStatus">
                                        <input type="hidden" name="ma_hd" value="<?= htmlspecialchars($row['MaHD']) ?>">
                                        <input type="hidden" name="ma_sp" value="<?= htmlspecialchars($row['MaSP']) ?>">
                                        <select name="state" class="form-select form-select-sm">
                                            <option value="Đang chuẩn bị hàng" <?= $row['State'] === 'Đang chuẩn bị hàng' ? 'selected' : '' ?>>Đang chuẩn bị hàng</option>
                                            <option value="Đang giao hàng" <?= $row['State'] === 'Đang giao hàng' ? 'selected' : '' ?>>Đang giao hàng</option>
                                            <option value="Đã giao hàng" <?= $row['State'] === 'Đã giao hàng' ? 'selected' : '' ?>>Đã giao hàng</option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-secondary">Cập nhật</button>
                                    </form>
                                    <div class="mt-2">
                                        <a href="../controller/c_exportInvoice.php?ma_hd=<?= urlencode($row['MaHD']) ?>&action=pdf" class="btn btn-sm btn-danger" target="_blank" title="Xuất PDF">
                                            <i class="fa-solid fa-file-pdf"></i> PDF
                                        </a>
                                        <a href="../controller/c_exportInvoice.php?ma_hd=<?= urlencode($row['MaHD']) ?>&action=csv" class="btn btn-sm btn-success" title="Xuất CSV">
                                            <i class="fa-solid fa-file-csv"></i> CSV
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                </tbody>
            </table>
        </div>

       <!-- Phân trang -->
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= ($current_page == 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=1&ten_tk=<?= rawurlencode($_GET['ten_tk'] ?? '') ?>&ten_sp=<?= rawurlencode($_GET['ten_sp'] ?? '') ?>&ngay_tu=<?= rawurlencode($_GET['ngay_tu'] ?? '') ?>&ngay_den=<?= rawurlencode($_GET['ngay_den'] ?? '') ?>" aria-label="First">
                        <span aria-hidden="true">&laquo;&laquo;</span>
                    </a>
                </li>
                <li class="page-item <?= ($current_page == 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= max(1, $current_page - 1) ?>&ten_tk=<?= rawurlencode($_GET['ten_tk'] ?? '') ?>&ten_sp=<?= rawurlencode($_GET['ten_sp'] ?? '') ?>&ngay_tu=<?= rawurlencode($_GET['ngay_tu'] ?? '') ?>&ngay_den=<?= rawurlencode($_GET['ngay_den'] ?? '') ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&ten_tk=<?= rawurlencode($_GET['ten_tk'] ?? '') ?>&ten_sp=<?= rawurlencode($_GET['ten_sp'] ?? '') ?>&ngay_tu=<?= rawurlencode($_GET['ngay_tu'] ?? '') ?>&ngay_den=<?= rawurlencode($_GET['ngay_den'] ?? '') ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= ($current_page == $total_pages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= min($total_pages, $current_page + 1) ?>&ten_tk=<?= rawurlencode($_GET['ten_tk'] ?? '') ?>&ten_sp=<?= rawurlencode($_GET['ten_sp'] ?? '') ?>&ngay_tu=<?= rawurlencode($_GET['ngay_tu'] ?? '') ?>&ngay_den=<?= rawurlencode($_GET['ngay_den'] ?? '') ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
                <li class="page-item <?= ($current_page == $total_pages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $total_pages ?>&ten_tk=<?= rawurlencode($_GET['ten_tk'] ?? '') ?>&ten_sp=<?= rawurlencode($_GET['ten_sp'] ?? '') ?>&ngay_tu=<?= rawurlencode($_GET['ngay_tu'] ?? '') ?>&ngay_den=<?= rawurlencode($_GET['ngay_den'] ?? '') ?>" aria-label="Last">
                        <span aria-hidden="true">&raquo;&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>


    </div>
</div>
<?php include "../template/script_footer.php"; ?>