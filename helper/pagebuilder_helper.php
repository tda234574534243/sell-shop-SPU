<?php
/**
 * Page Builder Helper - Giúp các trang dễ dàng sử dụng page builder
 */

include_once __DIR__ . '/../model/m_pagebuilder.php';
include_once __DIR__ . '/block_renderer.php';

/**
 * Load page builder blocks cho một trang cụ thể
 * 
 * @param string $pageSlug - Slug của trang (homepage, contact, introduce, etc)
 * @param string $section - Section cần load (left, center, right, hoặc 'all')
 * @return array - Mảng blocks nếu tồn tại, rỗng nếu không
 */
function loadPageBuilderBlocks($pageSlug, $section = 'all') {
    $pageBuilder = new M_pagebuilder();
    
    if ($section === 'all') {
        return $pageBuilder->getBlocks($pageSlug);
    }
    
    return $pageBuilder->getBlocksBySection($pageSlug, $section);
}

/**
 * Render page builder blocks để hiển thị
 * 
 * @param array $blocks - Mảng blocks từ page builder
 * @param string $wrapperClass - CSS class cho wrapper (tùy chọn)
 * @return string - HTML content
 */
function renderPageBuilderBlocks($blocks, $wrapperClass = '') {
    if (empty($blocks)) {
        return '';
    }
    
    $html = '';
    foreach ($blocks as $block) {
        $html .= '<div class="' . htmlspecialchars($wrapperClass) . '">' . renderBlock($block) . '</div>';
    }
    
    return $html;
}

/**
 * Check xem trang có blocks hay không
 * 
 * @param string $pageSlug - Slug của trang
 * @param string $section - Section cần check (tùy chọn)
 * @return bool - True nếu có blocks, False nếu không
 */
function hasPageBuilderBlocks($pageSlug, $section = 'center') {
    $pageBuilder = new M_pagebuilder();
    $blocks = $pageBuilder->getBlocksBySection($pageSlug, $section);
    return !empty($blocks);
}

/**
 * Get page info
 * 
 * @param string $pageSlug - Slug của trang
 * @return array|null - Page info hoặc null
 */
function getPageInfo($pageSlug) {
    $pageBuilder = new M_pagebuilder();
    return $pageBuilder->getPage($pageSlug);
}

/**
 * Get page status
 * 
 * @param string $pageSlug - Slug của trang
 * @return string - Status (published, draft, private)
 */
function getPageStatus($pageSlug) {
    $pageBuilder = new M_pagebuilder();
    return $pageBuilder->getPageStatus($pageSlug);
}

?>
