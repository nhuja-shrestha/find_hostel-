<?php
session_start();
require_once('../../config/db.php');

if (!isset($_GET['hostel_id'])) {
    die("Hostel ID missing!");
}

$hostel_id = intval($_GET['hostel_id']);

// Fetch hostel details
$hostel_sql = "SELECT * FROM hostels WHERE hostel_id = $hostel_id";
$hostel_result = mysqli_query($conn, $hostel_sql);
$hostel = mysqli_fetch_assoc($hostel_result);

if (!$hostel) {
    die("Hostel not found!");
}

// Fetch hostel images
$img_sql = "SELECT image_path FROM hostel_images WHERE hostel_id = $hostel_id";
$img_result = mysqli_query($conn, $img_sql);

$images = [];
while ($row = mysqli_fetch_assoc($img_result)) {
    $images[] = $row['image_path'];
}

$error = "";

/* ===============================
   HANDLE FORM SUBMISSION
================================ */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit;
    }

    $student_id = $_SESSION['user_id'];

    /* ===== RESERVE ROOM ===== */
    if (isset($_POST['reserve_room'])) {

        if ($hostel['rooms'] <= 0) {
            $error = "No rooms available!";
        } else {

            // ✅ CHECK FOR ACTIVE RESERVATIONS (pending or approved)
            $check = $conn->query("
                SELECT id FROM reservations 
                WHERE user_id = $student_id 
                AND hostel_id = $hostel_id 
                AND status IN ('pending', 'approved')
            ");

            if ($check->num_rows == 0) {

                $insert = "INSERT INTO reservations (user_id, hostel_id, status)
                           VALUES ($student_id, $hostel_id, 'pending')";

                if ($conn->query($insert)) {
                    header("Location: view_detail.php?hostel_id=$hostel_id&reserved=1");
                    exit;
                }
            } else {
                $error = "You already have a pending or approved reservation for this hostel. You can only have one active reservation per hostel.";
            }
        }
    }

    /* ===== SEND INQUIRY ===== */
    elseif (!empty($_POST['message'])) {

        $message = mysqli_real_escape_string($conn, $_POST['message']);

        $insert = "INSERT INTO inquiries (user_id, hostel_id, message, status)
                   VALUES ($student_id, $hostel_id, '$message', 'pending')";

        if (mysqli_query($conn, $insert)) {
            header("Location: view_detail.php?hostel_id=$hostel_id&sent=1");
            exit;
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}

// ✅ CHECK FOR ACTIVE RESERVATIONS (for UI display)
$has_active_reservation = false;
if (isset($_SESSION['user_id'])) {
    $student_id = $_SESSION['user_id'];
    $check = mysqli_query($conn, "
        SELECT id FROM reservations 
        WHERE user_id = $student_id 
        AND hostel_id = $hostel_id 
        AND status IN ('pending', 'approved')
    ");
    $has_active_reservation = mysqli_num_rows($check) > 0;
}

$reserved = isset($_GET['reserved']);
$success = isset($_GET['sent']) ? "Inquiry sent successfully!" : "";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($hostel['name']); ?> - Details</title>
<link rel="stylesheet" href="../css/dashboard.css">
<style>
/* ===== BODY ===== */
body {
    background: #f5f7fb;
    font-family: 'Segoe UI', sans-serif;
    margin: 0;
}

.container {
    max-width: 1200px;
    margin: auto;
    padding: 30px;
}

/* ===== CARD ===== */
.card {
    background: white;
    padding: 25px;
    border-radius: 16px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
}

/* ===== IMAGE GALLERY ===== */
.gallery {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 15px;
    margin-bottom: 30px;
}

.gallery img {
    width: 100%;
    height: 250px;
    object-fit: cover;
    border-radius: 16px;
    transition: 0.3s;
}

.gallery img:hover {
    transform: scale(1.05);
}

/* ===== CARD CONTENT LAYOUT ===== */
.card-content {
    display: flex;
    flex-direction: column;
    gap: 15px;
    text-align: left;
}

/* ===== TITLES ===== */
.section-title {
    font-weight: 600;
    font-size: 16px;
    margin-top: 20px;
    margin-bottom: 8px;
    text-align: left !important;
}

/* ===== PRICE BOX ===== */
.price-box {
    font-size: 28px;
    font-weight: bold;
    margin-bottom: 10px;
    text-align: left !important;
}

.price-box span {
    font-size: 14px;
    color: #6b7280;
}

/* ===== FACILITIES BADGES ===== */
.facilities, .badge {
    display: inline-block;
    margin-right: 8px;
    margin-bottom: 8px;
}

.badge {
    background: #eef2ff;
    padding: 8px 14px;
    border-radius: 50px;
    font-size: 14px;
    text-align: left !important;
}

/* ===== DESCRIPTION ===== */
.description-text {
    text-align: left !important;
    line-height: 1.6;
    color: #4b5563;
}

/* ===== INQUIRY SECTION ===== */
.inquiry-section {
    margin-top: 30px;
    text-align: left !important;
}

.inquiry-section h3 {
    margin-bottom: 12px;
    text-align: left !important;
}

.inquiry-section textarea {
    width: 100%;
    height: 100px;
    padding: 10px;
    border-radius: 10px;
    border: 1px solid #ddd;
    margin-bottom: 15px;
    resize: vertical;
}

.btn, .btn-primary {
    display: inline-block;
    padding: 12px 18px;
    border-radius: 10px;
    border: none;
    cursor: pointer;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    font-weight: 500;
    text-decoration: none;
    transition: 0.3s;
}

.btn:hover, .btn-primary:hover {
    opacity: 0.9;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn.secondary {
    background: white;
    border: 1px solid #ccc;
    color: #333;
    margin-left: 10px;
}

/* ===== ALERTS ===== */
.alert-success {
    background: #d1fae5;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 15px;
    border-left: 4px solid #10b981;
}

.alert-error {
    background: #fee2e2;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 15px;
    border-left: 4px solid #ef4444;
}

.alert-warning {
    background: #fef3c7;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 15px;
    border-left: 4px solid #f59e0b;
}

/* ===== RESPONSIVE ===== */
@media(max-width: 900px) {
    .gallery {
        grid-template-columns: 1fr;
    }
}

</style>
</head>
<body>
<div class="container">
    <div class="card">
        <!-- IMAGE GALLERY -->
        <div class="gallery">
            <?php if (!empty($images)): ?>
                <?php foreach ($images as $img): ?>
                    <img src="../../uploads/<?= htmlspecialchars($img); ?>" alt="Hostel Image">
                <?php endforeach; ?>
            <?php else: ?>
                <img src="https://via.placeholder.com/800x400?text=No+Image" alt="No Image">
            <?php endif; ?>
        </div>

        <!-- CONTENT -->
        <div class="card-content">
            <h2><?= htmlspecialchars($hostel['name']); ?></h2>
            <p>📍 <?= htmlspecialchars($hostel['location']); ?></p>

            <div class="price-box">Rs <span><?= number_format($hostel['price']); ?></span> / month</div>

            <div class="section-title">Available Rooms</div>
            <p>
                <?php
                $rooms = intval($hostel['rooms']);
                echo $rooms > 0
                    ? $rooms . ' room' . ($rooms > 1 ? 's' : '') . ' · ' . htmlspecialchars($hostel['room_type'])
                    : 'Contact for availability';
                ?>
            </p>

            <?php if (!empty($hostel['facilities'])): ?>
                <div class="section-title">Facilities</div>
                <?php
                $facility_icons = [
                    'wifi'       => '📶',
                    'meals'      => '🍽️',
                    'ac'         => '❄️',
                    'hot water'  => '🚿',
                    'gym'        => '💪',
                    'laundry'    => '🧺',
                    'parking'    => '🅿️',
                    'security'   => '🔒',
                    'study room' => '📚',
                    'garden'     => '🌳',
                ];
                foreach (explode(',', $hostel['facilities']) as $f):
                    $f_lower = strtolower(trim($f));
                    $icon = '';
                    foreach ($facility_icons as $key => $val) {
                        if (strpos($f_lower, $key) !== false) { $icon = $val . ' '; break; }
                    }
                    echo "<span class='badge'>" . $icon . htmlspecialchars(trim($f)) . "</span>";
                endforeach;
                ?>
            <?php endif; ?>

            <?php if (!empty($hostel['description'])): ?>
                <div class="section-title">Description</div>
                <p class="description-text"><?= nl2br(htmlspecialchars($hostel['description'])); ?></p>
            <?php endif; ?>

            <div class="inquiry-section">
                <h3>Reservation & Inquiry</h3>
                
                <?php if (!empty($success)): ?>
                    <div class="alert-success">✅ <?= htmlspecialchars($success); ?></div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="alert-error">⚠️ <?= htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($reserved): ?>
                    <div class="alert-success">✅ Reservation submitted! Check your reservations for status updates.</div>
                <?php endif; ?>

                <!-- ✅ RESERVATION WARNING IF ALREADY RESERVED -->
                <?php if ($has_active_reservation): ?>
                    <div class="alert-warning">
                        <strong>⚠️ Already Reserved</strong><br>
                        You already have a pending or approved reservation for this hostel. You can only have one active reservation per hostel at a time.
                    </div>
                <?php endif; ?>
                    
                <!-- Inquiry Form -->
                <form method="POST">
                    <div style="margin-bottom: 15px;">
                        <label for="inquiry_msg" style="display: block; margin-bottom: 8px; font-weight: 600;">Send Inquiry</label>
                        <textarea id="inquiry_msg" name="message" required placeholder="Write your inquiry here..."></textarea>
                        <button type="submit" class="btn">Send Inquiry</button>
                    </div>
                </form>

                <!-- Reservation Form -->
                <form method="POST">
                    <button type="submit" name="reserve_room" class="btn" <?php if ($has_active_reservation) echo 'disabled'; ?>>
                        <?php if ($has_active_reservation) echo '✓ Already Reserved'; else echo 'Reserve Room'; ?>
                    </button>
                </form>
                
                <br><a href="view_hostels.php" class="btn secondary">Back to Hostels</a>
            </div>
        </div>
    </div>

</div>
</body>
</html>