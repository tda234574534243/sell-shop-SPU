<?php
if (session_status() == PHP_SESSION_NONE) session_start();
?>
<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="./public/css/bootstrap.min1.css">
    <!-- FontAwesome CSS -->
    <link rel="stylesheet" href="./public/css/all.min.css">
    <link rel="stylesheet" href="./public/css/uf-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

</head>
<body class="body-theme-blue">
    <style>
        /* Improved blue gradient and polished form styles */
        body.body-theme-blue {
            background: linear-gradient(135deg, #0d6efd 0%, #3b82f6 45%, #60a5fa 100%) no-repeat fixed !important;
            background-color: #0d6efd !important;
            min-height: 100vh !important;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 16px;
            color: #ffffff;
        }

        /* Sign-in card with subtle glass effect and blur */
        body.body-theme-blue .uf-form-signin {
            width: 100%;
            max-width: 460px;
            box-shadow: 0 12px 30px rgba(8, 30, 80, 0.35);
            border-radius: 14px;
            background: linear-gradient(180deg, rgba(255,255,255,0.06), rgba(255,255,255,0.03));
            border: 1px solid rgba(255,255,255,0.08);
            padding: 28px;
            backdrop-filter: blur(6px) saturate(120%);
            -webkit-backdrop-filter: blur(6px) saturate(120%);
            color: #fff;
        }

        /* Inputs: translucent background with white text */
        .uf-input-group .form-control {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.12);
            color: #ffffff;
            box-shadow: none;
        }
        .uf-input-group .form-control::placeholder { color: rgba(255,255,255,0.75); }
        .uf-input-group .input-group-text {
            background: transparent;
            border: 1px solid rgba(255,255,255,0.06);
            color: rgba(255,255,255,0.95);
        }

        /* Primary button: blue-to-cyan gradient */
        .uf-btn-primary {
            background: linear-gradient(90deg,#2563eb 0%, #06b6d4 100%);
            border: none;
            color: #fff;
            font-weight: 600;
            box-shadow: 0 8px 20px rgba(37,99,235,0.22);
        }
        .uf-btn-primary:active, .uf-btn-primary:focus { transform: translateY(1px); }

        a { color: #cfeeff; }
        .text-white a { color: #e6f6ff; }
    </style>
        
    <div class="uf-form-signin">
        <div class="text-center">
            <a href="index.php"><img src="./media/image/other/logo-fb.png" alt="" width="100" height="100"></a>
            <h1 class="text-white h3">Account Login</h1>
        </div>
        <form class="mt-4" method="POST" action="controller/c_signIn.php">
            <div class="input-group uf-input-group input-group-lg mb-3">
                <span class="input-group-text fa fa-envelope"></span>
                <input type="email" class="form-control" id="email" name="email" placeholder="Email address" required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" title="Vui lòng nhập địa chỉ email hợp lệ.">
            </div>
            <div class="input-group uf-input-group input-group-lg mb-3">
                <span class="input-group-text fa fa-lock"></span>
                <input type="password" name="password" id="password" class="form-control" placeholder="password" required>
            </div>
            <?php if (isset($_GET['error']) && $_GET['error'] == 'invalid'): ?>
                <p style="color: red; text-align: center; font-weight: bold;">
                    Đăng nhập thất bại.
                </p>
            <?php endif; ?>

            <div class="d-flex mb-3 justify-content-between">
                <!-- <div class="form-check">
                    <input type="checkbox" class="form-check-input uf-form-check-input" id="exampleCheck1">
                    <label class="form-check-label text-white" for="exampleCheck1">Remember Me</label>
                </div>
                <a href="#">Forgot password?</a> -->
            </div>

            <div class="d-grid mb-4">
                <button type="submit" class="btn uf-btn-primary btn-lg">Login</button>
            </div>
            <!-- <div class="d-flex mb-3">
                <div class="dropdown-divider m-auto w-25"></div>
                <small class="text-nowrap text-white">Or login with</small>
                <div class="dropdown-divider m-auto w-25"></div>
            </div> -->
            <!-- <div class="uf-social-login d-flex justify-content-center">

                <a href="controller/c_google_login.php" class="uf-social-ic" title="Login with Google"><i class="fa-brands fa-google"></i></a>
            </div> -->
            <div class="mt-4 text-center">
                <span class="text-white">Don't have an account?</span>
                <a href="signUp.php">Sign Up</a>
            </div>
        </form>
        <?php include 'template/toastMess.php'; ?>
    </div>

    <!-- JavaScript -->

    <!-- Separate Popper and Bootstrap JS -->
    <script src="./public/JS/main.js"></script>
    <script src="./public/js/popper.min.js"></script>
    <script src="./public/js/bootstrap.min.js"></script>
</body>

</html>