<?php include ('../template/toastMess.php') ?>
<?php include "../template/sidebar.php"; ?>
<?php
    require_once '../model/m_pagebuilder.php';
    require_once '../helper/block_renderer.php';
    $m = new M_pagebuilder();
    $allPages = $m->getAllPages();
    $currentPage = $_GET['page'] ?? 'homepage';
    $page = $m->getPage($currentPage);
    $pageStatus = $page ? ($page['status'] ?? 'draft') : 'draft';
?>

<?php include('../template/head.php'); ?>

<!-- Restore Bootstrap for Admin (head.php now uses Tailwind globally) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-8a6Y3a5Z6mQv1Kc1F5a9Dq3y2Vq7Z1Q9w6l0r6b3f9p2v5g7t8c0x1y2z3a4b5c6" crossorigin="anonymous">
<style>
    /* Admin-only overrides to keep admin UI readable under the global dark theme */
    body { background: #f8f9fa; color: #212529; }
    .builder-wrapper { margin-left: 150px; }
    .builder-header, .builder-left, .preview-container { background: #fff; color: #212529; }
    .form-control { border-radius: 4px; }
    .btn { font-size: 13px; }
</style>

<!-- Quill Rich Text Editor -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<style>
    .builder-wrapper {
        display: flex;
        flex-direction: column;
        height: 100vh;
        margin-left: 150px;
    }
    
    .builder-header {
        background: white;
        border-bottom: 1px solid #ddd;
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        gap: 15px;
    }
    
    .builder-header h4 {
        margin: 0;
        font-size: 16px;
        font-weight: bold;
    }
    
    .page-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .status-indicator {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .status-draft { background: #fff3cd; color: #856404; }
    .status-published { background: #d4edda; color: #155724; }
    .status-private { background: #f8d7da; color: #721c24; }
    
    .builder-actions {
        display: flex;
        gap: 10px;
    }
    
    .builder-container {
        display: flex;
        flex: 1;
        overflow: hidden;
        gap: 0;
    }
    
    .builder-left {
        flex: 0 0 40%;
        border-right: 1px solid #ddd;
        background: white;
        overflow-y: auto;
        padding: 20px;
    }
    
    .builder-right {
        flex: 1;
        background: #f5f5f5;
        overflow-y: auto;
        padding: 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    
    .preview-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        width: 100%;
        max-width: 1100px;
    }
    
    .preview-inner {
        position: relative;
    }
    
    .preview-block-wrapper {
        position: relative;
        padding: 2px;
    }
    
    .preview-block-wrapper:hover .block-controls {
        opacity: 1;
    }
    
    .block-controls {
        position: absolute;
        right: 10px;
        top: 10px;
        display: flex;
        gap: 5px;
        opacity: 0;
        transition: opacity 0.2s;
        background: white;
        padding: 5px;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        z-index: 10;
    }
    
    .block-controls button {
        padding: 4px 8px;
        font-size: 11px;
        border: 1px solid #ddd;
        background: white;
        border-radius: 3px;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .block-controls button:hover {
        background: #f0f0f0;
        border-color: #007bff;
    }
    
    .block-controls .delete-btn:hover {
        background: #f8d7da;
        border-color: #dc3545;
    }
    
    .section-title {
        font-size: 14px;
        font-weight: bold;
        color: #333;
        margin-top: 20px;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 2px solid #007bff;
    }
    
    .block-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .block-list-item {
        background: #f9f9f9;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        padding: 10px;
        margin-bottom: 8px;
        cursor: grab;
        transition: all 0.2s;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 13px;
    }
    
    .block-list-item:active {
        cursor: grabbing;
    }
    
    .block-list-item.dragging {
        opacity: 0.5;
        background: #e7f1ff;
    }
    
    .block-list-item:hover {
        background: #e7f1ff;
        border-color: #007bff;
        box-shadow: 0 2px 4px rgba(0,123,255,0.2);
    }
    
    .block-list-item-info {
        flex: 1;
    }
    
    .block-list-badge {
        display: inline-block;
        background: #007bff;
        color: white;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 10px;
        font-weight: bold;
        margin-right: 6px;
    }
    
    .add-blocks-menu {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 6px;
        margin-top: 12px;
    }
    
    .add-block-btn {
        padding: 8px;
        border: 1px solid #ddd;
        background: #fafafa;
        border-radius: 4px;
        cursor: pointer;
        font-size: 11px;
        text-align: center;
        transition: all 0.2s;
    }
    
    .add-block-btn:hover {
        background: #e7f1ff;
        border-color: #007bff;
    }
    
    .form-section {
        background: #f0f0f0;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 12px;
        margin-top: 12px;
    }
    
    .form-section h6 {
        margin: 0 0 10px 0;
        font-size: 12px;
        font-weight: bold;
    }
    
    .form-section input, .form-section textarea, .form-section select {
        margin-bottom: 8px;
        font-size: 12px;
    }
    
    .form-section img {
        max-width: 100%;
        height: auto;
        border-radius: 4px;
        margin-top: 10px;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }
    
    /* Quill Editor */
    .ql-container { font-size: 14px; }
    .ql-editor { min-height: 250px; }
    .ql-toolbar { border: 1px solid #ddd; border-bottom: none; border-radius: 4px 4px 0 0; }
    .ql-container { border: 1px solid #ddd; border-radius: 0 0 4px 4px; }
    
    /* Gallery Styles */
    .gallery-preview { background: #f9f9f9; }
    .gallery-preview img { object-fit: contain; max-height: 300px; max-width: 100%; }
</style>

<div class="builder-wrapper">
    <!-- Header with Save Button -->
    <div class="builder-header">
        <div class="page-info">
            <h4><i class="fas fa-wand-magic-sparkles"></i> <?= htmlspecialchars($page['title'] ?? 'Page') ?></h4>
            <span class="status-indicator status-<?= $pageStatus ?>">
                <?= $pageStatus === 'published' ? '✓ Công khai' : ($pageStatus === 'private' ? '🔒 Riêng tư' : '📝 Nháp') ?>
            </span>
        </div>
        
        <div class="builder-actions">
            <a href="pages.php" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-list"></i> Quản lý trang
            </a>
            
            <!-- Undo/Redo Buttons -->
            <button type="button" class="btn btn-outline-info btn-sm" id="undoBtn" onclick="undoAction()" disabled title="Hoàn tác">
                <i class="fas fa-undo"></i>
            </button>
            <button type="button" class="btn btn-outline-info btn-sm" id="redoBtn" onclick="redoAction()" disabled title="Làm lại">
                <i class="fas fa-redo"></i>
            </button>
            
            <select class="form-select form-select-sm" style="width: auto;" onchange="changePage(this.value)">
                <option value="">Chuyển trang...</option>
                <?php foreach ($allPages as $slug => $p): ?>
                    <option value="<?= $slug ?>" <?= $slug === $currentPage ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="button" class="btn btn-success btn-sm" onclick="savePage()">
                <i class="fas fa-save"></i> Lưu thay đổi
            </button>
        </div>
    </div>
    
    <!-- Main Container -->
    <div class="builder-container bg-light flex-fill">
        <!-- LEFT: Blocks Management by Section -->
        <div class="builder-left">
            <?php 
            $sections = ['left' => 'LEFT SIDEBAR', 'center' => 'CENTER', 'right' => 'RIGHT SIDEBAR'];
            foreach ($sections as $section => $label): 
                $sectionBlocks = $m->getBlocksBySection($currentPage, $section);
            ?>
                <div style="margin-bottom: 30px; border: 1px solid #e0e0e0; border-radius: 6px; padding: 12px; background: #fafafa;">
                    <div class="section-title" style="margin: 0 0 10px 0; font-size: 12px;">📍 <?= $label ?></div>
                    
                    <?php if (count($sectionBlocks) > 0): ?>
                        <ul class="block-list" id="sortableBlocks-<?= $section ?>">
                            <?php foreach ($sectionBlocks as $block): ?>
                                <li class="block-list-item" data-id="<?= $block['id'] ?>" data-section="<?= $section ?>">
                                    <div class="block-list-item-info">
                                        <span class="block-list-badge"><?= htmlspecialchars($block['type']) ?></span>
                                        <span><?= htmlspecialchars(substr($block['data']['title'] ?? $block['data']['description'] ?? 'Block', 0, 20)) ?></span>
                                    </div>
                                    <button class="btn btn-sm btn-outline-primary" style="padding: 2px 6px; font-size: 10px;" onclick="selectBlock('<?= $block['id'] ?>')">✎</button>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div style="text-align: center; color: #999; font-size: 12px; padding: 8px;">Không có block</div>
                    <?php endif; ?>
                    
                    <div style="margin-top: 8px; display: grid; grid-template-columns: 1fr 1fr; gap: 4px;">
                        <button class="add-block-btn" style="font-size: 10px; padding: 6px;" onclick="addBlockType('text', '<?= $section ?>')">📝 Văn bản</button>
                        <button class="add-block-btn" style="font-size: 10px; padding: 6px;" onclick="addBlockType('vouchers', '<?= $section ?>')">🎟️ Vouchers</button>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div style="padding: 12px; background: white; border-radius: 6px;">
                <div class="section-title" style="margin: 0 0 10px 0; font-size: 12px;">+ Thêm Block Full-Width</div>
                <div class="add-blocks-menu">
                    <button class="add-block-btn" onclick="addBlockType('banner', 'center')">🖼️ Banner</button>
                    <button class="add-block-btn" onclick="addBlockType('featured_products', 'center')">⭐ Sản phẩm</button>
                    <button class="add-block-btn" onclick="addBlockType('announcement', 'center')">📢 Thông báo</button>
                    <button class="add-block-btn" onclick="addBlockType('hero', 'center')">🎯 Hero Section</button>
                    <button class="add-block-btn" onclick="addBlockType('contact', 'center')">📧 Liên hệ</button>
                    <button class="add-block-btn" onclick="addBlockType('testimonials', 'center')">⭐ Nhận xét</button>
                    <button class="add-block-btn" onclick="addBlockType('richtext', 'center')">✏️ Rich Text</button>
                    <button class="add-block-btn" onclick="addBlockType('gallery', 'center')">🖼️ Thư viện ảnh</button>
                </div>
            </div>
            
            <!-- Form for Editing -->
            <div id="blockEditForm"></div>
        </div>
        
        <!-- RIGHT: Live Preview (3 Column Layout) -->
        <div class="builder-right">
            <div class="preview-container">
                <div class="preview-inner" id="livePreview">
                    <div style="display: flex; gap: 15px; padding: 20px; min-height: 400px;">
                        <!-- LEFT COLUMN -->
                        <div style="flex: 0 0 25%; border-right: 2px solid #f0f0f0;">
                            <small style="color: #999; font-weight: bold;">LEFT SIDEBAR</small>
                            <div id="preview-left" style="margin-top: 10px;">
                                <?php $leftBlocks = $m->getBlocksBySection($currentPage, 'left'); ?>
                                <?php foreach ($leftBlocks as $block): ?>
                                    <div class="preview-block-wrapper" data-block-id="<?= $block['id'] ?>" style="margin-bottom: 15px;">
                                        <div class="block-controls">
                                            <button onclick="selectBlock('<?= $block['id'] ?>')">✎</button>
                                            <button class="delete-btn" onclick="deleteBlock('<?= $block['id'] ?>')">🗑</button>
                                        </div>
                                        <?= renderBlock($block) ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- CENTER COLUMN -->
                        <div style="flex: 1; min-width: 0;">
                            <small style="color: #999; font-weight: bold;">CENTER CONTENT</small>
                            <div id="preview-center" style="margin-top: 10px;">
                                <?php $centerBlocks = $m->getBlocksBySection($currentPage, 'center'); ?>
                                <?php foreach ($centerBlocks as $block): ?>
                                    <div class="preview-block-wrapper" data-block-id="<?= $block['id'] ?>" style="margin-bottom: 15px;">
                                        <div class="block-controls">
                                            <button onclick="selectBlock('<?= $block['id'] ?>')">✎</button>
                                            <button class="delete-btn" onclick="deleteBlock('<?= $block['id'] ?>')">🗑</button>
                                        </div>
                                        <?= renderBlock($block) ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- RIGHT COLUMN -->
                        <div style="flex: 0 0 25%; border-left: 2px solid #f0f0f0;">
                            <small style="color: #999; font-weight: bold;">RIGHT SIDEBAR</small>
                            <div id="preview-right" style="margin-top: 10px;">
                                <?php $rightBlocks = $m->getBlocksBySection($currentPage, 'right'); ?>
                                <?php foreach ($rightBlocks as $block): ?>
                                    <div class="preview-block-wrapper" data-block-id="<?= $block['id'] ?>" style="margin-bottom: 15px;">
                                        <div class="block-controls">
                                            <button onclick="selectBlock('<?= $block['id'] ?>')">✎</button>
                                            <button class="delete-btn" onclick="deleteBlock('<?= $block['id'] ?>')">🗑</button>
                                        </div>
                                        <?= renderBlock($block) ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../template/script_footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<script>
const pageSlug = '<?= htmlspecialchars($currentPage) ?>';

document.addEventListener('DOMContentLoaded', function() {
    // Initialize sortable for each section
    ['left', 'center', 'right'].forEach(section => {
        const el = document.getElementById(`sortableBlocks-${section}`);
        if (el) {
            Sortable.create(el, {
                animation: 150,
                ghostClass: 'dragging',
                onEnd: () => saveBlockOrder()
            });
        }
    });
});

function savePage() {
    toast({title: 'Thông báo', message: 'Tất cả thay đổi đã được lưu', type: 'success'});
}

function saveBlockOrder() {
    const allBlocks = [];
    ['left', 'center', 'right'].forEach(section => {
        const items = document.querySelectorAll(`#sortableBlocks-${section} .block-list-item`);
        items.forEach((item, idx) => {
            allBlocks.push({
                id: item.dataset.id,
                section: section,
                order: idx + 1
            });
        });
    });
    
    if (allBlocks.length === 0) return;
    
    fetch('/sell-shop-SPU/controller/c_pagebuilder.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=reorderBlocks&pageSlug=${pageSlug}&blockOrder=${JSON.stringify(allBlocks)}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            toast({title: 'Lưu', message: 'Sắp xếp blocks thành công', type: 'success'});
        }
    });
}

function changePage(slug) {
    if (slug) {
        window.location.href = `?page=${slug}`;
    }
}

function selectBlock(blockId) {
    fetch(`/sell-shop-SPU/controller/c_pagebuilder.php?action=getBlocks&pageSlug=${pageSlug}`)
    .then(r => r.json())
    .then(data => {
        const block = data.blocks.find(b => b.id === blockId);
        if (!block) return;
        
        let form = '<div class="form-section"><h6>Sửa: ' + block.type + '</h6>';
        const blockData = block.data;
        const section = block.section || 'center';
        
        switch(block.type) {
            case 'banner':
                form += `
                    <input type="text" class="form-control block-field mb-2" name="title" value="${esc(blockData.title)}" placeholder="Tiêu đề">
                    <input type="text" class="form-control block-field mb-2" name="subtitle" value="${esc(blockData.subtitle)}" placeholder="Mô tả">
                    <input type="text" class="form-control block-field mb-2" name="buttonText" value="${esc(blockData.buttonText)}" placeholder="Text nút">
                    
                    <label style="font-size: 11px; font-weight: bold; display: block; margin-bottom: 6px;">Ảnh nền:</label>
                    <div class="input-group mb-2" style="gap: 5px;">
                        <input type="text" class="form-control block-field" name="backgroundImage" value="${esc(blockData.backgroundImage)}" placeholder="URL hoặc chọn">
                        <button class="btn btn-outline-primary btn-sm" type="button" onclick="openImageUpload('${blockId}', 'backgroundImage')">Chọn ảnh</button>
                    </div>
                    ${blockData.backgroundImage ? `<img src="${esc(blockData.backgroundImage)}" style="max-width: 100%; border-radius: 4px;">` : ''}
                    
                    <button class="btn btn-success w-100 btn-sm mt-2" onclick="saveBlock('${blockId}')">Lưu</button>
                `;
                break;
            case 'featured_products':
                form += `
                    <input type="text" class="form-control block-field mb-2" name="title" value="${esc(blockData.title)}" placeholder="Tiêu đề">
                    <textarea class="form-control block-field" name="description" placeholder="Mô tả" rows="2">${esc(blockData.description)}</textarea>
                    <button class="btn btn-success w-100 btn-sm mt-2" onclick="saveBlock('${blockId}')">Lưu</button>
                `;
                break;
            case 'announcement':
                form += `
                    <div class="form-check mb-2">
                        <input type="checkbox" class="form-check-input block-field" name="enabled" ${blockData.enabled ? 'checked' : ''}>
                        <label class="form-check-label">Kích hoạt</label>
                    </div>
                    <textarea class="form-control block-field mb-2" name="message" placeholder="Nội dung" rows="2">${esc(blockData.message)}</textarea>
                    <select class="form-select block-field mb-2" name="type">
                        <option ${blockData.type === 'info' ? 'selected' : ''}>info</option>
                        <option ${blockData.type === 'success' ? 'selected' : ''}>success</option>
                        <option ${blockData.type === 'warning' ? 'selected' : ''}>warning</option>
                        <option ${blockData.type === 'danger' ? 'selected' : ''}>danger</option>
                    </select>
                    <button class="btn btn-success w-100 btn-sm" onclick="saveBlock('${blockId}')">Lưu</button>
                `;
                break;
            case 'text':
            case 'html':
                form += `
                    <textarea class="form-control block-field" name="content" placeholder="Nội dung" rows="4">${esc(blockData.content)}</textarea>
                    <button class="btn btn-success w-100 btn-sm mt-2" onclick="saveBlock('${blockId}')">Lưu</button>
                `;
                break;
            case 'hero':
                form += `
                    <input type="text" class="form-control block-field mb-2" name="title" value="${esc(blockData.title)}" placeholder="Tiêu đề chính">
                    <textarea class="form-control block-field mb-2" name="subtitle" placeholder="Tiêu đề phụ" rows="2">${esc(blockData.subtitle)}</textarea>
                    <input type="text" class="form-control block-field mb-2" name="buttonText" value="${esc(blockData.buttonText)}" placeholder="Text nút">
                    <input type="text" class="form-control block-field mb-2" name="buttonLink" value="${esc(blockData.buttonLink)}" placeholder="Link">
                    <label style="font-size: 11px; font-weight: bold; display: block; margin-bottom: 6px;">Ảnh nền:</label>
                    <div class="input-group mb-2" style="gap: 5px;">
                        <input type="text" class="form-control block-field" name="backgroundImage" value="${esc(blockData.backgroundImage)}" placeholder="URL">
                        <button class="btn btn-outline-primary btn-sm" type="button" onclick="openImageUpload('${blockId}', 'backgroundImage')">Chọn</button>
                    </div>
                    ${blockData.backgroundImage ? `<img src="${esc(blockData.backgroundImage)}" style="max-width: 100%;">` : ''}
                    <button class="btn btn-success w-100 btn-sm mt-2" onclick="saveBlock('${blockId}')">Lưu</button>
                `;
                break;
            case 'contact':
                form += `
                    <input type="text" class="form-control block-field mb-2" name="title" value="${esc(blockData.title)}" placeholder="Tiêu đề">
                    <textarea class="form-control block-field mb-2" name="description" placeholder="Mô tả" rows="2">${esc(blockData.description)}</textarea>
                    <input type="email" class="form-control block-field mb-2" name="email" value="${esc(blockData.email)}" placeholder="Email để nhận liên hệ">
                    <input type="text" class="form-control block-field mb-2" name="phone" value="${esc(blockData.phone)}" placeholder="Số điện thoại">
                    <input type="text" class="form-control block-field mb-2" name="address" value="${esc(blockData.address)}" placeholder="Địa chỉ">
                    <button class="btn btn-success w-100 btn-sm mt-2" onclick="saveBlock('${blockId}')">Lưu</button>
                `;
                break;
            case 'testimonials':
                form += `
                    <input type="text" class="form-control block-field mb-2" name="title" value="${esc(blockData.title)}" placeholder="Tiêu đề">
                    <textarea class="form-control block-field mb-2" name="content" placeholder="Nội dung nhận xét, cách nhau bởi dòng mới" rows="4">${esc(blockData.content)}</textarea>
                    <button class="btn btn-success w-100 btn-sm mt-2" onclick="saveBlock('${blockId}')">Lưu</button>
                `;
                break;
            case 'richtext':
                form += `
                    <div id="richTextEditor-${blockId}" class="form-section" style="height: 300px; margin-bottom: 10px;"></div>
                    <input type="hidden" class="block-field" name="content">
                    <button class="btn btn-success w-100 btn-sm mt-2" onclick="saveRichTextBlock('${blockId}')">Lưu</button>
                `;
                setTimeout(() => {
                    const quill = new Quill(`#richTextEditor-${blockId}`, {
                        theme: 'snow',
                        modules: {
                            toolbar: [
                                [{ 'header': [1, 2, 3, false] }],
                                ['bold', 'italic', 'underline', 'strike'],
                                ['blockquote', 'code-block'],
                                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                ['link', 'image'],
                                ['clean']
                            ]
                        }
                    });
                    quill.root.innerHTML = blockData.content || '';
                    window[`quillEditor_${blockId}`] = quill;
                }, 100);
                break;
            case 'gallery':
                form += `
                    <div class="form-section">
                        <h6>Cài đặt</h6>
                        <label style="font-size: 11px; font-weight: bold; display: block; margin-bottom: 6px;">Số cột</label>
                        <select class="form-select form-select-sm block-field mb-3" name="columns">
                            <option value="1" ${blockData.columns === 1 ? 'selected' : ''}>1 cột</option>
                            <option value="2" ${blockData.columns === 2 ? 'selected' : ''}>2 cột</option>
                            <option value="3" ${blockData.columns === 3 ? 'selected' : ''}>3 cột</option>
                        </select>
                        
                        <h6>Hình ảnh</h6>
                        <div id="galleryImages-${blockId}" class="mb-3"></div>
                        <button type="button" class="btn btn-outline-primary btn-sm w-100 mb-2" onclick="addGalleryImage('${blockId}')">
                            <i class="fas fa-plus"></i> Thêm hình ảnh
                        </button>
                    </div>
                    <button class="btn btn-success w-100 btn-sm mt-2" onclick="saveGalleryBlock('${blockId}')">Lưu</button>
                `;
                setTimeout(() => renderGalleryImages('${blockId}', blockData.images || []), 100);
                break;
        }
        
        form += '</div>';
        document.getElementById('blockEditForm').innerHTML = form;
        document.getElementById('blockEditForm').scrollIntoView({behavior: 'smooth'});
    });
}

function saveBlock(blockId) {
    const fields = document.querySelectorAll('#blockEditForm .block-field');
    const data = {};
    fields.forEach(f => {
        data[f.name] = f.type === 'checkbox' ? f.checked : f.value;
    });
    
    fetch('/sell-shop-SPU/controller/c_pagebuilder.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=updateBlock&pageSlug=${pageSlug}&blockId=${blockId}&data=${JSON.stringify(data)}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            toast({title: 'Lưu', message: 'Block đã cập nhật', type: 'success'});
            saveHistoryAction('updateBlock', 'Cập nhật block');
            refreshPreview();
        }
    });
}

function addBlockType(type, section = 'center') {
    const defaults = {
        banner: {title: 'Banner', subtitle: '', buttonText: 'Mua ngay', backgroundImage: ''},
        featured_products: {title: 'Sản phẩm nổi bật', description: ''},
        announcement: {message: '', type: 'info', enabled: true},
        vouchers: {title: 'Vouchers', description: ''},
        text: {content: 'Nội dung ở đây'},
        html: {content: '<p>HTML code</p>'},
        hero: {title: 'Chào mừng', subtitle: 'Mô tả ngắn gọn', buttonText: 'Bắt đầu', buttonLink: '#', backgroundImage: ''},
        contact: {title: 'Liên hệ với chúng tôi', description: 'Chúng tôi rất sẵn lòng nghe từ bạn', email: '', phone: '', address: ''},
        testimonials: {title: 'Nhận xét từ khách hàng', content: 'Nhận xét 1\nNhận xét 2\nNhận xét 3'},
        richtext: {content: '<p>Nhập nội dung của bạn ở đây</p>'},
        gallery: {columns: 3, images: []}
    };
    
    fetch('/sell-shop-SPU/controller/c_pagebuilder.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=addBlock&pageSlug=${pageSlug}&type=${type}&section=${section}&data=${JSON.stringify(defaults[type])}`
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            toast({title: 'Thêm', message: 'Block mới thêm thành công', type: 'success'});
            saveHistoryAction('addBlock', `Thêm block ${type}`);
            refreshPreview();
        }
    });
}

function deleteBlock(blockId) {
    if (!confirm('Xóa block này?')) return;
    fetch('/sell-shop-SPU/controller/c_pagebuilder.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=deleteBlock&pageSlug=${pageSlug}&blockId=${blockId}`
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            toast({title: 'Xóa', message: 'Block đã xóa', type: 'success'});
            saveHistoryAction('deleteBlock', 'Xóa block');
            refreshPreview();
        }
    });
}

function refreshPreview() {
    setTimeout(() => location.reload(), 500);
}

function openImageUpload(blockId, fieldName) {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.onchange = (e) => uploadImage(e, blockId, fieldName);
    input.click();
}

function uploadImage(event, blockId, fieldName) {
    const file = event.target.files[0];
    if (!file) return;
    
    if (file.size > 5 * 1024 * 1024) {
        toast({title: 'Lỗi', message: 'File quá lớn (max 5MB)', type: 'error'});
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'uploadImage');
    formData.append('image', file);
    
    toast({title: 'Đang tải', message: 'Vui lòng chờ...', type: 'info'});
    
    fetch('/sell-shop-SPU/controller/c_pagebuilder.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const targetInput = document.querySelector(`#blockEditForm [name="${fieldName}"]`) || document.querySelector(`[name="${fieldName}"]`);
            if (targetInput) targetInput.value = data.imageUrl;
            const img = document.querySelector('#blockEditForm .form-section img') || document.querySelector('.form-section img');
            if (img) {
                img.src = data.imageUrl;
            }
            toast({title: 'Thành công', message: 'Tải ảnh thành công', type: 'success'});
        } else {
            toast({title: 'Lỗi', message: data.message || 'Tải ảnh thất bại', type: 'error'});
        }
    });
}

