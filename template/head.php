<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PSHOP Việt Nam</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Bootstrap 5 JS + Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    
    <link rel="stylesheet" href="/sell-shop-SPU/public/CSS/style.css">
    <link rel="stylesheet" href="/sell-shop-SPU/public/CSS/base.css">
    <style>
        /* Header (navbar) blue theme */
        .navbar {
            background: linear-gradient(90deg, #0d6efd 0%, #0b5ed7 100%);
        }
        .navbar .navbar-brand, .navbar .nav-link { color: #fff !important; }
        .navbar .nav-link:hover { color: #e6f0ff !important; }
        /* Footer blue */
        #footer { background: #0b5ed7; color: #fff; padding: 2rem 0; }
        #footer a { color: #e6f0ff; }
        #footer .footer-social-link svg { color: #fff; }
        /* Header specifics */
        .navbar .brand-text { font-weight:700; letter-spacing:0.5px; }
        .navbar .nav-link { color: rgba(255,255,255,0.95) !important; }
        .navbar .nav-link i { font-size: 18px; }
        .badge.badge-danger { background:#dc3545; color:#fff; }
        /* Footer adjustments */
        #footer { background: linear-gradient(90deg,#0b5ed7,#0d6efd); color: #fff; }
        #footer .list-unstyled a { color: #fff; text-decoration:none; }
        #footer .list-unstyled a:hover { text-decoration:underline; }
        /* Small helpers */
        .navbar .form-control { width: 320px; }
        @media (max-width: 991.98px) { .navbar .form-control { width: 100%; margin-bottom:8px; } }

        /* Sticky footer: make body a column flex container and allow main content to grow */
        html, body { height: 100%; }
        body { display: flex; flex-direction: column; min-height: 100vh; }
        /* Make main content containers grow to push footer down */
        body > .container, body > .container-fluid, main { flex: 1 0 auto; }
        /* Ensure footer sticks to bottom when page content is short */
        #footer { margin-top: auto; }
        /* Footer column stability when some columns are empty */
        #footer .footer-col { min-height: 140px; display: flex; flex-direction: column; justify-content: space-between; padding-top: 8px; padding-bottom: 8px; }
        @media (max-width: 767.98px) { #footer .footer-col { min-height: auto; } }
    </style>
    <?php
        // Track user activity on every page
        if (file_exists(__DIR__ . '/../helper/user_tracking.php')) {
            include __DIR__ . '/../helper/user_tracking.php';
        }
    ?>
</head>
<body>
    <!-- Toast Container -->
    <div id="toastcs"></div>
