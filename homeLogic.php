<?php
// session_start();
require_once 'db_config.php';
require __DIR__ . "/vendor/autoload.php";

use Aws\Exception\AwsException;

// Function to get product categories
function getProductCategories() {
    // TODO
    global $db;
    // $query = $db->prepare("SELECT DISTINCT category FROM products");
    // $query->execute();
    // return $query->fetchAll(PDO::FETCH_COLUMN);
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
    // $query = $db->prepare("SELECT * FROM products WHERE category = :category");
    // $query->bindParam(':category', $category);
    // $query->execute();
    // return $query->fetchAll(PDO::FETCH_ASSOC);
    try {
        $result = $db->scan([
            'TableName' => 'products',
            'FilterExpression' => 'category = :category',
            'ExpressionAttributeValues' => [
                ':category' => ['S' => $category]
            ]
        ]);

        return $result['Items'];
    } catch (AwsException $e) {
        return [];
    }
}

// When POST request comes in, add all the items into cart session
function populateCartSession() {    
    // if receive request from home.php, add it to session
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // var_dump($_POST);
        $productId = $_POST["productId"];
        $productName = $_POST["productName"];
        $productPrice = $_POST["productPrice"];
        $productQuantity = $_POST["productQuantity"];

        // check if the productQuantity is valid
        if ($productQuantity > 0 && $productQuantity <= $_POST["productQuantity"]) {
            $_SESSION["cart"][] = array($productId, $productName, $productPrice, $productQuantity);
        }
    }
}
?>
