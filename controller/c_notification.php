<?php
require_once '../model/m_notification.php';

if (session_status() == PHP_SESSION_NONE) session_start();
// only admin
if (!isset($_SESSION['levelID']) || $_SESSION['levelID'] != 1) {
    header('Location: ../index.php'); exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$model = new M_notification();

if ($action === 'add') {
    $data = [
        'Title' => $_POST['Title'] ?? '',
        'Content' => $_POST['Content'] ?? '',
        'Type' => $_POST['Type'] ?? 'notice',
        'RelatedID' => !empty($_POST['RelatedID']) ? intval($_POST['RelatedID']) : null,
        'IsActive' => isset($_POST['IsActive']) ? 1 : 0
    ];
    $model->add($data);
    header('Location: ../admin/notifications.php'); exit;
}
if ($action === 'edit') {
    $id = intval($_POST['id'] ?? 0);
    $data = [
        'Title' => $_POST['Title'] ?? '',
        'Content' => $_POST['Content'] ?? '',
        'Type' => $_POST['Type'] ?? 'notice',
        'RelatedID' => !empty($_POST['RelatedID']) ? intval($_POST['RelatedID']) : null,
        'IsActive' => isset($_POST['IsActive']) ? 1 : 0
    ];
    $model->update($id, $data);
    header('Location: ../admin/notifications.php'); exit;
}
if ($action === 'delete') {
    $id = intval($_GET['id'] ?? 0);
    if ($id) $model->delete($id);
    header('Location: ../admin/notifications.php'); exit;
}

header('Location: ../admin/notifications.php'); exit;

?>
