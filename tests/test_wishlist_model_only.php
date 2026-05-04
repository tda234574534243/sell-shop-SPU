<?php
// tests/test_wishlist_model_only.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
chdir(__DIR__ . '/..');

if (session_status() == PHP_SESSION_NONE) session_start();
$_SESSION['user_id'] = 1;

include_once 'model/m_wishlist.php';
$mw = new M_wishlist();
$product = 'TEST01';

echo "Adding product $product for user " . $_SESSION['user_id'] . "\n";
$added = $mw->add($_SESSION['user_id'], $product);
var_export($added);
echo "\nIs favorited? "; var_export($mw->isFavorited($_SESSION['user_id'], $product));
echo "\nCount: "; var_export($mw->countByUser($_SESSION['user_id']));

echo "\nRemoving...\n";
$removed = $mw->remove($_SESSION['user_id'], $product);
var_export($removed);

echo "\nFinal count: "; var_export($mw->countByUser($_SESSION['user_id']));

file_put_contents('logs/test_wishlist_model_only.txt', ob_get_contents() ?: "");

echo "\nDone.\n";
