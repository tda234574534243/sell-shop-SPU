<?php
class M_homepage {
    private $dataFile = __DIR__ . '/../public/DATA/homepage.json';
    
    // Mặc định nếu file không tồn tại
    private $defaultData = [
        'banner' => [
            'title' => 'Chào mừng đến với Sup3rDup3rShop',
            'subtitle' => 'Mua sắm những sản phẩm chất lượng với giá tốt nhất',
            'buttonText' => 'Mua ngay',
            'backgroundImage' => '/sell-shop-SPU/media/image/Slider/slider-1.jpg'
        ],
        'featured' => [
            'title' => 'Sản phẩm nổi bật',
            'description' => 'Những sản phẩm được yêu thích nhất từ cộng đồng của chúng tôi'
        ],
        'promo' => [
            'title' => 'Khuyến mãi đặc biệt',
            'description' => 'Giảm giá lên đến 50% cho các sản phẩm được chọn'
        ],
        'announcement' => [
            'enabled' => false,
            'message' => '',
            'type' => 'info' // success, warning, danger
        ]
    ];
    
    public function __construct() {
        // Tạo file nếu chưa tồn tại
        if (!file_exists($this->dataFile)) {
            $this->saveData($this->defaultData);
        }
    }
    
    /**
     * Lấy toàn bộ dữ liệu trang chủ
     */
    public function getData() {
        if (file_exists($this->dataFile)) {
            $json = file_get_contents($this->dataFile);
            $data = json_decode($json, true);
            return $data ?: $this->defaultData;
        }
        return $this->defaultData;
    }
    
    /**
     * Lấy một section cụ thể
     */
    public function getSection($section) {
        $data = $this->getData();
        return isset($data[$section]) ? $data[$section] : null;
    }
    
    /**
     * Cập nhật một section
     */
    public function updateSection($section, $content) {
        $data = $this->getData();
        if (isset($data[$section])) {
            $data[$section] = array_merge($data[$section], $content);
            $this->saveData($data);
            return true;
        }
        return false;
    }
    
    /**
     * Cập nhật toàn bộ dữ liệu
     */
    public function updateData($newData) {
        return $this->saveData($newData);
    }
    
    /**
     * Lưu dữ liệu vào file
     */
    private function saveData($data) {
        try {
            $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            return file_put_contents($this->dataFile, $json) !== false;
        } catch (Exception $e) {
            error_log("Error saving homepage data: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Reset về mặc định
     */
    public function resetToDefault() {
        return $this->saveData($this->defaultData);
    }
}
?>
