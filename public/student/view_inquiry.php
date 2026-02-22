<?php
session_start();
require_once('../../config/db.php');

if (!isset($_SESSION['user_id'])) {
    die("Login required");
}

$inq_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

/* INSERT REPLY */
if (isset($_POST['reply'])) {

    $msg = $_POST['message'];

    $sql = "INSERT INTO inquiry_replies (inquiry_id, sender, message) 
            VALUES ('$inq_id', 'student', '$msg')";

    mysqli_query($conn, $sql);

    header("Location: inquiry_detail.php?id=" . $inq_id);
    exit;
}

/* FETCH INQUIRY */
$inq = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM inquiries WHERE id='$inq_id' AND user_id='$user_id'"
));

/* FETCH REPLIES */
$replies = mysqli_query($conn,
    "SELECT * FROM inquiry_replies WHERE inquiry_id='$inq_id'"
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inquiry Detail</title>
    <style>
        /* --- Inquiry Detail Page Styling --- */

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f5f7fa;
    margin: 0;
    padding: 0;
    color: #333;
}

.container {
    max-width: 800px;
    margin: 40px auto;
    padding: 0 20px;
}

h2, h3 {
    color: #0462a1;
    margin-bottom: 15px;
    text-align: center;
}

.box {
    background: #fff;
    padding: 20px 25px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    margin-bottom: 30px;
    line-height: 1.6;
}

.reply {
    background: #ffffff;
    border-radius: 12px;
    padding: 15px 20px;
    margin-bottom: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    transition: transform 0.2s, box-shadow 0.2s;
}

.reply:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.reply b {
    color: #0462a1;
    text-transform: capitalize;
}

.reply p {
    margin: 8px 0 0 0;
    color: #555;
    line-height: 1.5;
}

form {
    margin-top: 25px;
    display: flex;
    flex-direction: column;
}

form textarea {
    width: 100%;
    min-height: 100px;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 14px;
    resize: vertical;
    margin-bottom: 15px;
    transition: border-color 0.3s;
}

form textarea:focus {
    outline: none;
    border-color: #0462a1;
}

form button {
    align-self: flex-start;
    padding: 10px 20px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

form button:hover {
    background: linear-gradient(135deg, #764ba2, #667eea);
    transform: translateY(-2px);
}

a.btn {
    display: inline-block;
    margin-top: 25px;
    padding: 10px 18px;
    background: #eee;
    color: #333;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.3s ease;
}

a.btn:hover {
    background: #ddd;
    transform: translateY(-1px);
}

@media (max-width: 640px) {
    .container {
        padding: 0 15px;
    }
    form button, a.btn {
        width: 100%;
        text-align: center;
    }
}

    </style>
</head>
<body>

<div class="container">

    <h2>Inquiry Message</h2>

    <div class="box">
        <?= $inq['message']; ?>
    </div>

    <h3>Replies</h3>

    <?php while($row = mysqli_fetch_assoc($replies)) { ?>
        <div class="reply">
            <b><?= $row['sender']; ?>:</b>
            <p><?= $row['message']; ?></p>
        </div>
    <?php } ?>

    <form method="POST">
        <textarea name="message" required></textarea>
        <button type="submit" name="reply">Send Reply</button>
    </form><br>
    <a class="btn" href="my_inquiries.php">See all inquiries</a>
</div>

</body>
</html>
