<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$successScript = '';
$errorMsg = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id     = $_SESSION['user_id'];
    $module_code = trim($_POST['module_code'] ?? '');
    $title       = trim($_POST['title'] ?? '');
    
    if (isset($_FILES['note_file']) && $_FILES['note_file']['error'] == 0) {
        $file        = $_FILES['note_file'];
        $fileName    = $file['name'];
        $fileTmp     = $file['tmp_name'];
        $fileSize    = $file['size'];
        $fileExt     = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExts = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'zip'];
        
        $allowedMimeTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/zip',
            'application/x-zip-compressed'
        ];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $fileTmp);
        finfo_close($finfo);
        
        $maxFileSize = 20 * 1024 * 1024; // 20 MB

        if ($fileSize > $maxFileSize) {
            $errorMsg = "File is too large! Maximum allowed size is 20MB.";
        } elseif (in_array($fileExt, $allowedExts) && in_array($mimeType, $allowedMimeTypes)) {
            $cleanFileName = preg_replace('/[^a-zA-Z0-9.\-_]/', '_', basename($fileName));
            $newFileName = time() . '_' . $cleanFileName;
            $uploadDir   = 'uploads/';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $destination = $uploadDir . $newFileName;
            
            if (move_uploaded_file($fileTmp, $destination)) {
                $sql = "INSERT INTO notes (user_id, module_code, title, category, file_path, uploaded_at) VALUES (?, ?, ?, ?, ?, NOW())";
                $stmt = $pdo->prepare($sql);
                
                if ($stmt->execute([$user_id, $module_code, $title, $module_code, $destination])) {
                    $successScript = "
                    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                icon: 'success',
                                title: 'Note Uploaded Successfully!',
                                text: 'Redirecting to your dashboard...',
                                showConfirmButton: false,
                                timer: 1600,
                                timerProgressBar: true
                            }).then(() => {
                                window.location.href = 'dashboard.php';
                            });
                        });
                    </script>";
                } else {
                    $errorMsg = "Database error. Failed to save note info.";
                }
            } else {
                $errorMsg = "Failed to move uploaded file. Check 'uploads' folder permissions.";
            }
        } else {
            $errorMsg = "Invalid file! Only genuine PDF, DOC, PPT, and ZIP documents are allowed.";
        }
    } else {
        $errorMsg = "Please select a valid file to upload.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Note - Xnotes</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=1.2">

    <style>
        .custom-file-box {
            background-color: rgba(255, 255, 255, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 50px;
            padding: 0.65rem 1.25rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s ease;
        }
        .custom-file-box:hover {
            background-color: #ffffff;
            box-shadow: 0 0 10px rgba(150, 123, 182, 0.35);
        }
        .upload-icon-btn {
            font-size: 1.4rem;
            color: #967bb6;
            display: flex;
            align-items: center;
        }
        .file-name-text {
            color: #6c757d;
            font-size: 0.95rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg glass-element shadow-sm py-2">
        <div class="container-fluid px-4">
            <a class="navbar-brand p-0 m-0" href="index.php">
                <img src="images/logonew1.png" alt="Xnotes Logo" height="80">
            </a>
            <div class="d-flex ms-auto align-items-center">
                <a class="nav-link icon-btn px-2" href="dashboard.php" title="Back to Dashboard">
                    <i class="bi bi-arrow-left-circle-fill"></i>
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-5 mb-5 flex-grow-1 d-flex justify-content-center align-items-center">
        <div class="card glass-element glass-card p-4 p-md-5" style="width: 100%; max-width: 580px;">
            <h3 class="fw-bold text-center mb-4" style="color: #333;">Upload a New Note</h3>
            
            <?php if(!empty($errorMsg)): ?>
                <div class="alert alert-danger text-center fw-bold"><?php echo htmlspecialchars($errorMsg); ?></div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Note Title</label>
                    <input type="text" class="form-control form-control-lg rounded-pill" name="title" placeholder="E.g. Web Tech Chapter 1" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Module Code & Name</label>
                    <select class="form-select form-select-lg rounded-pill" id="moduleSelect" name="module_code" required>
                        <option value="" disabled selected>Select a module...</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Select File (PDF, DOC, PPT, ZIP)</label>
                    <label class="custom-file-box shadow-sm" for="noteFileInput">
                        <span class="upload-icon-btn">
                            <i class="bi bi-cloud-arrow-up-fill"></i>
                        </span>
                        <span class="file-name-text" id="fileNameDisplay">Choose a file to upload...</span>
                    </label>
                    <input type="file" id="noteFileInput" name="note_file" class="d-none" accept=".pdf,.doc,.docx,.ppt,.pptx,.zip" required>
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-theme btn-lg py-2 shadow-sm">
                        <i class="bi bi-upload me-2"></i> Upload Note
                    </button>
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

    <?php echo $successScript; ?>

    <script>
        const modules = [
            [1, "ICT 1202", "Electronic Circuits"], [1, "ICT 1305", "Program Designing and Programming"], [1, "ICT 1111", "Productivity and Collaborative Tools"], [1, "CMT 1301", "Fundamentals of Physics for Technology"], [1, "CMT 1303", "Fundamentals of Mathematics for Technology"], [1, "CML 1301", "Personality Development"], [1, "CMT 1005", "Communication Skills I"], [1, "ICT 1210", "Introduction to Multimedia"], [1, "ICT 1108", "Skill Development Project I"], [1, "ICT 1209", "Web Technologies"], [1, "ICT 1207", "Human Computer Interaction"], [1, "CML 1203", "Principles of Management"], [1, "CML 1204", "Health and Wellbeing"], [1, "CMT 1009", "Communication Skills II"], [1, "CMT 1307", "Mathematics For Technology I"], [1, "ENT 1302", "Fundamentals of Electricity and Magnetism"],
            [2, "ICT 2202", "Operating Systems"], [2, "ICT 2303", "Data Structures and Algorithms"], [2, "ICT 2304", "Object Oriented Programming"], [2, "ICT 2207", "Software System Design"], [2, "ICT 2212", "Skill Development Project II"], [2, "CML 2202", "Engineering Economics"], [2, "CMT 2002", "Communication Skills III"], [2, "EET 2207", "Mathematics for Technology II"], [2, "ICT 2305", "Computational Mathematics"], [2, "ICT 2214", "Introduction to Information Systems"], [2, "ICT 2211", "Fundamentals of Statistics"], [2, "ICT 2213", "Data Communication and Networking"], [2, "ICT 2308", "Database Systems"], [2, "ICT 2109", "Communication and Learning Skills"], [2, "CML 2204", "Foreign Language"], [2, "CML 2205", "Ethics for Science and Technology"],
            [3, "ICT 3201", "Software Project Management"], [3, "ICT 3203", "Scientific Computer Applications"], [3, "CML 3101", "Legal and Patent Aspects"], [3, "ICT 3312", "Software Verification and Validation"], [3, "ICT 3206", "Skills Development Project III"], [3, "ICT 3314", "Advanced Computer Networks"], [3, "ICT 3208", "Design and Analysis of Algorithms"], [3, "ICT 3307", "Computational Statistics"], [3, "ICT 3217", "Advance Computer Networks"], [3, "ICT 3209", "Computer Organization and Architecture"], [3, "ICT 3310", "Information Security"], [3, "ICT 3311", "Robotics"], [3, "ICT 3315", "Internet of Things"], [3, "ICT 3213", "Advanced SW System Design"], [3, "ICT 3216", "Research Methodology"], [3, "ICT 3204", "E-Business Systems"], [3, "CML 3203", "Basics of Accountancy"],
            [4, "ICT 4301", "Mobile Computing"], [4, "ICT 4202", "Internet Applications"], [4, "ICT 4203", "Software Engineering"], [4, "ICT 4205", "Current Topics in Information Technology"], [4, "ICT 4306", "Data Science"], [4, "ICT 4207", "Artificial Intelligence"], [4, "ICT 4210", "Digital Image Processing"], [4, "ICT 4211", "Computer Graphics and Visualization"], [4, "CML 4201", "Entrepreneurship"], [4, "CML 4202", "Human Resource Management"]
        ];

        const moduleSelect = document.getElementById('moduleSelect');
        modules.forEach(mod => {
            const opt = document.createElement('option');
            opt.value = mod[1];
            opt.textContent = `${mod[1]} - ${mod[2]}`;
            moduleSelect.appendChild(opt);
        });

        document.getElementById('noteFileInput').addEventListener('change', function() {
            const fileName = this.files[0] ? this.files[0].name : "Choose a file to upload...";
            const display = document.getElementById('fileNameDisplay');
            display.innerText = fileName;
            display.style.color = '#333';
            display.style.fontWeight = '500';
        });
    </script>
</body>
</html>