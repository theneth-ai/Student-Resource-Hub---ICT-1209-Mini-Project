<?php

session_start();
require_once '../includes/db.php'; 

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?"); //Check the email in the Database
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    
    if ($user && password_verify($password, $user['password'])) {
        
       
        session_regenerate_id(true); //Update the session id
        
       
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        
       
        header("Location: ../dashboard.php");
        exit();
        
    } else {
        $error = "Incorrect Password or Email";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Xnotes</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="stylesheet" href="../css/style.css?v=1.1">
</head>

<body>

    <!-- NAVIGATION BAR with Glass Effect-->
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
                        <a class="nav-link px-3" href="../contact.php" title="About / Contact">
                            <i class="bi bi-info-circle-fill icon-btn"></i>
                        </a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <a class="nav-link btn px-4 fw-semibold active"
                            style="border: 2px solid #967bb6; color: #967bb6; border-radius: 50px;"
                            href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn px-4 text-white fw-semibold shadow-sm"
                            style="background-color: #967bb6; border-radius: 50px;" href="register.php">Signup</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- MAIN CONTENT (LOGIN FORM) -->
    <div class="container d-flex justify-content-center align-items-center mt-5 mb-5 main-content">
        <div class="card glass-element glass-card border-0 rounded-4" style="width: 100%; max-width: 450px;">
            <div class="card-body p-4 p-md-5">

                <div class="text-center mb-4">
                    <i class="bi bi-box-arrow-in-right text-theme" style="font-size: 3rem;"></i>
                    <h3 class="fw-bold mt-2">Welcome Back</h3>
                    <p class="text-dark fw-medium">Login to access your Xnotes</p>
                </div>
            <?php if($error != ''): ?>
                     <div class="alert alert-danger text-center fw-bold"><?php echo $error; ?></div>

             <?php endif; ?>
                <div id="loginError" class="alert alert-danger d-none text-center" role="alert"></div>

                <form id="loginForm" method="POST" action="">
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Email Address</label>
                        <div class="input-group shadow-sm rounded">
                            <span class="input-group-text"><i class="bi bi-envelope text-theme"></i></span>
                            <input type="email" class="form-control form-control-lg" id="email" name="email"
                                placeholder="username@tec.rjt.ac.lk">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold">Password</label>
                        <div class="input-group shadow-sm rounded">
                            <span class="input-group-text"><i class="bi bi-lock text-theme"></i></span>
                            <input type="password" class="form-control form-control-lg" id="password" name="password"
                                placeholder="Enter your password">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input shadow-sm" id="rememberMe">
                            <label class="form-check-label fw-medium" for="rememberMe">Remember me</label>
                        </div>
                        <a href="forgot-password.php" class="text-theme text-decoration-none fw-semibold">Forgot Password?</a>
                    </div>

                    <div class="d-grid mb-4">
                        <button type="submit" class="btn btn-theme btn-lg shadow-sm">Login</button>
                    </div>

                    <div class="text-center">
                        <p class="mb-0 fw-medium">Don't have an account? <a href="register.php"
                                class="text-theme text-decoration-none fw-bold">Sign up</a></p>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <footer class="glass-element py-4 mt-auto">
        <div class="container text-center text-dark fw-medium">
            &copy; 2026 Xnotes | Rajarata University BICT
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Basic Login Validation -->
    <script>
        document.getElementById('loginForm').addEventListener('submit', function (event) {
            let email = document.getElementById('email').value.trim();
            let password = document.getElementById('password').value.trim();
            let errorAlert = document.getElementById('loginError');

            if (email === '' || password === '') {
                event.preventDefault();
                errorAlert.innerText = 'Please enter both email and password!';
                errorAlert.classList.remove('d-none');
            } else {
                errorAlert.classList.add('d-none');
            }
        });
    </script>
</body>

</html>