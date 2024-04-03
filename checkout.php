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

            try {
                $get_pdt_params = [
                    "TableName" => "products",
                    "Key" => [
                        "id" => ['N' => strval($productId)]
                    ]
                ];

                $result = $db->getItem(
                    $get_pdt_params
                );

                $target_product = $result['Item'];
            } catch (AwsException $e) {
                echo "Unable to get product:\n";
                echo $e->getMessage() . "\n";
            }

            try {
                $update_pdt_params = [
                    "TableName" => "products",
                    "Key" => [
                        "id" => ['N' => strval($productId)]
                    ],
                    "UpdateExpression" => "SET #Q = :q",
                    "ExpressionAttributeValues" => [
                        ":q" => ['N' => strval(intval($target_product['quantity']['N']) - $productQuantity)]
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
            
            // Get max sales id
            try {
                $get_max_sales_id_params = [
                    "TableName" => "sales",
                    "ProjectionExpression" => "id"
                ];

                $result = $db->scan(
                    $get_max_sales_id_params
                );
                $maxValue = 0;
                foreach ($result['Items'] as $item) {
                    $value = intval($item['id']['N']);
                    $maxValue = max($maxValue, $value);
                }
                $sales_id = $maxValue + 1;
            } catch (AwsException $e) {
                echo "Unable to get max sales id:\n";
                echo $e->getMessage() . "\n";
            }

            try {
                $add_sale_params = [
                    "TableName" => "sales",
                    "Item" => [
                        "id" => ['N' => strval($sales_id)],
                        "purchase_id" => ['N' => strval($purchase_id)],
                        "product_id" => ['N' => strval($productId)],
                        "quantity_sold" => ['N' => strval($productQuantity)],
                        "total_price" => ['N' => strval($total_price * 100)],
                        "sale_date" => ['S' => strval(date("Y-m-d H:i:s"))]
                    ]
                ];

                $result = $db->putItem(
                    $add_sale_params
                );
            } catch (AwsException $e) {
                echo "Unable to add sale:\n";
                echo $e . "\n";
                exit();
            }
        }

        // TODO
        // get shipping address information from the database - not storing in SESSION for security reasons

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

        if ($user && isset($user['shipping_address']['S'])) {
            $shipping_address = $user['shipping_address']['S'];
            // add order information into order table

            // Get max order id
            try {
                $get_max_order_id_params = [
                    "TableName" => "orders",
                    "ProjectionExpression" => "id"
                ];

                $result = $db->scan(
                    $get_max_order_id_params
                );
                $maxValue = 0;
                foreach ($result['Items'] as $item) {
                    $value = intval($item['id']['N']);
                    $maxValue = max($maxValue, $value);
                }
                $order_id = $maxValue + 1;
            } catch (AwsException $e) {
                echo "Unable to get max order id:\n";
                echo $e->getMessage() . "\n";
            }

            try {
                $add_order_params = [
                    "TableName" => "orders",
                    "Item" => [
                        "id" => ['N' => strval($order_id)],
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
    $get_max_purchase_id_params = [
        "TableName" => "sales",
        "ProjectionExpression" => "purchase_id"
    ];

    $result = $db->scan(
        $get_max_purchase_id_params
    );
    $maxValue = 0;
    foreach ($result['Items'] as $item) {
        $value = intval($item['purchase_id']['N']);
        $maxValue = max($maxValue, $value);
    }

    // Increment the latest_id to generate a new purchase_id
    $new_purchase_id = $maxValue + 1;
    return $new_purchase_id;
}
?>
