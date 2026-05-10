
<?php include ('../template/toastMess.php') ?>
<?php include "../template/sidebar.php"; ?>
<?php
    // ensure notifications loaded
    require_once '../model/m_notification.php';
    $m = new M_notification();
    $res = $m->getActive(100, null, true); // admin: load all active notifications

    // load users for recipient selection
    require_once '../model/m_database.php';
    $db = new M_database();
    $usersRes = $db->getConnection()->query("SELECT MaTK, TenTK, Email FROM account ORDER BY TenTK ASC");
    $usersMap = [];
    if ($usersRes) {
        while ($u = $usersRes->fetch_assoc()) {
            $usersMap[intval($u['MaTK'])] = $u;
        }
    }

    // edit mode: load item if id provided
    $editId = intval($_GET['id'] ?? 0);
    $editItem = $editId ? $m->getById($editId) : null;
?>
<div class="bg-light flex-fill">
    <div id="mainContent" class="p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold">Quản lý thông báo</h4>
        </div>

        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-light">
                <strong><?= $editItem ? 'Sửa thông báo' : 'Tạo thông báo mới' ?></strong>
            </div>
            <div class="card-body">
                <form method="post" action="../controller/c_notification.php">
                    <input type="hidden" name="action" value="<?= $editItem ? 'edit' : 'add' ?>">
                    <?php if ($editItem): ?><input type="hidden" name="id" value="<?= $editItem['id'] ?>"><?php endif; ?>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Tiêu đề</label>
                            <input name="Title" class="form-control" value="<?= htmlspecialchars($editItem['Title'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Người nhận</label>
                            <select name="Recipient" class="form-select">
                                <option value="0" <?= (intval(
                                    $editItem['RecipientUserId'] ?? 0)===0)? 'selected':'' ?>>Tất cả người dùng</option>
                                <?php foreach ($usersMap as $uid => $u): ?>
                                    <option value="<?= $uid ?>" <?= (intval($editItem['RecipientUserId'] ?? 0)===$uid)? 'selected':'' ?>><?= htmlspecialchars(($u['TenTK']?: $u['Email'])) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nội dung</label>
                        <textarea name="Content" class="form-control" rows="4"><?= htmlspecialchars($editItem['Content'] ?? '') ?></textarea>
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" name="IsActive" id="isActive" class="form-check-input" <?= (!$editItem || $editItem['IsActive'])? 'checked':'' ?>>
                        <label for="isActive" class="form-check-label">Hoạt động</label>
                    </div>
                    <div>
                        <button class="btn btn-success btn-sm" type="submit"><?= $editItem ? 'Lưu thay đổi' : 'Tạo' ?></button>
                        <?php if ($editItem): ?>
                            <a href="notifications.php" class="btn btn-secondary btn-sm ms-2">Hủy</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered">
                        <thead class="table-dark"><tr><th>ID</th><th>Tiêu đề</th><th>Người nhận</th><th>Trạng thái</th><th>Hành động</th></tr></thead>
                        <tbody>
                        <?php if ($res && $res->num_rows>0): while ($row = $res->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['Title']) ?></td>
                                <td>
                                    <?php $rid = intval($row['RecipientUserId'] ?? 0); if ($rid===0): ?>
                                        Tất cả
                                    <?php else: ?>
                                        <?= htmlspecialchars($usersMap[$rid]['TenTK'] ?? ($usersMap[$rid]['Email'] ?? 'Người dùng #'.$rid)) ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= $row['IsActive']? 'Hoạt động':'Không hoạt động' ?></td>
                                <td>
                                    <a href="notifications.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i> Sửa</a>
                                    <a href="../controller/c_notification.php?action=delete&id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn chắc chắn muốn xóa?')"><i class="fas fa-trash"></i> Xóa</a>
                                </td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="5" class="text-muted">Không có thông báo.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../template/script_footer.php'); ?>
