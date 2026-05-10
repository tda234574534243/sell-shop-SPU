<!-- Modern Luxury Footer -->
<footer id="footer" class="mt-auto pt-16 pb-8">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-12 mb-12">
      <!-- Brand Section -->
      <div class="space-y-4">
        <h3 class="font-montserrat text-xl font-bold bg-gradient-to-r from-indigo-400 to-rose-400 bg-clip-text text-transparent">Sup3rDup3r</h3>
        <p class="text-slate-400 text-sm leading-relaxed">Chúng tôi cung cấp sản phẩm công nghệ premium với dịch vụ vận chuyển & hỗ trợ tận tâm 24/7.</p>
        <p class="text-slate-400 text-sm"><span class="font-semibold text-indigo-400">Hotline:</span> 0123-456-789</p>
      </div>

      <!-- Quick Links -->
      <div class="space-y-4">
        <h4 class="font-montserrat font-bold text-slate-100">Liên Kết</h4>
        <ul class="space-y-2">
          <li><a href="index.php" class="text-slate-400 hover:text-indigo-400 transition text-sm">Trang Chủ</a></li>
          <li><a href="introduce.php" class="text-slate-400 hover:text-indigo-400 transition text-sm">Giới Thiệu</a></li>
          <li><a href="contact.php" class="text-slate-400 hover:text-indigo-400 transition text-sm">Liên Hệ</a></li>
          <li><a href="faq.php" class="text-slate-400 hover:text-indigo-400 transition text-sm">FAQ</a></li>
        </ul>
      </div>

      <!-- Social & Copyright -->
      <div class="space-y-4">
        <h4 class="font-montserrat font-bold text-slate-100">Theo Dõi</h4>
        <div class="flex gap-4">
          <a href="#" class="w-10 h-10 glass-effect rounded-full flex items-center justify-center text-slate-300 hover:text-indigo-400 hover:border-indigo-400/50 transition">
            <i class="fab fa-facebook fa-lg"></i>
          </a>
          <a href="#" class="w-10 h-10 glass-effect rounded-full flex items-center justify-center text-slate-300 hover:text-indigo-400 hover:border-indigo-400/50 transition">
            <i class="fab fa-youtube fa-lg"></i>
          </a>
          <a href="#" class="w-10 h-10 glass-effect rounded-full flex items-center justify-center text-slate-300 hover:text-rose-400 hover:border-rose-400/50 transition">
            <i class="fab fa-instagram fa-lg"></i>
          </a>
          <a href="#" class="w-10 h-10 glass-effect rounded-full flex items-center justify-center text-slate-300 hover:text-rose-400 hover:border-rose-400/50 transition">
            <i class="fab fa-tiktok fa-lg"></i>
          </a>
        </div>
        <p class="text-slate-500 text-xs">&copy; <?= date('Y') ?> Sup3rDup3r. Bản quyền.</p>
      </div>
    </div>

    <!-- Divider -->
    <div class="border-t border-indigo-500/10"></div>

    <!-- Chat Bubble -->
    <div class="mt-8">
      <?php if (file_exists(__DIR__ . '/chatBubble.php')) include_once __DIR__ . '/chatBubble.php'; ?>
    </div>
  </div>
</footer>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="/sell-shop-SPU/public/JS/main.js"></script>

</body>
</html>
