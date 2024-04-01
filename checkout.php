<?php
session_start();
require_once 'Database/db_config.php';
require __DIR__ . "/vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

$stripe_secret_key = "sk_test_51OztNs058WdMN6mxGjPTyPjp0xgS2TTpPUIpMwW4GDnlrPHFJEbHLHCW2p9y5y3BEpQS28KChAR7sjDTJ79z2PW800rQPIJBUy";

\Stripe\Stripe::setApiKey($stripe_secret_key);

// check if post request was sent
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_SESSION["cart"]) && !empty($_SESSION["cart"])) {
        // generate a unique purchase_id for each purchase
        $purchase_id = generatePurchaseId();
        $order_total_price = 0;
        $line_items = [];
        foreach ($_SESSION["cart"] as $item) {
            $productId = $item[0];
            $productName = $item[1];
            $productPrice = $item[2];
            $productQuantity = $item[3];

            $line_items[] = [
                "quantity" => $productQuantity,
                "price_data" => [
                    "currency" => "usd",
                    "unit_amount" => $productQuantity*$productPrice*100,
                    "product_data" => [
                        "name" => $productName
                    ]
                ]
            ];

            // update the products table with the reduced quantity
            $query = $db->prepare("UPDATE products SET quantity = quantity - :quantity WHERE id = :id");
            $query->bindParam(':quantity', $productQuantity);
            $query->bindParam(':id', $productId);
            $query->execute();

            // add purchase information into sales table
            $total_price = $productQuantity * $productPrice;
            $order_total_price += $total_price;
            $query = $db->prepare("INSERT INTO sales (purchase_id, product_id, quantity_sold, total_price, sale_date) VALUES (:purchase_id, :product_id, :quantity_sold, :total_price, NOW())");
            $query->bindParam(':purchase_id', $purchase_id);
            $query->bindParam(':product_id', $productId);
            $query->bindParam(':quantity_sold', $productQuantity);
            $query->bindValue(':total_price', $total_price);
            $query->execute();            
        }

         // get shipping address information from the database - not storing in SESSION for security reasons
         $query = $db->prepare("SELECT shipping_address FROM users WHERE id = :id");
         $query->bindParam(':id', $_SESSION["id"]);
         $query->execute();
         $user = $query->fetch(PDO::FETCH_ASSOC);

        if ($user && isset($user['shipping_address'])) {
            $shipping_address = $user['shipping_address'];
            // add order information into order table
            $query = $db->prepare("INSERT INTO orders (order_id, customer_id, total_amount, shipping_address, order_status) VALUES (:order_id, :customer_id, :total_amount, :shipping_address, :order_status)");
            $query->bindParam(':order_id', $purchase_id);
            $query->bindParam(':customer_id', $_SESSION["id"]);
            $query->bindParam(':total_amount', $order_total_price);
            $query->bindValue(':shipping_address', $shipping_address);
            $query->bindValue(':order_status', 'received');
            $query->execute();
        }

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
        $mail->addAddress($_SESSION['email'], $_SESSION['username']);

        $mail->Subject = "Order Number: " . $purchase_id . " has been received.";
        $mail->Body = "Your order has been received.";

        $mail->send();

        // remove all items from the cart session
        unset($_SESSION["cart"]);

        // checkout on Stripe
        $checkout_session = \Stripe\Checkout\Session::create([
            "mode" => "payment",
            "success_url" => "http://localhost/e-commerce/home.php",
            "line_items" => $line_items
        ]);

        http_response_code(303);
        header("Location: " . $checkout_session->url);
    }
}

function generatePurchaseId() {
    global $db;
    $query = $db->query("SELECT MAX(purchase_id) as max_id FROM sales");
    $row = $query->fetch(PDO::FETCH_ASSOC);
    $latest_id = $row['max_id'];
    // Increment the latest_id to generate a new purchase_id
    $new_purchase_id = $latest_id + 1;
    return $new_purchase_id;
}
?>
