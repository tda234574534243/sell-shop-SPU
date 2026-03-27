<?php include('template/head.php') ?>
<?php include('template/header.php') ?>
<?php include('template/toastMess.php') ?>

<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f9f9f9;
        margin: 0;
        padding: 0;
    }

    .main__container {
        max-width: 920px;
        margin: 36px auto;
        background-color: #ffffff;
        padding: 28px;
        border-radius: 12px;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
    }

    h2 {
        color: #2c3e50;
        border-bottom: 2px solid #3498db;
        padding-bottom: 5px;
        margin-bottom: 20px;
    }

    form input[type="text"],
    form input[type="email"],
    form input[type="password"],
    form textarea,
    form input[type="file"] {
        width: 100%;
        padding: 10px 12px;
        margin-bottom: 12px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        transition: border 0.18s ease, box-shadow 0.18s ease;
        background: #fbfbfb;
    }

    form input:focus,
    form textarea:focus {
        border-color: #3498db;
        outline: none;
    }

    form button {
        padding: 10px 18px;
        background-color: #1f6feb;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: background 0.18s ease, transform 0.08s ease;
        box-shadow: 0 4px 10px rgba(31,111,235,0.12);
    }

    form button:hover { background-color: #155ed8; transform: translateY(-1px); }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    table th, table td {
        text-align: left;
        padding: 10px;
        border-bottom: 1px solid #ddd;
    }

    table th {
        background-color: #ecf0f1;
        color: #2c3e50;
    }

    table tr:hover {
        background-color: #f1f1f1;
    }

    @media screen and (max-width: 600px) {
        .main__container {
            padding: 20px;
        }

        table, thead, tbody, th, td, tr {
            display: block;
        }

        table tr {
            margin-bottom: 15px;
            background: #fff;
            padding: 10px;
            border-radius: 8px;
        }

        table td {
            text-align: right;
            padding-left: 50%;
            position: relative;
        }

        table td::before {
            content: attr(data-label);
            position: absolute;
            left: 10px;
            width: 45%;
            font-weight: bold;
            text-align: left;
        }
    }

    /* Profile layout tweaks */
    .profile-grid { display: grid; grid-template-columns: 160px 1fr; gap: 18px; align-items: start; }
    .avatar-box img { width: 140px; height: 140px; object-fit: cover; border-radius: 12px; border: 1px solid #f0f0f0; }
    .profile-card { padding: 18px; background: #ffffff; border-radius: 10px; }
</style>

<div class="main__container">
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
    <table style="max-height: 400px; overflow-y: auto;">
        <tr>
            <th>Tên sản phẩm</th><th>Ngày mua</th><th>Giá tiền</th><th>Số lượng</th><th>Tổng tiền</th><th>Trạng thái</th>
        </tr>
        <?php while ($row = $orders1->fetch_assoc()): ?>
        <tr>
            <td data-label="Tên sản phẩm"><?= $row['TenSP'] ?></td>
            <td data-label="Ngày mua"><?= date_format(new DateTime($row['NgayMua']), "H:i:s d/m/Y") ?></td>
            <td data-label="Giá tiền"><?= number_format($row['GiaTien'], 0, ',', '.') ?>đ</td>
            <td data-label="Số lượng"><?= $row['SoLuong'] ?></td>
            <td data-label="Tổng tiền"><?= number_format($row['GiaTien']*$row['SoLuong'], 0, ',', '.') ?>đ</td>
            <td data-label="Trạng thái"><?= $row['State'] ?></td>
        </tr>
        <?php endwhile; ?>
         <?php while ($row = $orders2->fetch_assoc()): ?>
        <tr>
            <td data-label="Tên sản phẩm"><?= $row['TenSP'] ?></td>
            <td data-label="Ngày mua"><?= date_format(new DateTime($row['NgayMua']), "H:i:s d/m/Y") ?></td>
            <td data-label="Giá tiền"><?= number_format($row['GiaTien'], 0, ',', '.') ?>đ</td>
            <td data-label="Số lượng"><?= $row['SoLuong'] ?></td>
            <td data-label="Tổng tiền"><?= number_format($row['GiaTien']*$row['SoLuong'], 0, ',', '.') ?>đ</td>
            <td data-label="Trạng thái"><?= $row['State'] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
<?php include('template/footer.php') ?>
