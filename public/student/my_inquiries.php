<?php
session_start();
require_once('../../config/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$student_id = (int)$_SESSION['user_id'];

// SQL: Fetch inquiries and count if owner has replied
$sql = "SELECT i.*, h.name AS hostel_name,
        (SELECT COUNT(*) FROM inquiry_replies r 
         WHERE r.inquiry_id = i.id AND r.sender='owner') AS owner_replied
        FROM inquiries i
        JOIN hostels h ON i.hostel_id = h.hostel_id
        WHERE i.user_id = $student_id
        ORDER BY i.created_at DESC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Inquiries</title>
    <link rel="stylesheet" href="../css/dashboard.css">
  <style>
    /* --- My Inquiries Page Styling --- */

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f5f7fa;
    margin: 0;
    padding: 0;
    color: #333;
}

.container {
    max-width: 900px;
    margin: 40px auto;
    padding: 0 20px;
}

h2 {
    text-align: center;
    color: #0462a1;
    margin-bottom: 20px;
}

hr {
    border: 0;
    height: 1px;
    background: #ccc;
    margin-bottom: 30px;
}

.inq-item {
    background: #fff;
    border-radius: 12px;
    padding: 20px 25px;
    margin-bottom: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transition: transform 0.2s, box-shadow 0.2s;
}

.inq-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(0,0,0,0.12);
}

.inq-item strong {
    font-size: 18px;
    color: #0462a1;
}

.inq-item small {
    font-size: 12px;
}

.inq-item p {
    margin: 12px 0;
    line-height: 1.5;
    color: #555;
}

.status {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    text-transform: capitalize;
}

.status.active {
    background: #e6f7e6;
    color: #2ecc71;
}

.status.pending {
    background: #fff5e6;
    color: #f39c12;
}

.status.closed {
    background: #f9e6e6;
    color: #e74c3c;
}

.btn {
    display: inline-block;
    padding: 8px 16px;
    margin-top: 12px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn:hover {
    background: linear-gradient(135deg, #764ba2, #667eea);
    transform: translateY(-2px);
}

a.btn-secondary {
    display: inline-block;
    margin-top: 30px;
    background: #eee;
    color: #333;
    padding: 10px 18px;
}

a.btn-secondary:hover {
    background: #ddd;
    transform: translateY(-1px);
}

span[style*="color:green"], span[style*="color:gray"] {
    font-weight: 600;
    font-size: 13px;
}

  </style>
</head>
<body>

<div class="container">
    <h2>My Inquiries</h2>
    
    <hr>

    <?php if (mysqli_num_rows($result) == 0): ?>
        <p>No inquiries found.</p>
    <?php endif; ?>

    <?php while ($inq = mysqli_fetch_assoc($result)): ?>
        <div class="inq-item">
            <strong>🏠 <?= htmlspecialchars($inq['hostel_name']) ?></strong> 
            <small style="color:gray; float:right;"><?= $inq['created_at'] ?></small><br>
            
            <p><?= nl2br(htmlspecialchars(substr($inq['message'], 0, 100))) ?>...</p>

            <span class="status <?= $inq['status'] ?>">
                <?= ucfirst($inq['status']) ?>
            </span>

            <span style="margin-left:15px;">
                <?php if ($inq['owner_replied'] > 0): ?>
                    <span style="color:green;">✔ Owner Replied</span>
                <?php else: ?>
                    <span style="color:gray;">Waiting...</span>
                <?php endif; ?>
            </span>

            <br><br>
            <a class="btn" href="view_inquiry.php?id=<?= $inq['id'] ?>">View Conversation</a>
        </div>
    <?php endwhile; ?>
    <a class="btn" href="dashboard.php">Back to Dashboard</a>
</div>

</body>
</html>