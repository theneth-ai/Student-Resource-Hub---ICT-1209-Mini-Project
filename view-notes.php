<?php
session_start();

// User login 
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php?error=login_required");
    exit();
}

require_once 'includes/db.php';

$module_code = $_GET['code'] ?? '';
$module_name = $_GET['name'] ?? '';

$notes = [];
if (!empty($module_code)) {
    $searchTerm = trim($module_code) . '%';
    $stmt = $pdo->prepare("SELECT * FROM notes WHERE module_code LIKE ? OR category LIKE ? ORDER BY uploaded_at DESC");
    $stmt->execute([$searchTerm, $searchTerm]);
    $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Notes - Xnotes</title>
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

        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h2 class="fw-bold m-0 text-dark"><?php echo htmlspecialchars($module_code ?: 'All Notes'); ?></h2>
                <h5 class="text-muted"><?php echo htmlspecialchars($module_name); ?></h5>
            </div>
            <a href="index.php"
                class="glass-btn shadow-sm text-dark fw-semibold text-decoration-none d-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-arrow-left" style="font-size: 1.25rem;"></i> Back
            </a>
        </div>

        <div class="row mb-5">
            <?php if (!empty($notes)): ?>
                <?php foreach ($notes as $note): 
                    $fileExt = strtolower(pathinfo($note['file_path'], PATHINFO_EXTENSION));
                    
                    $iconClass = 'bi-file-earmark-text-fill text-secondary';
                    if ($fileExt === 'pdf') {
                        $iconClass = 'bi-file-earmark-pdf-fill text-danger';
                    } elseif (in_array($fileExt, ['zip', 'rar'])) {
                        $iconClass = 'bi-file-earmark-zip-fill text-warning';
                    } elseif (in_array($fileExt, ['doc', 'docx'])) {
                        $iconClass = 'bi-file-earmark-word-fill text-primary';
                    } elseif (in_array($fileExt, ['ppt', 'pptx'])) {
                        $iconClass = 'bi-file-earmark-ppt-fill text-danger';
                    }

                    $fileSize = file_exists($note['file_path']) ? round(filesize($note['file_path']) / (1024 * 1024), 2) . ' MB' : 'N/A';
                    $uploadDate = date('Y-m-d', strtotime($note['uploaded_at']));
                    
                   
                    $secureFileUrl = 'view-file.php?file=' . urlencode(basename($note['file_path']));
                ?>
                    <div class="col-12 mb-3">
                        <div class="card glass-element glass-card p-3 border-0">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="bi <?php echo $iconClass; ?>" style="font-size: 2.5rem;"></i>
                                    <div>
                                        <h5 class="fw-bold mb-1 text-dark"><?php echo htmlspecialchars($note['title']); ?></h5>
                                        <small class="text-dark fw-medium">Uploaded on: <?php echo $uploadDate; ?> | Size: <?php echo $fileSize; ?></small>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <?php if ($fileExt === 'pdf'): ?>
                                        <button type="button" 
                                            class="btn btn-outline-dark rounded-pill px-4 shadow-sm fw-semibold"
                                            onclick="openViewer('<?php echo $secureFileUrl; ?>', '<?php echo htmlspecialchars(addslashes($note['title'])); ?>')">
                                            <i class="bi bi-eye me-1"></i> View
                                        </button>
                                    <?php endif; ?>
                                    <a href="<?php echo $secureFileUrl; ?>" download class="btn btn-theme px-4 shadow-sm">
                                        <i class="bi bi-download me-1"></i> Download
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="card glass-element glass-card p-5 text-center">
                        <i class="bi bi-folder-x text-muted mb-3" style="font-size: 3rem;"></i>
                        <h4 class="text-muted fw-bold">No notes uploaded for this module yet.</h4>
                        <p class="text-muted mb-0">Check back later or upload one yourself from the dashboard.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- PDF Viewer Modal -->
    <div class="modal fade" id="pdfViewerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" style="height: 90vh;">
            <div class="modal-content h-100 shadow-lg">
                <div class="modal-header border-bottom py-2">
                    <h5 class="modal-title fw-bold text-dark" id="pdfModalTitle">View Note</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0 h-100">
                    <iframe id="pdfFrame" src="" width="100%" height="100%" style="border: none;"></iframe>
                </div>
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
        const viewerModal = new bootstrap.Modal(document.getElementById('pdfViewerModal'));
        function openViewer(filePath, title) {
            document.getElementById('pdfModalTitle').innerText = title;
            document.getElementById('pdfFrame').src = filePath + '&toolbar=1';
            viewerModal.show();
        }

        document.getElementById('pdfViewerModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('pdfFrame').src = '';
        });
    </script>
</body>

</html>