<?php
include '../config/db.php';

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email   = mysqli_real_escape_string($conn, trim($_POST['email']));
    $subject = mysqli_real_escape_string($conn, trim($_POST['subject']));
    $message = mysqli_real_escape_string($conn, trim($_POST['message']));

    if (empty($name) || empty($email) || empty($message)) {
        $error = "Please fill in all required fields.";
    } else {
        // NOTE: Create this table if it doesn't exist:
        // CREATE TABLE contact_messages (
        //     id INT AUTO_INCREMENT PRIMARY KEY,
        //     name VARCHAR(100),
        //     email VARCHAR(100),
        //     subject VARCHAR(200),
        //     message TEXT,
        //     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        // );
        $sql = "INSERT INTO contact_messages (name, email, subject, message)
                VALUES ('$name', '$email', '$subject', '$message')";

        if (mysqli_query($conn, $sql)) {
            // Redirect to avoid resubmit on refresh
            header("Location: contact.php?sent=1");
            exit;
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - Hostel Finder</title>
    <link rel="stylesheet" href="css/contacts.css">
</head>
<body>

    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">🏠 Hostel<span>Finder</span></div>
            <ul class="nav-menu">
                <li class="nav-item"><a href="dashboard.php">Home</a></li>
                <li class="nav-item"><a href="about.php">About</a></li>
                <li class="nav-item"><a href="contact.php" class="active">Contact</a></li>
            </ul>
            <div class="auth-buttons">
                <a href="login.php" class="btn-login">Login</a>
                <a href="register.php" class="btn-signup">Sign Up</a>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="hero">
        <h1>Contact Us</h1>
        <p>Have a question or feedback? We'd love to hear from you.</p>
    </section>

    <div class="container">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:25px;">

            <!-- Contact Form -->
            <div style="background:white; border-radius:14px; padding:30px; box-shadow:0 8px 22px rgba(0,0,0,0.09);">
                <h2 style="color:#0462a1; margin-top:0;">✉️ Send a Message</h2>

                <?php if (isset($_GET['sent'])): ?>
                    <div style="background:#f0fdf4; border:1px solid #bbf7d0; color:#16a34a; padding:12px 16px; border-radius:8px; margin-bottom:20px; font-weight:600;">
                        ✅ Message sent successfully! We'll get back to you soon.
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div style="background:#fff0f0; border:1px solid #fcc; color:#c33; padding:12px 16px; border-radius:8px; margin-bottom:20px;">
                        ⚠️ <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST">

                    <div style="margin-bottom:18px;">
                        <label style="display:block; font-weight:600; margin-bottom:6px; color:#333;">Full Name <span style="color:red;">*</span></label>
                        <input type="text" name="name" required
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                               placeholder="Your full name"
                               style="width:100%; padding:12px; border:2px solid #e0e0e0; border-radius:8px; font-size:14px; box-sizing:border-box; font-family:Poppins,Arial;">
                    </div>

                    <div style="margin-bottom:18px;">
                        <label style="display:block; font-weight:600; margin-bottom:6px; color:#333;">Email Address <span style="color:red;">*</span></label>
                        <input type="email" name="email" required
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                               placeholder="your@email.com"
                               style="width:100%; padding:12px; border:2px solid #e0e0e0; border-radius:8px; font-size:14px; box-sizing:border-box; font-family:Poppins,Arial;">
                    </div>

                    <div style="margin-bottom:18px;">
                        <label style="display:block; font-weight:600; margin-bottom:6px; color:#333;">Subject</label>
                        <input type="text" name="subject"
                               value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>"
                               placeholder="What is this about?"
                               style="width:100%; padding:12px; border:2px solid #e0e0e0; border-radius:8px; font-size:14px; box-sizing:border-box; font-family:Poppins,Arial;">
                    </div>

                    <div style="margin-bottom:20px;">
                        <label style="display:block; font-weight:600; margin-bottom:6px; color:#333;">Message <span style="color:red;">*</span></label>
                        <textarea name="message" required rows="5"
                                  placeholder="Write your message here..."
                                  style="width:100%; padding:12px; border:2px solid #e0e0e0; border-radius:8px; font-size:14px; box-sizing:border-box; resize:vertical; font-family:Poppins,Arial;"><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit"
                            style="background:#0462a1; color:white; border:none; padding:12px 28px; border-radius:8px; font-size:15px; font-weight:600; cursor:pointer; width:100%;">
                        📨 Send Message
                    </button>

                </form>
            </div>

            <!-- Contact Info -->
            <div>

                <div style="background:white; border-radius:14px; padding:25px; box-shadow:0 8px 22px rgba(0,0,0,0.09); margin-bottom:20px;">
                    <h2 style="color:#0462a1; margin-top:0;">📋 Contact Info</h2>

                    <div style="margin-bottom:18px; display:flex; align-items:flex-start; gap:14px;">
                        <span style="font-size:28px;">📍</span>
                        <div>
                            <strong style="display:block; color:#333;">Address</strong>
                            <span style="color:#555; font-size:14px;">Kathmandu, Nepal</span>
                        </div>
                    </div>

                    <div style="margin-bottom:18px; display:flex; align-items:flex-start; gap:14px;">
                        <span style="font-size:28px;">📧</span>
                        <div>
                            <strong style="display:block; color:#333;">Email</strong>
                            <span style="color:#555; font-size:14px;">support@hostelfinder.com</span>
                        </div>
                    </div>

                    <div style="margin-bottom:18px; display:flex; align-items:flex-start; gap:14px;">
                        <span style="font-size:28px;">📞</span>
                        <div>
                            <strong style="display:block; color:#333;">Phone</strong>
                            <span style="color:#555; font-size:14px;">+977 98XXXXXXXX</span>
                        </div>
                    </div>

                    <div style="display:flex; align-items:flex-start; gap:14px;">
                        <span style="font-size:28px;">🕐</span>
                        <div>
                            <strong style="display:block; color:#333;">Support Hours</strong>
                            <span style="color:#555; font-size:14px;">Sunday – Friday: 9am – 6pm</span>
                        </div>
                    </div>
                </div>

                <div style="background:white; border-radius:14px; padding:25px; box-shadow:0 8px 22px rgba(0,0,0,0.09);">
                    <h2 style="color:#0462a1; margin-top:0;">❓ FAQs</h2>

                    <div style="margin-bottom:14px;">
                        <strong style="color:#333;">Is HostelFinder free for students?</strong>
                        <p style="color:#555; font-size:14px; margin:4px 0 0;">Yes, completely free. Just register and start browsing.</p>
                    </div>

                    <div style="margin-bottom:14px;">
                        <strong style="color:#333;">How do I list my hostel?</strong>
                        <p style="color:#555; font-size:14px; margin:4px 0 0;">Register as a Hostel Owner and submit your listing for admin approval.</p>
                    </div>

                    <div>
                        <strong style="color:#333;">How long does a reply take?</strong>
                        <p style="color:#555; font-size:14px; margin:4px 0 0;">We typically respond within 24 hours on working days.</p>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2024 HostelFinder. All rights reserved.</p>
        <p>A trusted platform for students.</p>
    </footer>

</body>
</html>