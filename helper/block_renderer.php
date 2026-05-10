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
    <div class="container__banner" style="background-image: url('$bgImage'); background-size: contain; background-repeat: no-repeat; background-position: center; min-height:220px; display:flex; align-items:center;">
        <div class="banner__inner" style="width:100%;">
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
    <div id="products" class="py-4">
        <h3 class="font-montserrat text-xl font-bold text-slate-100 mb-2">$title</h3>
        <p class="text-slate-400 mb-4 text-sm">$description</p>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
HTML;
    
    if ($products && $products->num_rows > 0) {
        while ($p = $products->fetch_assoc()) {
            $price = number_format($p['GiaTien'], 0, ',', '.');
            $html .= <<<HTML
            <div class="group soft-shadow glass-effect rounded-2xl overflow-hidden p-4">
                <div class="relative mb-4 h-40 flex items-center justify-center bg-slate-800 rounded-lg">
                    <img src="{$p['ImageSP']}" alt="{$p['TenSP']}" class="h-full w-full object-contain transition group-hover:scale-105">
                </div>
                <p class="text-slate-300 font-semibold text-sm line-clamp-2 mb-2">{$p['TenSP']}</p>
                <p class="text-rose-400 font-bold text-lg mb-3">{$price}đ</p>
                <div class="flex gap-2 text-xs">
                    <a href="product_detail.php?id={$p['MaSP']}" class="flex-1 px-3 py-2 rounded-lg border border-indigo-500/50 text-indigo-400 hover:bg-indigo-500/20 text-center">Chi Tiết</a>
                    <form method="post" action="controller/c_addToCart.php" style="display:contents;">
                        <input type="hidden" name="product_id" value="{$p['MaSP']}">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="flex-1 px-3 py-2 rounded-lg bg-gradient-to-r from-indigo-500 to-indigo-600 text-white">Thêm</button>
                    </form>
                </div>
            </div>
HTML;
        }
    }
    $html .= "\n        </div>\n    </div>\n";
    
    return $html;
}

function renderAnnouncementBlock($data) {
    if (!($data['enabled'] ?? false)) {
        return '';
    }
    
    $message = htmlspecialchars($data['message'] ?? '');
    $type = htmlspecialchars($data['type'] ?? 'info');
    
    $color = $type === 'success' ? 'bg-emerald-600/10 border-emerald-500/20 text-emerald-300' : ($type === 'warning' ? 'bg-amber-600/10 border-amber-500/20 text-amber-300' : ($type === 'error' ? 'bg-rose-600/10 border-rose-500/20 text-rose-300' : 'bg-indigo-600/10 border-indigo-500/20 text-indigo-300'));

    return "<div class=\"glass-effect rounded-lg p-3 mb-4 border $color\"><div class=\"flex justify-between items-start gap-4\"><div><strong class=\"font-semibold\">Thông báo:</strong> <span class=\"ml-2\">".htmlspecialchars($message)."</span></div><button class=\"ml-4 text-slate-300\" onclick=\"this.closest('div').style.display='none'\">×</button></div></div>";
}

function renderVouchersBlock($data) {
    $title = $data['title'] ?? 'Vouchers & Khuyến mãi';
    $description = $data['description'] ?? '';
    
    include_once __DIR__ . '/../model/m_voucher.php';
    $vm = new M_voucher();
    $vouchers = $vm->getAll(6);
    
    $html = <<<HTML
    <div class="py-4">
        <h3 class="font-montserrat text-xl font-bold text-slate-100 mb-2">$title</h3>
        <p class="text-slate-400 mb-4 text-sm">$description</p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
HTML;
    
    if ($vouchers && $vouchers->num_rows > 0) {
        while ($v = $vouchers->fetch_assoc()) {
            $discount = $v['DiscountPercent'] ? $v['DiscountPercent'] . '%' : $v['DiscountAmount'] . 'đ';
            $html .= <<<HTML
            <div class="rounded-lg p-3 glass-effect soft-shadow">
                <p class="text-rose-400 font-bold text-sm mb-1">{$v['Code']}</p>
                <p class="text-slate-300 text-sm mb-2">{$v['Description']}</p>
                <p class="text-slate-200 text-sm mb-3"><strong>Giảm: $discount</strong></p>
                <a href="voucher_detail.php?id={$v['id']}" class="inline-block px-3 py-2 rounded-md bg-rose-400/10 text-rose-300 text-sm">Chi tiết</a>
            </div>
HTML;
        }
    }
    $html .= "\n        </div>\n    </div>\n";
    
    return $html;
}

function renderTextBlock($data) {
    $content = htmlspecialchars($data['content'] ?? '');
    return "<div class=\"py-4\"><div class=\"prose prose-invert text-slate-200\">".$content."</div></div>";
}

