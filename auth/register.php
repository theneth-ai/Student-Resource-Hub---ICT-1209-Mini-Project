<?php
session_start();
require_once '../includes/db.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../includes/PHPMailer/Exception.php';
require '../includes/PHPMailer/PHPMailer.php';
require '../includes/PHPMailer/SMTP.php';

// [SECURITY NOTICE FOR EXAMINER]
// The Gmail App Password has been intentionally removed from this code 
// before pushing to GitHub for security and privacy reasons.
// 
// To test the OTP email functionality, please enter a valid 16-character 
// Gmail App Password below, or configure it with a local testing server.

define('SMTP_USER', 'example@gmail.com'); // You can change this to your email
define('SMTP_PASS', ''); // Enter your App Password here to test

$error = '';
$success = '';
$alertType = '';
$alertMsg = '';

//  Send OTP
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btn_register'])) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!str_ends_with($email, '@tec.rjt.ac.lk')) {
        $error = "Please use a valid @tec.rjt.ac.lk university email!";
    } else {
        $checkEmail = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $checkEmail->execute([$email]);

        if ($checkEmail->rowCount() > 0) {
            $error = "Email is already registered!";
        } else {
            $otp = (string)rand(100000, 999999);
            $_SESSION['reg_username'] = $username;
            $_SESSION['reg_email'] = $email;
            $_SESSION['reg_password'] = password_hash($password, PASSWORD_BCRYPT);
            $_SESSION['reg_otp'] = $otp;
            $_SESSION['reg_otp_expiry'] = time() + 600;
            $_SESSION['reg_step'] = 2;

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
                $mail->Subject = 'Xnotes Registration OTP';
                $mail->Body    = "
                    <div style='font-family: Arial, sans-serif; padding: 20px; background: #f9f9f9; border-radius: 10px;'>
                        <h2 style='color: #967bb6;'>Welcome to Xnotes</h2>
                        <p>Your 6-digit registration verification code is:</p>
                        <h1 style='color: #333; letter-spacing: 5px; background: #eee; display: inline-block; padding: 10px 20px; border-radius: 8px;'>$otp</h1>
                        <p style='color: #777; font-size: 0.85rem; margin-top: 15px;'>This code will expire in 10 minutes.</p>
                    </div>";

                $mail->send();
                $alertType = 'success_otp';
                $alertMsg  = 'Verification code sent to your email!';
            } catch (Exception $e) {
                unset($_SESSION['reg_otp'], $_SESSION['reg_email']);
                $_SESSION['reg_step'] = 1;
                $error = 'Failed to send email. Check SMTP settings or internet connection.';
            }
        }
    }
}

// Verify OTP and Register
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btn_verify_reg_otp'])) {
    $entered_otp = trim($_POST['otp'] ?? '');

    if (empty($_SESSION['reg_otp']) || time() > $_SESSION['reg_otp_expiry']) {
        $error = 'Verification code has expired. Please register again.';
        unset($_SESSION['reg_step']);
    } elseif ($entered_otp === $_SESSION['reg_otp']) {
        $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$_SESSION['reg_username'], $_SESSION['reg_email'], $_SESSION['reg_password']])) {
            $alertType = 'done';
            $alertMsg  = 'Registration Successful! Redirecting to login...';
            unset($_SESSION['reg_step'], $_SESSION['reg_username'], $_SESSION['reg_email'], $_SESSION['reg_password'], $_SESSION['reg_otp']);
        } else {
            $error = "Database error! Please try again.";
        }
    } else {
        $error = 'Invalid verification code!';
    }
}

