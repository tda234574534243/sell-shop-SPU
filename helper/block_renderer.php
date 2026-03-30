<?php
/**
 * Helper functions để render blocks từ page builder
 */

function renderBlock($block, $pageContext = []) {
    $type = $block['type'] ?? '';
    $data = $block['data'] ?? [];
    
    switch($type) {
        case 'banner':
            return renderBannerBlock($data);
        case 'featured_products':
            return renderFeaturedProductsBlock($data);
        case 'announcement':
            return renderAnnouncementBlock($data);
        case 'vouchers':
            return renderVouchersBlock($data);
        case 'text':
            return renderTextBlock($data);
        case 'html':
            return renderHtmlBlock($data);
        default:
            return '';
    }
}

function renderBannerBlock($data) {
    $title = $data['title'] ?? 'Banner';
    $subtitle = $data['subtitle'] ?? '';
    $buttonText = $data['buttonText'] ?? 'Mua ngay';
    $bgImage = $data['backgroundImage'] ?? '/sell-shop-SPU/media/image/Slider/slider-1.jpg';
    
    return <<<HTML
    <div class="container__banner" style="background-image: url('$bgImage'); background-size: cover; background-position: center;">
        <div class="banner__inner">
            <div class="banner__title">
                <h3>$title</h3>
            </div>
            <div class="banner__description">
                <p>$subtitle</p>
                <a href="#products" class="btn btn-primary btn-sm">$buttonText</a>
            </div>
        </div>
    </div>
HTML;
}

function renderFeaturedProductsBlock($data) {
    $title = $data['title'] ?? 'Sản phẩm nổi bật';
    $description = $data['description'] ?? '';
    
    include_once __DIR__ . '/../model/m_database.php';
    $db = new M_database();
    $db->setQuery("SELECT * FROM products ORDER BY Sold DESC LIMIT 8");
    $products = $db->excuteQuery();
    
    $html = <<<HTML
    <div class="container py-4" id="products">
        <h3 class="mb-3">$title</h3>
        <p class="text-muted">$description</p>
        <div class="row">
HTML;
    
    if ($products && $products->num_rows > 0) {
        while ($p = $products->fetch_assoc()) {
            $price = number_format($p['GiaTien'], 0, ',', '.');
            $html .= <<<HTML
            <div class="col-6 col-md-3 mb-3">
                <div class="card h-100">
                    <img src="{$p['ImageSP']}" class="card-img-top" alt="{$p['TenSP']}">
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title mb-1" style="font-size:14px">{$p['TenSP']}</h6>
                        <p class="text-danger fw-bold mb-2" style="font-size:14px">{$price}đ</p>
                        <a href="product_detail.php?id={$p['MaSP']}" class="btn btn-sm btn-outline-primary mt-auto">Xem</a>
                    </div>
                </div>
            </div>
HTML;
        }
    }
    
    $html .= <<<HTML
        </div>
    </div>
HTML;
    
    return $html;
}

function renderAnnouncementBlock($data) {
    if (!($data['enabled'] ?? false)) {
        return '';
    }
    
    $message = htmlspecialchars($data['message'] ?? '');
    $type = htmlspecialchars($data['type'] ?? 'info');
    
    return <<<HTML
    <div class="alert alert-{$type} alert-dismissible fade show mb-0" role="alert">
        <strong>Thông báo:</strong> $message
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
HTML;
}

function renderVouchersBlock($data) {
    $title = $data['title'] ?? 'Vouchers & Khuyến mãi';
    $description = $data['description'] ?? '';
    
    include_once __DIR__ . '/../model/m_voucher.php';
    $vm = new M_voucher();
    $vouchers = $vm->getAll(6);
    
    $html = <<<HTML
    <div class="container py-4 bg-light">
        <h3 class="mb-3">$title</h3>
        <p class="text-muted">$description</p>
        <div class="row">
HTML;
    
    if ($vouchers && $vouchers->num_rows > 0) {
        while ($v = $vouchers->fetch_assoc()) {
            $discount = $v['DiscountPercent'] ? $v['DiscountPercent'] . '%' : $v['DiscountAmount'] . 'đ';
            $html .= <<<HTML
            <div class="col-md-4 mb-3">
                <div class="card border-2 border-warning">
                    <div class="card-body">
                        <h6 class="card-title"><code>{$v['Code']}</code></h6>
                        <p class="card-text small">{$v['Description']}</p>
                        <p class="mb-1"><strong>Giảm: $discount</strong></p>
                        <a href="voucher_detail.php?id={$v['id']}" class="btn btn-sm btn-warning">Chi tiết</a>
                    </div>
                </div>
            </div>
HTML;
        }
    }
    
    $html .= <<<HTML
        </div>
    </div>
HTML;
    
    return $html;
}

function renderTextBlock($data) {
    $content = htmlspecialchars($data['content'] ?? '');
    return <<<HTML
    <div class="container py-4">
        <p>$content</p>
    </div>
HTML;
}

function renderHtmlBlock($data) {
    $content = $data['content'] ?? '';
    return <<<HTML
    <div class="container py-4">
        $content
    </div>
HTML;
}

?>
