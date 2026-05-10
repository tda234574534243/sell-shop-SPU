<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PSHOP Việt Nam - Premium Tech Store</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Inter & Montserrat Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        /* Toast container: fixed so it doesn't push page content down */
        #toastcs {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 99999;
            pointer-events: none;
            margin: 0;
        }
        h1, h2, h3, h4, h5, h6, .heading {
            font-family: 'Montserrat', sans-serif;
        }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f0f0f0; }
        ::-webkit-scrollbar-thumb { background: #a0aec0; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #718096; }
        .glass-effect {
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .soft-shadow { box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08), 0 1px 3px rgba(0, 0, 0, 0.05); }
        .deep-shadow { box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12), 0 8px 16px rgba(0, 0, 0, 0.08); }
        html, body { height: 100%; background: #0f172a; }
        body { display: flex; flex-direction: column; min-height: 100vh; color: #e2e8f0; }
        body > div, body > nav, body > main { flex: 1 0 auto; }
    </style>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        luxury: { dark: '#0f172a', light: '#1e293b' },
                    },
                    fontFamily: { inter: ['Inter', 'sans-serif'], montserrat: ['Montserrat', 'sans-serif'] },
                },
            },
        }
    </script>
    <style>
        /* Ensure footer sticks to bottom when page content is short */
        #footer { margin-top: auto; }
        #footer { background: linear-gradient(135deg, rgba(15, 23, 42, 0.95) 0%, rgba(30, 41, 59, 0.95) 100%); border-top: 1px solid rgba(99, 102, 241, 0.2); }
        #footer a { transition: color 0.3s ease; }
        #footer a:hover { color: #818cf8; }
    </style>
    <?php
        // Track user activity on every page
        if (file_exists(__DIR__ . '/../helper/user_tracking.php')) {
            include __DIR__ . '/../helper/user_tracking.php';
        }
        // Global middleware logger
        if (file_exists(__DIR__ . '/../helper/middleware_logger.php')) {
            include __DIR__ . '/../helper/middleware_logger.php';
        }
    ?>
</head>
<body>
    <!-- Toast Container -->
    <div id="toastcs"></div>
