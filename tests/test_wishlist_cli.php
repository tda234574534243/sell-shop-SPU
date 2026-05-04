<?php
// tests/test_wishlist_cli.php
// Simulate an XHR POST to controller/c_wishlist.php with a logged-in session.
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure working directory is project root
chdir(__DIR__ . '/..');

// Simulate AJAX header
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

// Start session and set a test user id
if (session_status() == PHP_SESSION_NONE) session_start();
$_SESSION['user_id'] = 1; // adjust if needed; used only for auth check

// Provide test POST data
$_POST['action'] = 'add';
$_POST['product_id'] = 'TEST01';

ob_start();
include 'controller/c_wishlist.php';
$output = ob_get_clean();

file_put_contents('logs/test_wishlist_output.txt', $output);
echo "--- Controller Output ---\n";
echo $output;

echo "\n--- End ---\n";

// Now test remove
$_POST['action'] = 'remove';
$_POST['product_id'] = 'TEST01';

ob_start();
include 'controller/c_wishlist.php';
$output2 = ob_get_clean();
file_put_contents('logs/test_wishlist_output_remove.txt', $output2);
echo "--- Controller Output (remove) ---\n";
echo $output2;

echo "\n--- End Remove ---\n";

// Test isFavorited via model directly
include_once 'model/m_wishlist.php';
$mw = new M_wishlist();
$added = $mw->add($_SESSION['user_id'], 'TEST01');
echo "\nModel add() returned: "; var_export($added);
$isFav = $mw->isFavorited($_SESSION['user_id'], 'TEST01');
echo "\nModel isFavorited(): "; var_export($isFav);
$count = $mw->countByUser($_SESSION['user_id']);
echo "\nModel countByUser(): "; var_export($count);

// cleanup
$removed = $mw->remove($_SESSION['user_id'], 'TEST01');
echo "\nModel remove(): "; var_export($removed);

file_put_contents('logs/test_wishlist_model_log.txt', "add:" . ($added?1:0) . ", isFav:" . ($isFav?1:0) . ", count:$count, removed:" . ($removed?1:0));

echo "\nTests complete. Logs written to logs/ folder.\n";
