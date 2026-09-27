<?php
session_start();
require_once 'includes/db.php';

$successMsg = '';
$errorMsg = '';

$userName = '';
$userEmail = '';
$isLoggedIn = false;

if (isset($_SESSION['user_id'])) {
    $isLoggedIn = true;
    $userName  = $_SESSION['username'] ?? '';
    $userEmail = $_SESSION['email'] ?? '';

    if (empty($userName) || empty($userEmail)) {
        $stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmtUser->execute([$_SESSION['user_id']]);
        $currentUser = $stmtUser->fetch(PDO::FETCH_ASSOC);

        if ($currentUser) {
            $userName  = $currentUser['username'] ?? ($currentUser['name'] ?? '');
            $userEmail = $currentUser['email'] ?? '';
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);

    if (strpos($email, '@tec.rjt.ac.lk') !== false) { 
        $sql = "INSERT INTO messages (name, email, message) VALUES (?, ?, ?)"; 
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$name, $email, $message])) {
            $successMsg = "Your message has been sent successfully!";
        } else {
            $errorMsg = "An error occurred! Please try again.";
        }
    } else {
        $errorMsg = "Please use a valid university email!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Xnotes</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=1.2">
</head>

<body>

    <nav class="navbar navbar-expand-lg glass-element shadow-sm py-2">
        <div class="container-fluid px-4">
            <a class="navbar-brand p-0 m-0" href="index.php">
                <img src="images/logonew1.png" alt="Xnotes Logo" height="80">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center gap-2">
                    <li class="nav-item">
                        <a class="nav-link px-3" href="index.php" title="Home">
                            <i class="bi bi-house-door-fill icon-btn"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active px-3" href="contact.php" title="About / Contact">
                            <i class="bi bi-info-circle-fill icon-btn"></i>
                        </a>
                    </li>

                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item">
                            <a class="nav-link px-3" href="dashboard.php" title="Dashboard">
                                <i class="bi bi-person-circle icon-btn"></i>
                            </a>
                        </li>
                        <li class="nav-item ms-lg-2">
                            <a class="nav-link btn px-4 btn-logout shadow-sm" href="auth/logout.php">
                                <i class="bi bi-box-arrow-right me-1"></i> Logout
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-lg-3">
                            <a class="nav-link btn px-4 fw-semibold"
                                style="border: 2px solid #967bb6; color: #967bb6; border-radius: 50px;"
                                href="auth/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn px-4 btn-theme shadow-sm" href="auth/register.php">Signup</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5 mb-5 flex-grow-1 d-flex justify-content-center align-items-center">
        <div class="card glass-element glass-card p-4 p-md-5" style="width: 100%; max-width: 550px;">

            <h3 class="fw-bold text-center mb-4" style="color: #333;">Contact Us</h3>
            
            <?php if(!empty($errorMsg)): ?>
                <div class="alert alert-danger text-center fw-bold"><?php echo htmlspecialchars($errorMsg); ?></div>
            <?php endif; ?>

            <?php if(!empty($successMsg)): ?>
                <div class="alert alert-success text-center fw-bold"><?php echo htmlspecialchars($successMsg); ?></div>
            <?php endif; ?>
            <div id="msgAlert" class="alert d-none text-center" role="alert"></div>

            <form id="contactForm" method="POST" action="">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Name</label>
                    <input type="text" class="form-control form-control-lg rounded-pill" id="name" name="name" 
                        value="<?php echo htmlspecialchars($userName); ?>" 
                        placeholder="Enter your name" 
                        <?php echo $isLoggedIn ? 'readonly style="background-color: rgba(240, 240, 240, 0.7) !important;"' : ''; ?> 
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">University Email</label>
                    <input type="email" class="form-control form-control-lg rounded-pill" id="email" name="email" 
                        value="<?php echo htmlspecialchars($userEmail); ?>" 
                        placeholder="username@tec.rjt.ac.lk" 
                        <?php echo $isLoggedIn ? 'readonly style="background-color: rgba(240, 240, 240, 0.7) !important;"' : ''; ?> 
                        required>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Message</label>
                    <textarea class="form-control form-control-lg" id="message" name="message" rows="5" style="border-radius: 20px;" placeholder="How can we help you?" required></textarea>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-theme btn-lg py-2 shadow-sm">Submit</button>
                </div>
            </form>
        </div>
    </div>

    <footer class="glass-element py-4 mt-auto">
        <div class="container text-center text-dark fw-medium">
            &copy; 2026 Xnotes | Rajarata University BICT
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    let form = document.getElementById('contactForm');

    form.addEventListener('submit', function (e) {
        let email = document.getElementById('email').value.trim();
        let alertBox = document.getElementById('msgAlert');

        if (!email.endsWith('@tec.rjt.ac.lk')) {
            e.preventDefault(); 
            alertBox.textContent = "Please use a valid university email!";
            alertBox.className = "alert alert-danger text-center";
            alertBox.classList.remove('d-none');
        }
    });
    </script>
</body>

</html>