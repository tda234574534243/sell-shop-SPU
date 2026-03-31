<?php
class M_pagebuilder {
    private $dataFile = __DIR__ . '/../public/DATA/pages.json';
    private $historyFile = __DIR__ . '/../public/DATA/pages_history.json';
    private $maxHistoryItems = 50; // Lưu tối đa 50 lần thay đổi
    
    private $defaultPages = [
        'homepage' => [
            'title' => 'Trang chủ',
            'slug' => 'homepage',
            'status' => 'published',
            'createdAt' => '2026-03-30',
            'updatedAt' => '2026-03-30',
            'blocks' => [
                [
                    'id' => 'block-1',
                    'type' => 'banner',
                    'order' => 1,
                    'data' => [
                        'title' => 'Chào mừng đến với Sup3rDup3rShop',
                        'subtitle' => 'Mua sắm những sản phẩm chất lượng với giá tốt nhất',
                        'buttonText' => 'Mua ngay',
                        'backgroundImage' => '/sell-shop-SPU/media/image/Slider/slider-1.jpg'
                    ]
                ],
                [
                    'id' => 'block-2',
                    'type' => 'featured_products',
                    'order' => 2,
                    'data' => [
                        'title' => 'Sản phẩm nổi bật',
                        'description' => 'Những sản phẩm được yêu thích nhất từ cộng đồng của chúng tôi'
                    ]
                ]
            ]
        ],
        'about' => [
            'title' => 'Giới thiệu',
            'slug' => 'about',
            'status' => 'draft',
            'createdAt' => '2026-03-30',
            'updatedAt' => '2026-03-30',
            'blocks' => []
        ],
        'contact' => [
            'title' => 'Liên hệ',
            'slug' => 'contact',
            'status' => 'draft',
            'createdAt' => '2026-03-30',
            'updatedAt' => '2026-03-30',
            'blocks' => []
        ],
        'faq' => [
            'title' => 'Câu hỏi thường gặp',
            'slug' => 'faq',
            'status' => 'draft',
            'createdAt' => '2026-03-30',
            'updatedAt' => '2026-03-30',
            'blocks' => []
        ],
        'introduce' => [
            'title' => 'Giới thiệu',
            'slug' => 'introduce',
            'status' => 'draft',
            'createdAt' => '2026-03-30',
            'updatedAt' => '2026-03-30',
            'blocks' => []
        ]
    ];
    
    public function __construct() {
        if (!file_exists($this->dataFile)) {
            $this->saveData(['pages' => $this->defaultPages]);
        } else {
            // Ensure default pages exist, add missing ones
            $data = $this->getData();
            $modified = false;
            foreach ($this->defaultPages as $slug => $page) {
                if (!isset($data['pages'][$slug])) {
                    $data['pages'][$slug] = $page;
                    $modified = true;
                }
            }
            if ($modified) {
                $this->saveData($data);
            }
        }
    }
    
    /**
     * Lấy tất cả pages
     */
    public function getAllPages() {
        $data = $this->getData();
        return $data['pages'] ?? [];
    }
    
    /**
     * Lấy một page cụ thể
     */
    public function getPage($slug) {
        $pages = $this->getAllPages();
        return isset($pages[$slug]) ? $pages[$slug] : null;
    }
    
    /**
     * Tạo page mới
     */
    public function createPage($slug, $title) {
        $data = $this->getData();
        if (isset($data['pages'][$slug])) {
            return false; // Page đã tồn tại
        }
        
        $now = date('Y-m-d H:i:s');
        $data['pages'][$slug] = [
            'title' => $title,
            'slug' => $slug,
            'status' => 'draft',
            'createdAt' => $now,
            'updatedAt' => $now,
            'blocks' => []
        ];
        
        // Lưu vào JSON
        $result = $this->saveData($data);
        
        // Tạo file PHP cho trang mới
        if ($result) {
            $this->createPageFile($slug, $title);
        }
        
        return $result;
    }
    
