<?php include('template/head.php') ?>
<?php include('template/header.php') ?>
<?php include('template/toastMess.php') ?>

<?php
    include_once('model/m_database.php');
    $db = new M_database();

    $query = $_GET['query'] ?? '';
    // searchProduct now supports filters via GET params; render a two-column layout
    // Left: filter/search box; Right: product list (uses template/productList.php)
    // We'll not run manual queries here; productList.php will build the query based on GET.
    include('template/productList.php');
?>

<?php include('template/footer.php') ?>
