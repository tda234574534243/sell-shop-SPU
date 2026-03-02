<?php 
    require('../model/m_product.php');
    session_start();
    //Nếu Admin nhập thông tin sản phẩm và ấn Thêm sản phẩm thì
    //Tạo phương thức get set như thầy
    //Dùng hàm insert sản phẩm để create nó vào table
    
    //Nếu Admin chọn vào 1 sản phẩm nào đó trong list sản phẩm đang hiển thị trên web
    //Thì thông tin sản phẩm đó phải hiện trong các textbox
    //Admin có thể sửa bất kì thông tin và ấn Cập Nhật
    //Dùng hàm fixed để cập nhật lại vào bảng Products

    //Nếu Admin ấn vào sản phầm bất kì, thông tin sản phẩm đó cũng hiện trên các thanh textbox
    //Admin ấn Xóa sản phẩm
    //Gọi hàm remove để xóa sản phẩm đó trên bảng Products
    
?>