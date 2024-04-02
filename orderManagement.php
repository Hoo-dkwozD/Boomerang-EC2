<?php
session_start();
// if user is logged in and is an admin, if not redirect to login page
if (!isset($_SESSION['login_status']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'Database/db_config.php';

require __DIR__ . "/vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

// update order status if "Order Sent" button is clicked
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["order_id"])) {
    $order_id = $_POST["order_id"];
    if ($_POST["order_status"] == "order_sent") {
        $query = $db->prepare("UPDATE orders SET order_status = 'order_sent' WHERE order_id = :order_id");
        $query->bindParam(':order_id', $order_id);
        $query->execute();

        // send email to customer on new order_status
        $mail = new PHPMailer(true);

        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->Username = "isemail345@gmail.com";
        $mail->Password = "mpms eons xtrc clhn";

        $mail->setFrom("Guganesh99@gmail.com", "Boomerang Electronics");

        // get user email from user table
        $query = $db->prepare("SELECT email, username FROM users WHERE id IN (SELECT customer_id FROM orders WHERE order_id = :order_id)");
        $query->bindParam(':order_id', $order_id);
        $query->execute();
        $user = $query->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $mail->addAddress($user['email'], $user['username']);
        }

        $mail->Subject = "Order Number: " . $order_id . " has been sent.";
        $mail->Body = "Your order is on its way to you.";

        $mail->send();

    } else if ($_POST["order_status"] == "resolved") {
        $query = $db->prepare("UPDATE orders SET order_status = 'resolved' WHERE order_id = :order_id");
        $query->bindParam(':order_id', $order_id);
        $query->execute();

        // send email to customer on new order_status
        $mail = new PHPMailer(true);

        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->Username = "isemail345@gmail.com";
        $mail->Password = "mpms eons xtrc clhn";

        $mail->setFrom("Guganesh99@gmail.com", "Boomerang Electronics");

        // get user email from the user table
        $query = $db->prepare("SELECT email, username FROM users WHERE id IN (SELECT customer_id FROM orders WHERE order_id = :order_id)");
        $query->bindParam(':order_id', $order_id);
        $query->execute();
        $user = $query->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $mail->addAddress($user['email'], $user['username']);
        }

        $mail->Subject = "Order Number: " . $order_id . " has been resolved.";
        $mail->Body = "Your order has been resolved. Please do contact us at 12345678 should you have any queries.";

        $mail->send();
    }
}

// get orders from the database
$query = $db->prepare("SELECT order_id, customer_id, order_date, total_amount, order_status FROM orders");
$query->execute();
$orders = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management</title>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#"><img src="http://localhost:4566/img-bucket/S3s/images/brand_logo.png" alt="Company Logo" class="logo" style="height: 30px;"></a>
        <a class="nav-link text-white bg-info" href="dashboard.php" style="border-radius:5px; height: 40px;">Admin Dashboard</a>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="#" data-toggle="modal" data-target="#userModal">
                    <i class="fas fa-user profile-icon"></i>                    
                </a>
            </li>
        </ul>
    </nav>
    
    <!-- user profile modal -->
    <?php include 'userModal.php'; ?>

    <h1>Order Management</h1>
    <table border="1">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer ID</th>
                <th>Order Date</th>
                <th>Total Amount</th>
                <th>Order Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order) { ?>
                <tr>
                    <td><?php echo $order['order_id']; ?></td>
                    <td><?php echo $order['customer_id']; ?></td>
                    <td><?php echo $order['order_date']; ?></td>
                    <td><?php echo $order['total_amount']; ?></td>
                    <td><?php echo $order['order_status']; ?></td>
                    <td>
                        <?php if ($order['order_status'] !== 'order_sent' && $order['order_status'] !== 'resolved') { ?>
                            <form action="" method="POST">
                                <input type="hidden" name="order_status" value="order_sent">
                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                <button type="submit">Order Sent</button>
                            </form>
                        <?php } ?>
                        <?php if ($order['order_status'] !== 'resolved' && $order['order_status'] === 'order_sent') { ?>
                            <form action="" method="POST">
                                <input type="hidden" name="order_status" value="resolved">
                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                <button type="submit">Resolved</button>
                            </form>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</body>
</html>