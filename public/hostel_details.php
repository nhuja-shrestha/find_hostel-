<?php
session_start();
include '../config/db.php';

// ✅ Read ?id= (matches the link in dashboard.php)
$hostel_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($hostel_id == 0) {
    header("Location: dashboard.php");
    exit();
}

// Fetch hostel details
$query = "SELECT 
    h.*,
    u.name  as owner_name,
    u.email as owner_email,
    COALESCE(AVG(r.rating), 0) as avg_rating,
    COUNT(DISTINCT r.id) as review_count
FROM hostels h
LEFT JOIN users u ON h.owner_id = u.id
LEFT JOIN reviews r ON h.hostel_id = r.hostel_id
WHERE h.hostel_id = $hostel_id AND h.status = 'active'
GROUP BY h.hostel_id";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Hostel Query Failed: " . mysqli_error($conn));
}

$hostel = mysqli_fetch_assoc($result);


if (!$hostel) {
    // ✅ Fixed: was redirecting to index.php which doesn't exist
    header("Location: dashboard.php");
    exit();
}

// Fetch hostel images
$images_query  = "SELECT image_path FROM hostel_images WHERE hostel_id = $hostel_id";
$images_result = mysqli_query($conn, $images_query);

if (!$images_result) {
    die("Images Query Failed: " . mysqli_error($conn));
}

$images = [];
while ($row = mysqli_fetch_assoc($images_result)) {
    $images[] = $row['image_path'];
}



// Handle inquiry submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_hostel'])) {
    if (isset($_SESSION['user_id'])) {
        $user_id = (int)$_SESSION['user_id'];
        $message = mysqli_real_escape_string($conn, $_POST['message']);

        // ✅ Fixed: was inserting into 'bookings' — now uses 'inquiries' with correct column 'user_id'
        $book_query = "INSERT INTO inquiries (user_id, hostel_id, message, status)
                       VALUES ($user_id, $hostel_id, '$message', 'pending')";

        if (mysqli_query($conn, $book_query)) {
            // ✅ Redirect to prevent duplicate on refresh
            header("Location: hostel_details.php?id=$hostel_id&sent=1");
            exit();
        } else {
            $error_message = "Failed to send inquiry: " . mysqli_error($conn);
        }
    } else {
        $error_message = "Please login to inquire about this hostel.";
    }
}

$success_message = isset($_GET['sent']) ? "Inquiry sent successfully! The owner will contact you soon." : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($hostel['name']); ?> - Hostel Finder</title>
    <link rel="stylesheet" href="css/hostel_details.css">
