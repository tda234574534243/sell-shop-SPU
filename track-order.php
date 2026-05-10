<?php include('template/head.php') ?>
<?php include('template/header.php') ?>
<?php include('template/toastMess.php') ?>
<?php
    if (session_status() == PHP_SESSION_NONE) session_start();

    include_once 'model/m_database.php';
    include_once 'model/m_hoadon.php';
    include_once 'model/m_lsMua.php';
    include_once 'model/m_notification.php';
    include_once 'model/m_voucher.php';

    $db = new M_database();
    $hdModel = new M_hoadon();
    $lsModel = new M_lsMua();
    $nm = new M_notification();
    $vm = new M_voucher();

    $pageSlug = 'track-order';
    // Page builder permission check
    include_once 'model/m_pagebuilder.php';
    $pageBuilder = new M_pagebuilder();
    if (!$pageBuilder->canAccessPage($pageSlug)) {
        die('404 - Trang không tồn tại hoặc bạn không có quyền truy cập');
    }

    $sideNotifs = $nm->getActive(5);
    $sideVouchers = $vm->getAll(5);

    $totalCartQty = 0;
    if(isset($_SESSION['cart'])) {
        foreach($_SESSION['cart'] as $item) {
            $totalCartQty += (isset($item['qty']) ? $item['qty'] : 1);
        }
    }

    $order = null;
    $items = null;
    $orders = null; // for logged-in user's multiple orders
    $message = '';

    // Helper: map order State text (Vietnamese) to badge class
    function getOrderStateClass($state) {
        $s = mb_strtolower(trim((string)$state));
        if ($s === '') return 'state-pending';
        if (mb_stripos($s, 'đã giao') !== false || mb_stripos($s, 'da giao') !== false) return 'state-success';
        if (mb_stripos($s, 'đang giao') !== false) return 'state-pending';
        if (mb_stripos($s, 'đang chuẩn bị') !== false || mb_stripos($s, 'chuẩn bị') !== false) return 'state-pending';
        if (mb_stripos($s, 'hủy') !== false || mb_stripos($s, 'cancel') !== false) return 'state-cancel';
        // default
        return 'state-pending';
    }

    // Handle delete request from logged-in user
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['maHD'])) {
        if (!isset($_SESSION['user_id']) || intval($_SESSION['user_id']) <= 0) {
            $message = 'Bạn cần đăng nhập để xóa đơn.';
        } else {
            $delMaHD = intval($_POST['maHD']);
            if ($delMaHD <= 0) {
                $message = 'Mã đơn không hợp lệ.';
            } else {
                $resCheck = $hdModel->getHoaDon($delMaHD);
                if ($resCheck && $resCheck->num_rows > 0) {
                    $rowCheck = $resCheck->fetch_assoc();
                    if (intval($rowCheck['MaTK']) !== intval($_SESSION['user_id'])) {
                        $message = 'Bạn không có quyền xóa đơn này.';
                    } else {
                        // Check item states: prevent deletion if any item is preparing or delivering
                        $orderItemsRes = $lsModel->getLSMua($delMaHD);
                        $forbiddenStates = ['Đang chuẩn bị hàng', 'Đang giao hàng'];
                        $canDelete = true;
                        if ($orderItemsRes && $orderItemsRes->num_rows > 0) {
                            while ($rit = $orderItemsRes->fetch_assoc()) {
                                if (in_array(trim($rit['State']), $forbiddenStates, true)) {
                                    $canDelete = false;
                                    break;
                                }
                            }
                        }

                                if (!$canDelete) {
                                    $message = 'Không thể xóa đơn đang trong trạng thái chuẩn bị hoặc đang giao.';
                                } else {
                                    // Soft-hide invoice for this user so admin data remains intact
                                    $uid = intval($_SESSION['user_id']);
                                    $ok = $hdModel->hideForUser($delMaHD, $uid);
                                    if ($ok === false) {
                                        $message = 'Xóa đơn thất bại, vui lòng thử lại.';
                                    } else {
                                        $message = 'Đã xóa đơn khỏi danh sách của bạn (không ảnh hưởng đến quản trị).';
                                        if (function_exists('log_action')) {
                                            log_action('INFO', 'User hid invoice from their view', ['MaHD' => $delMaHD, 'MaTK' => $uid]);
                                        }
                                    }
                                }
                    }
                } else {
                    $message = 'Đơn không tồn tại.';
                }
            }
        }
    }

    // If user is logged in, load their orders into array and optionally filter by search query
    $ordersArr = [];
    if (isset($_SESSION['user_id']) && intval($_SESSION['user_id']) > 0) {
        $maTK = intval($_SESSION['user_id']);
        $ordersRes = $hdModel->getVisibleHoaDonByMaTK($maTK);
        if ($ordersRes === false || ($ordersRes && $ordersRes->num_rows === 0)) {
            $message = 'Bạn chưa có đơn hàng nào.';
        } else {
            while ($row = $ordersRes->fetch_assoc()) {
                $maHD = $row['MaHD'];
                // fetch items for this order into array
                $itemsRes = $lsModel->getLSMua($maHD);
                $itemsArr = [];
                if ($itemsRes && $itemsRes->num_rows > 0) {
                    while ($it = $itemsRes->fetch_assoc()) $itemsArr[] = $it;
                }
                $row['items'] = $itemsArr;
                $ordersArr[] = $row;
            }
            // If search query provided, filter ordersArr by substring match
            if (!empty($_GET['q'])) {
                $q = mb_strtolower(trim($_GET['q']));
                $filtered = [];
                foreach ($ordersArr as $ord) {
                    $hay = mb_strtolower($ord['MaHD'] . ' ' . ($ord['created_at'] ?? ($ord['createdAt'] ?? '')) . ' ' . ($ord['SoTien'] ?? '') );
                    // also include product names
                    foreach ($ord['items'] as $it) { $hay .= ' ' . mb_strtolower($it['TenSP']); }
                    if (mb_strpos($hay, $q) !== false) $filtered[] = $ord;
                }
                $ordersArr = $filtered;
            }
        }
    } else {
        // Fallback: allow lookup by maHD param
        if (!empty($_GET['maHD'])) {
            $maHD = intval($_GET['maHD']);
            if ($maHD <= 0) {
                $message = 'Mã đơn không hợp lệ.';
            } else {
                $res = $hdModel->getHoaDon($maHD);
                if ($res && $res->num_rows > 0) {
                    $order = $res->fetch_assoc();
                    $items = $lsModel->getLSMua($maHD);
                } else {
                    $message = 'Không tìm thấy đơn hàng với mã đã cung cấp.';
                }
            }
        }
    }
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-12 gap-6">
        <!-- Left Sidebar -->
        <aside class="col-span-12 lg:col-span-2">
            <div class="space-y-4">
                <!-- Notifications Widget -->
                <div class="soft-shadow glass-effect rounded-2xl p-4 border border-indigo-500/20">
                    <h6 class="font-montserrat font-bold text-slate-100 mb-3 text-sm flex items-center gap-2">
                        <i class="fas fa-bell text-indigo-400"></i> Tin Mới
                    </h6>
                    <div class="space-y-2">
                        <?php if ($sideNotifs && $sideNotifs->num_rows > 0): while($s = $sideNotifs->fetch_assoc()): ?>
                            <a href="notification_detail.php?id=<?= $s['id'] ?>" class="block text-slate-300 hover:text-indigo-400 text-xs hover:translate-x-1 transition">
                                <p class="font-semibold truncate"><?= htmlspecialchars($s['Title']) ?></p>
                            </a>
                        <?php endwhile; endif; ?>
                    </div>
                </div>

                <!-- Vouchers Widget -->
                <?php if ($sideVouchers && $sideVouchers->num_rows > 0): ?>
                    <div class="soft-shadow glass-effect rounded-2xl p-4 border border-rose-500/20">
                        <h6 class="font-montserrat font-bold text-slate-100 mb-3 text-sm flex items-center gap-2">
                            <i class="fas fa-gift text-rose-400"></i> Voucher
                        </h6>
                        <div class="space-y-2">
                            <?php while($vv = $sideVouchers->fetch_assoc()): ?>
                                <div class="p-2 rounded-lg glass-effect border border-dashed border-rose-400/30 hover:border-rose-400/60 transition">
                                    <p class="text-rose-400 font-bold text-xs"><?= htmlspecialchars($vv['Code']) ?></p>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="col-span-12 lg:col-span-7">
            <div class="space-y-6">
                <!-- Track Card -->
                <div class="soft-shadow glass-effect rounded-2xl p-6 border border-indigo-500/20">
                    <h3 class="font-montserrat text-2xl font-bold text-slate-100 mb-2 flex items-center gap-2">
                        <i class="fas fa-box text-indigo-400"></i> Theo Dõi Đơn Hàng
                    </h3>
                    <p class="text-slate-400 text-sm mb-4">Nhập mã đơn hoặc tên sản phẩm để kiểm tra trạng thái.</p>

                    <!-- Search Form -->
                    <form class="flex gap-2 mb-4" method="GET" action="track-order.php">
                        <input type="text" name="q" placeholder="Tìm kiếm đơn (mã, tên, ngày...)" value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>" class="flex-1 glass-effect rounded-xl px-4 py-3 text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition text-sm">
                        <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700 text-white font-semibold text-sm transition">Tìm</button>
                    </form>

                    <?php if ($message): ?>
                        <div class="p-3 rounded-lg bg-amber-500/20 border border-amber-500/50 mb-4">
                            <p class="text-amber-300 text-sm"><?= htmlspecialchars($message) ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Orders List -->
                    <?php if (!empty($ordersArr)): ?>
                        <h5 class="font-montserrat font-bold text-slate-100 mb-4">Đơn Hàng Của Bạn</h5>
                        <div class="space-y-4">
                            <?php foreach($ordersArr as $o):
                                $maHD = $o['MaHD'];
                                $orderItems = $o['items'] ?? [];
                                // determine deletable
                                $forbiddenStates = ['Đang chuẩn bị hàng', 'Đang giao hàng'];
                                $deletable = true;
                                foreach ($orderItems as $oi) {
                                    if (in_array(trim($oi['State']), $forbiddenStates, true)) { $deletable = false; break; }
                                }
                            ?>
                                <div class="soft-shadow glass-effect rounded-2xl p-4 border border-slate-700/30">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <p class="font-bold text-slate-100 text-lg">Đơn #<?= htmlspecialchars($o['MaHD']) ?></p>
                                            <p class="text-slate-400 text-xs">Thời gian: <?= htmlspecialchars($o['created_at'] ?? ($o['createdAt'] ?? '')) ?></p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-rose-400 font-bold">₫<?= number_format($o['SoTien'] ?? 0,0,',','.') ?></p>
                                        </div>
                                    </div>

                                    <div class="flex justify-end mb-3">
                                        <?php if ($deletable): ?>
                                            <form method="POST" onsubmit="return confirm('Bạn chắc chắn muốn xóa đơn #<?= htmlspecialchars($o['MaHD']) ?>?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="maHD" value="<?= htmlspecialchars($o['MaHD']) ?>">
                                                <button type="submit" class="px-4 py-2 rounded-lg border border-rose-500/50 text-rose-400 hover:bg-rose-500/20 text-xs font-semibold transition">Xóa Đơn</button>
                                            </form>
                                        <?php else: ?>
                                            <button disabled class="px-4 py-2 rounded-lg border border-slate-600 text-slate-400 text-xs font-semibold cursor-not-allowed" title="Đơn đang xử lý">Không thể xóa</button>
                                        <?php endif; ?>
                                    </div>

                                    <div class="space-y-2">
                                        <?php if (is_array($orderItems) && count($orderItems) > 0): foreach($orderItems as $it): ?>
                                            <div class="flex justify-between items-center p-3 rounded-lg bg-slate-800/30 hover:bg-slate-800/50 transition">
                                                <div>
                                                    <p class="font-semibold text-slate-200 text-sm"><?= htmlspecialchars($it['TenSP']) ?></p>
                                                    <p class="text-slate-400 text-xs">SL: <?= intval($it['SoLuong']) ?> | Giá: ₫<?= number_format($it['GiaTien'],0,',','.') ?></p>
                                                </div>
                                                <div>
                                                    <?php $stateRaw = $it['State'] ?? '';
                                                    $stateClass = getOrderStateClass($stateRaw);
                                                    $stateBg = ($stateClass === 'state-success') ? 'bg-green-500/20 border-green-500/30 text-green-400' : 
                                                               (($stateClass === 'state-cancel') ? 'bg-rose-500/20 border-rose-500/30 text-rose-400' : 'bg-amber-500/20 border-amber-500/30 text-amber-400');
                                                    ?>
                                                    <span class="px-3 py-1 rounded-full border text-xs font-semibold <?= $stateBg ?>"><?= htmlspecialchars($it['State']) ?></span>
                                                </div>
                                            </div>
                                        <?php endforeach; else: ?>
                                            <p class="text-slate-400 text-sm">Không có mặt hàng nào trong đơn này.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif ($order): ?>
                        <h5 class="font-montserrat font-bold text-slate-100 mb-4">Thông Tin Đơn #<?= htmlspecialchars($order['MaHD']) ?></h5>
                        
                        <div class="space-y-3 mb-6 p-4 rounded-lg bg-slate-800/30">
                            <div class="flex justify-between"><span class="text-slate-400">Mã khách:</span><span class="text-slate-200 font-semibold"><?= htmlspecialchars($order['MaTK'] ?? 'Khách lẻ') ?></span></div>
                            <div class="flex justify-between"><span class="text-slate-400">Tổng tiền:</span><span class="text-rose-400 font-bold">₫<?= number_format($order['SoTien'] ?? 0,0,',','.') ?></span></div>
                            <div class="flex justify-between"><span class="text-slate-400">Thời gian:</span><span class="text-slate-200"><?= htmlspecialchars($order['created_at'] ?? ($order['createdAt'] ?? '')) ?></span></div>
                        </div>

                        <h6 class="font-montserrat font-bold text-slate-100 mb-3">Mặt Hàng</h6>
                        <div class="space-y-2">
                            <?php if ($items && $items->num_rows > 0): while($it = $items->fetch_assoc()): ?>
                                <div class="flex justify-between items-center p-3 rounded-lg bg-slate-800/30 hover:bg-slate-800/50 transition">
                                    <div>
                                        <p class="font-semibold text-slate-200 text-sm"><?= htmlspecialchars($it['TenSP']) ?></p>
                                        <p class="text-slate-400 text-xs">SL: <?= intval($it['SoLuong']) ?> | Giá: ₫<?= number_format($it['GiaTien'],0,',','.') ?></p>
                                    </div>
                                    <div>
                                        <?php $stateRaw = $it['State'] ?? '';
                                        $stateClass = getOrderStateClass($stateRaw);
                                        $stateBg = ($stateClass === 'state-success') ? 'bg-green-500/20 border-green-500/30 text-green-400' : 
                                                   (($stateClass === 'state-cancel') ? 'bg-rose-500/20 border-rose-500/30 text-rose-400' : 'bg-amber-500/20 border-amber-500/30 text-amber-400');
                                        ?>
                                        <span class="px-3 py-1 rounded-full border text-xs font-semibold <?= $stateBg ?>"><?= htmlspecialchars($it['State']) ?></span>
                                    </div>
                                </div>
                            <?php endwhile; else: ?>
                                <p class="text-slate-400 text-sm">Không có mặt hàng nào được tìm thấy.</p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>

        <!-- Right Sidebar -->
        <aside class="col-span-12 lg:col-span-3">
            <div class="space-y-4">
                <!-- Cart Widget -->
                <div class="soft-shadow glass-effect rounded-2xl p-4 border border-indigo-500/20">
                    <h6 class="font-montserrat font-bold text-slate-100 mb-3 text-sm flex items-center gap-2">
                        <i class="fas fa-shopping-cart text-indigo-400"></i> Giỏ Hàng
                    </h6>
                    <div class="space-y-3">
                        <?php if($totalCartQty > 0): ?>
                            <p class="text-sm text-slate-300 mb-2">
                                Bạn đang có <strong class="text-rose-400"><?= $totalCartQty ?></strong> sản phẩm.
                            </p>
                            <a href="cart.php" class="block px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700 text-white text-center font-semibold text-sm transition">
                                Thanh Toán
                            </a>
                        <?php else: ?>
                            <p class="text-xs text-slate-400">Chưa có sản phẩm trong giỏ hàng.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>

<?php include('template/footer.php') ?>
