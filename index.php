<?php
session_start();
require_once 'includes/db.php';

$recentSubjectCodes = [];
if (isset($pdo)) {
    try {
        $stmt = $pdo->query("SELECT subject FROM notes ORDER BY id DESC LIMIT 10");
        $subjects = $stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($subjects as $s) {
            if (preg_match('/[A-Za-z]{3}\s*\d{4}/', $s, $match)) {
                $code = strtoupper(preg_replace('/\s+/', ' ', trim($match[0])));
                if (!in_array($code, $recentSubjectCodes)) {
                    $recentSubjectCodes[] = $code;
                }
            }
        }
    } catch (Exception $e) {
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xnotes - The BICT Resource Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=1.2">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                        <a class="nav-link active px-3" href="index.php" title="Home">
                            <i class="bi bi-house-door-fill icon-btn"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3" href="contact.php" title="About / Contact">
                            <i class="bi bi-info-circle-fill icon-btn"></i>
                        </a>
                    </li>

                    <?php if (isset($_SESSION['user_id'])): ?>
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

    <div class="container mt-5 main-content">
        <div class="text-center mb-4">
            <h1 class="fw-bold">Welcome to <span style="color:red;">X</span><span style="color:black">notes</span></h1>
            <p class="fs-5 mt-2 text-dark">The ultimate BICT Resource Hub for undergraduates</p>
        </div>

        <div class="row justify-content-center mb-5">
            <div class="col-md-10 col-lg-8">
                <div class="input-group input-group-lg glass-element rounded-pill overflow-hidden shadow-sm">
                    <span class="input-group-text bg-transparent border-0 px-4">
                        <i class="bi bi-search" style="color: #967bb6;"></i>
                    </span>
                    <input type="text" id="searchInput" class="form-control border-0 shadow-none bg-transparent"
                        placeholder="Search for lecture notes, past papers...">
                </div>
                <p class="text-muted mt-2 text-center" style="font-size: 0.85rem;">
                    <b>These can only be accessed with a valid tec.rjt.ac.lk domain email. You need to use your student
                        mail to view resources.</b>
                </p>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <h4 class="mb-0 fw-bold" id="categoryTitle">Popular Categories:</h4>
            <div class="d-flex align-items-center gap-3">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="upload.php"
                        class="glass-btn shadow-sm text-dark fw-semibold text-decoration-none d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-paperclip" style="font-size: 1.25rem;"></i> Add Note
                    </a>
                <?php else: ?>
                    <button type="button" onclick="showLoginAlert('upload')"
                        class="glass-btn shadow-sm text-dark fw-semibold text-decoration-none d-flex align-items-center justify-content-center gap-2 border-0">
                        <i class="bi bi-paperclip" style="font-size: 1.25rem;"></i> Add Note
                    </button>
                <?php endif; ?>

                <div class="position-relative d-flex align-items-center">
                    <select id="yearFilter" class="glass-btn shadow-sm text-dark fw-semibold m-0"
                        style="appearance: none; -webkit-appearance: none; outline: none; padding-right: 2.5rem; width: 120px;">
                        <option selected value="All">Year</option>
                        <option value="1">1st Year</option>
                        <option value="2">2nd Year</option>
                        <option value="3">3rd Year</option>
                        <option value="4">4th Year</option>
                    </select>
                    <i class="bi bi-chevron-down position-absolute fw-bold"
                        style="right: 15px; pointer-events: none; font-size: 0.9rem; color: #212529;"></i>
                </div>
            </div>
        </div>

        <div class="row mb-5" id="cardContainer"></div>
    </div>

    <footer class="glass-element py-4 mt-auto">
        <div class="container text-center text-dark fw-medium">
            &copy; 2026 Xnotes | Rajarata University BICT
        </div>
    </footer>

    <script>
        window.IS_LOGGED_IN = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
        window.RECENT_MODULE_CODES = <?php echo json_encode($recentSubjectCodes); ?>;

        function showLoginAlert(action = 'view') {
            const message = action === 'upload' 
                ? 'Please login with your university account to upload lecture notes!' 
                : 'Please login with your university account to view or download notes!';

            Swal.fire({
                icon: 'info',
                title: 'Login Required',
                text: message,
                showCancelButton: true,
                confirmButtonText: 'Login Now',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#967bb6',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'auth/login.php';
                }
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>

</body>

</html>