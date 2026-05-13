<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../public/CSS/style.css">
    <link rel="stylesheet" href="../public/CSS/base.css">
</head>
<?php include "../template/sidebar.php" ?>
<?php include('../template/toastMess.php') ?>
<?php
    require_once '../model/m_statistic.php';
    $stat = new M_statistic();
    
    $month = date('n');
    $year = date('Y');
    
    // Doanh thu (chỉ tính các mục đã giao)
    $monthlyRevenue = $stat->getMonthlyDeliveredRevenue($month, $year);
    $revenueChange = $stat->getRevenueChangeDelivered($month, $year);
    
    // Đơn hàng (đã giao)
    $totalOrders = $stat->getTotalDeliveredOrders($month, $year);
    // Tính phần trăm dựa trên các đơn đã giao (đồng bộ với doanh thu đã giao)
    $ordersChange = $stat->getOrdersChangeDelivered($month, $year);
    
    // Sản phẩm & Khách hàng
    $totalProducts = $stat->getTotalProducts();
    $totalCustomers = $stat->getTotalCustomers();
    
    // Doanh thu theo tháng trong năm (đã giao)
    $yearlyData = $stat->getYearlyDeliveredRevenue($year);
    // Top sản phẩm bán chạy (đã giao) — dùng cho biểu đồ tròn
    $topProducts = $stat->getTopProductsDelivered(5);
    $topProductsArr = [];
    if ($topProducts && $topProducts->num_rows > 0) {
        while ($r = $topProducts->fetch_assoc()) {
            $topProductsArr[] = $r;
        }
    }
    // Số đơn đã giao theo tháng (dùng cho biểu đồ đường)
    $yearlyOrders = $stat->getYearlyDeliveredOrders($year);
