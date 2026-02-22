<?php
session_start();
require_once('../../config/db.php');

// Only logged-in students allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$student_name = $_SESSION['name'] ?? "Student";
$user_id = $_SESSION['user_id'];

// Fetch all hostels
$sql = "SELECT hostel_id, name, location, price, description FROM hostels ORDER BY created_at DESC";
$res = mysqli_query($conn, $sql);
$hostels = mysqli_fetch_all($res, MYSQLI_ASSOC);

// ✅ Get user's active reservations (pending or approved)
$active_res = mysqli_query($conn, "
    SELECT hostel_id FROM reservations 
    WHERE user_id = $user_id 
    AND status IN ('pending', 'approved')
");
$active_hostels = [];
while ($row = mysqli_fetch_assoc($active_res)) {
    $active_hostels[] = $row['hostel_id'];
}
?>

<!DOCTYPE html>
<html>
<head>
<title>View Hostels</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="../css/dashboard.css">
<link rel="stylesheet" href="css/view_hostel.css">
</head>

<body>

<div class="hostel-container">

    <!-- HEADER -->
    <div class="hostel-header">
        <div>
            <h1>Explore Hostels</h1>
            <p class="subtitle">Find your perfect stay</p>
        </div>
        <div class="welcome">
            Welcome, <?= htmlspecialchars($student_name) ?>
        </div>
    </div>

    <!-- GRID -->
    <div class="hostel-grid">

        <?php if (empty($hostels)) { ?>
            <p>No hostels available.</p>
        <?php } ?>

        <?php foreach ($hostels as $h): ?>

        <?php
            $imgRes = mysqli_query($conn,
                "SELECT image_path FROM hostel_images WHERE hostel_id = {$h['hostel_id']} LIMIT 1"
            );
            $img = mysqli_fetch_assoc($imgRes);
            $thumb = $img ? "../../uploads/" . $img['image_path'] : null;
            
            // ✅ Check if user already has active reservation for this hostel
            $has_active = in_array($h['hostel_id'], $active_hostels);
        ?>

        <div class="hostel-card">

            <div class="image-wrapper">
                <?php if ($thumb && file_exists($thumb)) { ?>
                    <img src="<?= $thumb ?>" alt="Hostel Image">
                <?php } else { ?>
                    <img src="https://via.placeholder.com/600x400?text=No+Image">
                <?php } ?>
                
                
                <!-- ✅ Show badge if already reserved -->
                <?php if ($has_active): ?>
                    <div style="position: absolute; top: 10px; right: 10px; background: #ffc107; color: #333; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                        ✓ Already Reserved
                    </div>
                <?php endif; ?>
            </div>

            <div class="card-content">
                <h3><?= htmlspecialchars($h['name']) ?></h3>
                <p class="pricetag">Rs <?= number_format($h['price']) ?>/month</p>
                <p class="location">📍 <?= htmlspecialchars($h['location']) ?></p>

                <p class="description">
                    <?= nl2br(substr(htmlspecialchars($h['description']), 0, 100)) ?>...
                </p>
            
                <a class="btn-primary"
                   href="view_detail.php?hostel_id=<?= $h['hostel_id'] ?>">
                    View Details
                </a>
            </div>

        </div>

        <?php endforeach; ?>

    </div>

    <div class="back-section">
        <a class="btn-secondary" href="dashboard.php">Back to Dashboard</a>
    </div>

</div>

</body>
</html>