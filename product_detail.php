<?php include('template/head.php') ?>
<?php include('template/header.php') ?>
<?php include('template/toastMess.php') ?>
<?php include_once 'model/m_wishlist.php'; ?>

<?php
    $id = isset($_GET['id']) ? $_GET['id'] : 0;
    $db = new M_database();
    $db->setQuery("SELECT * FROM products WHERE MaSP = '$id'");
    $result = $db->excuteQuery();

    $product = $result ? $result->fetch_assoc() : null;
    $db->close();

    if (!$product) {
        echo "<div class='container py-5'><h3 class='text-danger'>Sản phẩm không tồn tại</h3></div>";
        include('template/footer.php');
        exit;
    }
?>

<div class="container py-5" style="min-height: 68vh;">
    <div class="row">
        <div class="col-md-5">
            <img src="<?= $product['ImageSP'] ?>" class="img-fluid rounded shadow" alt="<?= $product['TenSP'] ?>">
        </div>
        <div class="col-md-7">
            <h2><?= $product['TenSP'] ?></h2>
            <?php
                require_once 'model/m_comment.php';
                $cm = new M_comment();
                $avg = $cm->getAverageRating($product['MaSP']);
            ?>
            <div class="mb-2">
                <strong>Đánh giá:</strong>
                <span style="color:#f39c12;font-size:18px;"><?= $avg['avg'] ?> / 5</span>
                <small class="text-muted">(<?= $avg['count'] ?> đánh giá)</small>
            </div>
            <script>
                (function(){
                    // Star rating widget
                    function initStars(containerSelector){
                        document.querySelectorAll(containerSelector).forEach(function(container){
                            var stars = container.querySelectorAll('.star');
                            var inputId = container.getAttribute('data-target-input');
                            var input = document.getElementById(inputId);
                            var setRating = function(value){
                                stars.forEach(function(s){
                                    s.classList.toggle('filled', parseInt(s.dataset.value) <= value);
                                });
                                if (input) input.value = value || '';
                            };
                            stars.forEach(function(s){
                                s.addEventListener('mouseenter', function(){ setRating(parseInt(s.dataset.value)); });
                                s.addEventListener('click', function(){ setRating(parseInt(s.dataset.value)); });
                            });
                            container.addEventListener('mouseleave', function(){
                                // preserve selected value
                                var v = input ? parseInt(input.value||0) : 0;
                                setRating(v);
                            });
                        });
                    }
                    document.addEventListener('DOMContentLoaded', function(){ initStars('.star-rating'); });
                })();
            </script>
            <p class="text-muted">Phân loại: <?= $product['PhanLoai'] ?> | Ngày sản xuất: <?= $product['NSX'] ?></p>
            <h4 class="text-danger"><?= number_format($product['GiaTien'], 0, ',', '.') ?>đ</h4>
            
            <p><strong>Đã bán:</strong> <?= $product['Sold'] ?? 0 ?></p>
            <p><strong>Còn lại:</strong> <?= $product['SoLuong'] ?? 0 ?></p>

            <p class="mt-3">Thông tin sản phẩm: <?= nl2br($product['MoTa']) ?></p>

            <form method="post" action="controller/c_addToCart.php" class="mt-4">
                <input type="hidden" id="product_id" name="product_id" value="<?= $product['MaSP'] ?>" required>

                <div class="mb-3">
                    <label for="quantity" class="form-label me-2">Số lượng:</label>
                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?= $product['SoLuong'] ?>" class="form-control w-25 d-inline-block" required>
                </div>

                <button type="submit" class="btn btn-success">🛒 Thêm vào giỏ hàng</button>
                <?php $fav = ($isLoggedIn) ? (new M_wishlist())->isFavorited($maKH, $product['MaSP']) : false; ?>
                <button type="button" class="btn btn-outline-danger ms-2 fav-btn" data-product-id="<?= $product['MaSP'] ?>" data-favorited="<?= $fav?1:0 ?>">
                    <i class="fas fa-heart text-<?= $fav? 'danger':'muted' ?>"></i> Yêu thích
                </button>
                <a href="index.php" class="btn btn-secondary ms-2">← Quay lại</a>
            </form>

            <script>
                (function(){
                    try {
                        document.querySelectorAll('.fav-btn').forEach(function(btn){
                            btn.addEventListener('click', function(e){
                                console.log('[inline-fav] clicked', this);
                                var productId = this.getAttribute('data-product-id');
                                var fav = this.getAttribute('data-favorited') === '1';
                                var action = fav ? 'remove' : 'add';
                                fetch('controller/c_wishlist.php', {
                                    method: 'POST',
                                    headers: {'X-Requested-With':'XMLHttpRequest','Content-Type':'application/x-www-form-urlencoded'},
                                    body: 'action=' + encodeURIComponent(action) + '&product_id=' + encodeURIComponent(productId)
                                }).then(function(r){ return r.json(); }).then(function(data){
                                    console.log('[inline-fav] response', data);
                                    if (data && data.success) {
                                        // visual feedback: toggle class and attribute
                                        btn.setAttribute('data-favorited', fav ? '0' : '1');
                                        var ic = btn.querySelector('i.fas'); if (ic) {
                                            if (fav) { ic.classList.remove('text-danger'); ic.classList.add('text-muted'); }
                                            else { ic.classList.remove('text-muted'); ic.classList.add('text-danger'); }
                                        }
                                        // update wishlist badge if present
                                        var badge = document.querySelector('.wishlist-count'); if (badge) badge.innerText = data.count>99? '99+' : data.count;
                                    } else if (data && data.message) {
                                        alert(data.message);
                                    }
                                }).catch(function(err){ console.error(err); alert('Lỗi kết nối tới server'); });
                            });
                        });
                    } catch(e) { console.error(e); }
                })();
            </script>

            <hr>
            <!-- Comments & Rating -->
            <div class="card mt-4 p-3">
                <h5>Đánh giá và bình luận</h5>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <form method="post" action="controller/c_comment.php" class="mb-3" id="comment-form">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="MaSP" value="<?= htmlspecialchars($product['MaSP']) ?>">
                        <div class="mb-2">
                            <label class="form-label">Đánh giá:</label>
                            <div class="star-rating" data-target-input="rating-input">
                                <span class="star" data-value="1">☆</span>
                                <span class="star" data-value="2">☆</span>
                                <span class="star" data-value="3">☆</span>
                                <span class="star" data-value="4">☆</span>
                                <span class="star" data-value="5">☆</span>
                            </div>
                            <input type="hidden" name="Rating" id="rating-input" value="">
                        </div>
                        <div class="mb-2">
                            <textarea name="Content" class="form-control" rows="3" placeholder="Viết bình luận..."></textarea>
                        </div>
                        <button class="btn btn-primary btn-sm">Gửi</button>
                    </form>
                <?php else: ?>
                    <p><a href="signIn.php">Đăng nhập</a> để đánh giá hoặc bình luận.</p>
                <?php endif; ?>

                <?php
                    $comments = $cm->getCommentsByProduct($product['MaSP']);
                    if ($comments && $comments->num_rows > 0):
                        while ($c = $comments->fetch_assoc()):
                            if ($c['Hidden']) {
                                // show placeholder for hidden comment except to admin or owner
                                $isOwner = (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $c['MaTK']);
                                $isAdmin = isset($_SESSION['levelID']) && $_SESSION['levelID'] == 1;
                                if (!($isOwner || $isAdmin)) continue;
                            }
                ?>
                    <?php $hiddenClass = (isset($c['Hidden']) && $c['Hidden']) ? 'comment-hidden' : ''; ?>
                    <div class="border rounded p-2 mb-2 <?= $hiddenClass ?>">
                        <div class="d-flex justify-content-between">
                            <div class="d-flex align-items-start">
                                <?php $avatarSrc = !empty($c['Avatar']) ? $c['Avatar'] : 'media/image/avatars/default.png'; ?>
                                <img src="<?= htmlspecialchars($avatarSrc) ?>" alt="avatar" style="width:46px;height:46px;object-fit:cover;border-radius:50%;margin-right:10px;">
                                <div>
                                    <strong><?= htmlspecialchars($c['TenTK']) ?></strong>
                                    <?php if (!empty($c['Rating'])): ?>
                                        <span style="color:#f39c12;"> - <?= intval($c['Rating']) ?>★</span>
                                    <?php endif; ?>
                                    <small class="text-muted"> · <?= $c['CreatedAt'] ?></small>
                                </div>
                            </div>
                            <div>
                                <?php $isOwner = (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $c['MaTK']); $isAdmin = isset($_SESSION['levelID']) && $_SESSION['levelID'] == 1; ?>
                                <?php if ($isAdmin || $isOwner): ?>
                                    <a href="#" onclick="document.getElementById('edit-<?= $c['id'] ?>').style.display='block';return false;" class="me-2">Sửa</a>
                                    <a href="controller/c_comment.php?action=delete&id=<?= $c['id'] ?>&MaSP=<?= urlencode($product['MaSP']) ?>" onclick="return confirm('Xóa bình luận này?')" class="text-danger me-2">Xóa</a>
                                <?php else: ?>
                                    <!-- other users can hide (soft-hide) their own? only owner can hide -->
                                <?php endif; ?>
                                <?php if ($isOwner || $isAdmin): ?>
                                    <form method="post" action="controller/c_comment.php" style="display:inline;">
                                        <input type="hidden" name="action" value="hide">
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                        <input type="hidden" name="MaSP" value="<?= htmlspecialchars($product['MaSP']) ?>">
                                        <input type="hidden" name="hidden" value="<?= (isset($c['Hidden']) && $c['Hidden'])?0:1 ?>">
                                        <button class="btn btn-sm btn-outline-secondary" type="submit"><?= (isset($c['Hidden']) && $c['Hidden']) ? 'Bỏ ẩn' : 'Ẩn' ?></button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="mt-2"><?= nl2br(htmlspecialchars($c['Content'])) ?></div>

                        <!-- edit area (hidden) -->
                        <div id="edit-<?= $c['id'] ?>" style="display:none;margin-top:8px;">
                            <form method="post" action="controller/c_comment.php">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                <input type="hidden" name="MaSP" value="<?= htmlspecialchars($product['MaSP']) ?>">
                                <div class="mb-2">
                                    <select name="Rating" class="form-select w-25">
                                        <option value="">Không đánh giá</option>
                                        <?php for ($r=5;$r>=1;$r--): ?>
                                            <option value="<?= $r ?>" <?= (isset($c['Rating']) && $c['Rating']==$r)?'selected':'' ?>><?= $r ?> sao</option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <textarea name="Content" class="form-control mb-2"><?= htmlspecialchars($c['Content']) ?></textarea>
                                <button class="btn btn-primary btn-sm" type="submit">Lưu</button>
                                <button class="btn btn-secondary btn-sm" type="button" onclick="this.closest('#edit-<?= $c['id'] ?>').style.display='none'">Hủy</button>
                            </form>
                        </div>
                    </div>
                <?php
                        endwhile;
                    else:
                ?>
                    <p class="text-muted">Chưa có bình luận nào.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include('template/footer.php') ?>
