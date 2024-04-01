<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_SESSION["cart"])) {
        $index = $_POST["index"];
        unset($_SESSION["cart"][$index]);
        // re-index the array after removing an item
        $_SESSION["cart"] = array_values($_SESSION["cart"]);
    }
}
?>
