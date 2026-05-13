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


<div class="container__product max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <h2 class="text-center text-slate-100 mb-6 text-2xl font-montserrat font-bold">Danh sách sản phẩm</h2>

    <div class="grid grid-cols-12 gap-6">
        <!-- Filters -->
        <aside class="col-span-12 lg:col-span-3">
            <div class="soft-shadow glass-effect rounded-2xl p-4">
                <h5 class="font-semibold text-slate-200 mb-3">Lọc tìm kiếm</h5>
                <form method="get" class="space-y-3">
                    <div>
                        <label class="block text-xs text-slate-300 mb-1">Từ khóa</label>
                        <input type="text" name="query" class="w-full glass-effect rounded-xl px-3 py-2 text-slate-200 placeholder-slate-400 bg-slate-800/40 border border-slate-700" value="<?= htmlspecialchars($_GET['query'] ?? '') ?>" placeholder="Tên, mã, mô tả...">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-300 mb-1">Năm</label>
                        <select name="year" class="w-full glass-effect rounded-xl px-3 py-2 text-slate-200 bg-slate-800/40 border border-slate-700">
                            <option value="">Tất cả</option>
                            <?php foreach($years as $y): ?>
                                <option value="<?= $y ?>" <?= $filterYear===$y?'selected':'' ?>><?= $y ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-300 mb-1">Phân loại</label>
                        <select name="category" class="w-full glass-effect rounded-xl px-3 py-2 text-slate-200 bg-slate-800/40 border border-slate-700">
                            <option value="">Tất cả</option>
                            <?php foreach($categories as $c): ?>
                                <option value="<?= htmlspecialchars($c) ?>" <?= $filterCategory===$c?'selected':'' ?>><?= htmlspecialchars($c) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-300 mb-1">Sắp xếp theo giá</label>
                        <select name="sort" class="w-full glass-effect rounded-xl px-3 py-2 text-slate-200 bg-slate-800/40 border border-slate-700">
                            <option value="">Không</option>
                            <option value="asc" <?= $sortPrice==='asc'?'selected':'' ?>>Giá tăng dần</option>
                            <option value="desc" <?= $sortPrice==='desc'?'selected':'' ?>>Giá giảm dần</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-300 mb-1">Sắp xếp đặc biệt</label>
                        <select name="order" class="w-full glass-effect rounded-xl px-3 py-2 text-slate-200 bg-slate-800/40 border border-slate-700">
                            <option value="">Mặc định</option>
                            <option value="rating" <?= (($_GET['order'] ?? '')==='rating')?'selected':'' ?>>Theo đánh giá cao</option>
                            <option value="bestseller" <?= (($_GET['order'] ?? '')==='bestseller')?'selected':'' ?>>Sản phẩm bán chạy</option>
                        </select>
                    </div>
                    <div>
                        <button class="w-full py-2 rounded-xl bg-gradient-to-r from-indigo-500 to-indigo-600 text-white font-semibold">Áp dụng</button>
                    </div>
                </form>
            </div>
        </aside>

        <section class="col-span-12 lg:col-span-9">
    <?php
        // Pagination: show at most 3 rows per page. Grid shows 4 columns on large screens => 3 rows * 4 cols = 12 items per page.
        $perPage = 12;
        $total = count($products);
        $totalPages = max(1, (int)ceil($total / $perPage));
        $currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        if ($currentPage > $totalPages) $currentPage = $totalPages;
        $start = ($currentPage - 1) * $perPage;
        $pageItems = array_slice($products, $start, $perPage);
    ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php if(empty($products)): ?>
                    <div class="col-span-full">
                        <p class="text-center text-slate-400">Không có sản phẩm nào phù hợp.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($pageItems as $product): ?>
                        <div class="">
                            <div class="soft-shadow glass-effect rounded-2xl overflow-hidden h-full flex flex-col">
                                <div class="w-full h-48 bg-slate-800">
                                    <img src="<?= htmlspecialchars($product['ImageSP']) ?>" alt="<?= htmlspecialchars($product['TenSP']) ?>" class="w-full h-full object-cover">
                                </div>
                                <div class="p-4 flex-1 flex flex-col">
                                    <h3 class="text-sm md:text-base font-semibold text-slate-100 mb-2 line-clamp-2"><?= htmlspecialchars($product['TenSP']) ?></h3>
                                    <div class="mt-auto">
                                        <div class="text-indigo-300 font-bold text-lg"><?= number_format($product['GiaTien'],0,',','.') ?>đ</div>
                                        <div class="mt-3">
                                            <a href="product_detail.php?id=<?= $product['MaSP'] ?>" class="inline-block w-full text-center py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold">Xem chi tiết</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <?php if ($totalPages > 1): ?>
                <nav aria-label="Product pagination" class="mt-6">
                    <div class="flex justify-center">
                        <div class="inline-flex items-center space-x-2 bg-slate-900/40 soft-shadow glass-effect rounded-full px-3 py-2">
                            <?php
                                $params = $_GET;
                                unset($params['page']);
                                $base = strtok($_SERVER["REQUEST_URI"], '?');
                                $baseQuery = http_build_query($params);
                                if ($baseQuery) $base .= '?' . $baseQuery . '&page='; else $base .= '?page=';
                            ?>
                            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                                <a href="<?= $base . $p ?>" class="px-3 py-1 rounded-md <?php if ($p === $currentPage) echo 'bg-indigo-600 text-white'; else echo 'text-slate-300 hover:bg-slate-800'; ?>"><?php echo $p ?></a>
                            <?php endfor; ?>
                        </div>
                    </div>
                </nav>
            <?php endif; ?>
        </section>
    </div>
</div>