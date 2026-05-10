<?php
require_once "../model/m_donhang.php";
if (file_exists(__DIR__ . '/../helper/logger.php')) require_once __DIR__ . '/../helper/logger.php';

class ExportInvoiceController {
    public function exportCsv() {
        if (!isset($_GET['ma_hd'])) {
            die("Mã hóa đơn không hợp lệ!");
        }

        $model = new DonHangModel();
        $ma_hd = $_GET['ma_hd'];
        // sanitize ma_hd for use in filenames: allow only alnum, dash, underscore
        $safe_ma_hd = preg_replace('/[^a-zA-Z0-9_-]/', '_', $ma_hd);
        // sanitize ma_hd for use in filenames: allow only alnum, dash, underscore
        $safe_ma_hd = preg_replace('/[^a-zA-Z0-9_-]/', '_', $ma_hd);

        // Lấy chi tiết hóa đơn
        $query = "
            SELECT 
                ls.MaHD,
                acc.TenTK,
                acc.Email,
                acc.SDT,
                acc.DiaChi,
                p.TenSP,
                p.MaSP,
                ls.SoLuong,
                p.GiaTien,
                (ls.SoLuong * p.GiaTien) AS TongTien,
                ls.NgayMua
            FROM LS_Mua ls
            JOIN Account acc ON acc.MaTK = ls.MaTK
            JOIN Products p ON p.MaSP = ls.MaSP
            WHERE ls.MaHD = '" . $model->real_escape_string($ma_hd) . "'
            ORDER BY ls.NgayMua DESC
        ";

        $model->setQuery($query);
        $result = $model->excuteQuery();

        if ($result->num_rows === 0) {
            die("Không tìm thấy hóa đơn!");
        }

        // Tạo file CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="HoaDon_' . $ma_hd . '.csv"');

        $output = fopen('php://output', 'w');
        
        // BOM để support UTF-8 trong Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Header của CSV
        fputcsv($output, array('HÓA ĐƠN BÁN HÀNG'), ';');
        fputcsv($output, array(''), ';');
        
        // Thông tin khách hàng
        $first_row = $result->fetch_assoc();
        fputcsv($output, array('Mã hóa đơn:', $first_row['MaHD']), ';');
        fputcsv($output, array('Ngày mua:', $first_row['NgayMua']), ';');
        fputcsv($output, array(''), ';');
        
        fputcsv($output, array('THÔNG TIN KHÁCH HÀNG'), ';');
        fputcsv($output, array('Tên tài khoản:', $first_row['TenTK']), ';');
        fputcsv($output, array('Email:', $first_row['Email']), ';');
        fputcsv($output, array('Số điện thoại:', $first_row['SDT']), ';');
        fputcsv($output, array('Địa chỉ:', $first_row['DiaChi']), ';');
        fputcsv($output, array(''), ';');
        
        fputcsv($output, array('CHI TIẾT ĐƠN HÀNG'), ';');
        fputcsv($output, array('Mã sản phẩm', 'Tên sản phẩm', 'Số lượng', 'Đơn giá', 'Thành tiền'), ';');

        // Dữ liệu chi tiết
        $total = 0;
        fputcsv($output, array(
            $first_row['MaSP'],
            $first_row['TenSP'],
            $first_row['SoLuong'],
            $first_row['GiaTien'],
            $first_row['TongTien']
        ), ';');
        $total += $first_row['TongTien'];

        // Lấy các sản phẩm còn lại
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, array(
                $row['MaSP'],
                $row['TenSP'],
                $row['SoLuong'],
                $row['GiaTien'],
                $row['TongTien']
            ), ';');
            $total += $row['TongTien'];
        }

        fputcsv($output, array(''), ';');
        fputcsv($output, array('TỔNG CỘNG:', number_format($total, 0, ',', '.')), ';');

        if (function_exists('log_action')) log_action('INFO', 'Exported invoice CSV', ['MaHD' => $ma_hd, 'format' => 'csv', 'by' => $_SESSION['username'] ?? 'unknown']);
        fclose($output);
        exit();
    }

    public function exportPdf() {
        if (!isset($_GET['ma_hd'])) {
            die("Mã hóa đơn không hợp lệ!");
        }

        $model = new DonHangModel();
        $ma_hd = $_GET['ma_hd'];

        // Lấy chi tiết hóa đơn
        $query = "
            SELECT 
                ls.MaHD,
                acc.TenTK,
                acc.Email,
                acc.SDT,
                acc.DiaChi,
                p.TenSP,
                p.MaSP,
                ls.SoLuong,
                p.GiaTien,
                (ls.SoLuong * p.GiaTien) AS TongTien,
                ls.NgayMua
            FROM LS_Mua ls
            JOIN Account acc ON acc.MaTK = ls.MaTK
            JOIN Products p ON p.MaSP = ls.MaSP
            WHERE ls.MaHD = '" . $model->real_escape_string($ma_hd) . "'
            ORDER BY ls.NgayMua DESC
        ";

        $model->setQuery($query);
        $result = $model->excuteQuery();

        if ($result->num_rows === 0) {
            die("Không tìm thấy hóa đơn!");
        }

        // Tạo HTML cho PDF
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; }
        body { font-family: "Times New Roman", Times, serif; font-size: 12pt; line-height: 1.5; }
        .container { width: 800px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h1 { font-size: 24px; margin-bottom: 10px; }
        .info-section { display: table; width: 100%; margin-bottom: 20px; }
        .info-left, .info-right { display: table-cell; width: 50%; vertical-align: top; }
        .info-item { margin-bottom: 8px; }
        .info-item label { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        table th { background-color: #e0e0e0; border: 1px solid #000; padding: 10px; text-align: left; font-weight: bold; }
        table td { border: 1px solid #000; padding: 8px; }
        .total-section { text-align: right; margin-top: 20px; padding-top: 10px; border-top: 2px solid #000; }
        .total-line { font-size: 14px; font-weight: bold; margin-bottom: 10px; }
        .footer { text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #ccc; font-size: 11px; color: #666; }
        @media print { body { margin: 0; padding: 0; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>HÓA ĐƠN BÁN HÀNG</h1>
            <p style="margin: 5px 0;">Cửa hàng trực tuyến</p>
        </div>
        ';

        $first_row = $result->fetch_assoc();
        
        $html .= '
        <div class="info-section">
            <div class="info-left">
                <div class="info-item"><label>Mã hóa đơn:</label> <strong>' . htmlspecialchars($first_row['MaHD']) . '</strong></div>
                <div class="info-item"><label>Ngày:</label> ' . $first_row['NgayMua'] . '</div>
            </div>
            <div class="info-right">
                <div class="info-item"><label>Địa chỉ gửi hẹ:</label> ' . htmlspecialchars($first_row['DiaChi']) . '</div>
            </div>
        </div>

        <div style="margin: 30px 0;">
            <h3 style="margin-bottom: 10px;">THÔNG TIN KHÁCH HÀNG</h3>
            <div class="info-item"><label>Tên tài khoản:</label> ' . htmlspecialchars($first_row['TenTK']) . '</div>
            <div class="info-item"><label>Email:</label> ' . htmlspecialchars($first_row['Email']) . '</div>
            <div class="info-item"><label>Số điện thoại:</label> ' . htmlspecialchars($first_row['SDT']) . '</div>
        </div>

        <h3 style="margin-bottom: 15px;">CHI TIẾT ĐƠN HÀNG</h3>
        <table>
            <thead>
                <tr>
                    <th>Mã SP</th>
                    <th>Tên sản phẩm</th>
                    <th style="text-align: center;">Số lượng</th>
                    <th style="text-align: right;">Đơn giá</th>
                    <th style="text-align: right;">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
        ';

        $total = 0;
        $html .= '
                <tr>
                    <td>' . htmlspecialchars($first_row['MaSP']) . '</td>
                    <td>' . htmlspecialchars($first_row['TenSP']) . '</td>
                    <td style="text-align: center;">' . $first_row['SoLuong'] . '</td>
                    <td style="text-align: right;">' . number_format($first_row['GiaTien'], 0, ',', '.') . ' ₫</td>
                    <td style="text-align: right;"><strong>' . number_format($first_row['TongTien'], 0, ',', '.') . ' ₫</strong></td>
                </tr>
        ';
        $total += $first_row['TongTien'];

        while ($row = $result->fetch_assoc()) {
            $html .= '
                <tr>
                    <td>' . htmlspecialchars($row['MaSP']) . '</td>
                    <td>' . htmlspecialchars($row['TenSP']) . '</td>
                    <td style="text-align: center;">' . $row['SoLuong'] . '</td>
                    <td style="text-align: right;">' . number_format($row['GiaTien'], 0, ',', '.') . ' ₫</td>
                    <td style="text-align: right;"><strong>' . number_format($row['TongTien'], 0, ',', '.') . ' ₫</strong></td>
                </tr>
            ';
            $total += $row['TongTien'];
        }

        $html .= '
            </tbody>
        </table>

        <div class="total-section">
            <div class="total-line">TỔNG CỘNG: ' . number_format($total, 0, ',', '.') . ' ₫</div>
        </div>

        <div class="footer">
            <p>Cảm ơn quý khách đã mua hàng!</p>
            <p>Vui lòng kiểm tra hạn sử dụng các sản phẩm</p>
        </div>
    </div>
</body>
</html>';

        // Ghi HTML vào file tạm
        // Ensure sanitized id for filename is available
        $safe_ma_hd = isset($safe_ma_hd) ? $safe_ma_hd : preg_replace('/[^a-zA-Z0-9_-]/', '_', $ma_hd);
        $filename = 'HoaDon_' . $safe_ma_hd . '_' . date('YmdHis') . '.html';
        $filepath = realpath(__DIR__ . '/../media/uploads') . DIRECTORY_SEPARATOR . $filename;
        if ($filepath === false) {
            // ensure directory exists and resolve path
            if (!is_dir(__DIR__ . '/../media/uploads')) @mkdir(__DIR__ . '/../media/uploads', 0755, true);
            $filepath = __DIR__ . '/../media/uploads' . DIRECTORY_SEPARATOR . $filename;
        }
        
        // Đảm bảo thư mục tồn tại
        if (!is_dir('../media/uploads')) {
            @mkdir('../media/uploads', 0755, true);
        }
        
        file_put_contents($filepath, $html);

        // Download file
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="HoaDon_' . $ma_hd . '.html"');
        if (function_exists('log_action')) log_action('INFO', 'Exported invoice PDF/HTML', ['MaHD' => $ma_hd, 'format' => 'pdf/html', 'by' => $_SESSION['username'] ?? 'unknown']);
        echo $html;
        exit();
    }
}

// Kiểm tra action
$action = $_GET['action'] ?? 'pdf';

$export = new ExportInvoiceController();
if ($action === 'csv') {
    $export->exportCsv();
} else {
    $export->exportPdf();
}
?>
