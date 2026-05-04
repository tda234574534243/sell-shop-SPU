
(function(){
    const cartIcon = document.querySelector('.cart-icon')
    const cartSubIcon = document.querySelector('.cart-sub-icon')
    const cartTab = document.querySelector('.cartTab')
    const cartClose = document.querySelector('.close-cartTab')
    const cartlist = document.querySelector('.cart-list')

    if (cartIcon) cartIcon.addEventListener('click', () => {
        if (cartTab) cartTab.classList.toggle('showCart');
        showgiohangotherweb()
    })

    if (cartSubIcon) cartSubIcon.addEventListener('click', () => {
        if (cartTab) cartTab.classList.toggle('showCart');
        showgiohangotherweb()
    })

    if (cartClose) cartClose.addEventListener('click', () => {
        if (cartTab) cartTab.classList.toggle('showCart');
    })

    function showgiohangotherweb() {
        var giohang = JSON.parse(localStorage.getItem("giohang") || '[]');
        try { if (typeof cartNavNumber !== 'undefined' && cartNavNumber) cartNavNumber.innerHTML = giohang.length; } catch(e){}
        var ttgh = document.querySelector('.cart-list') ? document.querySelector('.cart-list').innerHTML : '';
        for (let i = 0; i < giohang.length; i++)
        {
            var temp =  '<div class="cart-item">'+
            '<p><span class="tenSp">'+giohang[i][3]+'</span></p>'+
            '<img src="'+giohang[i][0]+'" alt="">'+
            '<p>Giá: <span class="giaSp">'+giohang[i][1]+'</span> VND</p>'+
            '<p>Số lượng <span class="soLuongSP">'+giohang[i][2]+'</span></p>'+
            '<p>Thành tiền <span class="thanhtienSP">'+(giohang[i][2] * giohang[i][1])+'</span></p>'+
            '<div class="xoaSP" onclick="xoaSP(this)">'+
                '<svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">'+
                '<path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>'+
                '<path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>'+
                '</svg>'+
            '</div>'+
            '<div class="clear"></div>'+
            '</div>'
            
            if (ttgh.search(temp.slice(0,80)) < 0) {
                ttgh += temp
            }
        }
        var listEl = document.querySelector('.cart-list'); if (listEl) listEl.innerHTML = ttgh;
    }

    const myCarouselElement = document.querySelector('#myCarousel')
    if (myCarouselElement && typeof bootstrap !== 'undefined') {
        const carousel = new bootstrap.Carousel(myCarouselElement, {
          interval: 2000,
          touch: false
        })
    }

    //--------------Toast Message----------------
    function toast({ title = "", message = "", type = "info", duration = 3000 }) {
        const main = document.getElementById("toastcs");
        if (main) {
            const toast = document.createElement("div");

            const autoRemoveId = setTimeout(function () {
                if (toast.parentNode) {
                    main.removeChild(toast);
                }
            }, duration + 1000);

            toast.onclick = function (e) {
                if (e.target.closest(".toast__close")) {
                    main.removeChild(toast);
                    clearTimeout(autoRemoveId);
                }
            };

            const icons = {
                success: "fas fa-check-circle",
                info: "fas fa-info-circle",
                warning: "fas fa-exclamation-circle",
                error: "fas fa-exclamation-circle"
            };
            const icon = icons[type];
            const delay = (duration / 1000).toFixed(2); 
    
            toast.classList.add("toastcs", `toast--${type}`);
            toast.style.animation = `slideInLeft ease .3s, fadeOut linear 1s ${delay}s forwards`;

            toast.innerHTML = `
                <div class="toast__icon">
                    <i class="${icon}"></i>
                </div>
                <div class="toast__body">
                    <h3 class="toast__title">${title}</h3>
                    <p class="toast__msg">${message}</p>
                </div>
                <div class="toast__close">
                    <i class="fas fa-times"></i>
                </div>
            `;

            main.appendChild(toast);
        }
    }



    // Intercept add-to-cart forms and update cart badge via AJAX
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('form[action="controller/c_addToCart.php"]').forEach(function(form){
            form.addEventListener('submit', function(e){
                e.preventDefault();
                const formData = new FormData(form);
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).then(res => res.json()).then(data => {
                    if (data && data.success) {
                        const badge = document.querySelector('.cart-count');
                        if (badge) badge.innerText = data.count > 99 ? '99+' : data.count;
                        else if (data.count > 0) {
                            // try to add badge to cart icon
                            const cartLink = document.querySelector('.cart-icon a') || document.querySelector('.cart-icon');
                            if (cartLink) {
                                const span = document.createElement('span');
                                span.className = 'badge bg-danger rounded-pill cart-count';
                                span.style.cssText = 'position:absolute;top:-6px;right:-6px;font-size:11px;';
                                span.innerText = data.count > 99 ? '99+' : data.count;
                                cartLink.appendChild(span);
                            }
                        }
                        toast({ title: 'Thông báo', message: data.message || 'Đã thêm vào giỏ hàng', type: 'success', duration: 3000 });
                    } else {
                        toast({ title: 'Lỗi', message: (data && data.message) || 'Không thể thêm vào giỏ hàng', type: 'error', duration: 3000 });
                    }
                }).catch(err => {
                    console.error(err);
                    // fallback to full submit
                    form.submit();
                });
            });
        });
    });

    // Favorite (wishlist) button handler
    document.addEventListener('click', function(e){
        const btn = e.target.closest('.fav-btn');
        if (!btn) return;
        console.log('[fav-btn] clicked', btn);
        const productId = btn.getAttribute('data-product-id');
        const favorited = btn.getAttribute('data-favorited') === '1';
        // require login handled server-side
        const action = favorited ? 'remove' : 'add';
        fetch('controller/c_wishlist.php', {
            method: 'POST',
            headers: {'X-Requested-With':'XMLHttpRequest'},
            body: new URLSearchParams({ action: action, product_id: productId })
        }).then(res => res.json()).then(data => {
            console.log('[fav-btn] response', data);
            if (data && data.success) {
                const newFav = favorited ? '0' : '1';
                btn.setAttribute('data-favorited', newFav);
                const icon = btn.querySelector('i.fas');
                if (icon) {
                    if (newFav === '1') {
                        icon.classList.remove('text-muted');
                        icon.classList.add('text-danger');
                    } else {
                        icon.classList.remove('text-danger');
                        icon.classList.add('text-muted');
                    }
                }
                // update wishlist badge
                const badge = document.querySelector('.wishlist-count');
                if (badge) badge.innerText = data.count>99? '99+' : data.count;
                toast({ title: 'Thông báo', message: favorited? 'Đã bỏ yêu thích' : 'Đã thêm vào yêu thích', type: 'success', duration: 2000 });
            } else {
                // if user not logged in, redirect to signin
                if (data && data.message && /đăng nhập/i.test(data.message)) {
                    window.location.href = 'signIn.php';
                    return;
                }
                toast({ title: 'Lỗi', message: data && data.message ? data.message : 'Không thể cập nhật yêu thích', type: 'error', duration: 3000 });
            }
        }).catch(err => {
            console.error('[fav-btn] fetch error', err);
            toast({ title: 'Lỗi', message: 'Không thể kết nối tới server', type: 'error', duration: 3000 });
        });
    });

    // Cart quantity change handler (on cart page)
    document.addEventListener('change', function(e){
        const inp = e.target.closest('.cart-qty-input');
        if (!inp) return;
        const productId = inp.getAttribute('data-product-id');
        const qty = parseInt(inp.value) || 1;
        fetch('controller/c_updateCart.php', {
            method: 'POST',
            headers: {'X-Requested-With':'XMLHttpRequest'},
            body: new URLSearchParams({ product_id: productId, quantity: qty })
        }).then(res => res.json()).then(data => {
            if (data && data.success) {
                // update badge
                const badge = document.querySelector('.cart-count');
                if (badge) badge.innerText = data.count>99? '99+' : data.count;
                // update item total and overall total
                const line = inp.closest('.cart-line');
                if (line) {
                    const price = parseFloat(line.getAttribute('data-price')) || 0;
                    const itemTotal = price * qty;
                    const itemTotalEl = line.querySelector('.item-total');
                    if (itemTotalEl) itemTotalEl.innerText = itemTotal.toLocaleString('vi-VN') + ' đ';
                    // if quantity became zero (removed server-side), remove row
                    if (qty <= 0) {
                        line.remove();
                    }
                }
                // recalc whole total
                let sum = 0;
                document.querySelectorAll('.cart-line').forEach(function(l){
                    const p = parseFloat(l.getAttribute('data-price')) || 0;
                    const q = parseInt(l.querySelector('.cart-qty-input').value) || 0;
                    sum += p * q;
                });
                const totalEl = document.getElementById('orderTotal');
                if (totalEl) totalEl.innerText = sum.toLocaleString('vi-VN') + ' đ';
            } else {
                toast({ title: 'Lỗi', message: data.message || 'Cập nhật số lượng thất bại', type: 'error' });
            }
        }).catch(err => {
            console.error(err);
        });
    });

    // Remove cart item (AJAX)
    document.addEventListener('click', function(e){
        const btn = e.target.closest('.remove-cart-btn');
        if (!btn) return;
        const productId = btn.getAttribute('data-product-id');
        if (!confirm('Xác nhận xóa sản phẩm khỏi giỏ hàng?')) return;
        fetch('controller/c_removeCart_ajax.php', {
            method: 'POST',
            headers: {'X-Requested-With':'XMLHttpRequest'},
            body: new URLSearchParams({ product_id: productId })
        }).then(res => res.json()).then(data => {
            if (data && data.success) {
                // remove DOM line
                const line = document.querySelector('.cart-line[data-product-id="' + productId + '"]');
                if (line) line.remove();
                // update badge
                const badge = document.querySelector('.cart-count');
                if (badge) badge.innerText = data.count>99? '99+' : data.count;
                // recalc total
                let sum = 0;
                document.querySelectorAll('.cart-line').forEach(function(l){
                    const p = parseFloat(l.getAttribute('data-price')) || 0;
                    const q = parseInt(l.querySelector('.cart-qty-input').value) || 0;
                    sum += p * q;
                });
                const totalEl = document.getElementById('orderTotal');
                if (totalEl) totalEl.innerText = sum.toLocaleString('vi-VN') + ' đ';
                // if no items left show empty message
                if (document.querySelectorAll('.cart-line').length === 0) {
                    const lg = document.querySelector('.list-group'); if (lg) lg.innerHTML = '<div class="text-center py-4 text-muted">Giỏ hàng đang trống.</div>';
                }
                toast({ title: 'Thông báo', message: 'Đã xóa sản phẩm', type: 'success' });
            } else {
                toast({ title: 'Lỗi', message: data.message || 'Xóa thất bại', type: 'error' });
            }
        }).catch(err => console.error(err));
    });

})();


