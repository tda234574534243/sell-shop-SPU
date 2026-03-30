<?php include ('../template/toastMess.php') ?>
<?php include "../template/sidebar.php"; ?>
<?php
    require_once '../model/m_voucher.php';
    $m = new M_voucher();
    $res = $m->getAll(200);
?>
<?php include('../template/head.php');
$id = intval($_GET['id'] ?? 0);
$item = $id ? $m->getById($id) : null;
?>

<div class="bg-light flex-fill">
    <div id="mainContent" class="p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold">Quản lý voucher</h4>
            <a href="vouchers.php" class="btn btn-primary btn-sm">+ Tạo mới</a>
        </div>

        <div class="row">
            <!-- Form tạo/sửa voucher -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><?= $item ? 'Sửa voucher' : 'Tạo voucher mới' ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="../controller/c_voucher.php">
                            <input type="hidden" name="action" value="<?= $item ? 'edit' : 'add' ?>">
                            <?php if ($item): ?><input type="hidden" name="id" value="<?= $item['id'] ?>"><?php endif; ?>
                            
                            <div class="mb-3">
                                <label class="form-label">Code <span style="color:red;">*</span></label>
                                <input type="text" name="Code" class="form-control" value="<?= htmlspecialchars($item['Code'] ?? '') ?>" required>
                                <small class="text-muted">Mã voucher duy nhất</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Mô tả</label>
                                <textarea name="Description" class="form-control" rows="3"><?= htmlspecialchars($item['Description'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Giảm giá (%)</label>
                                <input type="number" name="DiscountPercent" class="form-control" min="0" max="100" value="<?= htmlspecialchars($item['DiscountPercent'] ?? '') ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Giảm tiền (VND)</label>
                                <input type="number" name="DiscountAmount" step="0.01" class="form-control" min="0" value="<?= htmlspecialchars($item['DiscountAmount'] ?? '') ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Số lượng</label>
                                <input type="number" name="Quantity" class="form-control" min="0" value="<?= htmlspecialchars($item['Quantity'] ?? '') ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Có hiệu lực từ</label>
                                <input type="date" name="ValidFrom" class="form-control" value="<?= htmlspecialchars($item['ValidFrom'] ?? '') ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Có hiệu lực đến</label>
                                <input type="date" name="ValidTo" class="form-control" value="<?= htmlspecialchars($item['ValidTo'] ?? '') ?>">
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save"></i> <?= $item ? 'Cập nhật' : 'Tạo mới' ?>
                                </button>
                                <?php if ($item): ?>
                                    <a href="vouchers.php" class="btn btn-outline-secondary">Huỷ</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Danh sách voucher -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-list"></i> Danh sách voucher
                            <?php if ($res && $res->num_rows > 0): ?>
                                <span class="badge badge-light"><?= $res->num_rows ?></span>
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">ID</th>
                                        <th>Code</th>
                                        <th>Mô tả</th>
                                        <th style="width: 80px;">Số lượng</th>
                                        <th style="width: 120px;">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($res && $res->num_rows > 0): 
                                        while ($row = $res->fetch_assoc()): 
                                    ?>
                                        <tr>
                                            <td><strong><?= $row['id'] ?></strong></td>
                                            <td><code><?= htmlspecialchars($row['Code']) ?></code></td>
                                            <td><small><?= htmlspecialchars(substr($row['Description'], 0, 40)) ?></small></td>
                                            <td><span class="badge badge-info"><?= intval($row['Quantity']) ?></span></td>
                                            <td>
                                                <a href="vouchers.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary" title="Sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="../controller/c_voucher.php?action=delete&id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn chắc chắn muốn xóa?')" title="Xóa">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php 
                                        endwhile; 
                                    else: 
                                    ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox"></i> Chưa có voucher nào
                                            </td>
                                        </tr>
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

<?php include('../template/script_footer.php'); ?>