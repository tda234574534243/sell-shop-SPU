<?php include "../template/sidebar.php"; ?>
<?php include ('../template/toastMess.php') ?>
<?php
    require_once '../model/m_statistic.php';
    $stat = new M_statistic();
    
    // Lấy tháng & năm từ request
    $month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
    $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
    
    // Validate
    if ($month < 1 || $month > 12) $month = date('n');
    if ($year < 2000 || $year > 2100) $year = date('Y');
    
    // Dữ liệu (chỉ tính mục đã giao)
    $revenue = $stat->getMonthlyDeliveredRevenue($month, $year);
    $orders = $stat->getTotalDeliveredOrders($month, $year);
    $topProducts = $stat->getTopProductsDelivered(10);
    $change = $stat->getRevenueChangeDelivered($month, $year);
?>
<?php include('../template/head.php'); ?>

<style>
    #mainContent {
        margin-left: 250px;
    }
    
    .revenue-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 8px;
        margin-bottom: 30px;
    }
    
    .revenue-header h2 {
        margin: 0;
        font-weight: bold;
    }
    
    .revenue-header .amount {
        font-size: 32px;
        margin-top: 10px;
    }
    
    .revenue-header .change {
        font-size: 14px;
        opacity: 0.9;
        margin-top: 5px;
    }
    
    .info-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .info-card {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        text-align: center;
    }
    
    .info-card .label {
        color: #999;
        font-size: 12px;
        text-transform: uppercase;
        margin-bottom: 10px;
    }
    
    .info-card .value {
        font-size: 28px;
        font-weight: bold;
        color: #333;
    }
    
    .top-products {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .top-products table {
        margin: 0;
        width: 100%;
    }
    
    .top-products th {
        background: #f8f9fa;
        padding: 12px;
        text-align: left;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
    }
    
    .top-products td {
        padding: 12px;
        border-bottom: 1px solid #dee2e6;
    }
    
    .top-products tbody tr:hover {
        background: #f9f9f9;
    }
    
    .rank {
        font-weight: bold;
        color: #667eea;
        font-size: 18px;
        min-width: 30px;
    }
</style>

<div class="bg-light flex-fill">
    <div id="mainContent" class="p-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold">Chi tiết doanh thu</h4>
            <form method="GET" class="d-flex gap-2">
                <select name="month" class="form-select" style="width: auto;">
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?= $i ?>" <?= $i == $month ? 'selected' : '' ?>>Tháng <?= $i ?></option>
                    <?php endfor; ?>
                </select>
                <select name="year" class="form-select" style="width: auto;">
                    <?php for ($y = 2020; $y <= 2030; $y++): ?>
                        <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Cập nhật</button>
            </form>
        </div>

        <!-- Revenue Header -->
        <div class="revenue-header">
            <h2>Thống kê tháng <?= str_pad($month, 2, '0', STR_PAD_LEFT) ?>/<?= $year ?></h2>
            <div class="amount"><?= number_format($revenue, 0, ',', '.') ?> ₫</div>
            <div class="change">
                <?php if ($change >= 0): ?>
                    <span style="color: #4ade80;">↑ +<?= $change ?>% so với tháng trước</span>
                <?php else: ?>
                    <span style="color: #f87171;">↓ <?= $change ?>% so với tháng trước</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Info Cards -->
        <div class="info-cards">
            <div class="info-card">
                <div class="label">Tổng đơn hàng</div>
                <div class="value"><?= $orders ?></div>
            </div>
            <div class="info-card">
                <div class="label">Giá trị trung bình/đơn</div>
                <div class="value"><?= $orders > 0 ? number_format($revenue / $orders, 0, ',', '.') : 0 ?> ₫</div>
            </div>
            <div class="info-card">
                <div class="label">Tính toán</div>
                <div class="value"><?= $orders ?> × <?= number_format($revenue / ($orders > 0 ? $orders : 1), 0, ',', '.') ?></div>
            </div>
        </div>

        <!-- Top Products -->
        <h5 class="fw-bold mb-3">Top 10 sản phẩm bán chạy</h5>
        <div class="top-products">
            <?php if ($topProducts && $topProducts->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 50px;">Xếp hạng</th>
                            <th>Mã SP</th>
                            <th>Tên sản phẩm</th>
                            <th style="text-align: center;">Lần bán</th>
                            <th style="text-align: center;">Số lượng</th>
                            <th style="text-align: right;">Doanh thu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $rank = 1;
                        while ($row = $topProducts->fetch_assoc()): 
                        ?>
                            <tr>
                                <td>
                                    <div class="rank">
                                        <?php if ($rank == 1): ?>
                                            🥇
                                        <?php elseif ($rank == 2): ?>
                                            🥈
                                        <?php elseif ($rank == 3): ?>
                                            🥉
                                        <?php else: ?>
                                            #<?= $rank ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><code><?= htmlspecialchars($row['MaSP']) ?></code></td>
                                <td><?= htmlspecialchars($row['TenSP']) ?></td>
                                <td style="text-align: center;"><?= $row['times_sold'] ?></td>
                                <td style="text-align: center;"><?= $row['total_qty'] ?></td>
                                <td style="text-align: right; font-weight: bold;"><?= number_format($row['total_revenue'], 0, ',', '.') ?> ₫</td>
                            </tr>
                            <?php $rank++; ?>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="padding: 40px; text-align: center; color: #999;">
                    <i class="fa-solid fa-inbox" style="font-size: 48px; margin-bottom: 20px;"></i>
                    <p>Không có dữ liệu bán hàng cho khoảng thời gian này</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include "../template/script_footer.php"; ?>
