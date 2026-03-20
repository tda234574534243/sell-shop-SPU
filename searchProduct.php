<?php include('template/head.php') ?>
<?php include('template/header.php') ?>
<?php include('template/toastMess.php') ?>

<?php
    include_once('model/m_database.php');
    $db = new M_database();

    $query = $_GET['query'] ?? '';

    if ($query) {
        $conn = $db->getConnection();
        $query = $conn->real_escape_string($query);

        $query = $_GET['query'] ?? '';
        $keywords = explode(" ", $query);
        $sql = "SELECT * FROM products WHERE ";

        foreach ($keywords as $i => $word) {
            $sql .= "TenSP LIKE '%" .$word. "%'";
            $sql .= " OR MaSP LIKE '%" .$word. "%'";
            $sql .= " OR PhanLoai LIKE '%" .$word. "%'";
            $sql .= " OR MoTa LIKE '%" .$word. "%'";
            $sql .= " OR GiaTien LIKE '%" .$word. "%'";
            if ($i < count($keywords) - 1) {
                $sql .= " OR ";
            }
        }

        $db->setQuery($sql);        
        $result = $db->excuteQuery();

        if ($result && $result->num_rows > 0) {
           include ('template/productList.php');
        }
        else {
            echo "<p>Không tìm thấy sản phẩm nào.</p>";
        }
    } else {
        $sql = "SELECT * FROM products WHERE 1=1";
        include('template/productList.php');
    }
?>

<?php include('template/footer.php') ?>
