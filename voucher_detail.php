<?php include('template/head.php'); include('template/header.php');
require_once 'model/m_voucher.php';
$m = new M_voucher();
$id = intval($_GET['id'] ?? 0);
$item = $m->getById($id);
?>
<div class="container py-4">
    <?php if (!$item): ?>
        <h3>Không tìm thấy voucher</h3>
    <?php else: ?>
        <h2><?= htmlspecialchars($item['Code']) ?></h2>
        <p class="text-muted"><?= $item['CreatedAt'] ?? '' ?></p>
        <div><?= nl2br(htmlspecialchars($item['Description'])) ?></div>
        <p>Giảm: <?= $item['DiscountPercent'] ? $item['DiscountPercent'].'%' : ($item['DiscountAmount'] ? number_format($item['DiscountAmount'],0,',','.').'đ' : '-') ?></p>
    <?php endif; ?>
</div>
<?php include('template/footer.php'); ?>
