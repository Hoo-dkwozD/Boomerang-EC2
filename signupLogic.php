<?php
session_start();
require_once 'Database/db_config.php';

// checks request method. If "POST", form in login.php was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["new_username"];
    $email = $_POST["new_email"];
    $password = $_POST["new_password"];
    $shipping_address = $_POST["new_address"];

    // check if username already exists
    $query = $db->prepare("SELECT * FROM users WHERE username = :username");
    $query->bindParam(':username', $username);
    
    // executes query prepared and fetch results as an associative array
    $query->execute();
    $user = $query->fetch(PDO::FETCH_ASSOC);

    // if user exists in db
    if ($user) {
        
        // get all signup errors to show  user
        $signup_error = array();

        if ($user['username'] == $username) {
            array_push($signup_error, "Username already exists");
        }
        if ($user['email'] == $email) {
            array_push($signup_error, "Username already exists");
        }

        $_SESSION['signup_error'] = $signup_error;
        
        header("Location: signup.php");
        exit();
    }

    // insert new user in database if username and email do not exist
    $query = $db->prepare("INSERT INTO users (username, email, password, shipping_address) VALUES (:username, :email, :password, :shipping_address)");
    $query->bindParam(':username', $username);
    $query->bindParam(':email', $email);
    $query->bindParam(':password', $password);
    $query->bindParam(':shipping_address', $shipping_address);

    // executes query prepared by creating new user
    $query->execute();

    // get last id from users table
    $user_id = $db->lastInsertId();

    // populate session for successful signup
    $_SESSION['id'] = $user_id;
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;
    $_SESSION['role'] = 'customer';
    $_SESSION['login_status'] = TRUE;

    header("Location: home.php");
    exit();
}
?>
