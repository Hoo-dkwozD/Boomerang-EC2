<?php
// session_start();
require_once 'Database/db_config.php';

// Function to get product categories
function getProductCategories() {
    global $db;
    $query = $db->prepare("SELECT DISTINCT category FROM products");
    $query->execute();
    return $query->fetchAll(PDO::FETCH_COLUMN);
}

// Function to get products based on category
function getProducts($category) {
    global $db;
    $query = $db->prepare("SELECT * FROM products WHERE category = :category");
    $query->bindParam(':category', $category);
    $query->execute();
    return $query->fetchAll(PDO::FETCH_ASSOC);
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
