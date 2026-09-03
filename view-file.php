<?php
// Session එක ආරම්භ කිරීම
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. User login 
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php?error=login_required");
    exit();
}


if (!isset($_GET['file']) || empty(trim($_GET['file']))) {
    http_response_code(400);
    die("Error: No file specified.");
}


$filename = basename($_GET['file']);
$filepath = __DIR__ . '/uploads/' . $filename;


if (!file_exists($filepath)) {
    http_response_code(404);
    die("Error: File not found on the server. Path: " . $filepath);
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $filepath);
finfo_close($finfo);

if (!$mime_type) {
    $mime_type = 'application/octet-stream';
}


header('Content-Type: ' . $mime_type);
header('Content-Length: ' . filesize($filepath));
header('Content-Disposition: inline; filename="' . $filename . '"');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');


if (ob_get_level()) {
    ob_end_clean();
}
readfile($filepath);
exit();