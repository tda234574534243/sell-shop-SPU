<?php
session_start();
require_once '../model/m_comment.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$product = $_POST['MaSP'] ?? $_GET['MaSP'] ?? null;
$id = isset($_POST['id']) ? intval($_POST['id']) : (isset($_GET['id']) ? intval($_GET['id']) : null);

$model = new M_comment();
$userId = $_SESSION['user_id'] ?? null;
$isAdmin = (isset($_SESSION['levelID']) && $_SESSION['levelID'] == 1);

switch ($action) {
    case 'add':
        if (!$userId) { header('Location: ../signIn.php'); exit; }
        $content = trim($_POST['Content'] ?? '');
        $rating = isset($_POST['Rating']) ? intval($_POST['Rating']) : null;
        $model->addComment($product, $userId, $content, $rating);
        header("Location: ../product_detail.php?id=".urlencode($product));
        exit;

    case 'edit':
        if (!$userId) { header('Location: ../signIn.php'); exit; }
        $content = trim($_POST['Content'] ?? '');
        $rating = isset($_POST['Rating']) ? intval($_POST['Rating']) : null;
        $ok = $model->editComment($id, $userId, $content, $rating, $isAdmin);
        header("Location: ../product_detail.php?id=".urlencode($product));
        exit;

    case 'delete':
        if (!$userId) { header('Location: ../signIn.php'); exit; }
        $ok = $model->deleteComment($id, $userId, $isAdmin);
        header("Location: ../product_detail.php?id=".urlencode($product));
        exit;

    case 'hide':
        if (!$userId) { header('Location: ../signIn.php'); exit; }
        // ensure the actor can hide/unhide: owner or admin
        $comment = $model->getCommentById($id);
        if (!$comment) { header("Location: ../product_detail.php?id=".urlencode($product)); exit; }
        $hidden = isset($_POST['hidden']) ? intval($_POST['hidden']) : 1;
        if ($isAdmin || ($comment['MaTK'] == $userId)) {
            $ok = $model->setHidden($id, $userId, $hidden, $isAdmin);
        }
        header("Location: ../product_detail.php?id=".urlencode($product));
        exit;

    default:
        header('Location: ../index.php'); exit;
}

?>
