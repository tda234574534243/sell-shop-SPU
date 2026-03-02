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
<body>
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
    </div>

    <!-- JavaScript -->

    <!-- Separate Popper and Bootstrap JS -->
    <script src="./public/js/popper.min.js"></script>
    <script src="./public/js/bootstrap.min.js"></script>
</body>

</html>