<?php
require_once '../includes/db.php'; 

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $checkEmail = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $checkEmail->execute([$email]);

    if ($checkEmail->rowCount() > 0) {
        $error = "Already Registered!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$username, $email, $hashed_password])) {
            $success = "Registration Successful!";
        } else {
            $error = "An error occured! Please try again.";
        }
    }
}
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

                <form id="registerForm" method="POST" action="">
                    <div class="mb-3">
                        <label for="fullName" class="form-label fw-semibold">Full Name</label>
                        <div class="input-group shadow-sm rounded">
                            <span class="input-group-text"><i class="bi bi-person text-theme"></i></span>
                            <input type="text" class="form-control form-control-lg" id="fullName" name="username" placeholder="Enter your full name">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">University Email</label>
                        <div class="input-group shadow-sm rounded">
                            <span class="input-group-text"><i class="bi bi-envelope text-theme"></i></span>
                            <input type="email" class="form-control form-control-lg" id="email" name="email" placeholder="username@tec.rjt.ac.lk">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Password</label>
                        <div class="input-group shadow-sm rounded bg-white">
                            <span class="input-group-text border-end-0 bg-transparent"><i class="bi bi-lock text-theme"></i></span>
                            <input type="password" class="form-control form-control-lg border-start-0 border-end-0 shadow-none" id="password" name="password" placeholder="Create a password">
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
                            <input type="password" class="form-control form-control-lg border-start-0 border-end-0 shadow-none" id="confirmPassword" placeholder="Confirm your password">
                            <button class="btn border-start-0 border text-muted bg-transparent" type="button" id="toggleConfirmPassword">
                                <i class="bi bi-eye-slash" id="toggleConfirmIcon"></i>
                            </button>
                        </div>
                    </div>
                    <div class="d-grid mb-4">
                        <button type="submit" class="btn btn-theme btn-lg shadow-sm">Sign Up</button>
                    </div>
                    <div class="text-center">
                        <p class="mb-0 fw-medium">Already have an account? <a href="login.php" class="text-theme text-decoration-none fw-bold">Login</a></p>
                    </div>
                </form>
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
        document.getElementById('registerForm').addEventListener('submit', function (event) {
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

        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
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
    </script>
</body>
</html>