function esc(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

// ===== RICH TEXT BLOCK =====
function saveRichTextBlock(blockId) {
    const quill = window[`quillEditor_${blockId}`];
    if (!quill) {
        toast({title: 'Lỗi', message: 'Editor chưa sẵn sàng', type: 'error'});
        return;
    }
    
    const content = quill.root.innerHTML;
    const data = { content };
    
    fetch('/sell-shop-SPU/controller/c_pagebuilder.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=updateBlock&pageSlug=${pageSlug}&blockId=${blockId}&data=${JSON.stringify(data)}`
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            toast({title: 'Lưu', message: 'Block đã cập nhật', type: 'success'});
            saveHistoryAction('updateBlock', 'Cập nhật Rich Text');
            refreshPreview();
        }
    });
}

// ===== GALLERY BLOCK =====
function addGalleryImage(blockId) {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.onchange = (e) => uploadGalleryImage(e, blockId);
    input.click();
}

function uploadGalleryImage(event, blockId) {
    const file = event.target.files[0];
    if (!file) return;
    
    const formData = new FormData();
    formData.append('action', 'uploadImage');
    formData.append('image', file);
    
    fetch('/sell-shop-SPU/controller/c_pagebuilder.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            if (!window[`galleryImages_${blockId}`]) {
                window[`galleryImages_${blockId}`] = [];
            }
            window[`galleryImages_${blockId}`].push({ url: data.imageUrl, caption: '' });
            renderGalleryImages(blockId, window[`galleryImages_${blockId}`]);
        }
    });
}

