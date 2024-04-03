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
            $categories[] = $item['category']['M']['S']['S'];
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
            'TableName' => 'products'
        ]);

        $pdts = [];
        foreach ($result['Items'] as $item) {
            if ($item['category']['M']['S']['S'] == $category) {
                $pdts[] = [
                    'id' => $item['id']['N'],
                    'name' => $item['name']['M']['S']['S'],
                    'category' => $item['category']['M']['S']['S'],
                    'price' => $item['price']['M']['N']['N'],
                    'description' => $item['description']['M']['S']['S'],
                    'image_filename' => $item['image_filename']['M']['S']['S'],
                    'quantity' => $item['quantity']['M']['N']['N']
                ];
            }
        }

        return $item;
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
