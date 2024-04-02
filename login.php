<?php
session_start();

// If session variables are present, redirect to home.php
if (isset($_SESSION['login_status'])) {
    if ($_SESSION['role'] == 'customer') {
        header("Location: home.php");
        exit();
    } else if ($_SESSION['role'] == 'admin') {
        header("Location: dashboard.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="http://localhost:4566/css-bucket/S3s/css/style.css">
</head>
<body>
    <div class="form-container">
        <img src="http://localhost:4566/img-bucket/S3s/images/brand_logo.png" alt="Company Logo" class="logo">
        <h2 class="text-center mb-4">Login</h2>
        <form action="./loginLogic.php" method="POST">
            <div class="form-group">
                <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
            </div>
            <div class="form-group">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>
        <p class="mt-3 text-center">Don't have an account? <a href="signup.php">Sign up here</a></p>
        <?php
            // Check if error message exists in session and display it
            if (isset($_SESSION['login_error'])) {
                echo '<div class="alert alert-danger" role="alert">' . $_SESSION['login_error'] . '</div>';
                // Unset the error message to prevent it from showing again
                unset($_SESSION['login_error']);
            }
        ?>
    </div>
</body>
</html>
