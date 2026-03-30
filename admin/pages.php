<?php include ('../template/toastMess.php') ?>
<?php include "../template/sidebar.php"; ?>
<?php
    require_once '../model/m_pagebuilder.php';
    $m = new M_pagebuilder();
    $allPages = $m->getAllPages();
?>
<?php include('../template/head.php'); ?>

<style>
    #mainContent {
        margin-left: 250px;
    }
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .pages-table table {
        margin: 0;
        width: 100%;
    }
    
    .pages-table td, .pages-table th {
        padding: 15px;
        vertical-align: middle;
    }
    
    .pages-table th {
        background: #f8f9fa;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
    }
    
    .pages-table tbody tr {
        border-bottom: 1px solid #dee2e6;
        transition: background 0.2s;
    }
    
    .pages-table tbody tr:hover {
        background: #f9f9f9;
    }
    
    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .status-published {
        background: #d4edda;
        color: #155724;
    }
    
    .status-draft {
        background: #fff3cd;
        color: #856404;
    }
    
    .status-private {
        background: #f8d7da;
        color: #721c24;
    }
    
    .page-title {
        font-weight: 500;
        color: #333;
    }
    
    .page-slug {
        font-size: 12px;
        color: #999;
        margin-top: 2px;
    }
    
    .date-small {
        font-size: 12px;
        color: #666;
    }
    
    .action-buttons {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
    }
    
    .action-buttons button, .action-buttons a {
        padding: 6px 12px;
        border-radius: 4px;
        border: 1px solid #dee2e6;
        background: white;
        cursor: pointer;
        font-size: 12px;
        transition: all 0.2s;
        text-decoration: none;
        color: #333;
    }
    
    .action-buttons a:hover, .action-buttons button:hover {
        background: #007bff;
        color: white;
        border-color: #007bff;
    }
    
    .action-buttons .delete-btn {
        border-color: #dc3545;
        color: #dc3545;
    }
    
    .action-buttons .delete-btn:hover {
        background: #dc3545;
        color: white;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }
    
    .empty-state i {
        font-size: 48px;
        margin-bottom: 15px;
        opacity: 0.5;
    }
    
    .stats-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border-left: 4px solid #007bff;
    }
    
    .stat-number {
        font-size: 28px;
        font-weight: bold;
        color: #007bff;
    }
    
    .stat-label {
        font-size: 12px;
        color: #999;
        text-transform: uppercase;
        margin-top: 5px;
    }
</style>

<div class="bg-light flex-fill">
    <div id="mainContent" class="p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0"><i class="fas fa-file-alt"></i> Quản lý trang</h4>
            <a href="page-builder.php?page=homepage" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Tạo trang mới
            </a>
        </div>
        
        <?php
        $published = 0;
        $draft = 0;
        $private = 0;
        
        foreach ($allPages as $page) {
            $status = $page['status'] ?? 'draft';
            if ($status === 'published') $published++;
            elseif ($status === 'draft') $draft++;
            elseif ($status === 'private') $private++;
        }
        ?>
        
        <!-- Stats -->
        <div class="stats-cards">
            <div class="stat-card" style="border-left-color: #28a745;">
                <div class="stat-number" style="color: #28a745;"><?= $published ?></div>
                <div class="stat-label">Trang công khai</div>
            </div>
            <div class="stat-card" style="border-left-color: #ffc107;">
                <div class="stat-number" style="color: #ffc107;"><?= $draft ?></div>
                <div class="stat-label">Nháp</div>
            </div>
            <div class="stat-card" style="border-left-color: #dc3545;">
                <div class="stat-number" style="color: #dc3545;"><?= $private ?></div>
                <div class="stat-label">Riêng tư</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= count($allPages) ?></div>
                <div class="stat-label">Tổng cộng</div>
            </div>
        </div>
        
        <!-- Pages Table -->
        <div class="pages-table">
            <?php if (count($allPages) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Tên trang</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th>Cập nhật</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allPages as $slug => $page): 
                            $status = $page['status'] ?? 'draft';
                            $statusLabel = $status === 'published' ? 'Công khai' : ($status === 'private' ? 'Riêng tư' : 'Nháp');
                            $createdAt = isset($page['createdAt']) ? date('d/m/Y H:i', strtotime($page['createdAt'])) : 'N/A';
                            $updatedAt = isset($page['updatedAt']) ? date('d/m/Y H:i', strtotime($page['updatedAt'])) : 'N/A';
                        ?>
                            <tr>
                                <td>
                                    <div class="page-title"><?= htmlspecialchars($page['title']) ?></div>
                                    <div class="page-slug"><?= htmlspecialchars($slug) ?></div>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $status ?>">
                                        <?= $statusLabel ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="date-small"><?= $createdAt ?></div>
                                </td>
                                <td>
                                    <div class="date-small"><?= $updatedAt ?></div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="page-builder.php?page=<?= $slug ?>">📝 Sửa</a>
                                        
                                        <select class="form-select" style="padding: 6px 8px; border-radius: 4px; border: 1px solid #dee2e6; font-size: 12px; width: auto;" onchange="changeStatus('<?= $slug ?>', this.value)">
                                            <option value="">Đổi trạng thái</option>
                                            <option value="draft" <?= $status === 'draft' ? 'disabled' : '' ?>>Nháp</option>
                                            <option value="published" <?= $status === 'published' ? 'disabled' : '' ?>>Công khai</option>
                                            <option value="private" <?= $status === 'private' ? 'disabled' : '' ?>>Riêng tư</option>
                                        </select>
                                        
                                        <?php if ($slug !== 'homepage'): ?>
                                            <button class="delete-btn" onclick="deletePage('<?= $slug ?>')">🗑️ Xóa</button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-file-alt"></i>
                    <p>Không có trang nào</p>
                    <p style="font-size: 12px;">Tạo trang mới để bắt đầu</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include('../template/script_footer.php'); ?>

<script>
function changeStatus(slug, status) {
    if (!status) return;
    
    fetch('/sell-shop-SPU/controller/c_pagebuilder.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=updatePageStatus&pageSlug=${slug}&status=${status}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            toast({title: 'Thành công', message: 'Cập nhật trạng thái thành công', type: 'success'});
            setTimeout(() => location.reload(), 1000);
        } else {
            toast({title: 'Lỗi', message: 'Không thể cập nhật trạng thái', type: 'error'});
        }
    });
}

function deletePage(slug) {
    if (slug === 'homepage') {
        toast({title: 'Lỗi', message: 'Không thể xóa trang chủ', type: 'error'});
        return;
    }
    
    if (!confirm('Bạn chắc chắn muốn xóa trang này? Hành động không thể hoàn tác.')) return;
    
    fetch('/sell-shop-SPU/controller/c_pagebuilder.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=deletePage&pageSlug=${slug}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            toast({title: 'Thành công', message: 'Xóa trang thành công', type: 'success'});
            setTimeout(() => location.reload(), 1000);
        } else {
            toast({title: 'Lỗi', message: 'Không thể xóa trang', type: 'error'});
        }
    });
}
</script>