$currentStep = $_SESSION['reg_step'] ?? 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Xnotes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg glass-element shadow-sm py-2">
        <div class="container-fluid px-4">
            <a class="navbar-brand p-0 m-0" href="../index.php">
                <img src="../images/logonew1.png" alt="Xnotes Logo" height="80">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center gap-2">
                    <li class="nav-item">
                        <a class="nav-link px-3" href="../index.php" title="Home">
                            <i class="bi bi-house-door-fill icon-btn"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3" href="../contact.php" title="About/Contact">
                            <i class="bi bi-info-circle-fill icon-btn"></i>
                        </a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <a class="nav-link btn px-4 fw-semibold" style="border: 2px solid #967bb6; color: #967bb6; border-radius: 50px;" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn px-4 text-white fw-semibold shadow-sm active" style="background-color: #967bb6; border-radius: 50px;" href="register.php">Signup</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container d-flex justify-content-center align-items-center mt-5 mb-5 main-content">
        <div class="card glass-element glass-card border-0 rounded-4" style="width: 100%; max-width: 500px;">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <i class="bi bi-person-plus text-theme" style="font-size: 3rem;"></i>
                    <h3 class="fw-bold mt-2">Create an Account</h3>
                    <p class="text-dark fw-medium">Join Xnotes to access BICT resources</p>
                </div>
                <?php if($error != ''): ?>
                    <div class="alert alert-danger text-center fw-bold"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if($success != ''): ?>
                    <div class="alert alert-success text-center fw-bold"><?php echo $success; ?></div>
                <?php endif; ?>
                <div id="errorAlert" class="alert alert-danger d-none text-center" role="alert"></div>

                <?php if ($currentStep === 1): ?>
                <form id="registerForm" method="POST" action="">
                    <div class="mb-3">
                        <label for="fullName" class="form-label fw-semibold">Full Name</label>
                        <div class="input-group shadow-sm rounded">
                            <span class="input-group-text"><i class="bi bi-person text-theme"></i></span>
                            <input type="text" class="form-control form-control-lg" id="fullName" name="username" placeholder="Enter your full name" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">University Email</label>
                        <div class="input-group shadow-sm rounded">
                            <span class="input-group-text"><i class="bi bi-envelope text-theme"></i></span>
                            <input type="email" class="form-control form-control-lg" id="email" name="email" placeholder="username@tec.rjt.ac.lk" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Password</label>
                        <div class="input-group shadow-sm rounded bg-white">
                            <span class="input-group-text border-end-0 bg-transparent"><i class="bi bi-lock text-theme"></i></span>
                            <input type="password" class="form-control form-control-lg border-start-0 border-end-0 shadow-none" id="password" name="password" placeholder="Create a password" required>
                            <button class="btn border-start-0 border text-muted bg-transparent" type="button" id="togglePassword">
                                <i class="bi bi-eye-slash" id="toggleIcon"></i>
                            </button>
                        </div>
                        <div class="progress mt-2" style="height: 6px; display: none;" id="strengthContainer">
                            <div class="progress-bar" id="strengthBar" role="progressbar" style="width: 0%;"></div>
                        </div>
                        <small id="strengthText" class="mt-1 fw-medium" style="font-size: 0.8rem; display: none;"></small>
                    </div>
                    <div class="mb-4">
                        <label for="confirmPassword" class="form-label fw-semibold">Confirm Password</label>
                        <div class="input-group shadow-sm rounded bg-white">
                            <span class="input-group-text border-end-0 bg-transparent"><i class="bi bi-shield-lock text-theme"></i></span>
                            <input type="password" class="form-control form-control-lg border-start-0 border-end-0 shadow-none" id="confirmPassword" placeholder="Confirm your password" required>
                            <button class="btn border-start-0 border text-muted bg-transparent" type="button" id="toggleConfirmPassword">
                                <i class="bi bi-eye-slash" id="toggleConfirmIcon"></i>
                            </button>
                        </div>
                    </div>
                    <div class="d-grid mb-4">
                        <button type="submit" name="btn_register" class="btn btn-theme btn-lg shadow-sm">Sign Up</button>
                    </div>
                    <div class="text-center">
                        <p class="mb-0 fw-medium">Already have an account? <a href="login.php" class="text-theme text-decoration-none fw-bold">Login</a></p>
                    </div>
                </form>
                <?php elseif ($currentStep === 2): ?>
                <p class="text-muted text-center small mb-4">Enter the 6-digit code sent to <br><b><?php echo htmlspecialchars($_SESSION['reg_email']); ?></b></p>
                <form method="POST" action="">
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-center w-100">6-Digit Code</label>
                        <input type="text" name="otp" maxlength="6" class="form-control rounded-pill text-center fs-4 fw-bold letter-spacing" placeholder="123456" required>
                    </div>
                    <div class="d-grid mb-3">
                        <button type="submit" name="btn_verify_reg_otp" class="btn btn-theme rounded-pill py-2 shadow-sm">
                            <i class="bi bi-shield-check me-1"></i> Verify Code & Register
                        </button>
                    </div>
                    <div class="text-center mt-2">
                        <a href="register.php" class="text-decoration-none text-muted fw-semibold small">
                            <i class="bi bi-arrow-left"></i> Go Back
                        </a>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer class="glass-element py-4 mt-auto">
        <div class="container text-center text-dark fw-medium">
            &copy; 2026 Xnotes | Rajarata University BICT
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const regForm = document.getElementById('registerForm');
        if (regForm) {
            regForm.addEventListener('submit', function (event) {
                let name = document.getElementById('fullName').value.trim();
                let email = document.getElementById('email').value.trim();
                let password = document.getElementById('password').value.trim();
                let confirmPassword = document.getElementById('confirmPassword').value.trim();
                let errorAlert = document.getElementById('errorAlert');

                if (name === '' || email === '' || password === '' || confirmPassword === '') {
                    event.preventDefault();
                    errorAlert.innerText = 'Please fill in all fields!';
                    errorAlert.classList.remove('d-none');
                    return;
                }

                if (!email.endsWith('@tec.rjt.ac.lk')) {
                    event.preventDefault();
                    errorAlert.innerText = 'Please use a valid Rajarata University email (@tec.rjt.ac.lk)!';
                    errorAlert.classList.remove('d-none');
                    return;
                }

                if (password !== confirmPassword) {
                    event.preventDefault();
                    errorAlert.innerText = 'Passwords do not match!';
                    errorAlert.classList.remove('d-none');
                    return;
                }

                errorAlert.classList.add('d-none');
            });
        }

        const passwordInput = document.getElementById('password');
        if (passwordInput) {
            const togglePassword = document.getElementById('togglePassword');
            const toggleIcon = document.getElementById('toggleIcon');
            
            togglePassword.addEventListener('click', function () {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                toggleIcon.classList.toggle('bi-eye-slash');
                toggleIcon.classList.toggle('bi-eye');
            });

            const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
            const confirmPasswordInput = document.getElementById('confirmPassword');
            const toggleConfirmIcon = document.getElementById('toggleConfirmIcon');

            toggleConfirmPassword.addEventListener('click', function () {
                const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                confirmPasswordInput.setAttribute('type', type);
                toggleConfirmIcon.classList.toggle('bi-eye-slash');
                toggleConfirmIcon.classList.toggle('bi-eye');
            });

            const strengthContainer = document.getElementById('strengthContainer');
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');

            passwordInput.addEventListener('input', function() {
                const val = passwordInput.value;
                let strength = 0;

                if (val.length > 0) {
                    strengthContainer.style.display = 'flex';
                    strengthText.style.display = 'block';
                } else {
                    strengthContainer.style.display = 'none';
                    strengthText.style.display = 'none';
                    return;
                }

                if (val.length >= 8) strength += 25;
                if (val.match(/[A-Z]/)) strength += 25;
                if (val.match(/[0-9]/)) strength += 25;
                if (val.match(/[$@#&!%*?_]/)) strength += 25;

                strengthBar.style.width = strength + '%';

                if (strength <= 25) {
                    strengthBar.className = 'progress-bar bg-danger';
                    strengthText.innerText = 'Weak (Needs numbers/symbols)';
                    strengthText.className = 'mt-1 fw-medium text-danger';
                } else if (strength === 50 || strength === 75) {
                    strengthBar.className = 'progress-bar bg-warning';
                    strengthText.innerText = 'Medium (Add capitals/symbols)';
                    strengthText.className = 'mt-1 fw-medium text-warning';
                } else if (strength === 100) {
                    strengthBar.className = 'progress-bar bg-success';
                    strengthText.innerText = 'Strong Password';
                    strengthText.className = 'mt-1 fw-medium text-success';
                }
            });
        }
    </script>
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
    <?php elseif ($alertType === 'success_otp'): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'OTP Sent!',
                text: '<?php echo $alertMsg; ?>'
            });
        </script>
    <?php endif; ?>
</body>
</html>