function renderGalleryImages(blockId, images) {
    window[`galleryImages_${blockId}`] = images;
    let html = '';
    images.forEach((img, idx) => {
        html += `
            <div class="gallery-preview" style="border: 1px solid #ddd; padding: 10px; border-radius: 4px; margin-bottom: 10px;">
                <div style="width: 100%; min-height: 250px; background: #fff; display: flex; align-items: center; justify-content: center; margin-bottom: 8px; border-radius: 3px; overflow: auto;">
                    <img src="${img.url}" style="max-width: 100%; max-height: 250px; object-fit: contain;">
                </div>
                <input type="text" class="form-control form-control-sm mb-2" value="${(img.caption||'').replace(/"/g, '&quot;')}" placeholder="Chú thích" onchange="updateGalleryCaption('${blockId}', ${idx}, this.value)">
                <button type="button" class="btn btn-sm btn-danger w-100" onclick="removeGalleryImage('${blockId}', ${idx})">Xóa</button>
            </div>
        `;
    });
    document.getElementById(`galleryImages-${blockId}`).innerHTML = html || '<p class="text-muted small">Chưa có hình ảnh</p>';
}

function updateGalleryCaption(blockId, index, caption) {
    window[`galleryImages_${blockId}`][index].caption = caption;
}

function removeGalleryImage(blockId, index) {
    window[`galleryImages_${blockId}`].splice(index, 1);
    renderGalleryImages(blockId, window[`galleryImages_${blockId}`]);
}

