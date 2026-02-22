<?php
session_start();
require_once('../../config/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$user_role = $_SESSION['role'];

// GET INQUIRY ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: Inquiry ID missing.");
}
$inq_id = (int)$_GET['id'];

// HANDLE FORM SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Reply
    if (isset($_POST['send_reply']) && !empty(trim($_POST['reply_message']))) {
        $msg = trim($_POST['reply_message']);
        $sender = ($user_role === 'owner') ? 'owner' : 'student';
        $stmt = $conn->prepare("INSERT INTO inquiry_replies (inquiry_id, sender, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $inq_id, $sender, $msg);
        $stmt->execute();
    }

    // Update Status (owner only)
    if (isset($_POST['update_status']) && $user_role === 'owner') {
        $new_status = $_POST['status'];
        $stmt = $conn->prepare("UPDATE inquiries SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $inq_id);
        $stmt->execute();
    }

    header("Location: view_inquiry.php?id=$inq_id");
    exit;
}

// FETCH INQUIRY DATA
$sql = "SELECT i.*, u.name AS student_name, h.name AS hostel_name 
        FROM inquiries i 
        JOIN users u ON i.user_id = u.id 
        JOIN hostels h ON i.hostel_id = h.hostel_id 
        WHERE i.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $inq_id);
$stmt->execute();
$inquiry = $stmt->get_result()->fetch_assoc();

if (!$inquiry) die("Inquiry not found.");

// FETCH REPLIES
$replies = $conn->query("SELECT * FROM inquiry_replies WHERE inquiry_id = $inq_id ORDER BY created_at ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Inquiry Thread - <?= htmlspecialchars($inquiry['hostel_name']) ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="../css/dashboard.css">
<style>
/* ===== CONTAINER ===== */
.box {
    max-width: 900px;
    margin: 40px auto;
    background: white;
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.05);
}

/* ===== HEADINGS ===== */
.box h2 {
    font-size: 28px;
    margin-bottom: 10px;
}
.box h3 {
    font-size: 20px;
    margin-top: 25px;
}

/* ===== CHAT BUBBLES ===== */
.chat-box {
    padding: 12px 16px;
    margin-bottom: 10px;
    border-radius: 16px;
    max-width: 75%;
    word-wrap: break-word;
}

.owner-msg {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    margin-left: auto;
    text-align: right;
}

.student-msg {
    background: #f3f4f6;
    color: #111827;
    margin-right: auto;
}

/* ===== TEXTAREA & BUTTON ===== */
form textarea {
    width: 100%;
    padding: 12px;
    border-radius: 12px;
    border: 1px solid #ddd;
    margin-top: 10px;
    resize: vertical;
    font-size: 14px;
}

.btn {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 12px 20px;
    border-radius: 10px;
    border: none;
    font-weight: 600;
    cursor: pointer;
    margin-top: 10px;
    display: inline-block;
    transition: 0.3s;
}

.btn:hover {
    opacity: 0.9;
}

/* Status update button */
.btn-status {
    background: #007bff;
}

/* Back link */
a.back-link {
    display: inline-block;
    margin-top: 20px;
    text-decoration: none;
    color: #333;
    padding: 10px 18px;
    border-radius: 10px;
    border: 1px solid #ccc;
    transition: 0.3s;
}

a.back-link:hover {
    background: #f3f4f6;
}
</style>
</head>
<body>

<div class="box">
    <h2>Conversation: <?= htmlspecialchars($inquiry['hostel_name']) ?></h2>
    <p><strong>Student:</strong> <?= htmlspecialchars($inquiry['student_name']) ?></p>
    <p><strong>Original Message:</strong><br><em><?= nl2br(htmlspecialchars($inquiry['message'])) ?></em></p>

    <h3>Messages</h3>
    <?php while($r = $replies->fetch_assoc()): ?>
        <div class="chat-box <?= ($r['sender'] == 'owner') ? 'owner-msg' : 'student-msg' ?>">
            <strong><?= ucfirst($r['sender']) ?>:</strong><br>
            <?= nl2br(htmlspecialchars($r['message'])) ?>
        </div>
    <?php endwhile; ?>

    <form method="POST">
        <textarea name="reply_message" placeholder="Write a reply..." required></textarea>
        <button type="submit" name="send_reply" class="btn">Send Message</button>
    </form>

    <?php if ($user_role === 'owner'): ?>
        <hr>
        <form method="POST">
            <h3>Update Status</h3>
            <select name="status">
                <option value="pending" <?= $inquiry['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="approved" <?= $inquiry['status'] == 'approved' ? 'selected' : '' ?>>Approved</option>
                <option value="rejected" <?= $inquiry['status'] == 'rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>
            <button type="submit" name="update_status" class="btn btn-status">Update Status</button>
        </form>
    <?php endif; ?>

    <a href="view_inquiries.php" class="back-link">⬅ Back to List</a>
</div>

</body>
</html>
