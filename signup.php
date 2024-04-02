<?php
session_start();

// If session variables are present, redirect to home.php
if (isset($_SESSION['login_status'])) {
    header("Location: home.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sign Up</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="http://localhost:4566/css-bucket/S3s/css/style.css">
</head>
<body>
    <div class="form-container">
    <img src="http://localhost:4566/img-bucket/S3s/images/brand_logo.png" alt="Company Logo" class="logo">
        <h2 class="text-center mb-4">Sign Up</h2>
        <form action="signupLogic.php" method="POST">
            <div class="form-group">
                <input type="text" class="form-control" id="new_username" name="new_username" placeholder="Username" required>
            </div>
            <div class="form-group">
                <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Password" required>
            </div>
            <div class="form-group">
                <input type="email" class="form-control" id="new_email" name="new_email" placeholder="Email" required>
            </div>
            <div class="form-group">
                <input type="text" class="form-control" id="new_address" name="new_address" placeholder="Shipping Address" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Sign Up</button>
        </form>
        <p class="mt-3 text-center">Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>
