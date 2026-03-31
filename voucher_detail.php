<?php include('template/head.php'); include('template/header.php');
require_once 'model/m_voucher.php';
require_once 'model/m_pagebuilder.php';
require_once 'helper/block_renderer.php';

$m = new M_voucher();
$id = intval($_GET['id'] ?? 0);
$item = $m->getById($id);

$pageBuilder = new M_pagebuilder();
$centerBlocks = $pageBuilder->getBlocksBySection('voucher_detail', 'center');
?>

<style>
    .voucher-detail-banner {
        min-height: 300px;
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        text-align: center;
        padding: 40px 20px;
    }
    
    .voucher-detail-banner h2 {
        font-size: 2.5rem;
        margin: 0;
    }
    
    .voucher-detail-card {
        max-width: 800px;
        margin: -60px auto 40px;
        padding: 40px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        position: relative;
        z-index: 10;
    }
    
    .voucher-code {
        background: #f5f5f5;
        border: 2px dashed #ff6600;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        margin-bottom: 20px;
    }
    
    .voucher-code code {
        font-size: 1.5rem;
        font-weight: bold;
        color: #ff6600;
        letter-spacing: 2px;
    }
    
    .voucher-discount {
        font-size: 2rem;
        font-weight: bold;
        color: #ff6600;
        margin: 20px 0;
    }
    
    .voucher-description {
        color: #666;
        line-height: 1.8;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }
</style>

<div class="container-fluid" style="padding: 0;">
    <?php if (!$item): ?>
        <div class="container py-4">
            <div class="alert alert-warning">Không tìm thấy voucher</div>
        </div>
    <?php else: ?>
        <!-- Page Builder Blocks -->
        <?php if (!empty($centerBlocks)): ?>
            <?php foreach ($centerBlocks as $block): ?>
                <div class="mb-4"><?= renderBlock($block) ?></div>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Default Banner -->
            <div class="voucher-detail-banner">
                <div>
                    <i class="fas fa-ticket-alt" style="font-size: 3rem; margin-bottom: 20px; display: block;"></i>
                    <h2><?= htmlspecialchars($item['Code']) ?></h2>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Voucher Detail -->
        <div class="voucher-detail-card">
            <div class="voucher-code">
                <small style="color: #999;">Mã giảm giá</small><br>
                <code><?= htmlspecialchars($item['Code']) ?></code>
            </div>
            
            <div class="voucher-discount">
                <?php if ($item['DiscountPercent']): ?>
                    Giảm <?= $item['DiscountPercent'] ?>%
                <?php elseif ($item['DiscountAmount']): ?>
                    Giảm <?= number_format($item['DiscountAmount'],0,',','.') ?>đ
                <?php else: ?>
                    Khuyến mãi đặc biệt
                <?php endif; ?>
            </div>
            
            <small class="text-muted">
                <i class="fas fa-calendar-alt"></i> 
                Ngày tạo: <?= $item['CreatedAt'] ?? 'N/A' ?>
            </small>
            
            <?php if ($item['ExpiryDate']): ?>
                <small class="text-danger d-block mt-2">
                    <i class="fas fa-clock"></i> 
                    Hết hạn: <?= $item['ExpiryDate'] ?>
                </small>
            <?php endif; ?>
            
            <div class="voucher-description">
                <h5>Chi tiết:</h5>
                <?= nl2br(htmlspecialchars($item['Description'])) ?>
            </div>
            
            <button class="btn btn-primary btn-lg w-100 mt-4" onclick="copyToClipboard('<?= htmlspecialchars($item['Code']) ?>')">
                <i class="fas fa-copy"></i> Sao chép mã
            </button>
        </div>
    <?php endif; ?>
</div>

<script>
function copyToClipboard(text) {
    const input = document.createElement('input');
    input.value = text;
    document.body.appendChild(input);
    input.select();
    document.execCommand('copy');
    document.body.removeChild(input);
    
    // Hiển thị thông báo
    if (typeof toast !== 'undefined') {
        toast({title: 'Thành công', message: 'Đã sao chép mã giảm giá', type: 'success'});
    } else {
        alert('Đã sao chép: ' + text);
    }
}
</script>

<?php include('template/footer.php'); ?>
