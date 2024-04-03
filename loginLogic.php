<?php
session_start();
require_once 'db_config.php';
require __DIR__ . "/vendor/autoload.php";

use Aws\Exception\AwsException;

// checks request method. If "POST", form in login.php was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // TODO
    // $query = $db->prepare("SELECT * FROM users WHERE username = :username AND password = :password");
    // $query->bindParam(':username', $username);
    // $query->bindParam(':password', $password);
    
    // executes query prepared and fetch results as an associative array
    // $query->execute();
    // $user = $query->fetch(PDO::FETCH_ASSOC);

    try {
        $result = $db->scan([
            'TableName' => 'users'
        ]);
        foreach ($result['Items'] as $user) {
            print_r($user['username']['M']);
        }
        exit();
    } catch (AwsException $e) {
        $user = NULL;
    }

    // if user exists in db
    if ($user) {
        // check if user is an admin or customer - if admin, go to orderManagement.php else home.php
        if ($user['role']['M']['S'] == 'admin') {
            $_SESSION['id'] = $user['id'];
            $_SESSION['username'] = $user['username']['M']['S'];
            $_SESSION['email'] = $user['email']['M']['S'];
            $_SESSION['role'] = 'admin';
            $_SESSION['login_status'] = TRUE;
            header("Location: dashboard.php");
            exit();
        } else {
            $_SESSION['id'] = $user['id'];
            $_SESSION['username'] = $user['username']['M']['S'];
            $_SESSION['email'] = $user['email']['M']['S'];
            $_SESSION['role'] = 'customer';
            $_SESSION['login_status'] = TRUE;
            header("Location: home.php");
            exit();
        }
    } else {
        // set the error message in session variable
        $_SESSION['login_error'] = "Invalid username or password";
        header("Location: login.php");
        exit();
    }
}
?>
