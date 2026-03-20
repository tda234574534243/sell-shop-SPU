<?php include('template/head.php') ?>
<?php include('template/header.php') ?>
<?php include('template/toastMess.php') ?>
<div class="main__container">
    <div class="container__banner">
        <div class="banner__inner">
            <div class="banner__title">
                <h3>PSHOP</h3>
            </div>

            <div class="banner__description">
                <p>Đồ điện tử ? Ghé PSHOP</p>
            </div>
        </div>
    </div>

    <main>
        <div class="slider" style="
            --width: 100px;
            --height: 50px;
            --quantity: 10;
        ">
            <div class="list">
                <div class="item" style="--position: 1"><img src="./media/image/Slider/slider1_1.png" alt=""></div>
                <div class="item" style="--position: 2"><img src="./media/image/Slider/slider1_2.png" alt=""></div>
                <div class="item" style="--position: 3"><img src="./media/image/Slider/slider1_3.png" alt=""></div>
                <div class="item" style="--position: 4"><img src="./media/image/Slider/slider1_4.png" alt=""></div>
                <div class="item" style="--position: 5"><img src="./media/image/Slider/slider1_5.png" alt=""></div>
                <div class="item" style="--position: 6"><img src="./media/image/Slider/slider1_6.png" alt=""></div>
                <div class="item" style="--position: 7"><img src="./media/image/Slider/slider1_7.png" alt=""></div>
                <div class="item" style="--position: 8"><img src="./media/image/Slider/slider1_8.png" alt=""></div>
                <div class="item" style="--position: 9"><img src="./media/image/Slider/slider1_9.png" alt=""></div>
                <div class="item" style="--position: 10"><img src="./media/image/Slider/slider1_10.png" alt=""></div>
            </div>
        </div>
    </main>
</div>
<?php $sql = "SELECT * FROM products WHERE 1=1"?>
<?php include('template/productList.php'); ?>
<?php include('template/footer.php') ?>
