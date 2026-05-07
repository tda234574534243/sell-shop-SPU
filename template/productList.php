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


    // Order handling: support price sort (sort=asc/desc), order by rating or bestseller via 'order' param
    $orderParam = $_GET['order'] ?? '';

    // Default select (we may need avg rating)
    $useRating = ($orderParam === 'rating');

    if ($useRating) {
        // select with subquery avg rating
        $sql = str_replace("SELECT * FROM products", "SELECT p.*, COALESCE((SELECT AVG(Rating) FROM Comments WHERE MaSP = p.MaSP),0) as avgR FROM products p", $sql);
    }

    // Build ORDER BY clause
    $orderClause = '';
    if ($sortPrice === 'asc') {
        $orderClause = 'GiaTien ASC';
    } elseif ($sortPrice === 'desc') {
        $orderClause = 'GiaTien DESC';
    }

    if ($orderParam === 'rating') {
        $orderClause = 'avgR DESC';
    } elseif ($orderParam === 'bestseller') {
        $orderClause = 'Sold DESC';
    }

    if ($orderClause !== '') {
        $sql .= ' ORDER BY ' . $orderClause;
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

    <div class="row">
        <div class="col-lg-3 mb-4">
            <div class="card p-3">
                <h5>Lọc tìm kiếm</h5>
                <form method="get">
                    <div class="mb-2">
                        <label class="form-label">Từ khóa</label>
                        <input type="text" name="query" class="form-control" value="<?= htmlspecialchars($_GET['query'] ?? '') ?>" placeholder="Tên, mã, mô tả...">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Năm</label>
                        <select name="year" class="form-select">
                            <option value="">Tất cả</option>
                            <?php foreach($years as $y): ?>
                                <option value="<?= $y ?>" <?= $filterYear===$y?'selected':'' ?>><?= $y ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Phân loại</label>
                        <select name="category" class="form-select">
                            <option value="">Tất cả</option>
                            <?php foreach($categories as $c): ?>
                                <option value="<?= htmlspecialchars($c) ?>" <?= $filterCategory===$c?'selected':'' ?>><?= htmlspecialchars($c) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Sắp xếp theo giá</label>
                        <select name="sort" class="form-select">
                            <option value="">Không</option>
                            <option value="asc" <?= $sortPrice==='asc'?'selected':'' ?>>Giá tăng dần</option>
                            <option value="desc" <?= $sortPrice==='desc'?'selected':'' ?>>Giá giảm dần</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Sắp xếp đặc biệt</label>
                        <select name="order" class="form-select">
                            <option value="">Mặc định</option>
                            <option value="rating" <?= (($_GET['order'] ?? '')==='rating')?'selected':'' ?>>Theo đánh giá cao</option>
                            <option value="bestseller" <?= (($_GET['order'] ?? '')==='bestseller')?'selected':'' ?>>Sản phẩm bán chạy</option>
                        </select>
                    </div>
                    <div class="d-grid">
                        <button class="btn btn-success">Áp dụng</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-9">
    <?php
        // Pagination: show at most 3 rows per page. Grid shows 3 columns on large screens => 3 rows * 3 cols = 9 items per page.
        $perPage = 9;
        $total = count($products);
        $totalPages = max(1, (int)ceil($total / $perPage));
        $currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        if ($currentPage > $totalPages) $currentPage = $totalPages;
        $start = ($currentPage - 1) * $perPage;
        $pageItems = array_slice($products, $start, $perPage);
    ?>

            <div class="row">
                <?php if(empty($products)): ?>
                    <div class="col-12">
                        <p class="text-center text-muted">Không có sản phẩm nào phù hợp.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($pageItems as $product): ?>
                        <div class="col-12 col-sm-6 col-md-4 col-lg-4 mb-4">
                            <div class="card h-100">
                                <img src="<?= htmlspecialchars($product['ImageSP']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['TenSP']) ?>">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?= htmlspecialchars($product['TenSP']) ?></h5>
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

            <?php if ($totalPages > 1): ?>
                <nav aria-label="Product pagination">
                    <ul class="pagination justify-content-center">
                        <?php
                            // build base url preserving existing query parameters except page
                            $params = $_GET;
                            unset($params['page']);
                            $base = strtok($_SERVER["REQUEST_URI"], '?');
                            $baseQuery = http_build_query($params);
                            if ($baseQuery) $base .= '?' . $baseQuery . '&page='; else $base .= '?page=';
                        ?>
                        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                            <li class="page-item <?= $p === $currentPage ? 'active' : '' ?>">
                                <a class="page-link" href="<?= $base . $p ?>"><?= $p ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>