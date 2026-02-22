<?php
session_start();
require_once '../../config/db.php'; // adjust path if needed

// ensure user is logged in as owner
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'owner') {
    die("Unauthorized. Please login as owner.");
}

$owner_id = (int) $_SESSION['user_id'];
$errors = [];
$success = "";

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // collect and sanitize inputs (use mysqli_real_escape_string to avoid SQL problems)
    $name        = mysqli_real_escape_string($conn, trim($_POST['name'] ?? ''));
    $location    = mysqli_real_escape_string($conn, trim($_POST['location'] ?? ''));
    $price       = mysqli_real_escape_string($conn, trim($_POST['price'] ?? '0'));
    $rooms       = mysqli_real_escape_string($conn, trim($_POST['rooms'] ?? '0'));
    $room_type   = mysqli_real_escape_string($conn, trim($_POST['room_type'] ?? ''));
    $description = mysqli_real_escape_string($conn, trim($_POST['description'] ?? ''));
    $facilities  = mysqli_real_escape_string($conn, trim($_POST['facilities'] ?? ''));
    $status      = 'active';

    // basic validation
    if ($name === '') $errors[] = "Hostel name is required.";
    if ($location === '') $errors[] = "Location is required.";
    if (!is_numeric($price) || $price < 0) $errors[] = "Price should be a valid non-negative number.";
    if (!is_numeric($rooms) || $rooms < 0) $errors[] = "Rooms should be a valid non-negative number.";

    // ensure images array exists and has at least one file
    $hasFiles = isset($_FILES['images']) && !empty($_FILES['images']['name'][0]);
    if (!$hasFiles) {
        $errors[] = "Please upload at least one image.";
    }

    // if validation passed, insert hostel then upload images
    if (empty($errors)) {

        // insert into hostels table
        $insert_sql = "INSERT INTO hostels 
            (owner_id, name, location, price, rooms, room_type, description, facilities, status)
            VALUES ('$owner_id', '$name', '$location', '$price', '$rooms', '$room_type', '$description', '$facilities', '$status')";

        if (!mysqli_query($conn, $insert_sql)) {
            $errors[] = "DB error (hostel insert): " . mysqli_error($conn);
        } else {

            $hostel_id = mysqli_insert_id($conn);

            // prepare upload folder (absolute path)
            $upload_dir = __DIR__ . '/../../uploads/'; // resolves to project_root/uploads
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // allowed extensions / mime types
            $allowed_ext = ['jpg','jpeg','png','gif','webp'];
            $max_file_size = 4 * 1024 * 1024; // 4 MB

            // loop files
            $file_count = count($_FILES['images']['name']);
            for ($i = 0; $i < $file_count; $i++) {
                $orig_name = $_FILES['images']['name'][$i] ?? '';
                $tmp_name  = $_FILES['images']['tmp_name'][$i] ?? '';
                $error     = $_FILES['images']['error'][$i] ?? 4;
                $size      = $_FILES['images']['size'][$i] ?? 0;

                if ($orig_name === '' || $error !== UPLOAD_ERR_OK) {
                    // skip this file but continue
                    continue;
                }

                // sanitize filename and get extension
                $orig_name = basename($orig_name);
                $ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));

                if (!in_array($ext, $allowed_ext)) {
                    // skip invalid type
                    continue;
                }

                if ($size > $max_file_size) {
                    // skip too large file
                    continue;
                }

                // build unique filename
                $safe_base = preg_replace('/[^A-Za-z0-9_\-]/', '_', pathinfo($orig_name, PATHINFO_FILENAME));
                $final_name = time() . "_" . $i . "_" . $safe_base . "." . $ext;
                $destination = $upload_dir . $final_name;

                if (move_uploaded_file($tmp_name, $destination)) {

                    // insert into hostel_images table (store filename only)
                    $img_sql = "INSERT INTO hostel_images (hostel_id, image_path) VALUES ('$hostel_id', '$final_name')";
                    mysqli_query($conn, $img_sql);
                }
            }

            $success = "Hostel added successfully.";
        }
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Add Hostel</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <style>
        /* dashboard.css - Add Hostel page styling */

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f5f7fa;
    margin: 0;
    padding: 0;
    color: #333;
}

h2 {
    text-align: center;
    margin: 30px 0 10px 0;
    color: #0462a1;
}

form {
    max-width: 600px;
    margin: 0 auto 50px auto;
    padding: 30px;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #333;
}

input[type="text"],
input[type="number"],
textarea,
select {
    width: 100%;
    padding: 10px 12px;
    margin-bottom: 18px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.3s;
}

input[type="text"]:focus,
input[type="number"]:focus,
textarea:focus,
select:focus {
    outline: none;
    border-color: #0462a1;
}

textarea {
    min-height: 100px;
    resize: vertical;
}

button {
    display: inline-block;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff;
    padding: 12px 25px;
    font-size: 16px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

button:hover {
    background: linear-gradient(135deg, #764ba2, #667eea);
    transform: translateY(-2px);
}

a {
    color: #0462a1;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}

div[style*="color:darkred"] {
    max-width: 600px;
    margin: 20px auto;
    padding: 15px 20px;
    background: #ffe6e6;
    border-left: 5px solid #e74c3c;
    border-radius: 8px;
}

div[style*="color:green"] {
    max-width: 600px;
    margin: 20px auto;
    padding: 15px 20px;
    background: #e6ffea;
    border-left: 5px solid #2ecc71;
    border-radius: 8px;
}

input[type="file"] {
    padding: 6px;
    border-radius: 6px;
    border: 1px solid #ccc;
    cursor: pointer;
}

@media (max-width: 640px) {
    form {
        padding: 20px;
    }

    button {
        width: 100%;
    }
}

    </style>
</head>
<body>
    <h2>Add Hostel</h2>

    <?php if (!empty($errors)): ?>
        <div style="color:darkred;">
            <ul>
                <?php foreach ($errors as $e) echo "<li>" . htmlspecialchars($e) . "</li>"; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div style="color:green;"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <label>Hostel Name</label><br>
        <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required><br>

        <label>Location</label><br>
        <input type="text" name="location" value="<?= htmlspecialchars($_POST['location'] ?? '') ?>" required><br>

        <label>Price</label><br>
        <input type="number" name="price" value="<?= htmlspecialchars($_POST['price'] ?? '') ?>" required><br>

        <label>Rooms</label><br>
        <input type="number" name="rooms" value="<?= htmlspecialchars($_POST['rooms'] ?? '') ?>" required><br>

        <label>Room Type</label><br>
        <select name="room_type">
            <option value="">--Select--</option>
            <option value="Single" <?= (isset($_POST['room_type']) && $_POST['room_type']=='Single')? 'selected':'' ?>>Single</option>
            <option value="Double" <?= (isset($_POST['room_type']) && $_POST['room_type']=='Double')? 'selected':'' ?>>Double</option>
            <option value="Dorm" <?= (isset($_POST['room_type']) && $_POST['room_type']=='Dorm')? 'selected':'' ?>>Dorm</option>
        </select><br>

        <label>Description</label><br>
        <textarea name="description"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea><br>

        <label>Facilities (comma separated)</label><br>
        <input type="text" name="facilities" value="<?= htmlspecialchars($_POST['facilities'] ?? '') ?>"><br>

        <label>Images (you can select multiple)</label><br>
        <input type="file" name="images[]" multiple accept="image/*" required><br><br>

        <button type="submit">Add Hostel</button>
        <a class="btn" href="dashboard.php">Back to Dashboard</a>
    </form>

    
</body>
</html>
