<?php
session_start();
require_once('../../config/db.php');

// Security: Ensure only owners can see this list
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../login.php");
    exit;
}

$owner_id = (int)$_SESSION['user_id'];

// SQL: Fetch inquiries for hostels owned by this user
$sql = "SELECT i.id AS inquiry_id, i.message, i.status, i.created_at, 
               u.name AS student_name, h.name AS hostel_name
        FROM inquiries i
        JOIN users u ON i.user_id = u.id
        JOIN hostels h ON i.hostel_id = h.hostel_id
        WHERE h.owner_id = ?
        ORDER BY i.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $owner_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Owner — Inquiries</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="../css/dashboard.css">
<link rel="stylesheet" href="css/view_inquiries.css">
</head>
<body>

<div class="container">
    <h2>📬 Hostel Inquiries</h2>

    <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <div class="card">
                <p><strong>From:</strong> <?= htmlspecialchars($row['student_name']) ?></p>
                <p><strong>Hostel:</strong> <?= htmlspecialchars($row['hostel_name']) ?></p>
                <p><?= htmlspecialchars(substr($row['message'], 0, 150)) ?>...</p>
                <p>Status: <span class="status <?= htmlspecialchars($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></span></p>
                <a href="view_inquiry.php?id=<?= $row['inquiry_id'] ?>" class="btn">Open Thread</a>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No inquiries found.</p>
    <?php endif; ?>

    <a href="dashboard.php" class="btn">← Back to Dashboard</a>
</div>

</body>
</html>