function renderHtmlBlock($data) {
    $content = $data['content'] ?? '';
    return "<div class=\"py-4\"><div class=\"rich-html\">".$content."</div></div>";
}

function renderHeroBlock($data) {
    $title = htmlspecialchars($data['title'] ?? '');
    $subtitle = htmlspecialchars($data['subtitle'] ?? '');
    $buttonText = htmlspecialchars($data['buttonText'] ?? 'Bắt đầu');
    $buttonLink = htmlspecialchars($data['buttonLink'] ?? '#');
    $bgImage = $data['backgroundImage'] ?? '/sell-shop-SPU/media/image/Slider/slider-1.jpg';
    
    return <<<HTML
    <div class="relative rounded-2xl overflow-hidden" style="background-image: url('$bgImage'); background-size: cover; background-position: center; min-height: 360px;">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
        <div class="relative z-10 max-w-3xl mx-auto p-8 text-center text-slate-100">
            <h1 class="text-3xl md:text-5xl font-montserrat font-bold mb-4">$title</h1>
            <p class="text-sm md:text-base mb-6">$subtitle</p>
            <a href="$buttonLink" class="inline-block px-6 py-3 rounded-xl bg-indigo-600 text-white font-semibold">$buttonText</a>
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
    <div class="py-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="font-montserrat text-2xl font-bold text-slate-100 mb-2">$title</h3>
                <p class="text-slate-300 mb-4">$description</p>
                <div class="space-y-3 text-slate-300 text-sm">
                    <div><i class="fas fa-envelope text-indigo-400 mr-2"></i><a href="mailto:$email" class="text-indigo-300">$email</a></div>
                    <div><i class="fas fa-phone text-indigo-400 mr-2"></i><a href="tel:$phone" class="text-indigo-300">$phone</a></div>
                    <div><i class="fas fa-map-marker-alt text-indigo-400 mr-2"></i><span>$address</span></div>
                </div>
            </div>
            <div>
                <form method="post" action="contact.php" class="space-y-3">
                    <input type="text" name="fullname" class="w-full rounded-lg bg-slate-900/40 px-3 py-2 text-slate-100" placeholder="Tên của bạn" required>
                    <input type="email" name="email" class="w-full rounded-lg bg-slate-900/40 px-3 py-2 text-slate-100" placeholder="Email của bạn" required>
                    <textarea name="message" rows="4" class="w-full rounded-lg bg-slate-900/40 px-3 py-2 text-slate-100" placeholder="Nội dung tin nhắn" required></textarea>
                    <button type="submit" class="w-full inline-block px-4 py-2 rounded-lg bg-indigo-600 text-white">Gửi</button>
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
    <div class="py-4">
        <h3 class="text-center font-montserrat text-2xl font-bold text-slate-100 mb-6">$title</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
HTML;
    
    foreach ($testimonials as $i => $testimonial) {
        if (empty($testimonial)) continue;
            $html .= <<<HTML
            <div class="rounded-lg p-4 glass-effect soft-shadow">
                <div class="mb-2 text-amber-400">★★★★★</div>
                <p class="text-slate-200">"$testimonial"</p>
                <div class="mt-4 text-sm text-slate-400">Khách hàng</div>
            </div>
HTML;
    }
    
    $html .= "\n        </div>\n    </div>\n";
    
    return $html;
}

function renderRichTextBlock($data) {
    $content = $data['content'] ?? '';
    return "<div class=\"py-4\"><div class=\"prose prose-invert rich-text-content\">".$content."</div></div>";
}

function renderGalleryBlock($data) {
    $images = $data['images'] ?? [];
    $columns = (int)($data['columns'] ?? 3);
    
    if (empty($images)) {
        return '<div class="container py-4"><p class="text-muted">Không có hình ảnh</p></div>';
    }
    
    $colsClass = $columns === 1 ? 'grid-cols-1' : ($columns === 2 ? 'grid-cols-2 md:grid-cols-2' : 'grid-cols-1 md:grid-cols-3');

    $html = "<div class=\"py-4\"><div class=\"grid $colsClass gap-3\">";
    
    foreach ($images as $img) {
        if (empty($img['url'])) continue;
        $caption = htmlspecialchars($img['caption'] ?? '');
        $html .= "<div class=\"gallery-item\">";
        $html .= "<img src=\"{$img['url']}\" class=\"w-full h-100 object-contain rounded shadow-sm\" alt=\"$caption\">";
        if (!empty($caption)) {
            $html .= "<p class=\"text-center mt-2 text-sm text-slate-400\">$caption</p>";
        }
        $html .= "</div>";
    }
    $html .= "</div></div>";
    
    return $html;
}

?>
