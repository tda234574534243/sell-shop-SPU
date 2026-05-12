<?php include('template/head.php') ?>
<?php include('template/header.php') ?>
<?php include('template/toastMess.php') ?>

<style>
    /* Use site defaults; remove forced white backgrounds so page matches Tailwind/glass theme */
    body {
        font-family: Inter, 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 0;
        padding: 0;
        background: transparent;
    }

    .main__container {
        max-width: 920px;
        margin: 36px auto;
        padding: 28px;
        border-radius: 12px;
    }

    h2 {
        color: #e6eef9;
        border-bottom: 1px solid rgba(99,102,241,0.12);
        padding-bottom: 8px;
        margin-bottom: 18px;
        font-size: 1.25rem;
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
    }

    form input:focus,
    form textarea:focus {
        border-color: rgba(99,102,241,0.9);
        outline: none;
    }

    form button {
        padding: 10px 18px;
        background-color: linear-gradient(90deg,#6366f1,#4f46e5);
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: background 0.18s ease, transform 0.08s ease;
        box-shadow: 0 6px 18px rgba(20,20,40,0.25);
    }

    form button:hover { transform: translateY(-1px); }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
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

    /* Orders improvements */
    .orders-wrap { max-height: 480px; overflow-y: auto; border-radius:8px; padding:6px; }
    .invoice-row { background: rgba(99,102,241,0.06); font-weight:700; border-left: 4px solid rgba(99,102,241,0.6); }
    .money, .currency { text-align: right; font-variant-numeric: tabular-nums; }
    .line-item { background: transparent; }
    .empty-note { color: #94a3b8; font-size:0.95rem; padding:12px 6px; }

    @media screen and (max-width: 600px) {
        .main__container { padding: 20px; }
        table, thead, tbody, th, td, tr { display: block; }
        table tr { margin-bottom: 15px; background: transparent; padding: 10px; border-radius: 8px; }
        table td { text-align: right; padding-left: 50%; position: relative; }
        table td::before { content: attr(data-label); position: absolute; left: 10px; width: 45%; font-weight: bold; text-align: left; }
    }

    /* Profile layout tweaks */
    .profile-grid { display: grid; grid-template-columns: 160px 1fr; gap: 18px; align-items: start; }
    .avatar-box img { width: 140px; height: 140px; object-fit: cover; border-radius: 12px; border: 1px solid rgba(255,255,255,0.04); }
    .profile-card { padding: 18px; background: transparent; border-radius: 10px; }
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
                    <div style="width:140px;height:140px;background:#f5f7fb;display:flex;align-items:center;justify-content:center;border-radius:12px;margin-bottom:8px;color:#9aa4b2;font-weight:600;">No Avatar</div>
                <?php endif; ?>
                <input type="file" name="Avatar" accept="image/*">
            </div>

            <div>
                <label for="HoTen">Họ tên:</label>
                <input type="text" name="HoTen" value="<?= htmlspecialchars($user['TenTK']) ?>" required>

                <label for="Email">Email:</label>
                <input type="email" name="Email" value="<?= htmlspecialchars($user['Email']) ?>" required>

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

                <div style="display:flex;justify-content:flex-end;margin-top:8px;">
                    <button type="submit">Cập nhật</button>
                </div>
            </div>
        </div>
    </form>
    </div>

    <hr>

    <h2>Lịch sử mua hàng</h2>
    <div class="orders-wrap">
    <table>
        <tr>
            <th>Tên sản phẩm</th><th>Ngày mua</th><th class="money">Giá tiền</th><th>Số lượng</th><th class="money">Tổng tiền</th><th>Trạng thái</th>
        </tr>
        <?php $hasRows = false; while ($row = $orders1->fetch_assoc()): $hasRows = true; ?>
        <tr class="line-item">
            <td data-label="Tên sản phẩm"><?= htmlspecialchars($row['TenSP']) ?></td>
            <td data-label="Ngày mua"><?= date_format(new DateTime($row['NgayMua']), "H:i:s d/m/Y") ?></td>
            <td data-label="Giá tiền" class="money currency"><?= number_format($row['GiaTien'], 0, ',', '.') ?>đ</td>
            <td data-label="Số lượng"><?= intval($row['SoLuong']) ?></td>
            <td data-label="Tổng tiền" class="money currency"><?= number_format($row['GiaTien']*$row['SoLuong'], 0, ',', '.') ?>đ</td>
            <td data-label="Trạng thái"><?= htmlspecialchars($row['State']) ?></td>
        </tr>
        <?php endwhile; if (!$hasRows): ?>
            <tr><td colspan="6" class="empty-note">Không có đặt hàng chờ xử lý.</td></tr>
        <?php endif; ?>
        <?php
            // Group lsMua rows by MaHD so we can show order total (which includes shipping stored in HoaDon.SoTien)
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
                $applyShip = 0;
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

                // determine if shipping fee applies for this invoice (any unit price > threshold)
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

                // show a summary row for the order (total includes shipping)
                ?>
                <tr class="invoice-row">
                    <td colspan="4">Hoá đơn #<?= htmlspecialchars($maHD) ?> &nbsp; — &nbsp; Ngày: <?= date_format(new DateTime($group['date']), "H:i:s d/m/Y") ?></td>
                    <td class="money currency"><?= number_format($orderTotal,0,',','.') ?>đ <?php if($appliedFee>0) echo "(<small>ship: " . number_format($appliedFee,0,',','.') . "đ</small>)"; ?></td>
                    <td>--</td>
                </tr>
                <?php
                // then show line items
                foreach ($group['items'] as $item) {
                    ?>
                    <tr>
                        <td data-label="Tên sản phẩm"><?= htmlspecialchars($item['TenSP']) ?></td>
                        <td data-label="Ngày mua"><?= date_format(new DateTime($item['NgayMua']), "H:i:s d/m/Y") ?></td>
                        <td data-label="Giá tiền"><?= number_format($item['GiaTien'], 0, ',', '.') ?>đ</td>
                        <td data-label="Số lượng"><?= $item['SoLuong'] ?></td>
                        <td data-label="Tổng tiền"><?= number_format($item['GiaTien']*$item['SoLuong'], 0, ',', '.') ?>đ</td>
                        <td data-label="Trạng thái"><?= htmlspecialchars($item['State']) ?></td>
                    </tr>
                    <?php
                }
            }
            $hoadonModel->close();
        ?>
    </table>
</div>
<?php include('template/footer.php') ?>
