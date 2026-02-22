<?php
session_start();
require_once("../../config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
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

$owner_id = (int)$_SESSION['user_id'];

// Ensure hostel belongs to this owner
$check = mysqli_query($conn, "
    SELECT hostel_id FROM hostels 
    WHERE hostel_id = $hostel_id AND owner_id = $owner_id
");

if (mysqli_num_rows($check) == 0) {
    die("You are not allowed to delete this hostel!");
}

// Delete images
mysqli_query($conn, "DELETE FROM hostel_images WHERE hostel_id = $hostel_id");

// Delete hostel
mysqli_query($conn, "DELETE FROM hostels WHERE hostel_id = $hostel_id");

// Redirect back
header("Location: manage_hostels.php?deleted=1");
exit;

?>
