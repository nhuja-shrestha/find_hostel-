<?php
session_start();
require "../../config/db.php";

if (!isset($_GET['id'])) {
    die("Hostel ID missing!");
}

$hostel_id = intval($_GET['id']);

// Fetch hostel details
$stmt = mysqli_prepare($conn, "SELECT * FROM hostels WHERE hostel_id = ?");
mysqli_stmt_bind_param($stmt, "i", $hostel_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$hostel = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$hostel) {
    die("Hostel not found!");
}

// Fetch existing images
$stmt = mysqli_prepare($conn, "SELECT * FROM hostel_images WHERE hostel_id = ?");
mysqli_stmt_bind_param($stmt, "i", $hostel_id);
mysqli_stmt_execute($stmt);
$img_result = mysqli_stmt_get_result($stmt);
$images = mysqli_fetch_all($img_result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Handle form submission (update hostel)
if (isset($_POST['update_hostel'])) {

    $name = $_POST['name'];
    $location = $_POST['location'];
    $price = $_POST['price'];
    $rooms = $_POST['rooms'];
    $room_type = $_POST['room_type'];
    $description = $_POST['description'];
    $facilities = $_POST['facilities'];
    $status = $_POST['status'];

    $update_sql = "
        UPDATE hostels SET 
        name = ?, 
        location = ?, 
        price = ?, 
        rooms = ?, 
        room_type = ?, 
        description = ?, 
        facilities = ?, 
        status = ?
        WHERE hostel_id = ?
    ";

    $stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($stmt, "ssdiisssi", 
        $name, $location, $price, $rooms, $room_type, 
        $description, $facilities, $status, $hostel_id
    );
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Upload new images
    if (!empty($_FILES['images']['name'][0])) {
        $uploadDir = "../../uploads/";

        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            $fileName = time() . "_" . basename($_FILES['images']['name'][$key]);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($tmp_name, $targetPath)) {
                mysqli_query($conn, "INSERT INTO hostel_images (hostel_id, image_path) VALUES ($hostel_id, '$fileName')");
            }
        }
    }

    echo "<script>alert('Hostel updated successfully!'); window.location='manage_hostels.php';</script>";
}

// Delete single image
if (isset($_GET['delete_image'])) {
    $img_id = intval($_GET['delete_image']);

    $q = mysqli_query($conn, "SELECT image_path FROM hostel_images WHERE id = $img_id");
    $img = mysqli_fetch_assoc($q);

    if ($img) {
        unlink("../../uploads/" . $img['image_path']);
        mysqli_query($conn, "DELETE FROM hostel_images WHERE id = $img_id");
    }

    header("Location: edit_hostel.php?id=$hostel_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Hostel - <?= htmlspecialchars($hostel['name']) ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="../css/dashboard.css">
<link rel="stylesheet" href="css/edit_hostel.css">
<style>

</style>
</head>
<body>

<div class="box">
    <h2>Edit Hostel</h2>

    <form method="POST" enctype="multipart/form-data">

        <label>Name:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($hostel['name']) ?>" required>

        <label>Location:</label>
        <input type="text" name="location" value="<?= htmlspecialchars($hostel['location']) ?>" required>

        <label>Price:</label>
        <input type="number" name="price" value="<?= $hostel['price'] ?>" required>

        <label>Rooms:</label>
        <input type="number" name="rooms" value="<?= $hostel['rooms'] ?>" required>

        <label>Room Type:</label>
        <select name="room_type" required>
            <option value="">--Select--</option>
            <option value="Single" <?= $hostel['room_type'] == "Single" ? "selected" : "" ?>>Single</option>
            <option value="Double" <?= $hostel['room_type'] == "Double" ? "selected" : "" ?>>Double</option>
            <option value="Dorm" <?= $hostel['room_type'] == "Dorm" ? "selected" : "" ?>>Dorm</option>
            <select>
        <label>Facilities (comma separated):</label>
        <textarea name="facilities" required><?= htmlspecialchars($hostel['facilities']) ?></textarea>

        <label>Description:</label>
        <textarea name="description" required><?= htmlspecialchars($hostel['description']) ?></textarea>

        <label>Status:</label>
        <select name="status" required>
             <option value="active" <?= $hostel['status'] == "active" ? "selected" : "" ?>>Select</option>
            <option value="active" <?= $hostel['status'] == "active" ? "selected" : "" ?>>Active</option>
          <option value="inactive" <?= $hostel['status'] == "inactive" ? "selected" : "" ?>>Inactive</option>
        </select>

        <label>Add New Images:</label>
        <input type="file" name="images[]" multiple>

        <button type="submit" name="update_hostel" class="btn">Update Hostel</button>
       <a class="btn" href="manage_hostels.php">Back to list</a>
    </form>

    <h3>Existing Images</h3>
    <div class="gallery">
        <?php foreach ($images as $img): ?>
            <div>
                <img src="../../uploads/<?= htmlspecialchars($img['image_path']) ?>">
                <a href="edit_hostel.php?id=<?= $hostel_id ?>&delete_image=<?= $img['id'] ?>" onclick="return confirm('Delete this image?')">Delete</a>
                
            </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>
