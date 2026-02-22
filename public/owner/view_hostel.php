<?php
session_start();
require_once('../../config/db.php');

// OWNER AUTH
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'owner') {
    header("Location: ../login.php");
    exit;
}

// Accept both ?id= and ?hostel_id=
if (isset($_GET['id'])) {
    $hostel_id = (int)$_GET['id'];
} elseif (isset($_GET['hostel_id'])) {
    $hostel_id = (int)$_GET['hostel_id'];
} else {
    die("Hostel ID missing!");
}

$owner_id = (int) $_SESSION['user_id'];

// Fetch hostel
$sql = "SELECT * FROM hostels WHERE hostel_id = $hostel_id AND owner_id = $owner_id LIMIT 1";
$res = mysqli_query($conn, $sql);

if (mysqli_num_rows($res) == 0) {
    die("Hostel not found or unauthorized!");
}

$hostel = mysqli_fetch_assoc($res);

// Fetch images
$img_sql = "SELECT image_path FROM hostel_images WHERE hostel_id = $hostel_id";
$img_res = mysqli_query($conn, $img_sql);
$images = mysqli_fetch_all($img_res, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Hostel - <?= htmlspecialchars($hostel['name']) ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="css/view_hostel.css">
</head>
<body>

<div class="detail-container">

    <!-- HEADER -->
    <div class="detail-header">
        <h1><?= htmlspecialchars($hostel['name']) ?></h1>
        <p class="location">📍 <?= htmlspecialchars($hostel['location']) ?></p>
    </div>

    <!-- IMAGE GALLERY -->
    <div class="gallery-grid">
        <?php if (!empty($images)) {
            foreach ($images as $img) {
                $imgPath = "../../uploads/" . $img['image_path']; ?>
                <div class="gallery-item">
                    <img src="<?= $imgPath ?>" alt="Hostel Image">
                </div>
        <?php } } else { ?>
            <p>No images available.</p>
        <?php } ?>
    </div>

    <!-- DETAIL LAYOUT -->
    <div class="detail-layout">

        <!-- LEFT INFO SECTION -->
        <div class="info-card">
            <div class="section-title">💰 Price</div>
            <p class="price">Rs <?= number_format($hostel['price']) ?> <span>/ month</span></p>

            <div class="section-title">🛏 Rooms</div>
            <p><?= $hostel['rooms'] ?> rooms (<?= htmlspecialchars($hostel['room_type']) ?>)</p>

            <div class="section-title">🏷 Facilities</div>
            <div class="facilities">
                <?php foreach (explode(",", $hostel['facilities']) as $f): ?>
                    <span class="badge"><?= htmlspecialchars(trim($f)) ?></span>
                <?php endforeach; ?>
            </div>

            <div class="section-title">📄 Description</div>
            <p><?= nl2br(htmlspecialchars($hostel['description'])) ?></p>

            <div class="section-title">📌 Status</div>
            <p><?= htmlspecialchars($hostel['status']) ?></p>
        </div>

        <!-- RIGHT ACTIONS & PRICE CARD -->
        <div class="price-card">
            <a href="edit_hostel.php?id=<?= $hostel_id ?>" class="btn-primary">Edit Hostel</a>
            <a href="delete_hostel.php?hostel_id=<?= $hostel_id ?>" class="btn-primary" 
               onclick="return confirm('Delete this hostel permanently?');">Delete Hostel</a>
            <a href="manage_hostels.php" class="btn-secondary">Back to List</a>
        </div>

    </div>
</div>

</body>
</html>
