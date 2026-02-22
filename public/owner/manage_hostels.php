<?php
session_start();
require_once '../../config/db.php';

// Ensure owner is logged in
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'owner') {
    header("Location: ../login.php");
    exit;
}

$owner_id = (int) $_SESSION['user_id'];

// Fetch hostels with 1 image each (LEFT JOIN)
$sql = "
    SELECT h.hostel_id, h.name, h.location, h.price, h.status,
           (SELECT image_path FROM hostel_images WHERE hostel_id = h.hostel_id LIMIT 1) AS image
    FROM hostels h
    WHERE h.owner_id = $owner_id
    ORDER BY h.hostel_id DESC
";

$result = mysqli_query($conn, $sql);
$hostels = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Hostels - Owner</title>
    <link rel="stylesheet" href="../css/dashboard.css">
   <link rel="stylesheet" href="css/manage_hostels.css">
</head>
<body>

<div class="container">
    <h1>Your Hostels</h1>

    <a href="add_hostel.php" style="background:#0462a1;padding:10px 15px;color:white;border-radius:8px;font-weight:600;text-decoration:none;">
        + Add New Hostel
    </a>
    <a href="dashboard.php" style="background:#555;padding:10px 15px;color:white;border-radius:8px;font-weight:600;text-decoration:none;margin-left:10px;">
        Back to Dashboard
    </a>
    <br><br>

    <?php if (empty($hostels)): ?>
        <div style="padding:15px;background:white;border-radius:10px;">
            You haven't added any hostels yet.
            <a href="add_hostel.php" style="color:#0462a1;font-weight:600;">Add your first hostel</a>
        </div>
    <?php else: ?>
        <?php foreach ($hostels as $h): ?>
            <div class="hostel-card">
                <div class="left">
                    <div class="thumb">
                        <?php if ($h['image']): ?>
                            <img src="../../uploads/<?php echo htmlspecialchars($h['image']); ?>">
                        <?php else: ?>
                            No Image
                        <?php endif; ?>
                    </div>

                    <div class="meta">
                        <h3><?php echo htmlspecialchars($h['name']); ?></h3>
                        <p>
                            <?php echo htmlspecialchars($h['location']); ?> · 
                            ₹<?php echo number_format($h['price']); ?> · 
                            <strong><?php echo htmlspecialchars($h['status']); ?></strong>
                        </p>
                    </div>
                </div>

                <div class="actions">
                    <a href="view_hostel.php?id=<?php echo $h['hostel_id']; ?>">View</a>
                    <a href="edit_hostel.php?id=<?php echo $h['hostel_id']; ?>">Edit</a>
                    <a class="danger" href="delete_hostel.php?id=<?php echo $h['hostel_id']; ?>" 
                       onclick="return confirm('Delete this hostel? This cannot be undone.');">
                       Delete
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

</body>
</html>
