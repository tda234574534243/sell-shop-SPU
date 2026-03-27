<?php
require_once '../model/m_account.php';
require_once '../controller/c_khachhang.php';
if (!isset($_GET['id'])) die('No id');
$id = intval($_GET['id']);
$acc = new M_account();
$result = $acc->getAccount($id);
if (!$result) die('User not found');
$user = $result->fetch_assoc();
include '../template/toastMess.php';
include "../template/sidebar.php";
?>
<div class="bg-light flex-fill">
    <div id="mainContent" class="p-4">
        <h4 class="fw-bold">Sửa thông tin người dùng (Admin)</h4>
        <div class="card card-body mt-3">
            <form action="../controller/c_khachhang.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="admin_update">
                <input type="hidden" name="id" value="<?= $user['MaTK'] ?>">
                <div class="row">
                    <div class="col-md-3">
                        <?php if (!empty($user['Avatar'])): ?>
                            <img src="<?= htmlspecialchars($user['Avatar']) ?>" style="width:100%;object-fit:cover;border-radius:6px;">
                        <?php else: ?>
                            <div style="width:100%;height:180px;background:#f5f7fb;display:flex;align-items:center;justify-content:center;color:#9aa4b2;">No Avatar</div>
                        <?php endif; ?>
                        <label class="form-label mt-2">Thay ảnh đại diện</label>
                        <input type="file" name="Avatar" accept="image/*" class="form-control">
                    </div>
                    <div class="col-md-9">
                        <div class="mb-2">
                            <label class="form-label">Tên tài khoản</label>
                            <input class="form-control" name="TenTK" value="<?= htmlspecialchars($user['TenTK']) ?>">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Email</label>
                            <input class="form-control" name="Email" value="<?= htmlspecialchars($user['Email']) ?>">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Số điện thoại</label>
                            <input class="form-control" name="SDT" value="<?= htmlspecialchars($user['SDT']) ?>">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Địa chỉ</label>
                            <textarea class="form-control" name="DiaChi"><?= htmlspecialchars($user['DiaChi']) ?></textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Level</label>
                            <select name="LevelID" class="form-select">
                                <option value="0" <?= ($user['LevelID']==0)?'selected':'' ?>>User</option>
                                <option value="1" <?= ($user['LevelID']==1)?'selected':'' ?>>Admin</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Khóa tài khoản</label>
                            <input type="hidden" name="Locked" value="0">
                            <input type="checkbox" name="Locked" value="1" <?= (isset($user['Locked']) && $user['Locked']==1)?'checked':'' ?> > Khóa
                        </div>
                        <hr>
                        <h6>Reset mật khẩu (Admin)</h6>
                        <div class="mb-2">
                            <label class="form-label">Mật khẩu mới (để trống nếu không đổi)</label>
                            <input class="form-control" name="NewPassword" type="password">
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="analystic_customer.php" class="btn btn-secondary me-2">Quay lại</a>
                            <button class="btn btn-primary" type="submit">Lưu thay đổi</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include "../template/script_footer.php"; ?>