    /**
     * Tạo file PHP cho trang mới
     */
    private function createPageFile($slug, $title) {
        try {
            $rootDir = dirname(dirname(__FILE__));
            $filePath = $rootDir . '/' . $slug . '.php';
            
            // Nếu file đã tồn tại, không tạo
            if (file_exists($filePath)) {
                return false;
            }
            
            // Template PHP cho trang mới
            $phpContent = '<?php include(\'template/head.php\') ?>
<?php include(\'template/header.php\') ?>
<?php include(\'template/toastMess.php\') ?>
<?php
    if (session_status() == PHP_SESSION_NONE) session_start();
    
    // Load page builder
    include_once \'model/m_pagebuilder.php\';
    include_once \'model/m_database.php\';
    include_once \'model/m_notification.php\';
    include_once \'model/m_voucher.php\';
    include_once \'helper/block_renderer.php\';

    $db = new M_database();
    $pageBuilder = new M_pagebuilder();
    $nm = new M_notification();
    $vm = new M_voucher();

    // Page slug
    $pageSlug = \'' . addslashes($slug) . '\';
    
    // Kiểm tra quyền truy cập trang
    if (!$pageBuilder->canAccessPage($pageSlug)) {
        die("404 - Trang không tồn tại hoặc bạn không có quyền truy cập");
    }
    
    // Load blocks từ page builder
    $leftBlocks = $pageBuilder->getBlocksBySection($pageSlug, \'left\');
    $centerBlocks = $pageBuilder->getBlocksBySection($pageSlug, \'center\');
    $rightBlocks = $pageBuilder->getBlocksBySection($pageSlug, \'right\');
    
    // Default data
    $sideNotifs = $nm->getActive(5);
    $sideVouchers = $vm->getAll(5);
    
    $totalCartQty = 0;
    if(isset($_SESSION[\'cart\'])) {
        foreach($_SESSION[\'cart\'] as $item) {
            $totalCartQty += (isset($item[\'qty\']) ? $item[\'qty\'] : 1);
        }
    }
?>

<style>
    #prev, #next {
        display: none;
    }
</style>

<div class="container-fluid py-4">
    <div class="row gx-4">
        <aside class="col-lg-2 d-none d-lg-block">
            <div class="side-promo">
                <?php if (!empty($leftBlocks)): foreach ($leftBlocks as $block): ?>
                    <div class="mb-3"><?= renderBlock($block) ?></div>
                <?php endforeach; endif; ?>

                <div class="system-widgets mt-3">
                    <div class="promo-card mb-3 p-3 shadow-sm rounded bg-white border-top border-warning border-3">
                        <h6 class="fw-bold mb-2 small text-uppercase"><i class="fas fa-bell text-warning me-2"></i>Tin mới</h6>
                        <?php if ($sideNotifs && $sideNotifs->num_rows > 0): while($s = $sideNotifs->fetch_assoc()): ?>
                            <div class="mb-2 border-bottom pb-2 last-child-border-0">
                                <a href="notification_detail.php?id=<?= $s[\'id\'] ?>" class="text-decoration-none text-dark small fw-bold d-block text-truncate"><?= htmlspecialchars($s[\'Title\']) ?></a>
                            </div>
                        <?php endwhile; endif; ?>
                    </div>

                    <?php if ($sideVouchers && $sideVouchers->num_rows > 0): ?>
                        <div class="promo-card p-3 shadow-sm rounded bg-white border-top border-danger border-3">
                            <h6 class="fw-bold mb-2 small text-uppercase"><i class="fas fa-ticket-alt text-danger me-2"></i>Voucher</h6>
                            <?php while($vv = $sideVouchers->fetch_assoc()): ?>
                                <div class="mb-2 p-2 rounded bg-light border border-dashed">
                                    <strong class="text-success small d-block"><?= htmlspecialchars($vv[\'Code\']) ?></strong>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </aside>

        <main class="col-12 col-lg-8">
            <div class="main__container">
                <?php if (!empty($centerBlocks)): ?>
                    <!-- Page Builder Blocks -->
                    <?php foreach ($centerBlocks as $block): ?>
                        <div class="mb-4"><?= renderBlock($block) ?></div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Default Content -->
                    <div class="alert alert-info" role="alert">
                        <h4 class="alert-heading">' . htmlspecialchars($title) . '</h4>
                        <p>Sử dụng Page Builder trong Admin để thêm nội dung cho trang này.</p>
                        <hr>
                        <p class="mb-0">Vào <strong>Admin > Quản lý trang > ' . htmlspecialchars($title) . '</strong> để bắt đầu chỉnh sửa.</p>
                    </div>
                <?php endif; ?>
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

                <?php if (!empty($rightBlocks)): foreach ($rightBlocks as $block): ?>
                    <div class="mb-3"><?= renderBlock($block) ?></div>
                <?php endforeach; endif; ?>
            </div>
        </aside>
    </div>
</div>

<?php include(\'template/footer.php\') ?>
';
            
            file_put_contents($filePath, $phpContent);
            return true;
        } catch (Exception $e) {
            error_log("Error creating page file for slug '{$slug}': " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cập nhật title page
     */
    public function updatePageTitle($slug, $title) {
        $data = $this->getData();
        if (!isset($data['pages'][$slug])) {
            return false;
        }
        
        $data['pages'][$slug]['title'] = $title;
        $data['pages'][$slug]['updatedAt'] = date('Y-m-d H:i:s');
        return $this->saveData($data);
    }
    
    /**
     * Cập nhật status page
     */
    public function updatePageStatus($slug, $status) {
        $data = $this->getData();
        if (!isset($data['pages'][$slug])) {
            return false;
        }
        
        if (!in_array($status, ['draft', 'published', 'private'])) {
            return false;
        }
        
        $data['pages'][$slug]['status'] = $status;
        $data['pages'][$slug]['updatedAt'] = date('Y-m-d H:i:s');
        return $this->saveData($data);
    }
    
    /**
     * Lấy status page
     */
    public function getPageStatus($slug) {
        $page = $this->getPage($slug);
        return $page['status'] ?? 'draft';
    }
    
    /**
     * Xóa page
     */
    public function deletePage($slug) {
        try {
            $data = $this->getData();
            if (!isset($data['pages'][$slug])) {
                error_log("Cannot delete page: '{$slug}' not found in data");
                return false;
            }
            
            // Xóa file PHP nếu tồn tại
            try {
                $rootDir = dirname(dirname(__FILE__));
                $filePath = $rootDir . '/' . $slug . '.php';
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            } catch (Exception $fileError) {
                error_log("Error deleting page file for slug '{$slug}': " . $fileError->getMessage());
            }
            
            unset($data['pages'][$slug]);
            $result = $this->saveData($data);
            
            if (!$result) {
                error_log("Failed to save data after deleting page: '{$slug}'");
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Error deleting page '{$slug}': " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lấy tất cả blocks của một page (từ tất cả sections)
     */
    public function getBlocks($pageSlug) {
        $page = $this->getPage($pageSlug);
        if (!$page) return [];
        
        $blocks = $page['blocks'] ?? [];
        // Đảm bảo blocks là array
        if (is_object($blocks) || (is_array($blocks) && !empty($blocks) && !isset($blocks[0]))) {
            $blocks = array_values((array)$blocks);
        }
        
        // Sort by section order and block order
        usort($blocks, function($a, $b) {
            $sectionOrder = ['left' => 0, 'center' => 1, 'right' => 2];
            $sectionA = $sectionOrder[$a['section'] ?? 'center'] ?? 1;
            $sectionB = $sectionOrder[$b['section'] ?? 'center'] ?? 1;
            
            if ($sectionA === $sectionB) {
                return $a['order'] - $b['order'];
            }
            return $sectionA - $sectionB;
        });
        
        return $blocks;
    }
    
    /**
     * Lấy blocks của một section
     */
    public function getBlocksBySection($pageSlug, $section = 'center') {
        $page = $this->getPage($pageSlug);
        if (!$page) return [];
        
        $blocks = $page['blocks'] ?? [];
        // Đảm bảo blocks là array
        if (is_object($blocks) || (is_array($blocks) && !empty($blocks) && !isset($blocks[0]))) {
            $blocks = array_values((array)$blocks);
        }
        
        $sectionBlocks = array_filter($blocks, function($b) use ($section) {
            return ($b['section'] ?? 'center') === $section;
        });
        
        // Re-index array sau filter để tránh gaps
        $sectionBlocks = array_values($sectionBlocks);
        
        usort($sectionBlocks, function($a, $b) {
            return $a['order'] - $b['order'];
        });
        
        return $sectionBlocks;
    }
    
    /**
     * Thêm block vào page
     */
    public function addBlock($pageSlug, $type, $data = [], $section = 'center') {
        $data_obj = $this->getData();
        if (!isset($data_obj['pages'][$pageSlug])) {
            return false;
        }
        
        $page = &$data_obj['pages'][$pageSlug];
        
        // Tìm order cao nhất cho section này
        $maxOrder = 0;
        foreach ($page['blocks'] as $block) {
            if (($block['section'] ?? 'center') === $section && $block['order'] > $maxOrder) {
                $maxOrder = $block['order'];
            }
        }
        
        $blockId = 'block-' . uniqid();
        $page['blocks'][] = [
            'id' => $blockId,
            'type' => $type,
            'section' => $section,
            'order' => $maxOrder + 1,
            'data' => $data
        ];
        
        return $this->saveData($data_obj) ? $blockId : false;
    }
    
    /**
     * Cập nhật block
     */
    public function updateBlock($pageSlug, $blockId, $data) {
        $data_obj = $this->getData();
        if (!isset($data_obj['pages'][$pageSlug])) {
            return false;
        }
        
        $page = &$data_obj['pages'][$pageSlug];
        foreach ($page['blocks'] as &$block) {
            if ($block['id'] === $blockId) {
                $block['data'] = array_merge($block['data'] ?? [], $data);
                return $this->saveData($data_obj);
            }
        }
        
        return false;
    }
    
    /**
     * Xóa block
     */
    public function deleteBlock($pageSlug, $blockId) {
        $data_obj = $this->getData();
        if (!isset($data_obj['pages'][$pageSlug])) {
            return false;
        }
        
        $page = &$data_obj['pages'][$pageSlug];
        $page['blocks'] = array_filter($page['blocks'], function($block) use ($blockId) {
            return $block['id'] !== $blockId;
        });
        
        return $this->saveData($data_obj);
    }
    
    /**
     * Sắp xếp lại block (drag & drop)
     */
    public function reorderBlocks($pageSlug, $blockOrder) {
        $data_obj = $this->getData();
        if (!isset($data_obj['pages'][$pageSlug])) {
            return false;
        }
        
        $page = &$data_obj['pages'][$pageSlug];
        
        // Handle both old format (array of block IDs) and new format (array of {id, section, order} objects)
        foreach ($blockOrder as $item) {
            if (is_string($item)) {
                // Old format: array of block IDs
                $blockId = $item;
                $section = 'center';
                $order = array_search($blockId, $blockOrder) + 1;
            } else {
                // New format: {id, section, order} object
                $blockId = $item['id'] ?? null;
                $section = $item['section'] ?? 'center';
                $order = $item['order'] ?? 1;
            }
            
            if ($blockId) {
                foreach ($page['blocks'] as &$block) {
                    if ($block['id'] === $blockId) {
                        $block['order'] = $order;
                        $block['section'] = $section;
                        break;
                    }
                }
            }
        }
        
        $page['updatedAt'] = date('Y-m-d H:i:s');
        return $this->saveData($data_obj);
    }
    
    /**
     * Lấy block cụ thể
     */
    public function getBlock($pageSlug, $blockId) {
        $page = $this->getPage($pageSlug);
        if (!$page) return null;
        
        foreach ($page['blocks'] as $block) {
            if ($block['id'] === $blockId) {
                return $block;
            }
        }
        
        return null;
    }
    
    /**
     * Lấy toàn bộ dữ liệu
     */
    private function getData() {
        if (file_exists($this->dataFile)) {
            $json = file_get_contents($this->dataFile);
            $data = json_decode($json, true);
            // Normalize blocks format
            if (!empty($data['pages'])) {
                foreach ($data['pages'] as &$page) {
                    if (isset($page['blocks']) && (is_object($page['blocks']) || (is_array($page['blocks']) && !empty($page['blocks']) && !isset($page['blocks'][0])))) {
                        $page['blocks'] = array_values((array)$page['blocks']);
                    }
                }
            }
            return $data ?: ['pages' => []];
        }
        return ['pages' => []];
    }
    
    /**
     * Lưu dữ liệu
     */
    private function saveData($data) {
        try {
            // Normalize blocks to ensure they're arrays
            if (!empty($data['pages'])) {
                foreach ($data['pages'] as &$page) {
                    if (isset($page['blocks'])) {
                        // Ensure blocks is an array
                        $blocks = $page['blocks'];
                        if (is_object($blocks) || !isset($blocks[0])) {
                            $blocks = array_values((array)$blocks);
                        }
                        $page['blocks'] = $blocks;
                    }
                }
            }
            
            $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            
            if ($json === false) {
                error_log("JSON encode error: " . json_last_error_msg());
                return false;
            }
            
            $result = file_put_contents($this->dataFile, $json);
            
            if ($result === false) {
                error_log("Failed to write to file: {$this->dataFile} (check file permissions)");
                return false;
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error saving pages data: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Reset về mặc định
     */
    public function resetToDefault() {
        return $this->saveData(['pages' => $this->defaultPages]);
    }
    
    /**
     * Lưu vào history (Undo/Redo)
     */
    public function saveHistory($pageSlug, $action, $description = '') {
        $history = $this->getHistoryData();
        if (!isset($history[$pageSlug])) {
            $history[$pageSlug] = [
                'current' => 0,
                'items' => []
            ];
        }
        
        // Xóa redo history nếu có thay đổi mới
        if ($history[$pageSlug]['current'] < count($history[$pageSlug]['items']) - 1) {
            $history[$pageSlug]['items'] = array_slice($history[$pageSlug]['items'], 0, $history[$pageSlug]['current'] + 1);
        }
        
        $data = $this->getData();
        $history[$pageSlug]['items'][] = [
            'timestamp' => date('Y-m-d H:i:s'),
            'action' => $action,
            'description' => $description,
            'data' => $data['pages'][$pageSlug] ?? []
        ];
        
        // Giới hạn lịch sử
        if (count($history[$pageSlug]['items']) > $this->maxHistoryItems) {
            $history[$pageSlug]['items'] = array_slice($history[$pageSlug]['items'], -$this->maxHistoryItems);
        }
        
        $history[$pageSlug]['current'] = count($history[$pageSlug]['items']) - 1;
        return $this->saveHistoryData($history);
    }
    
    /**
     * Undo
     */
    public function undo($pageSlug) {
        $history = $this->getHistoryData();
        if (!isset($history[$pageSlug]) || $history[$pageSlug]['current'] <= 0) {
            return false;
        }
        
        $history[$pageSlug]['current']--;
        $this->saveHistoryData($history);
        
        $data = $this->getData();
        $data['pages'][$pageSlug] = $history[$pageSlug]['items'][$history[$pageSlug]['current']]['data'];
        return $this->saveData($data);
    }
    
    /**
     * Redo
     */
    public function redo($pageSlug) {
        $history = $this->getHistoryData();
        if (!isset($history[$pageSlug]) || $history[$pageSlug]['current'] >= count($history[$pageSlug]['items']) - 1) {
            return false;
        }
        
        $history[$pageSlug]['current']++;
        $this->saveHistoryData($history);
        
        $data = $this->getData();
        $data['pages'][$pageSlug] = $history[$pageSlug]['items'][$history[$pageSlug]['current']]['data'];
        return $this->saveData($data);
    }
    
    /**
     * Lấy trạng thái undo/redo
     */
    public function getHistoryState($pageSlug) {
        $history = $this->getHistoryData();
        if (!isset($history[$pageSlug])) {
            return ['canUndo' => false, 'canRedo' => false];
        }
        
        $current = $history[$pageSlug]['current'];
        $total = count($history[$pageSlug]['items']);
        
        return [
            'canUndo' => $current > 0,
            'canRedo' => $current < $total - 1,
            'current' => $current,
            'total' => $total
        ];
    }
    
    /**
     * Lấy toàn bộ history data
     */
    private function getHistoryData() {
        if (file_exists($this->historyFile)) {
            $json = file_get_contents($this->historyFile);
            return json_decode($json, true) ?: [];
        }
        return [];
    }
    
    /**
     * Lưu history data
     */
    private function saveHistoryData($data) {
        try {
            $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            return file_put_contents($this->historyFile, $json) !== false;
        } catch (Exception $e) {
            error_log("Error saving history: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kiểm tra xem user có thể truy cập trang hay không
     * Trả về true nếu:
     * - Trang được published
     * - Người dùng là admin (levelID = 1)
     */
    public function canAccessPage($pageSlug) {
        $page = $this->getPage($pageSlug);
        
        if (!$page) {
            return false; // Trang không tồn tại
        }
        
        $status = $page['status'] ?? 'draft';
        
        // Nếu trang được published, ai cũng có thể truy cập
        if ($status === 'published') {
            return true;
        }
        
        // Nếu chưa published, chỉ admin mới truy cập được
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['levelID']) && $_SESSION['levelID'] == 1;
    }
}
?>

