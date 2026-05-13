<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <meta name="description" content="">
        <meta name="author" content="">
        <title>VNPAY RESPONSE</title>
        <!-- Bootstrap core CSS -->
        <link href="/vnpay_php/assets/bootstrap.min.css" rel="stylesheet"/>
        <!-- Custom styles for this template -->
        <link href="/vnpay_php/assets/jumbotron-narrow.css" rel="stylesheet">         
        <script src="/vnpay_php/assets/jquery-1.11.3.min.js"></script>
    </head>
    <body>
        <?php
        require_once("./config.php");
        $vnp_SecureHash = $_GET['vnp_SecureHash'];
        $inputData = array();
        foreach ($_GET as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }
        
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        // Include application models so we can update invoice status
        include_once __DIR__ . '/../model/m_database.php';
        include_once __DIR__ . '/../model/m_hoadon.php';
        include_once __DIR__ . '/../model/m_lsMua.php';
        $hdModel = new M_hoadon();
        $lsModel = new M_lsMua();
        ?>
        <!--Begin display -->
        <div class="container">
            <div class="header clearfix">
                <h3 class="text-muted">VNPAY RESPONSE</h3>
            </div>
            <div class="table-responsive">
                <div class="form-group">
                    <label >Mã đơn hàng:</label>

                    <label><?php echo $_GET['vnp_TxnRef'] ?></label>
                </div>    
                <div class="form-group">

                    <label >Số tiền:</label>
                    <label><?php echo $_GET['vnp_Amount'] ?></label>
                </div>  
                <div class="form-group">
                    <label >Nội dung thanh toán:</label>
                    <label><?php echo $_GET['vnp_OrderInfo'] ?></label>
                </div> 
                <div class="form-group">
                    <label >Mã phản hồi (vnp_ResponseCode):</label>
                    <label><?php echo $_GET['vnp_ResponseCode'] ?></label>
                </div> 
                <div class="form-group">
                    <label >Mã GD Tại VNPAY:</label>
                    <label><?php echo $_GET['vnp_TransactionNo'] ?></label>
                </div> 
                <div class="form-group">
                    <label >Mã Ngân hàng:</label>
                    <label><?php echo $_GET['vnp_BankCode'] ?></label>
                </div> 
                <div class="form-group">
                    <label >Thời gian thanh toán:</label>
                    <label><?php echo $_GET['vnp_PayDate'] ?></label>
                </div> 
                <div class="form-group">
                    <label >Kết quả:</label>
                    <label>
                        <?php
                        if ($secureHash == $vnp_SecureHash) {
                            if (isset($_GET['vnp_ResponseCode']) && $_GET['vnp_ResponseCode'] == '00') {
                                // Payment success according to VNPAY. Verify amount and update order status.
                                $maHD = isset($_GET['vnp_TxnRef']) ? $_GET['vnp_TxnRef'] : '';
                                $vnpAmountRaw = isset($_GET['vnp_Amount']) ? $_GET['vnp_Amount'] : '0';
                                // VNPAY sends amount in smallest currency unit (e.g., cents) — sample uses 100x.
                                // Use floatval/string handling to avoid 32-bit intval overflow on large amounts.
                                if (is_numeric($vnpAmountRaw)) {
                                    $vnpAmount = floatval($vnpAmountRaw) / 100.0;
                                } else {
                                    $vnpAmount = 0.0;
                                }
                                error_log("[vnpay_return] raw_vnp_Amount={$vnpAmountRaw} parsed_vnpAmount={$vnpAmount} maHD={$maHD}");

                                $msg = '';
                                if ($maHD === '') {
                                    $msg = "<span style='color:red'>Không có mã đơn hàng (vnp_TxnRef)</span>";
                                } else {
                                    $res = $hdModel->getHoaDon($maHD);
                                    if ($res && $res->num_rows > 0) {
                                        $row = $res->fetch_assoc();
                                        $storedAmount = floatval($row['SoTien']);
                                        if (abs($storedAmount - $vnpAmount) > 0.1) {
                                            error_log("VNPAY amount mismatch for MaHD={$maHD} vnp={$vnpAmount} db={$storedAmount}");
                                            $msg = "<span style='color:red'>Số tiền không khớp, thanh toán bị hủy tạm thời</span>";
                                        } else {
                                            $upd = $hdModel->updateStatus($maHD, 'Đã thanh toán');
                                            if ($upd !== false) {
                                                // Also update line-item states so UI shows 'Đang chuẩn bị hàng'
                                                $lsUpd = $lsModel->updateStateByMaHD($maHD, 'Đang chuẩn bị hàng');
                                                if ($lsUpd !== false) {
                                                    $msg = "<span style='color:blue'>GD Thanh cong. Hóa đơn và trạng thái mặt hàng đã được cập nhật.</span>";
                                                    echo "<script>window.location.href='http://localhost/sell-shop-SPU/index.php';</script>";
                                                } else {
                                                    error_log("Failed to update LS_Mua states for MaHD={$maHD}");
                                                    $msg = "<span style='color:orange'>GD Thanh cong. Tuy nhiên không cập nhật được trạng thái mặt hàng.</span>";
                                                }
                                            } else {
                                                $msg = "<span style='color:orange'>GD Thanh cong nhưng không cập nhật được hóa đơn.</span>";
                                            }
                                        }
                                    } else {
                                        $msg = "<span style='color:red'>Không tìm thấy hóa đơn tương ứng</span>";
                                    }
                                }
                                echo $msg;
                            } else {
                                echo "<span style='color:red'>GD Khong thanh cong</span>";
                            }
                        } else {
                            echo "<span style='color:red'>Chu ky khong hop le</span>";
                        }
                        ?>

                    </label>
                </div> 
            </div>
            <p>
                &nbsp;
            </p>
            <footer class="footer">
                   <p>&copy; VNPAY <?php echo date('Y')?></p>
            </footer>
        </div>  
    </body>
</html>