</head>
<body>

    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">🏠 Hostel<span>Finder</span></div>
            <ul class="nav-menu">
                <li class="nav-item"><a href="dashboard.php">Home</a></li>
                <li class="nav-item"><a href="about.php">About</a></li>
                <li class="nav-item"><a href="contact.php">Contact</a></li>
            </ul>
            <div class="auth-buttons">
                <a href="login.php" class="btn-login">Login</a>
                <a href="register.php" class="btn-signup">Sign Up</a>
            </div>
        </div>
    </nav>

    <div class="container">

        <!-- Back link -->
        <a href="dashboard.php" class="back-link">
            ← Back to Hostels
        </a>

        <!-- Image Gallery -->
        <div class="image-gallery">
            <div class="main-image" id="mainImage">
                <?php if (!empty($images)): ?>
                    <img src="../uploads/<?php echo htmlspecialchars($images[0]); ?>"
                         alt="<?php echo htmlspecialchars($hostel['name']); ?>">
                <?php else: ?>
                    <div class="placeholder">🏢</div>
                <?php endif; ?>
            </div>

            <?php if (count($images) > 1): ?>
            <div class="thumbnail-gallery">
                <?php foreach ($images as $image): ?>
                    <div class="thumbnail"
                         onclick="changeImage('../uploads/<?php echo htmlspecialchars($image); ?>')">
                        <img src="../uploads/<?php echo htmlspecialchars($image); ?>" alt="Thumbnail">
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Content Grid -->
        <div class="content-grid">

            <!-- LEFT: Hostel Info -->
            <div class="hostel-info">

                <div class="hostel-header">
                    <div>
                        <h1 class="hostel-title"><?php echo htmlspecialchars($hostel['name']); ?></h1>
                        <div class="hostel-location">
                            📍 <?php echo htmlspecialchars($hostel['location']); ?>
                        </div>
                    </div>
                    <div class="rating-badge">
                        ⭐ <?php echo number_format($hostel['avg_rating'], 1); ?>
                        <span>(<?php echo $hostel['review_count']; ?>)</span>
                    </div>
                </div>

                <!-- Compact facility badges -->
                <?php
                $facilities_compact = explode(',', $hostel['facilities'] ?? '');
                if (!empty($facilities_compact) && trim(implode('', $facilities_compact)) !== ''): ?>
                    <div class="facility-badges">
                        <?php foreach ($facilities_compact as $f):
                            $f_trim = trim($f);
                            if ($f_trim === '') continue;
                        ?>
                            <span class="badge"><?php echo htmlspecialchars($f_trim); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="price-tag">
                    Rs.<?php echo number_format($hostel['price'], 0); ?><span>/month</span>
                </div>

                <!-- Description -->
                <div class="info-section">
                    <h3>About This Hostel</h3>
                    <p><?php echo nl2br(htmlspecialchars($hostel['description'] ?? 'No description available.')); ?></p>
                </div>

                <!-- Rooms info -->
                <div class="info-section">
                    <h3>Hostel Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-icon">🛏️</span>
                            <div>
                                <strong>Rooms Available</strong><br>
                                <span><?php echo $hostel['rooms']; ?> rooms</span>
                            </div>
                        </div>
                        <div class="info-item">
                            <span class="info-icon">🚪</span>
                            <div>
                                <strong>Room Type</strong><br>
                                <span><?php echo htmlspecialchars($hostel['room_type']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Facilities -->
                <div class="info-section">
                    <h3>Facilities &amp; Amenities</h3>
                    <div class="facilities-grid">
                        <?php
                        $facilities = explode(',', $hostel['facilities'] ?? '');
                        $facility_icons = [
                            'wifi'       => '📶',
                            'meals'      => '🍽️',
                            'ac'         => '❄️',
                            'hot water'  => '🚿',
                            'gym'        => '💪',
                            'laundry'    => '🧺',
                            'parking'    => '🅿️',
                            'security'   => '🔒',
                            'study room' => '📚',
                            'garden'     => '🌳',
                        ];
                        foreach ($facilities as $facility):
                            if (trim($facility) == '') continue;
                            $f_lower = strtolower(trim($facility));
                            $icon = '✓';
                            foreach ($facility_icons as $key => $val) {
                                if (strpos($f_lower, $key) !== false) { $icon = $val; break; }
                            }
                        ?>
                            <div class="facility-item">
                                <span class="facility-icon"><?php echo $icon; ?></span>
                                <span><?php echo htmlspecialchars(trim($facility)); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

           

            <!-- RIGHT: Inquiry Card -->
            <div>
                <div class="booking-card">
                    <h3>Send an Inquiry</h3>

                    <?php if (!empty($success_message)): ?>
                        <div class="success-message">
                            ✅ <?php echo $success_message; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($error_message)): ?>
                        <div class="error-message">
                            ⚠️ <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Owner info -->
                    <div class="owner-info">
                        <h4>Owner Information</h4>
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($hostel['owner_name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($hostel['owner_email']); ?></p>
                    </div>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <form method="POST" class="inquiry-form">
                            <div>
                                <label>Your Message</label>
                                <textarea name="message" required rows="4"
                                    placeholder="Tell the owner about yourself, when you'd like to move in, etc."></textarea>
                            </div>
                            <button type="submit" name="book_hostel" class="btn-submit">
                                📨 Send Inquiry
                            </button>
                        </form>
                    <?php else: ?>
                        <a href="login.php" class="btn-login-action">
                            Login to Send Inquiry
                        </a>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2024 HostelFinder. All rights reserved.</p>
        <p>A trusted platform for students.</p>
    </footer>

    <script>
        function changeImage(imagePath) {
            document.getElementById('mainImage').innerHTML =
                '<img src="' + imagePath + '" alt="Hostel Image">';
        }
    </script>
</body>
</html>