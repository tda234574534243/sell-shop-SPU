<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        html, body {
            height: 100%;
            font-family: 'Segoe UI', sans-serif;
        }
        
        .sidebar {
            background-color:rgb(44, 58, 80);
            color: #ecf0f1;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            width: 250px;
            height: 100vh;
        }
        .sidebar a {
            color: #ecf0f1;
            text-decoration: none;
            display: block;
            padding: 10px 15px;
            border-radius: 5px;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background-color: #34495e;
        }
        .sidebar a i {
            margin-right: 10px;
        }
        
        .notification-badge {
            background-color: #f1c40f;
            color: #2c3e50;
            font-size: 13px;
            padding: 2px 6px;
            border-radius: 10px;
            float: right;
        }
       /*  */
       .sidebar.collapsed {
            width: 70px;
        }

        .sidebar.collapsed a span {
            display: none;
        }

        .sidebar.collapsed h4 {
            display: none;
        }
        .sidebar.collapsed a {
            text-align: center;
        }

        .sidebar.collapsed a i {
            margin-right: 0;
        }
        #mainContent
        {
            margin-left: 70px;
        }
        .sidebar ul.nav li {
            margin-left: -8px;
            margin-bottom: 5px;
        }
        
    </style>
    <style>
        /* Mặc định ô phân trang là màu đen và có dạng ô vuông */
        .pagination .page-item .page-link {
            color: black; /* Màu chữ trắng */
            border: 1px solid black; /* Viền ô phân trang */
            padding: 8px 16px; /* Thêm khoảng cách cho ô phân trang */
            border-radius: 0; /* Không bo góc, để có dạng ô vuông */
        }

        /* Màu cam khi ô phân trang được chọn (active) */
        .pagination .page-item.active .page-link {
            background-color: #ff8c00; /* Màu cam đẹp */
            border-color: #ff8c00; /* Viền cam */
        }

        /* Khi hover lên các ô phân trang */
        .pagination .page-link:hover {
            background-color: #555; /* Màu nền xám đậm khi hover */
            color: white; /* Chữ vẫn là màu trắng khi hover */
        }

        /* Khi ô phân trang bị disabled (không thể click) */
        .pagination .page-item.disabled .page-link {
            color: #ccc; /* Màu xám nhạt cho các ô không thể click */
            background-color: transparent;
            border-color: transparent;
        }


    </style>
</head>
<body>
    <div class="container-fluid p-0 d-flex h-90vh flex-column">
        <div id="sidebarMenu" class="sidebar  collapsed d-flex flex-column flex-shrink-0 p-3">
       
            <button id="toggleSidebar" class="btn btn-light d-flex justify-content-center align-items-center" style="width: 100%; height: 40px; margin-bottom:10px;">
                <i class="fa-solid fa-bars"></i>
            </button>

            <h4 class="text-center mb-4"><i class="fa-solid fa-store me-2"></i>Trang Admin</h4> <hr>
            <ul class="nav nav-pills flex-column mb-auto">
                <!-- <li>
                    <a href="./analystic_general.php"><i class="fa-solid fa-chart-line"></i><span class="ms-2">Thống kê</span></a>
                </li> -->
                <li>
                    <a href="./analystic_product.php"><i class="fa-solid fa-box"></i><span class="ms-2">Sản phẩm</span></a>
                </li>
                <li>
                    <a href="./statistic_order.php"><i class="fa-solid fa-file-invoice"></i><span class="ms-2">Đơn hàng</span> </a>
                </li>
                <li>
                    <a href="./analystic_customer.php"><i class="fa-solid fa-users"></i><span class="ms-2">Khách hàng</span></a>
                </li>
                <li>
                    <a href="#" id="logoutButton">
                        <i class="fa-solid fa-right-from-bracket"></i><span class="ms-2">Đăng xuất</span>
                    </a>
                </li>
            </ul>
        </div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

