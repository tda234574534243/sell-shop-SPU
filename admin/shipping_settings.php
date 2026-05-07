<?php include ('../template/toastMess.php') ?>
<?php include "../template/sidebar.php"; ?>
<?php
    $configPath = __DIR__ . '/../public/DATA/shipping.json';
    $config = ['threshold' => 10000000, 'fee' => 0];
    if (file_exists($configPath)) {
        $raw = file_get_contents($configPath);
        $json = json_decode($raw, true);
        if (is_array($json)) $config = array_merge($config, $json);
    }
?>
<?php include('../template/head.php'); ?>

<div class="bg-light flex-fill">
    <div id="mainContent" class="p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold">Cài đặt vận chuyển</h4>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Thiết lập phí shipper</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="../controller/c_shipping.php">
                            <div class="mb-3">
                                <label class="form-label">Ngưỡng giá (VND)</label>
                                <input type="number" name="threshold" class="form-control" min="0" value="<?= htmlspecialchars($config['threshold']) ?>" required>
                                <small class="text-muted">Áp dụng khi giá 1 mặt hàng vượt quá giá trị này</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Phí ship (VND)</label>
                                <input type="number" name="fee" class="form-control" min="0" value="<?= htmlspecialchars($config['fee']) ?>" required>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save"></i> Lưu
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../template/script_footer.php'); ?>
