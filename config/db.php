<?php
// Database credentials
$host = "localhost";       // Host name
$user = "root";            // MySQL username
$pass = "";                // MySQL password
$dbname = "hostel_finder1"; // Database name

// Create connection
$conn = mysqli_connect($host, $user, $pass, $dbname);

// Check connection
if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

// Optional: Set charset to utf8 for special characters support
mysqli_set_charset($conn, "utf8");
?>
