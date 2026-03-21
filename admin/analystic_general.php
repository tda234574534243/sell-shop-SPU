<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../public/CSS/style.css">
    <link rel="stylesheet" href="../public/CSS/base.css">
</head>
<?php include "../template/sidebar.php" ?>
<?php include('../template/toastMess.php') ?>
<div class="bg-light flex-fill">
    <style>
        .card {
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1); /* Đổ bóng cho card ngay từ ban đầu */
            transition: transform 0.3s ease; /* Thêm hiệu ứng phóng to khi hover */
            cursor: pointer;
        }

        .card:hover {
            transform: scale(1.05); /* Phóng to 5% khi hover */
        }

        .card-link {
            text-decoration: none; /* Bỏ gạch chân của link */
            color: inherit; /* Giữ màu sắc mặc định của card */
        }

        .card-header i {
            font-size: 2rem; /* Làm cho icon to hơn */
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
            <?php
                $month = date('n');
                $year = date('Y');
                echo "<h4 class='fw-bold'>Thống kê tháng $month/$year</h4>";
            ?>
        </div>
        <hr>
        <div class="d-flex justify-content-between flex-wrap gap-4 px-5">
            <!-- Tổng doanh thu -->
            <a href="link-to-doanh-thu-page.php" class="card-link">
                <div class="card mb-4" style="width: 18rem;">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <i class="fa-solid fa-money-bill-wave fa-2x"></i>
                    </div>
                    <div class="card-body py-5">
                        <p class="card-text">Tổng doanh thu</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="card-title"><b>35,000,000 VNĐ</b></h4>
                            <p class="mb-0 fw-semibold text-success percentage percentage-positive">+5.5%</p>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Tổng sản phẩm -->
            <a href="link-to-san-pham-page.php" class="card-link">
                <div class="card mb-4" style="width: 18rem;">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <i class="fa-solid fa-boxes fa-2x"></i>
                    </div>
                    <div class="card-body py-5">
                        <p class="card-text">Tổng sản phẩm</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="card-title"><b>98</b></h4>
                            <p class="mb-0 fw-semibold text-success percentage percentage-positive">+12.75%</p>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Tổng đơn hàng -->
            <a href="link-to-don-hang-page.php" class="card-link">
                <div class="card mb-4" style="width: 18rem;">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <i class="fa-solid fa-cart-shopping fa-2x"></i> <!-- Giỏ hàng -->
                    </div>
                    <div class="card-body py-5">
                        <p>Tổng đơn hàng</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="card-title"><b>256</b></h4>
                            <p class="mb-0 fw-semibold text-danger percentage percentage-negative">-4.95%</p>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Tổng khách hàng -->
            <a href="link-to-khach-hang-page.php" class="card-link">
                <div class="card mb-4" style="width: 18rem;">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <i class="fa-solid fa-users fa-2x"></i>
                    </div>
                    <div class="card-body py-5">
                        <p class="card-text">Tổng khách hàng</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="card-title"><b>3,125</b></h4>
                            <p class="mb-0 fw-semibold text-success percentage percentage-positive">+3.5%</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <hr>

        <!-- biểu đồ -->
        <div class="mb-4">
            <?php
                $year = date('Y');
                echo "<h4 class='fw-bold'>Thống kê doanh thu năm $year</h4>";
            ?>
        </div>
        <canvas id="revenueChart" height="100"></canvas>
    </div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<script>
    const ctx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
            datasets: [{
                label: 'Doanh thu (VNĐ)',
                data: [50000000, 71000000, 60000000, 80000000, 35000000],
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