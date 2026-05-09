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

<style>
    .track-card { background: #fff; padding: 24px; border-radius: 12px; box-shadow: 0 8px 20px rgba(0,0,0,0.06); }
    .track-form input { padding: 12px; border-radius: 8px; border: 1px solid #ddd; width: 100%; }
    .track-form button { background:#ff6600;color:#fff;border:none;padding:10px 16px;border-radius:8px; }
    .state-badge { padding:6px 10px;border-radius:999px;font-size:13px; }
    .state-pending{ background:#fff3cd;color:#856404;border:1px solid #ffeeba; }
    .state-success{ background:#d4edda;color:#155724;border:1px solid #c3e6cb; }
    .state-cancel{ background:#f8d7da;color:#721c24;border:1px solid #f5c6cb; }
</style>

<div class="container-fluid py-4">
    <div class="row gx-4">
        <aside class="col-lg-2 d-none d-lg-block">
            <div class="side-promo">
                <div class="system-widgets mt-3">
                    <div class="promo-card mb-3 p-3 shadow-sm rounded bg-white border-top border-warning border-3">
                        <h6 class="fw-bold mb-2 small text-uppercase"><i class="fas fa-bell text-warning me-2"></i>Tin mới</h6>
                        <?php if ($sideNotifs && $sideNotifs->num_rows > 0): while($s = $sideNotifs->fetch_assoc()): ?>
                            <div class="mb-2 border-bottom pb-2 last-child-border-0">
                                <a href="notification_detail.php?id=<?= $s['id'] ?>" class="text-decoration-none text-dark small fw-bold d-block text-truncate"><?= htmlspecialchars($s['Title']) ?></a>
                            </div>
                        <?php endwhile; endif; ?>
                    </div>

                    <?php if ($sideVouchers && $sideVouchers->num_rows > 0): ?>
                        <div class="promo-card p-3 shadow-sm rounded bg-white border-top border-danger border-3">
                            <h6 class="fw-bold mb-2 small text-uppercase"><i class="fas fa-ticket-alt text-danger me-2"></i>Voucher</h6>
                            <?php while($vv = $sideVouchers->fetch_assoc()): ?>
                                <div class="mb-2 p-2 rounded bg-light border border-dashed">
                                    <strong class="text-success small d-block"><?= htmlspecialchars($vv['Code']) ?></strong>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </aside>

        <main class="col-12 col-lg-8">
            <div class="main__container">
                <div class="track-card">
                    <h3>Theo dõi đơn hàng</h3>
                    <p class="text-muted">Nhập mã đơn hàng (MaHD) để kiểm tra trạng thái.</p>

                    <form class="track-form row g-2 mb-3" method="GET" action="track-order.php">
                        <div class="col-9">
                            <input type="text" name="q" placeholder="Tìm kiếm đơn (mã đơn, tên sản phẩm, ngày...)" value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
                        </div>
                        <div class="col-3">
                            <button type="submit" class="w-100">Tìm</button>
                        </div>
                    </form>

                    <?php if ($message): ?>
                        <div class="alert alert-warning"><?= htmlspecialchars($message) ?></div>
                    <?php endif; ?>

                    <?php if (!empty($ordersArr)): ?>
                        <h5>Đơn hàng của bạn</h5>
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
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <strong>Đơn #<?= htmlspecialchars($o['MaHD']) ?></strong>
                                            <div class="small text-muted">Thời gian: <?= htmlspecialchars($o['created_at'] ?? ($o['createdAt'] ?? '')) ?></div>
                                        </div>
                                        <div class="text-end">
                                            <div>Tổng: <strong><?= number_format($o['SoTien'] ?? 0) ?> VND</strong></div>
                                        </div>
                                    </div>

                                            <div class="d-flex justify-content-end mb-2">
                                                <?php if ($deletable): ?>
                                                    <form method="POST" onsubmit="return confirm('Bạn chắc chắn muốn xóa đơn #<?= htmlspecialchars($o['MaHD']) ?>?');">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="maHD" value="<?= htmlspecialchars($o['MaHD']) ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Xóa đơn</button>
                                                    </form>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-outline-secondary" disabled title="Đơn đang xử lý, không thể xóa">Không thể xóa</button>
                                                <?php endif; ?>
                                            </div>

                                    <div class="list-group">
                                        <?php if (is_array($orderItems) && count($orderItems) > 0): foreach($orderItems as $it): ?>
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="fw-bold"><?= htmlspecialchars($it['TenSP']) ?></div>
                                                    <div class="small text-muted">Số lượng: <?= intval($it['SoLuong']) ?> &nbsp; | &nbsp; Giá: <?= number_format($it['GiaTien']) ?> VND</div>
                                                </div>
                                                <div>
                                                        <?php $stateRaw = $it['State'] ?? ''; $stateClass = getOrderStateClass($stateRaw); ?>
                                                        <span class="state-badge <?= $stateClass ?>"><?= htmlspecialchars($it['State']) ?></span>
                                                </div>
                                            </div>
                                        <?php endforeach; else: ?>
                                            <div class="small text-muted">Không có mặt hàng nào trong đơn này.</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php elseif ($order): ?>
                        <h5>Thông tin đơn hàng #<?= htmlspecialchars($order['MaHD']) ?></h5>
                        <p><strong>Khách hàng:</strong> <?= htmlspecialchars($order['MaTK'] ?? 'Khách lẻ') ?></p>
                        <p><strong>Tổng tiền:</strong> <?= number_format($order['SoTien'] ?? 0) ?> VND</p>
                        <p><strong>Thời gian:</strong> <?= htmlspecialchars($order['created_at'] ?? ($order['createdAt'] ?? '')) ?></p>

                        <h6 class="mt-3">Mặt hàng</h6>
                        <div class="list-group">
                                        <?php if ($items && $items->num_rows > 0): while($it = $items->fetch_assoc()): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold"><?= htmlspecialchars($it['TenSP']) ?></div>
                                        <div class="small text-muted">Số lượng: <?= intval($it['SoLuong']) ?> &nbsp; | &nbsp; Giá: <?= number_format($it['GiaTien']) ?> VND</div>
                                    </div>
                                    <div>
                                                    <?php $stateRaw = $it['State'] ?? ''; $stateClass = getOrderStateClass($stateRaw); ?>
                                        <span class="state-badge <?= $stateClass ?>"><?= htmlspecialchars($it['State']) ?></span>
                                    </div>
                                </div>
                            <?php endwhile; else: ?>
                                <div class="small text-muted">Không có mặt hàng nào được tìm thấy.</div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>

        <aside class="col-lg-2 d-none d-lg-block">
            <div class="side-promo">
                <div class="promo-card shadow-sm border-0 mb-3 bg-white p-3 rounded border-start border-4 border-primary">
                    <h6 class="fw-bold text-primary small text-uppercase"><i class="fas fa-shopping-basket me-2"></i>Giỏ hàng</h6>
                    <div class="cart-status mt-2">
                        <?php if($totalCartQty > 0): ?>
                            <p class="small mb-2">Bạn đang có <strong class="text-danger"><?= $totalCartQty ?></strong> món.</p>
                            <a href="cart.php" class="btn btn-sm btn-primary w-100 py-1 rounded-pill" style="font-size: 11px;">THANH TOÁN</a>
                        <?php else: ?>
                            <p class="text-muted small mb-0" style="font-size: 11px;">Chưa có sản phẩm nào.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>

<?php include('template/footer.php') ?>
