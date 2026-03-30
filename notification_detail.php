<?php include('template/head.php'); include('template/header.php');
require_once 'model/m_notification.php';
$m = new M_notification();
$id = intval($_GET['id'] ?? 0);
$item = $m->getById($id);
?>
<div class="container py-4">
    <?php if (!$item): ?>
        <h3>Không tìm thấy thông báo</h3>
    <?php else: ?>
        <h2><?= htmlspecialchars($item['Title']) ?></h2>
        <p class="text-muted"><?= $item['CreatedAt'] ?></p>
        <div><?= nl2br(htmlspecialchars($item['Content'])) ?></div>
    <?php endif; ?>
</div>
<?php include('template/footer.php'); ?>
