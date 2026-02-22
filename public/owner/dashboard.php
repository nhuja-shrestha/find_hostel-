<?php
session_start();
require_once('../../config/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
if ($_SESSION['role'] !== 'owner') {
    header("Location: ../login.php");
    exit;
}

$owner_id   = (int)$_SESSION['user_id'];
$owner_name = $_SESSION['name'] ?? 'Owner';

/* --- TOTAL HOSTELS --- */
$res           = mysqli_query($conn, "SELECT COUNT(*) AS total FROM hostels WHERE owner_id = $owner_id");
$total_hostels = mysqli_fetch_assoc($res)['total'];

/* --- TOTAL INQUIRIES ---
   ✅ Fixed: was using 'bookings' table — now uses 'inquiries'
*/
$res             = mysqli_query($conn, "
    SELECT COUNT(b.id) AS total 
    FROM inquiries b
    JOIN hostels h ON b.hostel_id = h.hostel_id
    WHERE h.owner_id = $owner_id
");
$total_inquiries = mysqli_fetch_assoc($res)['total'];

/* --- HOSTELS LIST --- */
$res     = mysqli_query($conn, "
    SELECT hostel_id, name, location, price, status, created_at, description 
    FROM hostels 
    WHERE owner_id = $owner_id 
    ORDER BY created_at DESC
");
$hostels = mysqli_fetch_all($res, MYSQLI_ASSOC);

/* --- RECENT INQUIRIES ---
   ✅ Fixed: was using 'bookings' table — now uses 'inquiries'
   ✅ Fixed: JOIN was ON b.id = u.id (wrong!) — now uses b.user_id = u.id
*/
$res       = mysqli_query($conn, "
    SELECT b.id, b.message, b.status, b.created_at,
           u.name AS student_name, h.name AS hostel_name
    FROM inquiries b
    JOIN users u ON b.user_id = u.id
    JOIN hostels h ON b.hostel_id = h.hostel_id
    WHERE h.owner_id = $owner_id
    ORDER BY b.created_at DESC
    LIMIT 8
");
$inquiries = mysqli_fetch_all($res, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Owner Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="../css/dashboard.css">
<link rel="stylesheet" href="css/dashboard.css">
</head>

<body>
<div class="container">

<header>
    <div>
        <h2 style="margin:0;color:var(--accent);">Owner Dashboard</h2>
        <div class="muted">Welcome, <?= htmlspecialchars($owner_name) ?></div>
    </div>
    
</header>

<div class="grid">
    <div class="card">
        <h3>Total Hostels</h3>
        <div style="font-size:28px;font-weight:700;"><?= $total_hostels ?></div>
        <div class="muted">Your listings</div>
    </div>

    <div class="card">
        <h3>Total Inquiries</h3>
        <div style="font-size:28px;font-weight:700;"><?= $total_inquiries ?></div>
        <div class="muted">Messages &amp; booking requests</div>
    </div>

    <div class="card">
        <h3>Quick Links</h3>
        <a href="manage_hostels.php" class="link">Manage Hostels</a><br>
        <a class="link" href="view_inquiries.php">View Inquiries</a><br>
        <a class="link" href="view_reservations.php">View reservations</a>
    </div>
</div>

<!-- YOUR HOSTELS -->
<h3 style="margin-top:25px;">Your Hostels</h3>
<div class="hostel-list">

<?php if (empty($hostels)): ?>
    <div class="card">No hostels added yet. <a class="link" href="add_hostel.php">Add now</a></div>
<?php else: ?>
    <?php foreach ($hostels as $h):
        $imgQuery = mysqli_query($conn,
            "SELECT image_path FROM hostel_images WHERE hostel_id = {$h['hostel_id']} LIMIT 1"
        );
        $imgData  = mysqli_fetch_assoc($imgQuery);
        $imgPath  = $imgData ? "../../uploads/" . $imgData['image_path'] : null;
    ?>
    <div class="hostel-item">

        <div style="display:flex; gap:14px;">
            <div class="thumb">
                <?php if ($imgPath && file_exists($imgPath)): ?>
                    <img src="<?= $imgPath ?>">
                <?php else: ?>
                    No Image
                <?php endif; ?>
            </div>

            <div class="hostel-meta">
                <h4><?= htmlspecialchars($h['name']) ?></h4>
                <p><?= htmlspecialchars($h['location']) ?> • Rs <?= number_format($h['price']) ?></p>
                <div class="desc">
                    <?= nl2br(substr(htmlspecialchars($h['description']), 0, 110)) ?>...
                </div>
                <div class="small" style="margin-top:6px;">
                    Status: <strong><?= $h['status'] ?></strong> |
                    Added: <?= date("M j, Y", strtotime($h['created_at'])) ?>
                </div>
            </div>
        </div>

    </div>
    <?php endforeach; ?>
<?php endif; ?>

</div>

<!-- RECENT INQUIRIES -->
<h3 style="margin-top:25px;">Recent Inquiries</h3>

<?php if (empty($inquiries)): ?>
    <div class="card">No inquiries yet.</div>
<?php else: ?>
    <?php foreach ($inquiries as $inq): ?>
    <div class="inq-item">
        <strong><?= htmlspecialchars($inq['student_name']) ?></strong> asked about
        <strong><?= htmlspecialchars($inq['hostel_name']) ?></strong><br>
        <span class="muted"><?= $inq['created_at'] ?></span>
        <p><?= nl2br(htmlspecialchars($inq['message'])) ?></p>
        <a class="link" href="view_inquiry.php?id=<?= $inq['id'] ?>">Open</a>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<br>
<a href="../logout.php" class="link">Logout</a>

</div>
</body>
</html>