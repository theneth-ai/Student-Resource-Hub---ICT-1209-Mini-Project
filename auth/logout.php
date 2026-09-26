<?php
session_start();
require_once '../includes/db.php';

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
}

if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time() - 3600, '/');
}

$_SESSION = array(); //Delete the session data
session_destroy();

header("Location: login.php");
exit();
?>