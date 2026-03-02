<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('toggleSidebar');
    const sidebar = document.getElementById('sidebarMenu');
    const mainContent = document.getElementById('mainContent');

    toggleBtn.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
        mainContent.style.marginLeft = sidebar.classList.contains('collapsed') ? '70px' : '250px';
    });

    // ==== Gán class "active" dựa trên URL tuyệt đối ====
    const currentURL = window.location.href;
    const navLinks = document.querySelectorAll('.sidebar a');

    navLinks.forEach(link => {
        const linkURL = new URL(link.href, window.location.origin).href;

        if (currentURL === linkURL) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });
});
</script>
<script>
window.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has("page")) {
        // Cuộn đến vị trí bảng sản phẩm (hoặc ID nào bạn muốn)
        const table = document.querySelector("table");
        if (table) {
            table.scrollIntoView({
                behavior: "smooth"
            });
        }
    }
});
</script>
<script>
document.getElementById('logoutButton').addEventListener('click', function(e) {
    e.preventDefault(); // Ngăn chuyển hướng mặc định

    // Nếu bạn cần xác nhận:
    if (confirm("Bạn có chắc chắn muốn đăng xuất không?")) {
        window.location.href = "../controller/c_logout.php";
    }
});
</script>
<script>
function toast({
    title = "",
    message = "",
    type = "info",
    duration = 3000
}) {
    const main = document.getElementById("toastcs");
    if (main) {
        const toast = document.createElement("div");

        const autoRemoveId = setTimeout(function() {
            if (toast.parentNode) {
                main.removeChild(toast);
            }
        }, duration + 1000);

        toast.onclick = function(e) {
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
</script>