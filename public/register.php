<?php
include '../config/db.php';
session_start();

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name     = mysqli_real_escape_string($conn, $_POST['name']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $role     = $_POST['role'];
    $id_type  = 'citizenship'; // Fixed — only citizenship accepted

    // Validate identity document uploads (front + back)
    $allowed    = ['image/jpeg', 'image/jpg', 'image/png'];
    $upload_dir = '../uploads/id_documents/';

    if (!isset($_FILES['id_front']) || $_FILES['id_front']['error'] !== UPLOAD_ERR_OK) {
        $error = "Please upload the front photo of your Citizenship Card.";
    } elseif (!isset($_FILES['id_back']) || $_FILES['id_back']['error'] !== UPLOAD_ERR_OK) {
        $error = "Please upload the back photo of your Citizenship Card.";
    } else {
        // Validate both files
        $files_ok = true;
        foreach (['id_front' => 'Front', 'id_back' => 'Back'] as $field => $label) {
            $f     = $_FILES[$field];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($finfo, $f['tmp_name']);
            finfo_close($finfo);
            if (!in_array($mime, $allowed)) {
                $error = "$label photo: only JPG and PNG images are accepted.";
                $files_ok = false; break;
            }
            if ($f['size'] > 5 * 1024 * 1024) {
                $error = "$label photo must be under 5MB.";
                $files_ok = false; break;
            }
        }

        if ($files_ok) {
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            $ext_front   = strtolower(pathinfo($_FILES['id_front']['name'], PATHINFO_EXTENSION));
            $front_fname = 'citizenship_front_' . uniqid() . '.' . $ext_front;

            $ext_back    = strtolower(pathinfo($_FILES['id_back']['name'], PATHINFO_EXTENSION));
            $back_fname  = 'citizenship_back_' . uniqid() . '.' . $ext_back;

            if (
                move_uploaded_file($_FILES['id_front']['tmp_name'], $upload_dir . $front_fname) &&
                move_uploaded_file($_FILES['id_back']['tmp_name'],  $upload_dir . $back_fname)
            ) {
                $sql = "INSERT INTO users (name, email, password, role, id_type, id_document_front, id_document_back, verification_status)
                        VALUES ('$name', '$email', '$password', '$role', '$id_type', '$front_fname', '$back_fname', 'pending')";

                if (mysqli_query($conn, $sql)) {
                    header("Location: login.php?registered=1");
                    exit();
                } else {
                    $error = "Registration error: " . mysqli_error($conn);
                }
            } else {
                $error = "Failed to upload one or more document photos. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Hostel Finder</title>
    <link rel="stylesheet" href="../css/reg_css.css">
    <style>
        /* Citizenship Card display block */
        .citizenship-display {
            display: flex;
            align-items: center;
            gap: 14px;
            background: white;
            border: 2px solid #667eea;
            border-radius: 12px;
            padding: 14px 18px;
            margin-bottom: 18px;
        }
        .citizenship-icon  { font-size: 36px; }
        .citizenship-title { font-weight: 700; color: #333; font-size: 15px; }
        .citizenship-sub   { font-size: 12px; color: #888; margin-top: 2px; }
    </style>
</head>
<body>
<div class="register-container">

    <!-- ── LEFT ── -->
    <div class="register-left">
        <div class="icon">🎓</div>
        <h1>Join HostelFinder</h1>
        <p>Create your account and start your journey to finding the perfect hostel accommodation.</p>
        <div class="features-list">
            <div class="feature-item">Browse verified hostels</div>
            <div class="feature-item">Compare prices &amp; amenities</div>
            <div class="feature-item">Read genuine reviews</div>
            <div class="feature-item">Book instantly online</div>
        </div>
    </div>

    <!-- ── RIGHT ── -->
    <div class="register-right">
        <div class="register-header">
            <h2>Create Account</h2>
            <p>Fill in your details to get started</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">

            <!-- Name -->
            <div class="form-group">
                <label for="name">Full Name</label>
                <div class="input-wrapper">
                    <span class="input-icon">👤</span>
                    <input type="text" id="name" name="name"
                           placeholder="Enter your full name"
                           value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                </div>
            </div>

            <!-- Email -->
            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-wrapper">
                    <span class="input-icon">📧</span>
                    <input type="email" id="email" name="email"
                           placeholder="Enter your email"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <span class="input-icon">🔒</span>
                    <input type="password" id="password" name="password"
                           placeholder="Create a strong password" required>
                </div>
            </div>

            <!-- Role -->
            <div class="form-group">
                <label>Select Your Role</label>
                <div class="role-selector">
                    <div class="role-option">
                        <input type="radio" id="student" name="role" value="student" checked>
                        <label for="student" class="role-card">
                            <div class="role-icon">🎓</div>
                            <div class="role-title">Student</div>
                            <div class="role-desc">Looking for a hostel</div>
                        </label>
                    </div>
                    <div class="role-option">
                        <input type="radio" id="owner" name="role" value="owner">
                        <label for="owner" class="role-card">
                            <div class="role-icon">🏢</div>
                            <div class="role-title">Hostel Owner</div>
                            <div class="role-desc">List your property</div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- ══════════════════════════════════════
                 IDENTITY VERIFICATION SECTION
                 ══════════════════════════════════════ -->
            <div class="verify-section">

                <div class="verify-section-title">
                     Citizenship Verification
                    <span class="verify-badge">REQUIRED</span>
                </div>              

                <!-- Front + Back upload -->
                <div class="form-group" style="margin-bottom:0;">
                    <br>

                    <div class="upload-grid">

                        <!-- ── FRONT ── -->
                        <div>
                            <div class="upload-slot-label">
                                 Front Side <span class="side-badge">FRONT</span>
                            </div>

                            <div class="upload-zone" id="frontZone"
                                 ondragover="handleDragOver(event,'front')"
                                 ondragleave="handleDragLeave('front')"
                                 ondrop="handleDrop(event,'front')">
                                <input type="file" name="id_front" id="id_front"
                                       accept="image/jpeg,image/jpg,image/png"
                                       onchange="handleFileSelect(event,'front')">
                                <div class="upload-placeholder" id="frontPlaceholder">
                                    <div class="upload-icon">🪪</div>
                                    <div class="upload-title">Front photo</div>
                                    <div class="upload-hint">Click or drag<br><strong>JPG / PNG</strong> · max 5 MB</div>
                                </div>
                            </div>

                            <div class="preview-wrap" id="frontPreviewWrap">
                                <img id="frontPreviewImg" src="" alt="Front preview">
                                <div class="preview-overlay">
                                    <span class="preview-filename" id="frontFilename"></span>
                                    <button type="button" class="preview-remove"
                                            onclick="removeFile('front')" title="Remove">✕</button>
                                </div>
                            </div>
                            <div class="slot-ok" id="frontOk">✅ Front photo ready</div>
                        </div>

                        <!-- ── BACK ── -->
                        <div>
                            <div class="upload-slot-label">
                                 Back Side <span class="side-badge back">BACK</span>
                            </div>

                            <div class="upload-zone" id="backZone"
                                 ondragover="handleDragOver(event,'back')"
                                 ondragleave="handleDragLeave('back')"
                                 ondrop="handleDrop(event,'back')">
                                <input type="file" name="id_back" id="id_back"
                                       accept="image/jpeg,image/jpg,image/png"
                                       onchange="handleFileSelect(event,'back')">
                                <div class="upload-placeholder" id="backPlaceholder">
                                    <div class="upload-icon">🔄</div>
                                    <div class="upload-title">Back photo</div>
                                    <div class="upload-hint">Click or drag<br><strong>JPG / PNG</strong> · max 5 MB</div>
                                </div>
                            </div>

                            <div class="preview-wrap" id="backPreviewWrap">
                                <img id="backPreviewImg" src="" alt="Back preview">
                                <div class="preview-overlay">
                                    <span class="preview-filename" id="backFilename"></span>
                                    <button type="button" class="preview-remove"
                                            onclick="removeFile('back')" title="Remove">✕</button>
                                </div>
                            </div>
                            <div class="slot-ok" id="backOk">✅ Back photo ready</div>
                        </div>

                    </div><!-- /.upload-grid -->

                    <!-- Shown only when BOTH photos are uploaded -->
                    <div class="upload-success-bar" id="bothReadyBar">
                        ✅ Both photos uploaded — pending admin review after registration
                    </div>
                </div>

            </div>
           

            <br>
            <button type="submit" class="btn-register"> Create Account</button>
        </form>

        <div class="divider"><span>OR</span></div>

        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>

        <div class="back-home">
            <a href="dashboard.php">← Back to Home</a>
        </div>
    </div>
</div>

<script>
    const state = { front: false, back: false };

    function handleFileSelect(e, side) {
        const file = e.target.files[0];
        if (file && validateFile(file)) showPreview(file, side);
    }

    function handleDragOver(e, side) {
        e.preventDefault();
        document.getElementById(side + 'Zone').classList.add('drag-over');
    }
    function handleDragLeave(side) {
        document.getElementById(side + 'Zone').classList.remove('drag-over');
    }
    function handleDrop(e, side) {
        e.preventDefault();
        document.getElementById(side + 'Zone').classList.remove('drag-over');
        const file = e.dataTransfer.files[0];
        if (!file || !validateFile(file)) return;
        const dt = new DataTransfer();
        dt.items.add(file);
        document.getElementById('id_' + side).files = dt.files;
        showPreview(file, side);
    }

    function validateFile(file) {
        if (!['image/jpeg','image/jpg','image/png'].includes(file.type)) {
            alert('Only JPG and PNG images are accepted.'); return false;
        }
        if (file.size > 5 * 1024 * 1024) {
            alert('File must be under 5 MB.'); return false;
        }
        return true;
    }

    function showPreview(file, side) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(side + 'PreviewImg').src             = e.target.result;
            document.getElementById(side + 'Filename').textContent       = file.name;
            document.getElementById(side + 'PreviewWrap').classList.add('active');
            document.getElementById(side + 'Ok').classList.add('active');
            document.getElementById(side + 'Placeholder').style.display  = 'none';
            state[side] = true;
            checkBothReady();
        };
        reader.readAsDataURL(file);
    }

    function removeFile(side) {
        document.getElementById('id_' + side).value                          = '';
        document.getElementById(side + 'PreviewImg').src                      = '';
        document.getElementById(side + 'PreviewWrap').classList.remove('active');
        document.getElementById(side + 'Ok').classList.remove('active');
        document.getElementById(side + 'Placeholder').style.display           = '';
        state[side] = false;
        checkBothReady();
    }

    function checkBothReady() {
        const bar = document.getElementById('bothReadyBar');
        if (state.front && state.back) bar.classList.add('active');
        else                            bar.classList.remove('active');
    }
</script>
</body>
</html>