<?php
include '../config/db.php';

// Get search term if submitted
$search = trim($_GET['search'] ?? '');
$where  = "WHERE h.status = 'active'";

$params = [];
if (!empty($search)) {
    $s      = mysqli_real_escape_string($conn, $search);
    $where .= " AND (h.name LIKE '%$s%' OR h.location LIKE '%$s%' OR h.facilities LIKE '%$s%')";
}

// Fetch active hostels with their images and average ratings
$query = "
    SELECT h.hostel_id, h.name, h.location, h.price, h.rooms, h.room_type,
           h.description, h.facilities, h.created_at
    FROM hostels h
    $where
    ORDER BY h.created_at DESC
";
$result = mysqli_query($conn, $query);
$hostels = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Fetch images for hostels
foreach ($hostels as &$hostel) {
    $hid = $hostel['hostel_id'];
    $imgQ = mysqli_query($conn, "SELECT image_path FROM hostel_images WHERE hostel_id = $hid LIMIT 1");
    $img = mysqli_fetch_assoc($imgQ);
    $hostel['images'] = $img ? [$img['image_path']] : [];
}
unset($hostel); // break reference

// Get statistics
$stats_query  = "
    SELECT 
        (SELECT COUNT(*) FROM hostels WHERE status='active') as total_hostels,
        (SELECT COUNT(DISTINCT location) FROM hostels WHERE status='active') as total_locations
";
$stats_result = mysqli_query($conn, $stats_query);
$stats        = mysqli_fetch_assoc($stats_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hostel Finder - Home</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">🏠 Hostel<span>Finder</span></div>
            <ul class="nav-menu">
                <li class="nav-item"><a href="dashboard.php" class="active">Home</a></li>
                <li class="nav-item"><a href="about.php">About</a></li>
                <li class="nav-item"><a href="contact.php">Contact</a></li>
            </ul>
            <div class="auth-buttons">
                <a href="login.php" class="btn-login">Login</a>
                <a href="register.php" class="btn-signup">Sign Up</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <h1>Find Your Perfect Hostel</h1>
        <p>Discover comfortable and affordable hostels near your college</p>
        <form method="GET" action="dashboard.php">
            <div class="search-box">
                <input type="text" name="search" id="searchInput"
                       placeholder="Search by location, hostel name, or facility..."
                       value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Search</button>
            </div>
        </form>
    </section>

    <!-- Main Content -->
    <div class="container">

        <?php if (!empty($search)): ?>
            <p style="color:#555; margin-bottom:10px;">
                Showing results for <strong>"<?php echo htmlspecialchars($search); ?>"</strong>
                — <?php echo count($hostels); ?> hostel(s) found.
                <a href="dashboard.php" style="color:#0462a1; margin-left:8px;">✕ Clear search</a>
            </p>
        <?php endif; ?>

        <h2 class="section-title">
            <?php echo !empty($search) ? 'Search Results' : 'Featured Hostels'; ?>
        </h2>

        <!-- Hostel Listings -->
        <div class="hostel-grid">
            <?php if (empty($hostels)): ?>
                <div class="no-hostels">
                    <div class="no-hostels-icon">🏠</div>
                    <h3><?php echo !empty($search) ? 'No hostels matched your search.' : 'No Hostels Available'; ?></h3>
                    <p><?php echo !empty($search) ? 'Try a different keyword.' : 'Check back later for new listings!'; ?></p>
                </div>
            <?php else: ?>
                <?php foreach ($hostels as $hostel): ?>
                    <div class="hostel-box">
                        <div class="hostel-image">
                            <?php if (!empty($hostel['images'])): ?>
                                <img src="../uploads/<?php echo htmlspecialchars($hostel['images'][0]); ?>"
                                     alt="<?php echo htmlspecialchars($hostel['name']); ?>">
                            <?php else: ?>
                                🏢
                            <?php endif; ?>
                            <div class="hostel-badge">
                                <?php echo strtoupper($hostel['room_type'] ?? 'VERIFIED'); ?>
                            </div>
                        </div>
                        <div class="hostel-content">
                            <div class="hostel-header">
                                <div class="hostel-name"><?php echo htmlspecialchars($hostel['name']); ?></div>
                            </div>
                            <div class="hostel-location">
                                📍 <?php echo htmlspecialchars($hostel['location']); ?>
                            </div>
                            <div class="hostel-amenities">
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
                                    'garden'     => '🌳'
                                ];
                                $count = 0;
                                foreach ($facilities as $facility):
                                    if ($count >= 4) break;
                                    $facility_lower = strtolower(trim($facility));
                                    $icon = '✓';
                                    foreach ($facility_icons as $key => $value) {
                                        if (strpos($facility_lower, $key) !== false) {
                                            $icon = $value;
                                            break;
                                        }
                                    }
                                    $count++;
                                ?>
                                    <div class="amenity">
                                        <span class="amenity-icon"><?php echo $icon; ?></span>
                                        <span><?php echo htmlspecialchars(trim($facility)); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="hostel-footer">
                                <div class="hostel-price">
                                    Rs.<?php echo number_format($hostel['price'], 0); ?><span>/month</span>
                                </div>
                                <a href="hostel_details.php?id=<?php echo $hostel['hostel_id']; ?>" class="btn-view">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2024 HostelFinder. All rights reserved.</p>
        <p>A trusted platform for students.</p>
    </footer>

</body>
</html>
