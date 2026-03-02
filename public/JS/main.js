const cartIcon = document.querySelector('.cart-icon')
const cartSubIcon = document.querySelector('.cart-sub-icon')
const cartTab = document.querySelector('.cartTab')
const cartClose = document.querySelector('.close-cartTab')
const cartlist = document.querySelector('.cart-list')

cartIcon.addEventListener('click', () => {
    cartTab.classList.toggle('showCart');
    showgiohangotherweb()
})

cartSubIcon.addEventListener('click', () => {
    cartTab.classList.toggle('showCart');
    showgiohangotherweb()
})

cartClose.addEventListener('click', () => {
    cartTab.classList.toggle('showCart');
})


function showgiohangotherweb() {
    var giohang = JSON.parse(localStorage.getItem("giohang"));
    cartNavNumber.innerHTML = giohang.length;
    if (document.querySelector('.cart-list').innerText == ""){
        var ttgh = "";
    }
    else {
        ttgh = document.querySelector('.cart-list').innerHTML;
    }
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
    document.querySelector('.cart-list').innerHTML = ttgh;
}

const myCarouselElement = document.querySelector('#myCarousel')
const carousel = new bootstrap.Carousel(myCarouselElement, {
  interval: 2000,
  touch: false
})

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


