<?php
session_start();
require_once 'db_config.php';
require __DIR__ . "/vendor/autoload.php";

use Aws\Exception\AwsException;
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

            // TODO
            // update the products table with the reduced quantity
            // $query = $db->prepare("UPDATE products SET quantity = quantity - :quantity WHERE id = :id");
            // $query->bindParam(':quantity', $productQuantity);
            // $query->bindParam(':id', $productId);
            // $query->execute();

            try {
                $update_pdt_params = [
                    "TableName" => "products",
                    "Key" => [
                        "id" => ['N' => strval($productId)]
                    ],
                    "UpdateExpression" => "SET #Q = :q",
                    "ExpressionAttributeValues" => [
                        ":q" => ['M' => "['N' => strval($productQuantity)]"]
                    ],
                    "ExpressionAttributeNames" => [
                        "#Q" => "quantity"
                    ]
                ];

                $result = $db->updateItem(
                    $update_pdt_params
                );
            } catch (AwsException $e) {
                echo "Unable to update product quantity:\n";
                echo $e->getMessage() . "\n";
                exit();
            }

            // TODO
            // add purchase information into sales table
            $total_price = $productQuantity * $productPrice;
            $order_total_price += $total_price;
            // $query = $db->prepare("INSERT INTO sales (purchase_id, product_id, quantity_sold, total_price, sale_date) VALUES (:purchase_id, :product_id, :quantity_sold, :total_price, NOW())");
            // $query->bindParam(':purchase_id', $purchase_id);
            // $query->bindParam(':product_id', $productId);
            // $query->bindParam(':quantity_sold', $productQuantity);
            // $query->bindValue(':total_price', $total_price);
            // $query->execute();    
            
            try {
                $add_sale_params = [
                    "TableName" => "sales",
                    "Item" => [
                        "purchase_id" => ['N' => strval($purchase_id)],
                        "product_id" => ['N' => strval($productId)],
                        "quantity_sold" => ['N' => strval($productQuantity)],
                        "total_price" => ['N' => strval($total_price)],
                        "sale_date" => ['S' => strval(date("Y-m-d H:i:s"))]
                    ]
                ];

                $result = $db->putItem(
                    $add_sale_params
                );
            } catch (AwsException $e) {
                echo "Unable to add sale:\n";
                echo $e->getMessage() . "\n";
                exit();
            }
        }

        // TODO
        // get shipping address information from the database - not storing in SESSION for security reasons
        // $query = $db->prepare("SELECT shipping_address FROM users WHERE id = :id");
        // $query->bindParam(':id', $_SESSION["id"]);
        // $query->execute();
        // $user = $query->fetch(PDO::FETCH_ASSOC);

        try {
            $get_user_params = [
                "TableName" => "users",
                "Key" => [
                    "id" => ['N' => strval($_SESSION["id"])]
                ]
            ];

            $result = $db->getItem(
                $get_user_params
            );

            $user = $result['Item'];
        } catch (AwsException $e) {
            echo "Unable to get user:\n";
            echo $e->getMessage() . "\n";
            exit();
        }

        if ($user && isset($user['shipping_address']['M']['S']['S'])) {
            $shipping_address = $user['shipping_address']['M']['S']['S'];
            // TODO
            // add order information into order table
            // $query = $db->prepare("INSERT INTO orders (order_id, customer_id, total_amount, shipping_address, order_status) VALUES (:order_id, :customer_id, :total_amount, :shipping_address, :order_status)");
            // $query->bindParam(':order_id', $purchase_id);
            // $query->bindParam(':customer_id', $_SESSION["id"]);
            // $query->bindParam(':total_amount', $order_total_price);
            // $query->bindValue(':shipping_address', $shipping_address);
            // $query->bindValue(':order_status', 'received');
            // $query->execute();

            try {
                $add_order_params = [
                    "TableName" => "orders",
                    "Item" => [
                        "order_id" => ['N' => strval($purchase_id)],
                        "customer_id" => ['N' => strval($_SESSION["id"])],
                        "total_amount" => ['N' => strval($order_total_price)],
                        "shipping_address" => ['S' => $shipping_address],
                        "order_status" => ['S' => 'received']
                    ]
                ];

                $result = $db->putItem(
                    $add_order_params
                );
            } catch (AwsException $e) {
                echo "Unable to add order:\n";
                echo $e->getMessage() . "\n";
                exit();
            }
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
            "success_url" => "http://localhost/home.php",
            "line_items" => $line_items
        ]);

        http_response_code(303);
        header("Location: " . $checkout_session->url);
    }
}

function generatePurchaseId() {
    // TODO
    global $db;
    // $query = $db->query("SELECT MAX(purchase_id) as max_id FROM sales");
    // $row = $query->fetch(PDO::FETCH_ASSOC);
    // $latest_id = $row['max_id'];
    try {
        $get_max_purchase_id_params = [
            "TableName" => "sales",
            "ProjectionExpression" => "purchase_id",
            "Select" => "MAX"
        ];

        $result = $db->scan(
            $get_max_purchase_id_params
        );

        $latest_id = $result['Items'][0]['purchase_id']['N'];

        // Increment the latest_id to generate a new purchase_id
        $new_purchase_id = $latest_id + 1;
        return $new_purchase_id;
    } catch (AwsException $e) {
        return 'Unable to generate purchase_id';
    }
}
?>
