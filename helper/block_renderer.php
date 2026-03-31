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
        case 'richtext':
            return renderRichTextBlock($data);
        case 'gallery':
            return renderGalleryBlock($data);
        case 'hero':
            return renderHeroBlock($data);
        case 'contact':
            return renderContactBlock($data);
        case 'testimonials':
            return renderTestimonialsBlock($data);
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

function renderHeroBlock($data) {
    $title = htmlspecialchars($data['title'] ?? '');
    $subtitle = htmlspecialchars($data['subtitle'] ?? '');
    $buttonText = htmlspecialchars($data['buttonText'] ?? 'Bắt đầu');
    $buttonLink = htmlspecialchars($data['buttonLink'] ?? '#');
    $bgImage = $data['backgroundImage'] ?? '/sell-shop-SPU/media/image/Slider/slider-1.jpg';
    
    return <<<HTML
    <div class="hero-section" style="background-image: url('$bgImage'); background-size: cover; background-position: center; min-height: 400px; display: flex; align-items: center; justify-content: center; text-align: center; color: white; position: relative;">
        <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.4);"></div>
        <div style="position: relative; z-index: 1; max-width: 600px; padding: 40px 20px;">
            <h1 style="font-size: 48px; font-weight: bold; margin-bottom: 20px;">$title</h1>
            <p style="font-size: 16px; margin-bottom: 30px;">$subtitle</p>
            <a href="$buttonLink" class="btn btn-primary btn-lg">$buttonText</a>
        </div>
    </div>
HTML;
}

function renderContactBlock($data) {
    $title = htmlspecialchars($data['title'] ?? 'Liên hệ');
    $description = htmlspecialchars($data['description'] ?? '');
    $email = htmlspecialchars($data['email'] ?? '');
    $phone = htmlspecialchars($data['phone'] ?? '');
    $address = htmlspecialchars($data['address'] ?? '');
    
    return <<<HTML
    <div class="container py-4">
        <div class="row">
            <div class="col-md-6">
                <h3>$title</h3>
                <p>$description</p>
                <div class="mt-4">
                    <h6><i class="fas fa-envelope"></i> Email</h6>
                    <p><a href="mailto:$email">$email</a></p>
                </div>
                <div class="mt-3">
                    <h6><i class="fas fa-phone"></i> Điện thoại</h6>
                    <p><a href="tel:$phone">$phone</a></p>
                </div>
                <div class="mt-3">
                    <h6><i class="fas fa-map-marker-alt"></i> Địa chỉ</h6>
                    <p>$address</p>
                </div>
            </div>
            <div class="col-md-6">
                <form method="post" action="contact.php">
                    <div class="mb-3">
                        <input type="text" class="form-control" name="fullname" placeholder="Tên của bạn" required>
                    </div>
                    <div class="mb-3">
                        <input type="email" class="form-control" name="email" placeholder="Email của bạn" required>
                    </div>
                    <div class="mb-3">
                        <textarea class="form-control" name="message" rows="4" placeholder="Nội dung tin nhắn" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Gửi</button>
                </form>
            </div>
        </div>
    </div>
HTML;
}

function renderTestimonialsBlock($data) {
    $title = htmlspecialchars($data['title'] ?? 'Nhận xét');
    $content = $data['content'] ?? '';
    $testimonials = array_filter(array_map('trim', explode("\n", $content)));
    
    $html = <<<HTML
    <div class="container py-4">
        <h3 class="text-center mb-4">$title</h3>
        <div class="row">
HTML;
    
    foreach ($testimonials as $i => $testimonial) {
        if (empty($testimonial)) continue;
        $html .= <<<HTML
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="mb-2">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="card-text">"$testimonial"</p>
                    </div>
                    <div class="card-footer bg-light">
                        <small class="text-muted">Khách hàng</small>
                    </div>
                </div>
            </div>
HTML;
    }
    
    $html .= <<<HTML
        </div>
    </div>
HTML;
    
    return $html;
}

function renderRichTextBlock($data) {
    $content = $data['content'] ?? '';
    return <<<HTML
    <div class="container py-4">
        <div class="rich-text-content">
            $content
        </div>
    </div>
HTML;
}

function renderGalleryBlock($data) {
    $images = $data['images'] ?? [];
    $columns = (int)($data['columns'] ?? 3);
    
    if (empty($images)) {
        return '<div class="container py-4"><p class="text-muted">Không có hình ảnh</p></div>';
    }
    
    $colSize = $columns === 1 ? 12 : ($columns === 2 ? 6 : 4);
    
    $html = <<<HTML
    <div class="container py-4">
        <div class="row g-3">
HTML;
    
    foreach ($images as $img) {
        if (empty($img['url'])) continue;
        $caption = htmlspecialchars($img['caption'] ?? '');
        $html .= <<<HTML
            <div class="col-12 col-md-$colSize">
                <div class="gallery-item">
                    <img src="{$img['url']}" class="img-fluid rounded shadow-sm" alt="$caption" style="width: 100%; height: auto;">
                    <?php if (!empty('$caption')): ?>
                        <p class="text-center mt-2 small text-muted">$caption</p>
                    <?php endif; ?>
                </div>
            </div>
HTML;
    }
    
    $html .= <<<HTML
        </div>
    </div>
HTML;
    
    return $html;
}

?>
