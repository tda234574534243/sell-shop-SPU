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
    // Note: ordersChange is still based on all orders by default; keep original behavior
    $ordersChange = $stat->getOrdersChange($month, $year);
    
    // Sản phẩm & Khách hàng
    $totalProducts = $stat->getTotalProducts();
    $totalCustomers = $stat->getTotalCustomers();
    
    // Doanh thu theo tháng trong năm (đã giao)
    $yearlyData = $stat->getYearlyDeliveredRevenue($year);
?>
<div class="bg-light flex-fill">
    <style>
        .card {
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            cursor: pointer;
        }

        .card:hover {
            transform: scale(1.05);
        }

        .card-link {
            text-decoration: none;
            color: inherit;
        }

        .card-header i {
            font-size: 2rem;
        }

        .card-body .d-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .percentage {
            padding: 0.3rem 0.5rem;
            border-radius: 0.3rem;
        }

        .percentage-positive {
            background-color: #e0f7e0;
            border: 1px solid #d4edda;
            color: #28a745;
        }

        .percentage-negative {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #dc3545;
        }
    </style>
    <!-- Main Content -->
    <div id="mainContent" class="p-4">
        <div class="mb-4">
            <h4 class='fw-bold'>Thống kê tháng <?= $month ?>/<?= $year ?></h4>
        </div>
        <hr>
        <div class="d-flex justify-content-between flex-wrap gap-4 px-5">
            <!-- Tổng doanh thu -->
            <div class="card" style="width: 18rem;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <i class="fa-solid fa-money-bill-wave fa-2x"></i>
                </div>
                <div class="card-body py-5">
                    <p class="card-text">Tổng doanh thu</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title"><b><?= number_format($monthlyRevenue, 0, ',', '.') ?> VNĐ</b></h4>
                        <p class="mb-0 fw-semibold percentage <?= $revenueChange >= 0 ? 'percentage-positive text-success' : 'percentage-negative text-danger' ?>">
                            <?= $revenueChange >= 0 ? '+' : '' ?><?= $revenueChange ?>%
                        </p>
                    </div>
                </div>
            </div>

            <!-- Tổng sản phẩm -->
            <div class="card" style="width: 18rem;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <i class="fa-solid fa-boxes fa-2x"></i>
                </div>
                <div class="card-body py-5">
                    <p class="card-text">Tổng sản phẩm</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title"><b><?= $totalProducts ?></b></h4>
                        <p class="mb-0 fw-semibold text-success percentage percentage-positive">Đang kinh doanh</p>
                    </div>
                </div>
            </div>

            <!-- Tổng đơn hàng -->
            <div class="card" style="width: 18rem;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <i class="fa-solid fa-cart-shopping fa-2x"></i>
                </div>
                <div class="card-body py-5">
                    <p>Tổng đơn hàng</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title"><b><?= $totalOrders ?></b></h4>
                        <p class="mb-0 fw-semibold percentage <?= $ordersChange >= 0 ? 'percentage-positive text-success' : 'percentage-negative text-danger' ?>">
                            <?= $ordersChange >= 0 ? '+' : '' ?><?= $ordersChange ?>%
                        </p>
                    </div>
                </div>
            </div>

            <!-- Tổng khách hàng -->
            <div class="card" style="width: 18rem;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <i class="fa-solid fa-users fa-2x"></i>
                </div>
                <div class="card-body py-5">
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
        <canvas id="revenueChart" height="100"></canvas>
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
            plugins: {
                legend: {
                    onClick: null
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString('vi-VN') + ' VNĐ';
                        }
                    }
                }
            }
        }
    });
</script>
</body>
</html>
<?php include "../template/script_footer.php"; ?>