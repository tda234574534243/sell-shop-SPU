<?php include('template/head.php') ?>
<?php include('template/header.php') ?>
<?php include('template/toastMess.php') ?>

<style>
    /* Mobile-first responsive design for user profile */
    body {
        font-family: Inter, 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 0;
        padding: 0;
        background: transparent;
    }

    .main__container {
        max-width: 100%;
        margin: 16px;
        padding: 16px;
        border-radius: 12px;
    }
    @media screen and (min-width: 768px) {
        .main__container {
            max-width: 920px;
            margin: 36px auto;
            padding: 28px;
        }
    }

    h2 {
        color: #e6eef9;
        border-bottom: 2px solid rgba(99,102,241,0.3);
        padding-bottom: 12px;
        margin-bottom: 20px;
        margin-top: 24px;
        font-size: 1.3rem;
        font-weight: 600;
    }
    h2:first-of-type { margin-top: 0; }

    h3 {
        color: #cbd5e1;
        font-size: 1rem;
        font-weight: 600;
        margin: 16px 0 12px 0;
    }

    label {
        display: block;
        color: #cbd5e1;
        font-size: 0.95rem;
        font-weight: 500;
        margin-bottom: 8px;
        margin-top: 12px;
    }

    form input[type="text"],
    form input[type="email"],
    form input[type="password"],
    form textarea,
    form input[type="file"] {
        width: 100%;
        padding: 10px 12px;
        margin-bottom: 12px;
        border: 1px solid rgba(255,255,255,0.06);
        border-radius: 8px;
        transition: border 0.18s ease, box-shadow 0.18s ease;
        background: rgba(15,23,42,0.45);
        color: #e6eef9;
        font-size: 16px;
        box-sizing: border-box;
    }

    @media screen and (max-width: 640px) {
        form input[type="text"],
        form input[type="email"],
        form input[type="password"],
        form textarea,
        form input[type="file"] {
            padding: 14px 14px;
            font-size: 16px;
            min-height: 48px;
            border-radius: 10px;
        }
    }

    /* Visually mute readonly email field to indicate non-editable */
    input.readonly-muted {
        opacity: 0.6;
        background: rgba(255,255,255,0.02);
        color: #bfcfe8;
        cursor: not-allowed;
        pointer-events: none;
    }

    form input:focus,
    form textarea:focus {
        border-color: rgba(99,102,241,0.9);
        outline: none;
        box-shadow: 0 0 0 2px rgba(99,102,241,0.15);
    }

    form button {
        min-height: 44px;
        padding: 10px 18px;
        background-color: linear-gradient(90deg,#6366f1,#4f46e5);
        color: white;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        transition: background 0.18s ease, transform 0.08s ease;
        box-shadow: 0 6px 18px rgba(20,20,40,0.25);
        font-size: 16px;
        font-weight: 500;
        width: auto;
    }

    @media screen and (max-width: 640px) {
        form button {
            width: 100%;
            min-height: 48px;
            padding: 14px 20px;
            font-size: 16px;
        }
    }

    form button:hover { transform: translateY(-1px); }

    /* Profile Grid - Single column on mobile, 2 cols on desktop */
    .profile-grid {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    @media screen and (min-width: 768px) {
        .profile-grid {
            display: grid;
            grid-template-columns: 160px 1fr;
            gap: 24px;
            align-items: start;
        }
    }

    .avatar-box {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .avatar-box img {
        width: 100%;
        max-width: 140px;
        height: auto;
        aspect-ratio: 1;
        object-fit: cover;
        border-radius: 12px;
        border: 1px solid rgba(255,255,255,0.04);
    }
    @media screen and (max-width: 640px) {
        .avatar-box img {
            max-width: 120px;
        }
    }

    .profile-card { padding: 0; background: transparent; border-radius: 10px; }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        display: none;
    }

    table th, table td {
        text-align: left;
        padding: 10px;
        border-bottom: 1px solid rgba(255,255,255,0.04);
        color: #dbeafe;
    }

    table th {
        background-color: rgba(255,255,255,0.02);
        color: #c7d2fe;
    }

    table tr:hover {
        background-color: rgba(255,255,255,0.02);
    }

    /* Mobile card-based order display */
    .orders-wrap {
        max-height: none;
        overflow-y: visible;
        border-radius: 0;
        padding: 0;
    }
    .order-card {
        background: rgba(99,102,241,0.08);
        border: 1px solid rgba(99,102,241,0.2);
        border-left: 4px solid rgba(99,102,241,0.6);
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 16px;
    }
    .order-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }
    .order-card-title {
        font-weight: 600;
        color: #cbd5e1;
        font-size: 0.95rem;
    }
    .order-card-date {
        color: #94a3b8;
        font-size: 0.85rem;
    }
    .order-card-items {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .order-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid rgba(255,255,255,0.02);
        font-size: 0.9rem;
    }
    .order-item-left {
        flex: 1;
    }
    .order-item-name {
        color: #dbeafe;
        font-weight: 500;
        margin-bottom: 4px;
    }
    .order-item-qty {
        color: #94a3b8;
        font-size: 0.85rem;
    }
    .order-item-right {
        text-align: right;
        color: #a5f3fc;
        font-weight: 500;
        font-variant-numeric: tabular-nums;
    }
    .order-status {
        color: #a5f3fc;
        font-size: 0.85rem;
    }
    .order-total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        margin-top: 12px;
        border-top: 1px solid rgba(99,102,241,0.3);
        font-weight: 600;
        color: #e0e7ff;
    }
    .empty-note {
        color: #94a3b8;
        font-size: 0.95rem;
        padding: 20px 16px;
        text-align: center;
        background: rgba(30,41,59,0.5);
        border-radius: 10px;
        border: 1px dashed rgba(255,255,255,0.05);
    }

    @media screen and (max-width: 600px) {
        .main__container { padding: 20px; }
        table, thead, tbody, th, td, tr { display: block; }
        table tr { margin-bottom: 15px; background: transparent; padding: 10px; border-radius: 8px; }
        table td { text-align: right; padding-left: 50%; position: relative; }
        table td::before { content: attr(data-label); position: absolute; left: 10px; width: 45%; font-weight: bold; text-align: left; }
    }
