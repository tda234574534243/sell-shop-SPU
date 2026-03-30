<?php include ('../template/toastMess.php') ?>
<?php include "../template/sidebar.php"; ?>
<?php
    require_once '../model/m_homepage.php';
    $m = new M_homepage();
    $data = $m->getData();
?>
<?php include('../template/head.php'); ?>

<div class="bg-light flex-fill">
    <div id="mainContent" class="p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold"><i class="fas fa-home"></i> Tùy chỉnh trang chủ</h4>
            <form method="post" action="../controller/c_homepage.php" style="display:inline;">
                <input type="hidden" name="action" value="reset">
                <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Reset về mặc định?')">
                    <i class="fas fa-redo"></i> Reset
                </button>
            </form>
        </div>

        <div class="row">
            <!-- Banner Section -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-image"></i> Banner chính</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="../controller/c_homepage.php">
                            <input type="hidden" name="action" value="update_banner">
                            
                            <div class="mb-3">
                                <label class="form-label">Tiêu đề Banner</label>
                                <input type="text" name="banner_title" class="form-control" value="<?= htmlspecialchars($data['banner']['title'] ?? '') ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Mô tả phụ</label>
                                <input type="text" name="banner_subtitle" class="form-control" value="<?= htmlspecialchars($data['banner']['subtitle'] ?? '') ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Văn bản nút CTA</label>
                                <input type="text" name="banner_button" class="form-control" value="<?= htmlspecialchars($data['banner']['buttonText'] ?? '') ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">URL hình ảnh nền</label>
                                <input type="text" name="banner_image" class="form-control" value="<?= htmlspecialchars($data['banner']['backgroundImage'] ?? '') ?>" placeholder="/sell-shop-SPU/media/image/Slider/slider-1.jpg">
                                <small class="text-muted">Ví dụ: /sell-shop-SPU/media/image/Slider/slider-1.jpg</small>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-save"></i> Lưu Banner
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Featured Products Section -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-star"></i> Sản phẩm nổi bật</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="../controller/c_homepage.php">
                            <input type="hidden" name="action" value="update_featured">
                            
                            <div class="mb-3">
                                <label class="form-label">Tiêu đề</label>
                                <input type="text" name="featured_title" class="form-control" value="<?= htmlspecialchars($data['featured']['title'] ?? '') ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Mô tả</label>
                                <textarea name="featured_desc" class="form-control" rows="3"><?= htmlspecialchars($data['featured']['description'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="alert alert-info mb-3">
                                <i class="fas fa-info-circle"></i> Sản phẩm nổi bật sẽ được chọn dựa trên lượt xem và mua
                            </div>
                            
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-save"></i> Lưu
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Promotions Section -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0"><i class="fas fa-gift"></i> Khuyến mãi</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="../controller/c_homepage.php">
                            <input type="hidden" name="action" value="update_promo">
                            
                            <div class="mb-3">
                                <label class="form-label">Tiêu đề khuyến mãi</label>
                                <input type="text" name="promo_title" class="form-control" value="<?= htmlspecialchars($data['promo']['title'] ?? '') ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Mô tả</label>
                                <textarea name="promo_desc" class="form-control" rows="3"><?= htmlspecialchars($data['promo']['description'] ?? '') ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-warning w-100">
                                <i class="fas fa-save"></i> Lưu
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Announcement Section -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-bullhorn"></i> Thông báo</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="../controller/c_homepage.php">
                            <input type="hidden" name="action" value="update_announcement">
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" name="announcement_enabled" class="form-check-input" id="enableAnnounce" <?= ($data['announcement']['enabled'] ?? false) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="enableAnnounce">
                                    Kích hoạt thông báo
                                </label>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Nội dung thông báo</label>
                                <textarea name="announcement_message" class="form-control" rows="3" placeholder="Nhập nội dung thông báo ..."><?= htmlspecialchars($data['announcement']['message'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Loại thông báo</label>
                                <select name="announcement_type" class="form-select">
                                    <option value="info" <?= ($data['announcement']['type'] ?? 'info') == 'info' ? 'selected' : '' ?>>Thông tin</option>
                                    <option value="success" <?= ($data['announcement']['type'] ?? 'info') == 'success' ? 'selected' : '' ?>>Thành công</option>
                                    <option value="warning" <?= ($data['announcement']['type'] ?? 'info') == 'warning' ? 'selected' : '' ?>>Cảnh báo</option>
                                    <option value="danger" <?= ($data['announcement']['type'] ?? 'info') == 'danger' ? 'selected' : '' ?>>Lỗi/Nguy hiểm</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-info w-100 text-white">
                                <i class="fas fa-save"></i> Lưu
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Section -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-eye"></i> Xem trước</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-light border">
                            <h3><?= htmlspecialchars($data['banner']['title'] ?? 'Tiêu đề') ?></h3>
                            <p class="text-muted"><?= htmlspecialchars($data['banner']['subtitle'] ?? '') ?></p>
                            <button class="btn btn-primary btn-sm"><?= htmlspecialchars($data['banner']['buttonText'] ?? 'Mua ngay') ?></button>
                            <?php if (!empty($data['banner']['backgroundImage'])): ?>
                                <br><small class="text-muted">Hình nền: <?= htmlspecialchars($data['banner']['backgroundImage']) ?></small>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($data['announcement']['enabled'] ?? false): ?>
                            <div class="alert alert-<?= $data['announcement']['type'] ?? 'info' ?> mt-3">
                                <strong>Thông báo:</strong> <?= htmlspecialchars($data['announcement']['message'] ?? '') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../template/script_footer.php'); ?>
