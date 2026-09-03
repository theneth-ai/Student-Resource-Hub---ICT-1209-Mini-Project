<?php
session_start();
require_once '../includes/db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../includes/PHPMailer/Exception.php';
require '../includes/PHPMailer/PHPMailer.php';
require '../includes/PHPMailer/SMTP.php';


define('SMTP_USER', 'pasindur91@gmail.com');
define('SMTP_PASS', ''); 


$alertType = '';
$alertMsg  = '';


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btn_send_otp'])) {
    $email = trim($_POST['email'] ?? '');

    if (!str_ends_with($email, '@tec.rjt.ac.lk')) {
        $alertType = 'error';
        $alertMsg  = 'Please use a valid @tec.rjt.ac.lk university email!';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $otp = (string)rand(100000, 999999);
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_otp']   = $otp;
            $_SESSION['otp_expiry']  = time() + 600;
            $_SESSION['step']        = 2;

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = SMTP_USER;
                $mail->Password   = SMTP_PASS;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom(SMTP_USER, 'Xnotes Hub');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Xnotes Password Reset OTP';
                $mail->Body    = "
                    <div style='font-family: Arial, sans-serif; padding: 20px; background: #f9f9f9; border-radius: 10px;'>
                        <h2 style='color: #967bb6;'>Xnotes Verification Code</h2>
                        <p>Your 6-digit password reset verification code is:</p>
                        <h1 style='color: #333; letter-spacing: 5px; background: #eee; display: inline-block; padding: 10px 20px; border-radius: 8px;'>$otp</h1>
                        <p style='color: #777; font-size: 0.85rem; margin-top: 15px;'>This code will expire in 10 minutes. If you did not request this, please ignore this email.</p>
                    </div>";

                $mail->send();
                $alertType = 'success_step1';
                $alertMsg  = 'Verification code has been sent to your email!';
            } catch (Exception $e) {
                unset($_SESSION['reset_otp'], $_SESSION['reset_email']);
                $_SESSION['step'] = 1;
                $alertType = 'error';
                $alertMsg  = 'Failed to send email. Check SMTP settings or internet connection.';
            }
        } else {
            $alertType = 'error';
            $alertMsg  = 'No account found registered under this email!';
        }
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btn_verify_otp'])) {
    $entered_otp = trim($_POST['otp'] ?? '');

    if (empty($_SESSION['reset_otp']) || time() > $_SESSION['otp_expiry']) {
        $alertType = 'error';
        $alertMsg  = 'Verification code has expired. Please try again.';
        $_SESSION['step'] = 1;
    } elseif ($entered_otp === $_SESSION['reset_otp']) {
        $_SESSION['step'] = 3;
    } else {
        $alertType = 'error';
        $alertMsg  = 'Invalid verification code! Please check your email.';
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btn_update_pass'])) {
    $new_pass  = $_POST['new_password'] ?? '';
    $conf_pass = $_POST['confirm_password'] ?? '';
    $email     = $_SESSION['reset_email'] ?? '';

    if (strlen($new_pass) < 6) {
        $alertType = 'error';
        $alertMsg  = 'New password must be at least 6 characters long!';
    } elseif ($new_pass !== $conf_pass) {
        $alertType = 'error';
        $alertMsg  = 'New password and confirm password do not match!';
    } elseif (!empty($email)) {
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        
        if ($updateStmt->execute([$hashed, $email])) {
            session_destroy();
            $alertType = 'done';
            $alertMsg  = 'Password updated successfully! Redirecting to login...';
        } else {
            $alertType = 'error';
            $alertMsg  = 'Failed to update password. Try again.';
        }
    }
}

$currentStep = $_SESSION['step'] ?? 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Xnotes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css?v=1.2">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 py-5">

    <div class="card glass-element glass-card p-4 p-md-5" style="width: 100%; max-width: 480px;">
        <div class="text-center mb-4">
            <a href="../index.php">
                <img src="../images/logonew1.png" alt="Xnotes Logo" height="70">
            </a>
            <h4 class="fw-bold mt-3 text-dark">Password Recovery</h4>
        </div>

  
        <?php if ($currentStep === 1): ?>
            <p class="text-muted text-center small mb-4">Enter your campus email address to receive a 6-digit verification code.</p>
            <form method="POST" action="">
                <div class="mb-4">
                    <label class="form-label fw-semibold">Campus Email</label>
                    <input type="email" name="email" class="form-control rounded-pill" placeholder="username@tec.rjt.ac.lk" required>
                </div>
                <div class="d-grid mb-3">
                    <button type="submit" name="btn_send_otp" class="btn btn-theme rounded-pill py-2 shadow-sm">
                        <i class="bi bi-send-fill me-1"></i> Send Verification Code
                    </button>
                </div>
            </form>

    
        <?php elseif ($currentStep === 2): ?>
            <p class="text-muted text-center small mb-4">Enter the 6-digit code sent to <br><b><?php echo htmlspecialchars($_SESSION['reset_email']); ?></b></p>
            <form method="POST" action="">
                <div class="mb-4">
                    <label class="form-label fw-semibold text-center w-100">6-Digit Code</label>
                    <input type="text" name="otp" maxlength="6" class="form-control rounded-pill text-center fs-4 fw-bold letter-spacing" placeholder="123456" required>
                </div>
                <div class="d-grid mb-3">
                    <button type="submit" name="btn_verify_otp" class="btn btn-theme rounded-pill py-2 shadow-sm">
                        <i class="bi bi-shield-check me-1"></i> Verify Code
                    </button>
                </div>
            </form>

        
        <?php elseif ($currentStep === 3): ?>
            <p class="text-muted text-center small mb-4">Create a new secure password for your account.</p>
            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label fw-semibold">New Password</label>
                    <input type="password" name="new_password" class="form-control rounded-pill" placeholder="At least 6 characters" required>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control rounded-pill" placeholder="Confirm new password" required>
                </div>
                <div class="d-grid mb-3">
                    <button type="submit" name="btn_update_pass" class="btn btn-theme rounded-pill py-2 shadow-sm">
                        <i class="bi bi-check-circle-fill me-1"></i> Reset Password
                    </button>
                </div>
            </form>
        <?php endif; ?>

        <div class="text-center mt-2">
            <a href="login.php" class="text-decoration-none text-muted fw-semibold small">
                <i class="bi bi-arrow-left"></i> Back to Login
            </a>
        </div>
    </div>

    <?php if ($alertType === 'done'): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '<?php echo $alertMsg; ?>',
                timer: 1800,
                showConfirmButton: false,
                timerProgressBar: true
            }).then(() => {
                window.location.href = 'login.php';
            });
        </script>
    <?php elseif ($alertType === 'success_step1'): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'OTP Sent!',
                text: '<?php echo $alertMsg; ?>'
            });
        </script>
    <?php elseif ($alertType === 'error'): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?php echo $alertMsg; ?>'
            });
        </script>
    <?php endif; ?>

</body>
</html>