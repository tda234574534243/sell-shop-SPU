<?php
class M_pagebuilder {
    private $dataFile = __DIR__ . '/../public/DATA/pages.json';
    
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
        ]
    ];
    
    public function __construct() {
        if (!file_exists($this->dataFile)) {
            $this->saveData(['pages' => $this->defaultPages]);
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
        
        return $this->saveData($data);
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
        $data = $this->getData();
        if (!isset($data['pages'][$slug])) {
            return false;
        }
        
        unset($data['pages'][$slug]);
        return $this->saveData($data);
    }
    
    /**
     * Lấy tất cả blocks của một page (từ tất cả sections)
     */
    public function getBlocks($pageSlug) {
        $page = $this->getPage($pageSlug);
        if (!$page) return [];
        
        $blocks = $page['blocks'] ?? [];
        
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
        $sectionBlocks = array_filter($blocks, function($b) use ($section) {
            return ($b['section'] ?? 'center') === $section;
        });
        
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
            return $data ?: ['pages' => []];
        }
        return ['pages' => []];
    }
    
    /**
     * Lưu dữ liệu
     */
    private function saveData($data) {
        try {
            $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            return file_put_contents($this->dataFile, $json) !== false;
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
}
?>
