<?php include ('../template/toastMess.php') ?>
<?php include "../template/sidebar.php"; ?>
<?php
    require_once '../model/m_voucher.php';
    $m = new M_voucher();
    $res = $m->getAll(200);
?>
<?php include('../template/head.php');
require_once '../model/m_voucher.php';
$m = new M_voucher();
$id = intval($_GET['id'] ?? 0);
$item = $id ? $m->getById($id) : null;
?>



<div class="bg-light flex-fill">
    <div id="mainContent" class="p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold">Quản lý voucher</h4>
        </div>

<div class="container py-4">
    <h3><?= $item? 'Sửa voucher':'Tạo voucher mới' ?></h3>
    <form method="post" action="../controller/c_voucher.php">
        <input type="hidden" name="action" value="<?= $item? 'edit':'add' ?>">
        <?php if ($item): ?><input type="hidden" name="id" value="<?= $item['id'] ?>"><?php endif; ?>
        <div class="mb-3">
            <label>Code</label>
            <input name="Code" class="form-control" value="<?= htmlspecialchars($item['Code'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label>Mô tả</label>
            <textarea name="Description" class="form-control" rows="4"><?= htmlspecialchars($item['Description'] ?? '') ?></textarea>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3"><label>Giảm (%)</label><input name="DiscountPercent" class="form-control" value="<?= htmlspecialchars($item['DiscountPercent'] ?? '') ?>"></div>
            <div class="col-md-4 mb-3"><label>Giảm tiền</label><input name="DiscountAmount" class="form-control" value="<?= htmlspecialchars($item['DiscountAmount'] ?? '') ?>"></div>
            <div class="col-md-4 mb-3"><label>Số lượng</label><input name="Quantity" class="form-control" value="<?= htmlspecialchars($item['Quantity'] ?? '') ?>"></div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3"><label>Valid From</label><input type="date" name="ValidFrom" class="form-control" value="<?= htmlspecialchars($item['ValidFrom'] ?? '') ?>"></div>
            <div class="col-md-6 mb-3"><label>Valid To</label><input type="date" name="ValidTo" class="form-control" value="<?= htmlspecialchars($item['ValidTo'] ?? '') ?>"></div>
        </div>
        <button class="btn btn-primary">Lưu</button>
        <a href="vouchers.php" class="btn btn-secondary">Hủy</a>
    </form>
</div>


        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered">
                        <thead class="table-dark"><tr><th>ID</th><th>Code</th><th>Mô tả</th><th>Số lượng</th><th>Hành động</th></tr></thead>
                        <tbody>
                        <?php if ($res && $res !== false && $res->num_rows>0): while ($row = $res->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['Code']) ?></td>
                                <td><?= htmlspecialchars($row['Description']) ?></td>
                                <td><?= intval($row['Quantity']) ?></td>
                                <td>
                                    <a href="edit_voucher.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-secondary">Sửa</a>
                                    <a href="../controller/c_voucher.php?action=delete&id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Xóa?')">Xóa</a>
                                </td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="5" class="text-muted">Không có voucher hoặc lỗi kết nối CSDL.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
            </div>
        </div>
    </div>
</div>

