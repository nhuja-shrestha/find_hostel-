<?php
session_start();
include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $query  = "SELECT * FROM users WHERE email='$email' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        // Plain password comparison (no hashing for now)
        if ($password == $user['password']) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role']    = $user['role'];
            $_SESSION['name']    = $user['name'];

            if ($user['role'] == 'owner') {
                header("Location: owner/dashboard.php");
            } else if ($user['role'] == 'student') {
                header("Location: student/dashboard.php");
            }
            exit();
        } else {
            $error = "Incorrect password!";
        }
    } else {
        $error = "No account found with that email!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Hostel Finder</title>
    <link rel="stylesheet" href="../css/log_css.css">
</head>
<body>
    <div class="login-container">

        <!-- Left Side -->
        <div class="login-left">
            <div class="icon">🏠</div>
            <h1>Welcome Back!</h1>
            <p>Sign in to access your hostel finder account and discover the perfect accommodation for your needs.</p>
        </div>

        <!-- Right Side -->
        <div class="login-right">
            <div class="login-header">
                <h2>Login</h2>
                <p>Enter your credentials to continue</p>
            </div>

            <?php if (isset($_GET['registered'])): ?>
                <div class="success-message">
                    ✅ Account created successfully! Please log in.
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrapper">
                        <span class="input-icon">📧</span>
                        <input type="email" id="email" name="email"
                               placeholder="Enter your email" required
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🔒</span>
                        <input type="password" id="password" name="password"
                               placeholder="Enter your password" required>
                    </div>
                </div>

                

                <button type="submit" class="btn-login">Login</button>
            </form>

            <div class="divider">
                <span>OR</span>
            </div>

            <div class="register-link">
                Don't have an account? <a href="register.php">Register here</a>
            </div>

            <div class="back-home">
                <a href="dashboard.php">← Back to Home</a>
            </div>
        </div>
    </div>
</body>
</html>