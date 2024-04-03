<?php
session_start();
require_once 'db_config.php';
require __DIR__ . "/vendor/autoload.php";

use Aws\Exception\AwsException;

// checks request method. If "POST", form in login.php was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["new_username"];
    $email = $_POST["new_email"];
    $password = $_POST["new_password"];
    $shipping_address = $_POST["new_address"];

    // TODO
    // check if username already exists
    // $query = $db->prepare("SELECT * FROM users WHERE username = :username");
    // $query->bindParam(':username', $username);
    
    // executes query prepared and fetch results as an associative array
    // $query->execute();
    // $user = $query->fetch(PDO::FETCH_ASSOC);

    try {
        $result = $db->scan([
            'TableName' => 'users',
            'FilterExpression' => 'username = :username AND email = :email',
            'ExpressionAttributeValues' => [
                ':username' => ['S' => $username],
                ':email' => ['S' => $email]
            ]
        ]);
        $user = count($result['Item']) > 0 ? $result['Item'][0] : NULL;
    } catch (AwsException $e) {
        $user = NULL;
    }

    // if user exists in db
    if ($user) {
        // get all signup errors to show  user
        $signup_error = array();

        if ($user['username']['S'] == $username) {
            array_push($signup_error, "Username already exists");
        }
        if ($user['email']['S'] == $email) {
            array_push($signup_error, "Email already exists");
        }

        $_SESSION['signup_error'] = $signup_error;
        header("Location: signup.php");
        exit();
    }

    // TODO
    // insert new user in database if username and email do not exist
    // $query = $db->prepare("INSERT INTO users (username, email, password, shipping_address) VALUES (:username, :email, :password, :shipping_address)");
    // $query->bindParam(':username', $username);
    // $query->bindParam(':email', $email);
    // $query->bindParam(':password', $password);
    // $query->bindParam(':shipping_address', $shipping_address);

    // executes query prepared by creating new user
    // $query->execute();

    // get last id from users table
    // $user_id = $db->lastInsertId();

    // Get max user id from users table
    try {
        $result = $db->scan([
            'TableName' => 'users',
            'ProjectionExpression' => 'id',
        ]);

        $max_id = 0;
        foreach ($result['Items'] as $item) {
            $max_id = max($max_id, intval($item['id']['N']));
        }
        $max_id++;
    } catch (AwsException $e) {
        echo "Unable to get user count:\n";
        echo $e->getMessage() . "\n";
    }

    try {
        $result = $db->putItem([
            'TableName' => 'users',
            'Item' => [
                'id' => ['N' => strval($max_id)],
                'username' => ['S' => $username],
                'email' => ['S' => $email],
                'password' => ['S' => $password],
                'shipping_address' => ['S' => $shipping_address],
                'role' => ['S' => 'customer']
            ]
        ]);
        $user_id = $max_id;
    } catch (AwsException $e) {
        echo "Unable to add user:\n";
        echo $e->getMessage() . "\n";
    }

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
