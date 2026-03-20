<?php include('template/head.php') ?>
<?php include('template/header.php') ?>
<?php include('template/toastMess.php') ?>

<style>
    body {
        font-family: 'Arial', sans-serif;
        background: #f4f7f6;
        margin: 0;
        padding: 0;
    }

    .contact-container {
        width: 90%;
        max-width: 1000px;
        margin: 50px auto;
        background: #fff;
        padding: 40px;
        border-radius: 16px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .contact-container h2 {
        text-align: center;
        color: #ff6600;
        margin-bottom: 30px;
    }

    .contact-form {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .contact-form input,
    .contact-form textarea {
        width: 100%;
        padding: 15px;
        border: 1px solid #ccc;
        border-radius: 12px;
        font-size: 16px;
        resize: none;
    }

    .contact-form button {
        background-color: #ff6600;
        color: white;
        border: none;
        padding: 15px;
        border-radius: 12px;
        font-size: 16px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .contact-form button:hover {
        background-color: #e65c00;
    }

    .contact-info {
        margin-top: 40px;
        text-align: center;
        color: #333;
    }

    .contact-info h3 {
        margin-bottom: 10px;
        color: #222;
    }

    .contact-info p {
        margin: 4px 0;
    }

    @media (max-width: 768px) {
        .contact-container {
            padding: 20px;
        }

        .contact-form input,
        .contact-form textarea {
            font-size: 15px;
        }
    }
</style>

<body>
    <div class="contact-container">
        <h2>Liên hệ với PSHOP</h2>
        <form class="contact-form" action="process_contact.php" method="POST">
            <input type="text" name="name" placeholder="Họ và tên" required>
            <input type="email" name="email" placeholder="Email của bạn" required>
            <input type="text" name="subject" placeholder="Chủ đề">
            <textarea name="message" rows="6" placeholder="Nội dung tin nhắn" required></textarea>
            <button type="submit">Gửi tin nhắn</button>
        </form>

        <div class="contact-info">
            <h3>Thông tin liên hệ</h3>
            <p>📍 Địa chỉ: 123 Nguyễn Văn Linh, TP.HCM</p>
            <p>📞 Hotline: 0909 999 999</p>
            <p>✉️ Email: contact@pshop.vn</p>
        </div>
    </div>
<?php include('template/footer.php') ?>
</body>
