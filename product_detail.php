<?php include('template/head.php') ?>
<?php include('template/header.php') ?>
<?php include('template/toastMess.php') ?>
<?php include_once 'model/m_wishlist.php'; ?>

<?php
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    $db = new M_database();
    $conn = $db->getConnection();
    $product = null;
    if ($stmt = $conn->prepare("SELECT * FROM products WHERE MaSP = ?")) {
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result ? $result->fetch_assoc() : null;
        $stmt->close();
    } else {
        // fallback to safe escaped query if prepare fails
        $safeId = $db->real_escape_string($id);
        $db->setQuery("SELECT * FROM products WHERE MaSP = '$safeId'");
        $result = $db->excuteQuery();
        $product = $result ? $result->fetch_assoc() : null;
    }
    $db->close();

    if (!$product) {
        echo "<div class='container py-5'><h3 class='text-danger'>Sản phẩm không tồn tại</h3></div>";
        include('template/footer.php');
        exit;
    }
?>

<main class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-12 gap-6 items-start">
        <div class="col-span-12 md:col-span-5">
            <div class="soft-shadow glass-effect rounded-2xl overflow-hidden p-4">
                <img src="<?= $product['ImageSP'] ?>" class="w-full h-80 object-contain bg-slate-900 rounded-lg" alt="<?= $product['TenSP'] ?>">
            </div>
        </div>
        <div class="col-span-12 md:col-span-7">
            <div class="soft-shadow glass-effect rounded-2xl p-6">
                <h2 class="text-2xl font-montserrat font-bold text-slate-100 mb-2"><?= $product['TenSP'] ?></h2>
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
                <p class="text-sm text-slate-400">Phân loại: <?= $product['PhanLoai'] ?> · Ngày sản xuất: <?= $product['NSX'] ?></p>
                <div class="mt-4 mb-4">
                    <div class="text-2xl text-rose-400 font-bold"><?= number_format($product['GiaTien'], 0, ',', '.') ?>đ</div>
                    <div class="flex gap-4 text-sm text-slate-300 mt-2">
                        <div>Đã bán: <span class="font-semibold text-slate-100"><?= $product['Sold'] ?? 0 ?></span></div>
                        <div>Còn lại: <span class="font-semibold text-slate-100"><?= $product['SoLuong'] ?? 0 ?></span></div>
                    </div>
                </div>

                <div class="prose prose-invert mb-4 text-slate-200">Thông tin sản phẩm:<div class="mt-2 text-sm"><?= nl2br($product['MoTa']) ?></div></div>

                <form method="post" action="controller/c_addToCart.php" class="mt-4 flex flex-col sm:flex-row items-start gap-3">
                    <input type="hidden" id="product_id" name="product_id" value="<?= $product['MaSP'] ?>" required>
                    <div class="flex items-center gap-3">
                        <label for="quantity" class="text-sm text-slate-300">Số lượng:</label>
                        <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?= $product['SoLuong'] ?>" class="w-20 rounded-lg bg-slate-900/40 px-3 py-2 text-slate-100" required>
                    </div>

                    <div class="flex gap-2 mt-2 sm:mt-0">
                        <button type="submit" class="px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-500 to-indigo-600 text-white font-semibold">🛒 Thêm vào giỏ hàng</button>
                        <?php $fav = ($isLoggedIn) ? (new M_wishlist())->isFavorited($maKH, $product['MaSP']) : false; ?>
                        <button type="button" class="px-4 py-2 rounded-xl border border-rose-500 text-rose-400 fav-btn" data-product-id="<?= $product['MaSP'] ?>" data-favorited="<?= $fav?1:0 ?>">
                            <i class="fas fa-heart <?= $fav? 'text-rose-400':'' ?>"></i>
                        </button>
                        <a href="index.php" class="inline-flex items-center px-4 py-2 rounded-xl bg-slate-800 text-slate-300">← Quay lại</a>
                    </div>
                </form>

            <script>
                (function(){
                    try {
                        document.querySelectorAll('.fav-btn').forEach(function(btn){
                            btn.addEventListener('click', function(e){
                                var productId = this.getAttribute('data-product-id');
                                var fav = this.getAttribute('data-favorited') === '1';
                                var action = fav ? 'remove' : 'add';
                                fetch('controller/c_wishlist.php', {
                                    method: 'POST',
                                    headers: {'X-Requested-With':'XMLHttpRequest','Content-Type':'application/x-www-form-urlencoded'},
                                    body: 'action=' + encodeURIComponent(action) + '&product_id=' + encodeURIComponent(productId)
                                }).then(function(r){ return r.json(); }).then(function(data){
                                    if (data && data.success) {
                                        btn.setAttribute('data-favorited', fav ? '0' : '1');
                                        var ic = btn.querySelector('i.fas'); if (ic) {
                                            if (fav) { ic.classList.remove('text-rose-400'); ic.classList.add('text-slate-300'); }
                                            else { ic.classList.remove('text-slate-300'); ic.classList.add('text-rose-400'); }
                                        }
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

            <hr class="my-6 border-slate-700/40">
            <div class="mt-4">
                <h4 class="text-lg font-semibold text-slate-100 mb-3">Đánh giá và bình luận</h4>
                <?php if (isset($_SESSION['user_id'])):
                    $hasComment = $cm->userHasComment($product['MaSP'], $_SESSION['user_id']);
                ?>
                    <?php if (!$hasComment): ?>
                    <form method="post" action="controller/c_comment.php" class="mb-4" id="comment-form">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="MaSP" value="<?= htmlspecialchars($product['MaSP']) ?>">
                        <div class="mb-2">
                            <label class="block text-sm text-slate-300 mb-1">Đánh giá:</label>
                            <div class="star-rating flex gap-1" data-target-input="rating-input">
                                <button type="button" class="star text-2xl" data-value="1">☆</button>
                                <button type="button" class="star text-2xl" data-value="2">☆</button>
                                <button type="button" class="star text-2xl" data-value="3">☆</button>
                                <button type="button" class="star text-2xl" data-value="4">☆</button>
                                <button type="button" class="star text-2xl" data-value="5">☆</button>
                            </div>
                            <input type="hidden" name="Rating" id="rating-input" value="">
                        </div>
                        <div class="mb-2">
                            <textarea name="Content" class="w-full rounded-lg bg-slate-900/40 px-3 py-2 text-slate-100" rows="3" placeholder="Viết bình luận..."></textarea>
                        </div>
                        <button class="px-4 py-2 rounded-lg bg-indigo-600 text-white">Gửi</button>
                    </form>
                    <?php else: ?>
                        <p class="text-slate-300">Bạn đã gửi đánh giá/bình luận cho sản phẩm này. Nếu muốn chỉnh sửa, tìm bình luận của bạn bên dưới và chọn <strong>Sửa</strong>.</p>
                    <?php endif; ?>
                <?php else: ?>
                    <p><a href="signIn.php" class="text-indigo-400">Đăng nhập</a> để đánh giá hoặc bình luận.</p>
                <?php endif; ?>

                <?php
                    $comments = $cm->getCommentsByProduct($product['MaSP']);
                    if ($comments && $comments->num_rows > 0):
                        while ($c = $comments->fetch_assoc()):
                            if ($c['Hidden']) {
                                $isOwner = (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $c['MaTK']);
                                $isAdmin = isset($_SESSION['levelID']) && $_SESSION['levelID'] == 1;
                                if (!($isOwner || $isAdmin)) continue;
                            }
                ?>
                    <?php $hiddenClass = (isset($c['Hidden']) && $c['Hidden']) ? 'opacity-60' : ''; ?>
                    <div class="p-3 rounded-lg mb-3 bg-slate-900/30 <?= $hiddenClass ?>">
                        <div class="flex items-start gap-3">
                            <?php $avatarSrc = !empty($c['Avatar']) ? $c['Avatar'] : 'media/image/avatars/default.png'; ?>
                            <img src="<?= htmlspecialchars($avatarSrc) ?>" alt="avatar" class="w-12 h-12 rounded-full object-cover">
                            <div class="flex-1">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <strong class="text-slate-100"><?= htmlspecialchars($c['TenTK']) ?></strong>
                                        <?php if (!empty($c['Rating'])): ?><span class="text-amber-400"> - <?= intval($c['Rating']) ?>★</span><?php endif; ?>
                                        <div class="text-xs text-slate-400"><?= $c['CreatedAt'] ?></div>
                                    </div>
                                    <div class="text-right">
                                        <?php $isOwner = (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $c['MaTK']); $isAdmin = isset($_SESSION['levelID']) && $_SESSION['levelID'] == 1; ?>
                                        <?php if ($isAdmin || $isOwner): ?>
                                            <a href="#" onclick="document.getElementById('edit-<?= $c['id'] ?>').style.display='block';return false;" class="text-sm text-indigo-400 mr-2">Sửa</a>
                                            <a href="controller/c_comment.php?action=delete&id=<?= $c['id'] ?>&MaSP=<?= urlencode($product['MaSP']) ?>" onclick="return confirm('Xóa bình luận này?')" class="text-sm text-rose-400">Xóa</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="mt-2 text-sm text-slate-200"><?= nl2br(htmlspecialchars($c['Content'])) ?></div>

                                <div id="edit-<?= $c['id'] ?>" style="display:none;margin-top:8px;">
                                    <form method="post" action="controller/c_comment.php" class="mt-3 space-y-2">
                                        <input type="hidden" name="action" value="edit">
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                        <input type="hidden" name="MaSP" value="<?= htmlspecialchars($product['MaSP']) ?>">
                                        <div>
                                            <select name="Rating" class="rounded-lg bg-slate-900/40 px-2 py-1 text-slate-100">
                                                <option value="">Không đánh giá</option>
                                                <?php for ($r=5;$r>=1;$r--): ?>
                                                    <option value="<?= $r ?>" <?= (isset($c['Rating']) && $c['Rating']==$r)?'selected':'' ?>><?= $r ?> sao</option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        <textarea name="Content" class="w-full rounded-lg bg-slate-900/40 px-3 py-2 text-slate-100"><?= htmlspecialchars($c['Content']) ?></textarea>
                                        <div class="flex gap-2">
                                            <button class="px-3 py-1 rounded bg-indigo-600 text-white" type="submit">Lưu</button>
                                            <button class="px-3 py-1 rounded bg-slate-700 text-slate-200" type="button" onclick="this.closest('#edit-<?= $c['id'] ?>').style.display='none'">Hủy</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                        endwhile;
                    else:
                ?>
                    <p class="text-slate-400">Chưa có bình luận nào.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php include('template/footer.php') ?>