</style>

<div class="main__container glass-effect soft-shadow">
    <?php
        include_once('model/m_account.php');
        include_once('model/m_giohang.php');
        include_once('model/m_lsMua.php');

        $acc = new M_account();
        $lsMua = new M_lsMua();
        $cart = new M_giohang();


        $maKH = $_SESSION['user_id'] ?? 0;
        if ($maKH <= 0) die("Vui lòng đăng nhập");
        $user = $acc->getAccount($maKH)->fetch_assoc();
      
        $orders1 = $cart->getCartItems($maKH);
        $orders2 = $lsMua->getLSMuaByMaTK($maKH);

        $acc->close();
        $cart->close();
        $lsMua->close();
    ?>

    <h2>Thông tin cá nhân</h2>
    <div class="profile-card">
    <form action="controller/c_updateProfile.php" method="post" enctype="multipart/form-data">
        <div class="profile-grid">
            <div class="avatar-box">
                <?php if (!empty($user['Avatar'])): ?>
                    <img src="<?= htmlspecialchars($user['Avatar']) ?>" alt="Avatar">
                <?php else: ?>
                    <div style="width:100%;max-width:140px;aspect-ratio:1;background:#1e293b;display:flex;align-items:center;justify-content:center;border-radius:12px;color:#64748b;font-weight:600;font-size:0.85rem;border:1px solid rgba(255,255,255,0.04);">No Avatar</div>
                <?php endif; ?>
                <input type="file" name="Avatar" accept="image/*">
            </div>
            <div style="flex:1;">

            <div>
                <label for="HoTen">Họ tên:</label>
                <input type="text" name="HoTen" value="<?= htmlspecialchars($user['TenTK']) ?>" required>

                <label for="Email">Email:</label>
                <input type="email" name="Email" value="<?= htmlspecialchars($user['Email']) ?>" readonly class="readonly-muted" aria-disabled="true">
                <small style="display:block;color:#94a3b8;margin-bottom:8px;">Email không thể thay đổi từ đây. Liên hệ quản trị nếu muốn cập nhật.</small>

                <label for="SDT">Số điện thoại:</label>
                <input type="text" name="SDT" value="<?= htmlspecialchars($user['SDT']) ?>">

                <label for="DiaChi">Địa chỉ:</label>
                <textarea name="DiaChi"><?= htmlspecialchars($user['DiaChi']) ?></textarea>

                <hr>
                <h3 style="margin-top:6px;margin-bottom:8px;font-size:1rem;color:#444;">Đổi mật khẩu</h3>
                <label for="CurrentPassword">Mật khẩu hiện tại (nhập nếu muốn đổi):</label>
                <input type="password" name="CurrentPassword">

                <label for="NewPassword">Mật khẩu mới:</label>
                <input type="password" name="NewPassword">

                <label for="ConfirmPassword">Xác nhận mật khẩu mới:</label>
                <input type="password" name="ConfirmPassword">

                <div style="margin-top:12px;">
                    <button type="submit">Cập nhật hồ sơ</button>
                </div>
            </div>
        </div>
    </form>
    </div>

    <hr>

    <h2>Lịch sử mua hàng</h2>
    <div class="orders-wrap">
    <?php 
        // Display pending cart items
        $hasRows = false;
        while ($row = $orders1->fetch_assoc()): 
            $hasRows = true; 
    ?>
        <div class="order-card">
            <div class="order-card-items">
                <div class="order-item">
                    <div class="order-item-left">
                        <div class="order-item-name"><?= htmlspecialchars($row['TenSP']) ?></div>
                        <div class="order-item-qty">Số lượng: <?= intval($row['SoLuong']) ?></div>
                    </div>
                    <div class="order-item-right"><?= number_format($row['GiaTien']*$row['SoLuong'], 0, ',', '.') ?>đ</div>
                </div>
            </div>
            <div class="order-total">
                <span>Ngày mua: <?= date_format(new DateTime($row['NgayMua']), "d/m/Y H:i") ?></span>
                <span class="order-status"><?= htmlspecialchars($row['State']) ?></span>
            </div>
        </div>
    <?php endwhile; 
    if (!$hasRows): ?>
        <div class="empty-note">Không có đặt hàng chờ xử lý.</div>
    <?php endif; ?>
    
    <?php
        // Group lsMua rows by MaHD
        include_once('model/m_hoadon.php');
        $hoadonModel = new M_hoadon();

        $lsRows = [];
        while ($r = $orders2->fetch_assoc()) {
            $maHD = $r['MaHD'] ?? 0;
            if (!isset($lsRows[$maHD])) $lsRows[$maHD] = ['items' => [], 'date' => $r['NgayMua'] ?? null];
            $lsRows[$maHD]['items'][] = $r;
        }

        foreach ($lsRows as $maHD => $group) {
            // compute sum of line items
            $sumItems = 0;
            foreach ($group['items'] as $it) {
                $sumItems += floatval($it['GiaTien']) * intval($it['SoLuong']);
            }

            // load shipping config
            $shippingFee = 0;
            $shippingThreshold = 10000000;
            $shippingConfigPath = __DIR__ . '/public/DATA/shipping.json';
            if (file_exists($shippingConfigPath)) {
                $raw = file_get_contents($shippingConfigPath);
                $j = json_decode($raw, true);
                if (is_array($j)) {
                    if (isset($j['threshold'])) $shippingThreshold = floatval($j['threshold']);
                    if (isset($j['fee'])) $shippingFee = floatval($j['fee']);
                }
            }

            // determine if shipping fee applies
            $appliedFee = 0;
            if ($shippingFee > 0) {
                foreach ($group['items'] as $it) {
                    if (floatval($it['GiaTien']) > $shippingThreshold) {
                        $appliedFee = $shippingFee;
                        break;
                    }
                }
            }

            $orderTotal = $sumItems + $appliedFee;
    ?>
        <div class="order-card">
            <div class="order-card-header">
                <div class="order-card-title">Hoá đơn #<?= htmlspecialchars($maHD) ?></div>
                <div class="order-card-date"><?= date_format(new DateTime($group['date']), "d/m/Y H:i") ?></div>
            </div>
            <div class="order-card-items">
    <?php foreach ($group['items'] as $item): ?>
                <div class="order-item">
                    <div class="order-item-left">
                        <div class="order-item-name"><?= htmlspecialchars($item['TenSP']) ?></div>
                        <div class="order-item-qty">SL: <?= $item['SoLuong'] ?> × <?= number_format($item['GiaTien'], 0, ',', '.') ?>đ</div>
                    </div>
                    <div class="order-item-right"><?= number_format($item['GiaTien']*$item['SoLuong'], 0, ',', '.') ?>đ</div>
                </div>
    <?php endforeach; ?>
            </div>
            <div class="order-total">
                <div>
                    <span>Tổng cộng</span>
                    <?php if($appliedFee>0): ?>
                    <div style="font-size:0.85rem;color:#94a3b8;margin-top:4px;">Phí vận chuyển: <?= number_format($appliedFee,0,',','.') ?>đ</div>
                    <?php endif; ?>
                </div>
                <div style="font-size:1.1rem;font-weight:700;color:#a5f3fc;"><?= number_format($orderTotal,0,',','.') ?>đ</div>
            </div>
        </div>
    <?php
        }
        $hoadonModel->close();
    ?>
</div>
<?php include('template/footer.php') ?>
