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
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createPageModal">
                <i class="fas fa-plus"></i> Tạo trang mới
            </button>
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
                                        
                                        <button class="btn btn-sm btn-outline-primary" style="padding: 6px 12px; border-radius: 4px; border: 1px solid #dee2e6; font-size: 12px; transition: all 0.2s;" onclick="editPageInfo('<?= $slug ?>', '<?= htmlspecialchars(addslashes($page['title'])) ?>')">⚙️ Thông tin</button>
                                        
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
        body: `action=deletePage&pageSlug=${encodeURIComponent(slug)}`
    })
    .then(r => {
        if (!r.ok) {
            throw new Error(`HTTP error! status: ${r.status}`);
        }
        return r.json();
    })
    .then(data => {
        console.log('Delete response:', data);
        if (data.success) {
            toast({title: 'Thành công', message: 'Xóa trang thành công', type: 'success'});
            setTimeout(() => location.reload(), 1000);
        } else {
            toast({title: 'Lỗi', message: data.message || 'Không thể xóa trang', type: 'error'});
        }
    })
    .catch(err => {
        console.error('Delete error:', err);
        toast({title: 'Lỗi', message: 'Lỗi kết nối: ' + err.message, type: 'error'});
    });
}

function createPage() {
    const title = document.getElementById('newPageTitle').value.trim();
    const slug = document.getElementById('newPageSlug').value.trim();
    
    if (!title) {
        toast({title: 'Lỗi', message: 'Vui lòng nhập tên trang', type: 'error'});
        return;
    }
    
    if (!slug) {
        toast({title: 'Lỗi', message: 'Vui lòng nhập slug trang', type: 'error'});
        return;
    }
    
    // Validate slug format (only lowercase, numbers, hyphens)
    if (!/^[a-z0-9-]+$/.test(slug)) {
        toast({title: 'Lỗi', message: 'Slug chỉ được chứa chữ thường, số và gạch ngang', type: 'error'});
        return;
    }
    
    fetch('/sell-shop-SPU/controller/c_pagebuilder.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=createPage&title=${encodeURIComponent(title)}&slug=${encodeURIComponent(slug)}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            toast({title: 'Thành công', message: 'Tạo trang mới thành công', type: 'success'});
            setTimeout(() => {
                window.location.href = `page-builder.php?page=${slug}`;
            }, 1000);
        } else {
            toast({title: 'Lỗi', message: 'Trang này đã tồn tại hoặc có lỗi xảy ra', type: 'error'});
        }
    })
    .catch(err => {
        toast({title: 'Lỗi', message: 'Không thể tạo trang: ' + err.message, type: 'error'});
    });
}

function generateSlug() {
    const title = document.getElementById('newPageTitle').value.trim();
    if (!title) return;
    
    const slug = title
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '') // Remove accents
        .replace(/[^\w\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-+|-+$/g, '');
    
    document.getElementById('newPageSlug').value = slug;
}

function editPageInfo(slug, title) {
    document.getElementById('editPageSlug').value = slug;
    document.getElementById('editPageTitle').value = title;
    const modal = new bootstrap.Modal(document.getElementById('editPageModal'));
    modal.show();
}

function savePageInfo() {
    const slug = document.getElementById('editPageSlug').value;
    const title = document.getElementById('editPageTitle').value.trim();
    
    if (!title) {
        toast({title: 'Lỗi', message: 'Vui lòng nhập tên trang', type: 'error'});
        return;
    }
    
    fetch('/sell-shop-SPU/controller/c_pagebuilder.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=updatePageTitle&pageSlug=${encodeURIComponent(slug)}&title=${encodeURIComponent(title)}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            toast({title: 'Thành công', message: 'Cập nhật thông tin trang thành công', type: 'success'});
            setTimeout(() => location.reload(), 1000);
        } else {
            toast({title: 'Lỗi', message: 'Không thể cập nhật thông tin trang', type: 'error'});
        }
    })
    .catch(err => {
        toast({title: 'Lỗi', message: 'Lỗi: ' + err.message, type: 'error'});
    });
}
</script>

<!-- Modal Chỉnh sửa thông tin trang -->
<div class="modal fade" id="editPageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Chỉnh sửa thông tin trang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Slug (không thể thay đổi)</label>
                    <input type="text" class="form-control" id="editPageSlug" disabled>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Tên trang <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="editPageTitle" placeholder="Nhập tên trang mới">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="savePageInfo()">
                    <i class="fas fa-save"></i> Lưu thay đổi
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tạo Trang Mới -->
<div class="modal fade" id="createPageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus"></i> Tạo trang mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Tên trang <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="newPageTitle" placeholder="VD: Liên hệ, Chính sách, ..." oninput="generateSlug()">
                    <small class="text-muted">Tên sẽ xuất hiện trong menu điều hướng</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Slug URL <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">/</span>
                        <input type="text" class="form-control" id="newPageSlug" placeholder="vd: lien-he">
                    </div>
                    <small class="text-muted">Tự động tạo từ tên trang hoặc nhập thủ công</small>
                </div>
                <div class="alert alert-info alert-sm" role="alert">
                    <strong>Lưu ý:</strong> Slug phải là duy nhất và chỉ chứa chữ thường, số và gạch ngang
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="createPage()">
                    <i class="fas fa-check"></i> Tạo trang
                </button>
            </div>
        </div>
    </div>
</div>
