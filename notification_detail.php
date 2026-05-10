<?php include('template/head.php'); include('template/header.php');
require_once 'model/m_notification.php';
require_once 'model/m_pagebuilder.php';
require_once 'helper/block_renderer.php';

$m = new M_notification();
$id = intval($_GET['id'] ?? 0);
$item = $m->getById($id);

if ($item) {
    if (session_status() == PHP_SESSION_NONE) session_start();
    $currentUser = $_SESSION['user_id'] ?? null;
    $isAdmin = isset($_SESSION['levelID']) && $_SESSION['levelID']==1;
    $rid = intval($item['RecipientUserId'] ?? 0);
    if ($rid !== 0 && !$isAdmin && intval($currentUser) !== $rid) {
        // not allowed to view private notification
        $item = null;
    }
}

$pageBuilder = new M_pagebuilder();
$centerBlocks = $pageBuilder->getBlocksBySection('notification_detail', 'center');
?>

<style>
    .notification-detail-banner {
        min-height: 300px;
        background: linear-gradient(135deg, rgba(99,102,241,0.12) 0%, rgba(199,39,120,0.08) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #e6eef9;
        text-align: center;
        padding: 40px 20px;
    }

    .notification-detail-banner h2 {
        font-size: 2rem;
        margin: 0;
    }

    .notification-content {
        max-width: 800px;
        margin: 40px auto;
        padding: 30px;
        background: transparent;
        border-radius: 12px;
        border: 1px solid rgba(255,255,255,0.04);
        color: #e6eef9;
    }

    .notification-meta {
        color: rgba(255,255,255,0.6);
        font-size: 0.95rem;
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid rgba(255,255,255,0.04);
    }
</style>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 pt-24">
    <?php if (!$item): ?>
        <div class="alert alert-warning">Không tìm thấy thông báo</div>
    <?php else: ?>
        <!-- Page Builder Blocks -->
        <?php if (!empty($centerBlocks)): ?>
            <?php foreach ($centerBlocks as $block): ?>
                <div class="mb-4"><?= renderBlock($block) ?></div>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Default Banner -->
            <div class="notification-detail-banner">
                <div class="max-w-4xl mx-auto px-4 text-center">
                    <i class="fas fa-bell" style="font-size: 3rem; margin-bottom: 20px; display: block;"></i>
                    <h2><?= htmlspecialchars($item['Title']) ?></h2>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Notification Content -->
        <div class="notification-content glass-effect soft-shadow">
            <h2><?= htmlspecialchars($item['Title']) ?></h2>
            <div class="notification-meta">
                <small><i class="fas fa-calendar-alt"></i> <?= $item['CreatedAt'] ?></small>
            </div>
            <div class="notification-body" style="line-height: 1.8;">
                <?= nl2br(htmlspecialchars($item['Content'])) ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include('template/footer.php'); ?>
