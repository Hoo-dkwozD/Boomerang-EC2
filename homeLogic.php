<?php
// session_start();
require_once 'db_config.php';
require __DIR__ . "/vendor/autoload.php";

use Aws\Exception\AwsException;

// Function to get product categories
function getProductCategories() {
    // TODO
    global $db;
    try {
        $result = $db->scan([
            'TableName' => 'products',
            'ProjectionExpression' => 'category',
            'Select' => 'SPECIFIC_ATTRIBUTES'
        ]);

        $categories = [];
        foreach ($result['Items'] as $item) {
            $categories[] = $item['category']['S'];
        }
        return array_unique($categories);
    } catch (AwsException $e) {
        return [];
    }
}

// Function to get products based on category
function getProducts($category) {
    // TODO
    global $db;
    try {
        $result = $db->scan([
            'TableName' => 'products'
        ]);

        $pdts = [];
        foreach ($result['Items'] as $item) {
            if ($item['category']['S'] == $category) {
                $pdts[] = [
                    'id' => intval($item['id']['N']),
                    'name' => $item['name']['S'],
                    'category' => $item['category']['S'],
                    'price' => (intval($item['price']['N']) / 100),
                    'description' => $item['description']['S'],
                    'image_filename' => $item['image_filename']['S'],
                    'quantity' => intval($item['quantity']['N'])
                ];
            }
        }

        return $pdts;
    } catch (AwsException $e) {
        return [];
    }
}

// When POST request comes in, add all the items into cart session
function populateCartSession() {    
    // if receive request from home.php, add it to session
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // var_dump($_POST);
        $productId = intval($_POST["productId"]);
        $productName = $_POST["productName"];
        $productPrice = floatval($_POST["productPrice"]);
        $productQuantity = intval($_POST["productQuantity"]);

        // check if the productQuantity is valid
        if ($productQuantity > 0 && $productQuantity <= $_POST["productQuantity"]) {
            $_SESSION["cart"][] = array($productId, $productName, $productPrice, $productQuantity);
        }
    }
}
?>
