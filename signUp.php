<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="./public/img/favicon.png">
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
            <h1 class="text-white h3">Account Register</h1>
        </div>
        <form class="mt-4" method="POST" action="controller/c_signUp.php">
            <div class="input-group uf-input-group input-group-lg mb-3">
                <span class="input-group-text fa fa-user"></span>
                <input type="text" class="form-control" name="TenTK" placeholder="Account name" required>
            </div>
            <div class="input-group uf-input-group input-group-lg mb-3">
                <span class="input-group-text fa fa-envelope"></span>
                <input type="email" name="Email" class="form-control" placeholder="Email address" required
                    pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" title="Vui lòng nhập địa chỉ email hợp lệ.">
            </div>
            <div class="input-group uf-input-group input-group-lg mb-3">
                <span class="input-group-text fa fa-phone"></span>
                <input type="text" name="SDT" class="form-control" placeholder="phone" required>
            </div>
            <div class="input-group uf-input-group input-group-lg mb-3">
                <span class="input-group-text fa fa-home"></span>
                <input type="text" name="DiaChi" class="form-control" placeholder="Address" required>
            </div>
            <div class="input-group uf-input-group input-group-lg mb-3">
                <span class="input-group-text fa fa-lock"></span>
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <div class="input-group uf-input-group input-group-lg mb-3">
                <span class="input-group-text fa fa-lock"></span>
                <input type="password" name="confirnPassword" class="form-control" placeholder="Confirn password"
                    required>
            </div>
            <?php
            if (isset($_GET['error'])) {
                if ($_GET['error'] == 'exists') {
                    echo '<p style="color: white; text-align: center; font-weight: bold;">Email hoặc SDT đã tồn tại</p>';
                } elseif ($_GET['error'] == 'passwordmismatch') {
                    echo '<p style="color: white; text-align: center; font-weight: bold;">Mật khẩu và xác nhận mật khẩu không khớp</p>';
                }
            }
            if (isset($_GET['message']) && isset($_GET['status'])) {
                $alertClass = $_GET['status'] === 'success' ? 'alert-success' : 'alert-danger';
            
                echo '<div class="alert ' . $alertClass . ' text-center mx-auto m-3 w-50 d-flex justify-content-center align-items-center gap-2">';
                echo '<span>' . htmlspecialchars($_GET['message']) . '</span>';
            
                if ($_GET['status'] === 'success') {
                    echo '
                        <div class="spinner-border text-success" role="status" style="width: 1.2rem; height: 1.2rem;">
                            <span class="visually-hidden"></span>
                        </div>';
                }
            
                echo '</div>';
            
                if ($_GET['status'] === 'success') {
                    echo '
                        <script>
                            setTimeout(function() {
                                window.location.href = "./signin.php";
                            }, 3000);
                        </script>
                    ';
                }
            }
            ?>

            <div class="d-grid mb-4">
                <button type="submit" class="btn uf-btn-primary btn-lg">Sign Up</button>
            </div>
            <div class="mt-4 text-center">
                <span class="text-white">Already a member?</span>
                <a href="signIn.php">Login</a>
            </div>
        </form>
    </div>

    <!-- JavaScript -->

    <!-- Separate Popper and Bootstrap JS -->
    <script src="./public/js/popper.min.js"></script>
    <script src="./public/js/bootstrap.min.js"></script>
</body>

</html>