function saveGalleryBlock(blockId) {
    const columnsEl = document.querySelector('#blockEditForm [name="columns"]') || document.querySelector('[name="columns"]');
    const columns = columnsEl ? parseInt(columnsEl.value || 1, 10) : 1;
    const images = window[`galleryImages_${blockId}`] || [];
    
    const data = { columns, images };
    
    fetch('/sell-shop-SPU/controller/c_pagebuilder.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=updateBlock&pageSlug=${pageSlug}&blockId=${blockId}&data=${JSON.stringify(data)}`
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            toast({title: 'Lưu', message: 'Block đã cập nhật', type: 'success'});
            saveHistoryAction('updateBlock', 'Cập nhật Gallery');
            refreshPreview();
        }
    });
}

// ===== UNDO/REDO =====
function undoAction() {
    fetch('/sell-shop-SPU/controller/c_pagebuilder.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=undo&pageSlug=${pageSlug}`
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            toast({title: 'Hoàn tác', message: 'Đã hoàn tác thay đổi', type: 'success'});
            refreshPreview();
            updateHistoryButtons();
        }
    });
}

function redoAction() {
    fetch('/sell-shop-SPU/controller/c_pagebuilder.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=redo&pageSlug=${pageSlug}`
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            toast({title: 'Làm lại', message: 'Đã làm lại thay đổi', type: 'success'});
            refreshPreview();
            updateHistoryButtons();
        }
    });
}

function saveHistoryAction(action, description) {
    fetch('/sell-shop-SPU/controller/c_pagebuilder.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=saveHistory&pageSlug=${pageSlug}&historyAction=${action}&description=${encodeURIComponent(description)}`
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            updateHistoryButtons();
        }
    });
}

function updateHistoryButtons() {
    fetch(`/sell-shop-SPU/controller/c_pagebuilder.php?action=getHistoryState&pageSlug=${pageSlug}`)
    .then(r => r.json())
    .then(d => {
        if (d.success && d.state) {
            document.getElementById('undoBtn').disabled = !d.state.canUndo;
            document.getElementById('redoBtn').disabled = !d.state.canRedo;
        }
    });
}

document.addEventListener('DOMContentLoaded', updateHistoryButtons);
</script>

