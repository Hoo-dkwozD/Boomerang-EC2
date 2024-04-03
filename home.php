<?php
session_start();
// Check if customer is logged in, if not redirect to login page
if (!isset($_SESSION['login_status']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}

require_once 'homeLogic.php';

// Get product categories and default currentCategory to 'Smart TV'
$categories = getProductCategories();
$currentCategory = isset($_GET['category']) ? $_GET['category'] : 'Smart TV';

// Get products for the current category
$products = getProducts($currentCategory);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="http://localhost:4566/css-bucket/S3s/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#"><img src="http://localhost:4566/img-bucket/S3s/images/brand_logo.png" alt="Company Logo" class="logo"></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav w-100">
                <?php foreach ($categories as $category) { ?>
                    <li class="nav-item <?php echo $category == $currentCategory ? 'active' : ''; ?>">
                        <a class="nav-link" href="?category=<?php echo $category; ?>"><?php echo $category; ?></a>
                    </li>
                <?php } ?>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="#" data-toggle="modal" data-target="#userModal">
                        <i class="fas fa-user profile-icon"></i>                    
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-toggle="modal" data-target="#cartModal">
                        <i class="fa-solid fa-cart-shopping"></i>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- User Profile Modal -->
    <?php include 'userModal.php'; ?>

    <!-- Cart Modal -->
    <?php include 'cartModal.php'; ?>
</head>

<body>
    <!-- Product List -->
    <div class="container mt-4">
        <h1><?php echo (gettype($products[0]['quantity'])); ?></h1>
        <div class="row">
            <?php foreach ($products as $product) { ?>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="item-card">
                        <img src="http://localhost:4566/img-bucket/S3s/images/<?php echo $product['image_filename']; ?>" alt="<?php echo $product['name']; ?>" class="img-fluid">
                        <h5><?php echo $product['name']; ?></h5>
                        <p><?php echo $product['description']; ?></p>
                        <p>$<?php echo $product['price']; ?></p>
                        <p>Units available: <?php echo $product['quantity']; ?></p>
                        <form class="addToCartForm" method="post">
                            <input type="hidden" name="productId" value="<?php echo $product['id']; ?>">
                            <input type="hidden" name="productName" value="<?php echo $product['name']; ?>">
                            <input type="hidden" name="productPrice" value="<?php echo $product['price']; ?>">
                            <input type="number" name="productQuantity" value="1" min="1" max="<?php echo $product['quantity']; ?>">
                            <button type="submit" class="btn btn-primary" name="addToCart">Add to Cart</button>
                        </form>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <!-- this is to submit the form asynchronously so that users won't be redirected to homelogic.php  -->
    <script> 
        document.getElementById("addToCartForm").addEventListener("submit", function (event) {
            event.preventDefault(); // prevent default form submission

            var form = this;
            var formData = new FormData(form);

            // send a POST request to homeLogic.php
            var xhr = new XMLHttpRequest();
            xhr.open("POST", form.action, true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    // not priority but code to update UI on item added goes here:
                }
            };
            // send the formData over
            xhr.send(formData);
        });
    </script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
