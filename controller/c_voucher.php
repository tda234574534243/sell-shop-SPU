<?php
require_once '../model/m_voucher.php';

if (session_status() == PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['levelID']) || $_SESSION['levelID'] != 1) { header('Location: ../index.php'); exit; }

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$model = new M_voucher();

if ($action === 'add') {
    $data = [
        'Code'=> $_POST['Code'] ?? '',
        'Description'=> $_POST['Description'] ?? '',
        'DiscountPercent'=> !empty($_POST['DiscountPercent'])?intval($_POST['DiscountPercent']):null,
        'DiscountAmount'=> !empty($_POST['DiscountAmount'])?floatval($_POST['DiscountAmount']):null,
        'ValidFrom'=> $_POST['ValidFrom'] ?? null,
        'ValidTo'=> $_POST['ValidTo'] ?? null,
        'Quantity'=> !empty($_POST['Quantity'])?intval($_POST['Quantity']):null
    ];
    $model->add($data);
    header('Location: ../admin/vouchers.php'); exit;
}
if ($action === 'edit') {
    $id = intval($_POST['id'] ?? 0);
    $data = [
        'Code'=> $_POST['Code'] ?? '',
        'Description'=> $_POST['Description'] ?? '',
        'DiscountPercent'=> !empty($_POST['DiscountPercent'])?intval($_POST['DiscountPercent']):null,
        'DiscountAmount'=> !empty($_POST['DiscountAmount'])?floatval($_POST['DiscountAmount']):null,
        'ValidFrom'=> $_POST['ValidFrom'] ?? null,
        'ValidTo'=> $_POST['ValidTo'] ?? null,
        'Quantity'=> !empty($_POST['Quantity'])?intval($_POST['Quantity']):null
    ];
    $model->update($id, $data);
    header('Location: ../admin/vouchers.php'); exit;
}
if ($action === 'delete') {
    $id = intval($_GET['id'] ?? 0);
    if ($id) $model->delete($id);
    header('Location: ../admin/vouchers.php'); exit;
}

header('Location: ../admin/vouchers.php'); exit;

?>
