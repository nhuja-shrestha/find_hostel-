<?php
session_start();
require_once('../../config/db.php');

if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit; }
if ($_SESSION['role'] !== 'student') { header("Location: ../login.php"); exit; }

$student_id   = (int)$_SESSION['user_id'];
$student_name = $_SESSION['name'] ?? 'Student';

$res           = mysqli_query($conn, "SELECT COUNT(*) AS total FROM hostels");
$total_hostels = mysqli_fetch_assoc($res)['total'];

$res            = mysqli_query($conn, "SELECT COUNT(*) AS total FROM inquiries WHERE user_id = $student_id");
$total_bookings = mysqli_fetch_assoc($res)['total'];

$res     = mysqli_query($conn, "
    SELECT hostel_id, name, location, price, description, created_at
    FROM hostels ORDER BY created_at DESC LIMIT 8
");
$hostels = mysqli_fetch_all($res, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Student Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>

    <!-- HEADER -->
    <header>
        <div>
            <h2>Student Dashboard</h2>
            <span>Welcome, <?= htmlspecialchars($student_name) ?></span>
        </div>
       
    </header>

    <div class="inner">

        <!-- STAT CARDS -->
        <div class="grid">
            <div class="card">
                <h3>Total Hostels</h3>
                <div class="stat-number"><?= $total_hostels ?></div>
                <div class="muted">Available hostels</div>
            </div>
            <div class="card">
                <h3>Your Inquiries</h3>
                <div class="stat-number"><?= $total_bookings ?></div>
                <div class="muted">Messages you have sent</div>
            </div>
            <div class="card">
                <h3>Quick Links</h3>
                <a href="view_hostels.php" class="link">View Hostels</a><br>
                <a href="my_inquiries.php" class="link">My Inquiries</a><br>
                <a href="my_reservations.php" class="link">My Reservations</a>
            </div>
        </div>

        <!-- LATEST HOSTELS -->
        <h3 class="section-title">Latest Hostels</h3>
        <div class="hostel-list">
            <?php foreach ($hostels as $h):
                $imgQ    = mysqli_query($conn, "SELECT image_path FROM hostel_images WHERE hostel_id = {$h['hostel_id']} LIMIT 1");
                $img     = mysqli_fetch_assoc($imgQ);
                $imgPath = $img ? "../../uploads/" . $img['image_path'] : null;
            ?>
            <div class="hostel-card">
                <?php if ($imgPath && file_exists($imgPath)): ?>
                    <img src="<?= $imgPath ?>" alt="<?= htmlspecialchars($h['name']) ?>">
                <?php else: ?>
                    <img src="https://via.placeholder.com/400x180?text=No+Image" alt="No Image">
                <?php endif; ?>
                <div class="hostel-body">
                    <h4><?= htmlspecialchars($h['name']) ?></h4>
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
                        <p class="meta"><?= htmlspecialchars($h['location']) ?></p>
                        <div class="price-card">Rs <?= number_format($h['price']) ?></div>
                    </div>
                    <p class="desc"><?= htmlspecialchars(substr($h['description'], 0, 100)) ?>...</p>
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;">
                        <a class="link" href="view_detail.php?hostel_id=<?= $h['hostel_id'] ?>">View Details →</a>
                        <a class="btn-outline" href="view_detail.php?hostel_id=<?= $h['hostel_id'] ?>#inquire">Inquire</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <a href="../logout.php" class="logout-link">Logout</a>

    </div>
</body>
</html>