<?php
require_once 'homeLogic.php';

populateCartSession();

if (isset($_SESSION["cart"])) {
    $cartItems = $_SESSION["cart"];
} else {
    $cartItems = array();
}
?>

<!-- Cart Modal -->
<div id="cartModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="cartModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cartModalLabel">Cart</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php foreach ($cartItems as $index => $item) { ?>
                    <div id="<?php echo $index; ?>" class="item-card">
                        <h5><?php echo $item[1]; ?></h5>
                        <h5>$<?php echo $item[2]; ?></h5>
                        <h5><?php echo $item[3]; ?></h5>
                        <!-- send in the index to the removeItem() function when delete btn is clicked -->
                        <button type="button" class="btn btn-danger" onClick="removeItem(<?php echo $index; ?>)">Delete Item</button>
                    </div>
                <?php } ?>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <form action="./checkout.php" method="POST">
                    <button type="submit" class="btn btn-primary">Checkout</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- removeItem from cart functionality -->
<script>
    function removeItem(index) {
        // send AJAX request to update session inn removeCartItem.php
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "removeCartItem.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onload = function() {
            if (xhr.status === 200) {
                // if information has been passed successfully, delete the HTML elem from the UI
                var itemElement = document.getElementById(index);
                if (itemElement) {
                    itemElement.remove();
                }
            }
        };
        xhr.send("index=" + index);
    }
</script>