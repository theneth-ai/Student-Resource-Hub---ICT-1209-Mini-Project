<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$profileStatus = null;
$statusMessage = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $newName      = trim($_POST['new_name'] ?? '');
    $oldPassword  = $_POST['old_password'] ?? '';
    $newPassword  = $_POST['new_password'] ?? '';
    $confPassword = $_POST['confirm_password'] ?? '';

    if (empty($newName)) {
        $profileStatus = 'error';
        $statusMessage = 'Name cannot be empty!';
    } else {
        $stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmtUser->execute([$user_id]);
        $user = $stmtUser->fetch();

        if ($user) {
            $canUpdate = true;
            $updatePassword = false;

            if (!empty($newPassword) || !empty($oldPassword) || !empty($confPassword)) {
                if (empty($oldPassword)) {
                    $profileStatus = 'error';
                    $statusMessage = 'Please enter your current password to set a new password.';
                    $canUpdate = false;
                } elseif (!password_verify($oldPassword, $user['password'])) {
                    $profileStatus = 'error';
                    $statusMessage = 'Current password does not match!';
                    $canUpdate = false;
                } elseif (strlen($newPassword) < 6) {
                    $profileStatus = 'error';
                    $statusMessage = 'New password must be at least 6 characters long!';
                    $canUpdate = false;
                } elseif ($newPassword !== $confPassword) {
                    $profileStatus = 'error';
                    $statusMessage = 'New password and confirm password do not match!';
                    $canUpdate = false;
                } else {
                    $updatePassword = true;
                }
            }

            if ($canUpdate) {
                if ($updatePassword) {
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $updateStmt = $pdo->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
                    $updateStmt->execute([$newName, $hashedPassword, $user_id]);
                } else {
                    $updateStmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
                    $updateStmt->execute([$newName, $user_id]);
                }

                $_SESSION['username'] = $newName;
                $profileStatus = 'success';
                $statusMessage = 'Profile updated successfully!';
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_note_id'])) {
    $delete_id = $_POST['delete_note_id'];
    
    $stmt = $pdo->prepare("SELECT file_path FROM notes WHERE id = ? AND user_id = ?");
    $stmt->execute([$delete_id, $user_id]);
    $noteToDelete = $stmt->fetch();
    
    if ($noteToDelete) {
        if (file_exists($noteToDelete['file_path'])) {
            unlink($noteToDelete['file_path']);
        }
        $delStmt = $pdo->prepare("DELETE FROM notes WHERE id = ? AND user_id = ?");
        $delStmt->execute([$delete_id, $user_id]);
        
        header("Location: dashboard.php");
        exit();
    }
}

$stmt = $pdo->prepare("SELECT * FROM notes WHERE user_id = ? ORDER BY uploaded_at DESC");
$stmt->execute([$user_id]);
$notes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Xnotes</title>
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
                        <a class="nav-link px-3" href="index.php" title="Home">
                            <i class="bi bi-house-door-fill icon-btn"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3" href="contact.php" title="About / Contact">
                            <i class="bi bi-info-circle-fill icon-btn"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3" href="upload.php" title="Upload Note">
                            <i class="bi bi-cloud-arrow-up-fill icon-btn"></i>
                        </a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a class="nav-link btn px-4 btn-logout shadow-sm" href="auth/logout.php">
                            <i class="bi bi-box-arrow-right me-1"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5 mb-5 main-content">
        <h3 class="fw-bold mb-4" style="color: #333;">My Dashboard</h3>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card glass-element glass-card p-4 text-center">
                    <i class="bi bi-person-circle display-1 text-secondary mb-3"></i>
                    <h4 class="fw-bold"><?php echo htmlspecialchars($_SESSION['username']); ?></h4>
                    <p class="text-dark mb-2"><?php echo htmlspecialchars($_SESSION['email']); ?></p>
                    <span class="badge rounded-pill text-white px-3 py-2 mb-3" style="background-color: #967bb6;">BICT Undergraduate</span>
                    <button class="btn btn-sm btn-outline-dark rounded-pill fw-semibold px-3" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                        <i class="bi bi-pencil-square me-1"></i> Edit Profile
                    </button>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card glass-element glass-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold m-0">My Uploaded Notes</h5>
                        <a href="upload.php" class="btn btn-sm btn-theme px-3 shadow-sm">+ Add New</a>
                    </div>

                    <ul class="list-group list-group-flush">
                        <?php if (count($notes) > 0): ?>
                            <?php foreach ($notes as $note): 
                                $fileExt = strtolower(pathinfo($note['file_path'], PATHINFO_EXTENSION));
                                // Create secured file url...
                                $secureFileUrl = 'view-file.php?file=' . urlencode(basename($note['file_path']));
                            ?>
                                <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center py-3 px-0 border-bottom">
                                    <div>
                                        <h6 class="fw-bold mb-1 text-dark"><?php echo htmlspecialchars($note['title']); ?></h6>
                                        <small class="text-secondary fw-medium"><?php echo htmlspecialchars($note['module_code'] ?: $note['category']); ?></small><br>
                                        <small class="text-muted" style="font-size: 0.8rem;">Uploaded on: <?php echo date('Y-m-d', strtotime($note['uploaded_at'])); ?></small>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <?php if ($fileExt === 'pdf'): ?>
                                            <button type="button" 
                                                class="btn btn-sm btn-outline-dark rounded-pill px-3 shadow-sm fw-semibold"
                                                onclick="openViewer('<?php echo $secureFileUrl; ?>', '<?php echo htmlspecialchars(addslashes($note['title'])); ?>')">
                                                <i class="bi bi-eye"></i> View
                                            </button>
                                        <?php else: ?>
                                            <a href="<?php echo $secureFileUrl; ?>" download class="btn btn-sm btn-outline-dark rounded-pill px-3 shadow-sm fw-semibold">
                                                <i class="bi bi-download"></i> Get
                                            </a>
                                        <?php endif; ?>
                                        <button class="btn btn-sm btn-outline-danger rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $note['id']; ?>" title="Delete Note">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <p class="text-muted fw-medium mb-0">You haven't uploaded any notes yet.</p>
                            </div>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-element glass-card p-3 border-0">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark">Edit Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Change Name</label>
                            <input type="text" name="new_name" class="form-control rounded-pill" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" required>
                        </div>
                        <hr class="my-4">
                        <h6 class="fw-bold text-dark mb-2">Change Password (Optional)</h6>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Campus Email</label>
                            <input type="email" class="form-control rounded-pill" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" readonly style="background-color: rgba(240, 240, 240, 0.7);">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Current Password</label>
                            <input type="password" name="old_password" class="form-control rounded-pill" placeholder="Enter current password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">New Password</label>
                            <input type="password" name="new_password" class="form-control rounded-pill" placeholder="Enter new password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control rounded-pill" placeholder="Confirm new password">
                        </div>
                    </div>
                    <div class="modal-footer border-0 justify-content-center pt-0">
                        <button type="submit" name="update_profile" class="btn btn-theme rounded-pill px-4 shadow-sm">Save Changes</button>
                    </div>
                </form>
            </div>
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

    <!-- Delete Confirmation Modals -->
    <?php if (count($notes) > 0): ?>
        <?php foreach ($notes as $note): ?>
            <div class="modal fade" id="deleteModal<?php echo $note['id']; ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-sm modal-dialog-centered">
                    <div class="modal-content shadow text-center p-3">
                        <form action="" method="POST">
                            <div class="modal-body">
                                <i class="bi bi-exclamation-circle text-danger display-4"></i>
                                <h5 class="fw-bold mt-3">Are you sure?</h5>
                                <p class="text-muted">Do you really want to delete <b><?php echo htmlspecialchars($note['title']); ?></b>? This cannot be undone.</p>
                                <input type="hidden" name="delete_note_id" value="<?php echo $note['id']; ?>">
                            </div>
                            <div class="modal-footer border-0 justify-content-center flex-nowrap">
                                <button type="button" class="btn btn-light rounded-pill px-3 w-50" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger rounded-pill px-3 w-50">Delete</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

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

    <?php if ($profileStatus === 'success'): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '<?php echo $statusMessage; ?>',
                timer: 1600,
                showConfirmButton: false
            });
        </script>
    <?php elseif ($profileStatus === 'error'): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Update Failed',
                text: '<?php echo $statusMessage; ?>'
            });
        </script>
    <?php endif; ?>
</body>
</html>