<?php

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// THONG TIN KET NOI 
$host = 'sql100.infinityfree.com'; // MYSQL HOSTNAME
$user = 'ifo_40506311';           // MYSQL USERNAME
$pass = 'buiduc2312';             // MYSQL PASSWORD
$db   = 'if0_40506311_test';       // MYSQL DATABASE NAME 

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die('Connection failed: ' . $conn->connect_error);
}
$conn->set_charset('utf8mb4');

function current_user_id() {
  return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

?>