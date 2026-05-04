<?php
// tests/seed_session.php
error_reporting(E_ALL);
ini_set('display_errors',1);
chdir(__DIR__ . '/..');
session_start();
$_SESSION['user_id'] = 1;
header('Content-Type: application/json');
echo json_encode(['success'=>true,'message'=>'session set','session_id'=>session_id()]);
