<?php
    $db = new M_database();

   $db->setQuery("SELECT DISTINCT RIGHT(NSX, 4) as YearOnly FROM products ORDER BY YearOnly DESC");
    $resYear = $db->excuteQuery();
    $years = [];
    while ($row = $resYear->fetch_assoc()) {
        $years[] = $row['YearOnly'];
    }

    // Lấy danh sách phân loại
    $db->setQuery("SELECT DISTINCT PhanLoai FROM products");
    $resCategory = $db->excuteQuery();
    $categories = [];
    while ($row = $resCategory->fetch_assoc()) {
        $categories[] = $row['PhanLoai'];
    }

    // Lấy filter từ URL
    $filterYear = $_GET['year'] ?? '';
    $filterCategory = $_GET['category'] ?? '';
    $sortPrice = $_GET['sort'] ?? '';
    $query = $_GET['query'] ?? '';

    // Xây dựng query động
    $sql = "SELECT * FROM products WHERE 1=1";

    // Lọc theo năm sản xuất
    if ($filterYear !== '') {
        $sql .= " AND RIGHT(NSX, 4) = " . (int)$filterYear;
    }

    // Lọc theo phân loại
    if ($filterCategory !== '') {
        $sql .= " AND PhanLoai = '" . $db->conn->real_escape_string($filterCategory) . "'";
    }

    if ($query !== '') {
        $query = $db->conn->real_escape_string($query); // escape trước
        $keywords = explode(" ", $query);
        
        $sql .= " AND (";
        foreach ($keywords as $i => $word) {
            $word = $db->conn->real_escape_string($word);
            if ($i > 0) $sql .= " OR ";
            $sql .= "TenSP LIKE '%$word%' OR MaSP LIKE '%$word%' OR PhanLoai LIKE '%$word%' OR MoTa LIKE '%$word%' OR GiaTien LIKE '%$word%'";
        }
        $sql .= ")";
    }


    // Sắp xếp theo giá
    if ($sortPrice === 'asc') {
        $sql .= " ORDER BY GiaTien ASC";
    } elseif ($sortPrice === 'desc') {
        $sql .= " ORDER BY GiaTien DESC";
    }

    // Thực thi query và lấy dữ liệu
    $db->setQuery($sql);
    $result = $db->excuteQuery();

    $products = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }

    $db->close();
?>


<div class="container__product container py-5">
    <h2 class="text-center mb-4">Danh sách sản phẩm</h2>

    <!-- Filter Form -->
    <form class="row g-3 mb-4" method="get">
        <input type="hidden" name="query" value="<?= htmlspecialchars($_GET['query'] ?? '') ?>">
        <div class="col-md-3">
            <select name="year" class="form-select">
                <option value=""> Năm sản xuất </option>
                <?php foreach($years as $y): ?>
                    <option value="<?= $y ?>"
                        <?= $filterYear===$y?'selected':'' ?>>
                        <?= $y ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <select name="category" class="form-select">
                <option value=""> Phân loại </option>
                <?php foreach($categories as $c): ?>
                    <option value="<?= $c ?>"
                        <?= $filterCategory===$c?'selected':'' ?>>
                        <?= $c ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <select name="sort" class="form-select">
                <option value=""> Sắp xếp giá </option>
                <option value="asc"  <?= $sortPrice==='asc'?'selected':'' ?>>Giá tăng dần</option>
                <option value="desc" <?= $sortPrice==='desc'?'selected':'' ?>>Giá giảm dần</option>
            </select>
        </div>
        <div class="col-md-3 d-grid">
            <button type="submit" class="btn btn-success">Áp dụng</button>
        </div>
    </form>

    <!-- Product Grid -->
    <div class="row">
        <?php if(empty($products)): ?>
            <div class="col-12">
                <p class="text-center text-muted">Không có sản phẩm nào phù hợp.</p>
            </div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">
                    <div class="card h-100">
                        <img src="<?= $product['ImageSP'] ?>" class="card-img-top" alt="<?= $product['TenSP'] ?>">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= $product['TenSP'] ?></h5>
                            <p class="card-text text-danger fw-bold"><?= number_format($product['GiaTien'],0,',','.') ?>đ</p>
                            <p class="card-text small text-muted mb-1">
                            </p>
                            <a href="product_detail.php?id=<?= $product['MaSP'] ?>" class="btn btn-primary mt-auto">Xem chi tiết</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>