?>
<div class="bg-light flex-fill">
    <style>
        /* Compact stat cards */
        .card {
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.06);
            transition: transform 0.15s ease;
            cursor: default;
            border-radius: 8px;
        }

        .card-link { text-decoration: none; color: inherit; }

        .card-header i { font-size: 1.5rem; }

        .card-body { padding: 0.8rem 1rem; }

        .card .card-text { margin: 0; font-size: 0.85rem; color: #6c757d; }
        .card .card-title { font-size: 1.05rem; margin:0; }

        .percentage { padding: 0.2rem 0.45rem; border-radius: 0.3rem; font-size: 0.85rem; }
        .percentage-positive { background-color: #e9f7ee; border: 1px solid #d4edda; color: #198754; }
        .percentage-negative { background-color: #fff0f0; border: 1px solid #f5c6cb; color: #dc3545; }

        /* Layout for stats */
        .stat-cards { gap:16px; padding: 0 2rem; display:flex; flex-wrap:wrap; }
        .stat-cards .card { width: 14.5rem; }

        /* Charts layout: side by side on wide screens, stacked on small */
        .charts-row { display:flex; gap:24px; flex-wrap:wrap; align-items:flex-start; }
        .chart-box { flex:1 1 420px; min-width:280px; background:white; padding:12px; border-radius:8px; box-shadow:0 6px 12px rgba(0,0,0,0.04); }
        .chart-small { max-width:360px; }
        .chart-full { width:100%; margin-top:20px; background:white; padding:12px; border-radius:8px; box-shadow:0 6px 12px rgba(0,0,0,0.04); }
        .chart-full canvas { width:100% !important; height:220px !important; }

        /* Make Chart.js canvases responsive while compact */
        .chart-box canvas { width:100% !important; height:180px !important; }
    </style>
    <!-- Main Content -->
    <div id="mainContent" class="p-4">
        <div class="mb-4">
            <h4 class='fw-bold'>Thống kê tháng <?= $month ?>/<?= $year ?></h4>
        </div>
        <hr>
        <div class="stat-cards">
            <!-- Tổng doanh thu -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <i class="fa-solid fa-money-bill-wave fa-2x"></i>
                </div>
                <div class="card-body">
                    <p class="card-text">Tổng doanh thu</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title"><b><?= number_format($monthlyRevenue, 0, ',', '.') ?> VNĐ</b></h4>
                        <?php
                            if ($revenueChange > 0) {
                                $revClass = 'percentage-positive text-success';
                                $revSign = '+';
                            } elseif ($revenueChange < 0) {
                                $revClass = 'percentage-negative text-danger';
                                $revSign = '';
                            } else {
                                $revClass = 'text-muted';
                                $revSign = '';
                            }
                        ?>
                        <p class="mb-0 fw-semibold percentage <?= $revClass ?>">
                            <?= $revSign ?><?= $revenueChange ?>%
                        </p>
                    </div>
                </div>
            </div>

            <!-- Tổng sản phẩm -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <i class="fa-solid fa-boxes fa-2x"></i>
                </div>
                <div class="card-body">
                    <p class="card-text">Tổng sản phẩm</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title"><b><?= $totalProducts ?></b></h4>
                        <p class="mb-0 fw-semibold text-success percentage percentage-positive">Đang kinh doanh</p>
                    </div>
                </div>
            </div>

            <!-- Tổng đơn hàng -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <i class="fa-solid fa-cart-shopping fa-2x"></i>
                </div>
                <div class="card-body">
                    <p>Tổng đơn hàng</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title"><b><?= $totalOrders ?></b></h4>
                        <?php
                            if ($ordersChange > 0) {
                                $ordClass = 'percentage-positive text-success';
                                $ordSign = '+';
                            } elseif ($ordersChange < 0) {
                                $ordClass = 'percentage-negative text-danger';
                                $ordSign = '';
                            } else {
                                $ordClass = 'text-muted';
                                $ordSign = '';
                            }
                        ?>
                        <p class="mb-0 fw-semibold percentage <?= $ordClass ?>">
                            <?= $ordSign ?><?= $ordersChange ?>%
                        </p>
                    </div>
                </div>
            </div>

            <!-- Tổng khách hàng -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <i class="fa-solid fa-users fa-2x"></i>
                </div>
                <div class="card-body">
                    <p class="card-text">Tổng khách hàng</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title"><b><?= $totalCustomers ?></b></h4>
                        <p class="mb-0 fw-semibold text-success percentage percentage-positive">Đã đăng ký</p>
                    </div>
                </div>
            </div>
        </div>
        <hr>

        <!-- biểu đồ -->
        <div class="mb-4">
            <h4 class='fw-bold'>Thống kê doanh thu năm <?= $year ?></h4>
        </div>

        <div class="charts-row">
            <div class="chart-box">
                <canvas id="revenueChart"></canvas>
            </div>
            <div class="chart-box chart-small">
                <h6 class="mb-2">Cơ cấu doanh thu - Top sản phẩm</h6>
                <canvas id="productPieChart"></canvas>
            </div>
        </div>
    </div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const ctx = document.getElementById('revenueChart').getContext('2d');
    const yearlyData = <?= json_encode($yearlyData) ?>;
    
    // Tạo mảng 12 tháng
    const monthlyRevenue = [];
    for (let i = 1; i <= 12; i++) {
        monthlyRevenue.push(yearlyData[i] || 0);
    }
    
    const revenueChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
            datasets: [{
                label: 'Doanh thu (VNĐ)',
                data: monthlyRevenue,
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1,
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString('vi-VN') + ' VNĐ';
                        },
                        font: { size: 11 }
                    }
                },
                x: { ticks: { font: { size: 11 } } }
            }
        }
    });

    // Biểu đồ đường: số đơn đã giao theo tháng (đứng riêng, không dính)
    const ordersCtx = (function(){
        const container = document.createElement('div');
        container.className = 'chart-full';
        const h6 = document.createElement('h6');
        h6.className = 'mb-2';
        h6.innerText = 'Số đơn đã giao theo tháng (<?= $year ?>)';
        container.appendChild(h6);
        const canvas = document.createElement('canvas');
        canvas.id = 'ordersLineChart';
        container.appendChild(canvas);
        const parent = document.querySelector('.charts-row').parentNode;
        parent.insertBefore(container, document.querySelector('.charts-row').nextSibling);
        return canvas.getContext('2d');
    })();

    const yearlyOrders = <?= json_encode($yearlyOrders) ?>;
    const monthlyOrders = [];
    for (let i = 1; i <= 12; i++) monthlyOrders.push(yearlyOrders[i] || 0);

    const ordersLine = new Chart(ordersCtx, {
        type: 'line',
        data: {
            labels: ['Tháng 1','Tháng 2','Tháng 3','Tháng 4','Tháng 5','Tháng 6','Tháng 7','Tháng 8','Tháng 9','Tháng 10','Tháng 11','Tháng 12'],
            datasets: [{
                label: 'Số đơn đã giao',
                data: monthlyOrders,
                borderColor: 'rgba(54,162,235,0.95)',
                backgroundColor: 'rgba(54,162,235,0.12)',
                tension: 0.3,
                pointRadius: 3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } } },
                x: { ticks: { font: { size: 11 } } }
            }
        }
    });
    // Biểu đồ tròn top sản phẩm
    const pieCtx = document.getElementById('productPieChart').getContext('2d');
    const topProducts = <?= json_encode($topProductsArr) ?>;
    const pieLabels = topProducts.map(p => p.TenSP);
    const pieData = topProducts.map(p => Number(p.total_revenue));
    const backgroundColors = [
        'rgba(54, 162, 235, 0.7)',
        'rgba(255, 99, 132, 0.7)',
        'rgba(255, 206, 86, 0.7)',
        'rgba(75, 192, 192, 0.7)',
        'rgba(153, 102, 255, 0.7)'
    ];

    const productPie = new Chart(pieCtx, {
        type: 'pie',
        data: {
            labels: pieLabels,
            datasets: [{
                data: pieData,
                backgroundColor: backgroundColors,
                borderColor: '#fff',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth:12, padding:8, usePointStyle:true, font: { size: 11 } } }
            }
        }
    });
</script>
</body>
</html>
<?php include "../template/script_footer.php"; ?>