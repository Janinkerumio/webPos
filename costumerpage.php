<?php
include("database.php");

$sql = "SELECT * FROM items_stock";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

$foodItems = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $foodItems[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Ordering System</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #fff3e0;
        }
        #menu {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            padding: 20px;
        }
        .food-item {
            width: 200px;
            padding: 10px;
            cursor: pointer;
            transition: transform 0.3s;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }
        .food-item:hover {
            transform: scale(1.1);
        }
        .food-item img {
            width: 100%;
            height: auto;
            border-radius: 10px;
        }
        #details-container, #customer-form {
        display: none; /* Hidden by default */
        padding: 25px;
        background: white;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3); /* Softer shadow */
        border-radius: 12px;
        text-align: left;
        z-index: 2;
        max-width: 90%;
        max-height: 80vh; /* Limit height for smaller screens */
        overflow: auto; /* Scroll if content is too big */
        box-sizing: border-box;
        animation: fadeIn 0.3s ease-in-out; /* Smooth fade-in effect */
}

        #overlay {
            display: none; /* Hidden by default */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4); /* Dark overlay */
            backdrop-filter: blur(5px); /* Blur background for focus effect */
            z-index: 1;
}

        .show {
            display: block !important;
        }
        button {
            background: green;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            transition: background 0.3s;
        }
        button:hover {
            background: darkgreen;
        }
        .close {
        position: absolute;
        top: 10px;
        right: 15px;
        font-size: 20px;
        background: red;
        border: none;
        cursor: pointer;
        transition: color 0.3s;
    }
        .close:hover {
        background-color:rgb(245, 85, 36);
}
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: center;
        }
        #details-image {
            width: 150px;
            height: auto;
            display: block;
            margin: 0 auto;
            object-fit: contain;
            transition: transform 0.2s ease-in-out;
        }
            @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translate(-50%, -55%);
        }
        to {
            opacity: 1;
            transform: translate(-50%, -50%);
        }
}
        @media (max-width: 768px) {
    table img {
        width: 60px; /* Smaller image for mobile */
    }
}

    </style>
</head>
<body>
<h1>Food Ordering System</h1>
<div id="menu">
    <?php foreach ($foodItems as $item) { ?>
        <div class="food-item" onclick="show_details('<?php echo $item['id']; ?>', '<?php echo $item['item_name']; ?>', <?php echo $item['price']; ?>, '<?php echo $item['image']; ?>')">
            <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['item_name']; ?>">
            <p><?php echo $item['item_name']; ?> - <?php echo $item['price']; ?> pesos</p>
        </div>
    <?php } ?>
</div>

<div id="details-container">
    <button class="close" onclick="closeDetails()">X</button>
    <img id="details-image" src="" alt="Item Image">
    <h3 id="item-name"></h3>
    <p>Price: <span id="item-price"></span> pesos</p>
    <input type="number" id="quantity" min="1" value="1">
    <button onclick="addToCart()">Add to Cart</button>
</div>

<div id="cart-container">
    <h2>Your Cart (<span id="cart-count">0</span>)</h2>
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Quantity</th>
                <th>Total</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="cart"></tbody>
    </table>
    <p id="total-price">Total: 0 pesos</p>
    <button onclick="placeOrder()">Place Order</button>
</div>

<div id="customer-form">
    <button class="close" onclick="closeCustomerForm()">X</button>
    <h2>Customer Information</h2>
    <input type="text" id="customer-name" placeholder="Enter Name"><br>
    <label>What table you are sitting?</label>
    <select id="table-number">
        <option>No Specified Table</option>
        <?php for ($i = 1; $i <= 5; $i++) { ?>
            <option>Table <?php echo $i; ?></option>
        <?php } ?>
    </select>
    <p id="form-total-price">Total: 0 pesos</p>
    <button id="submit_order" onclick="submitOrder()">Order Now</button>
</div>

<script>
    let cart = [];

    window.onclick = function(event) {
    if (event.target == document.getElementById("overlay")) {
        closeDetails();
        closeCustomerForm();
    }
}

    function show_details(id, name, price, image) {
        document.getElementById("item-name").innerText = name;
        document.getElementById("item-price").innerText = price;
        document.getElementById("details-image").src = image;
        document.getElementById("details-container").dataset.id = id;
        document.getElementById("details-container").classList.add("show");
    }

    function closeDetails() {
        document.getElementById("details-container").classList.remove("show");
    }

    function addToCart() {
        let id = document.getElementById("details-container").dataset.id;
        let name = document.getElementById("item-name").innerText;
        let price = parseFloat(document.getElementById("item-price").innerText);
        let quantity = parseInt(document.getElementById("quantity").value);

        if (quantity <= 0) {
            alert("Invalid Quantity!");
            return;
        }

        cart.push({ id, name, price, quantity });
        document.getElementById("quantity").value = 1;
        updateCart();
        closeDetails();
    }

    function updateCart() {
        let total = 0;
        $('#cart').empty();
        cart.forEach((item, index) => {
            let row = `<tr>
                <td>${item.name}</td>
                <td>${item.quantity}</td>
                <td>${item.price * item.quantity} pesos</td>
                <td><button onclick="removeItem(${index})">Remove</button></td>
            </tr>`;
            $('#cart').append(row);
            total += item.price * item.quantity;
        });
        $('#total-price').text(`Total: ${total} pesos`);
        $('#form-total-price').text(`Total: ${total} pesos`);
        $("#cart-count").text(cart.lenght);
    }

    function removeItem(index) {
        Swal.fire({
        title: "Are you sure?",
        text: "You want to remove this item?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, Remove!",
        cancelButtonText: "No, Cancel"
    }).then((result) => {
        if (result.isConfirmed) {
            cart.splice(index, 1);
            updateCart();
        }
    });
    }

    function placeOrder() {
        document.getElementById("form-total-price").innerText = document.getElementById("total-price").innerText;
        document.getElementById("customer-form").classList.add("show");
    }

    function closeCustomerForm() {
        document.getElementById("customer-form").classList.remove("show");
    }
    function submitOrder() {
    let name = document.getElementById("customer-name").value;
    let table = document.getElementById("table-number").value;

    if (name === "") {
        alert("Please Enter Customer Name!");
        return;
    }
}
function submitOrder() {
    let name = document.getElementById("customer-name").value;
    let table = document.getElementById("table-number").value;

    if (name === "") {
        Swal.fire({
            icon: 'warning',
            title: 'Oops...',
            text: 'Please enter customer name!',
        });
        return;
    }

    if (cart.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Cart is Empty!',
            text: 'Please add items before placing the order.',
        });
        return;
    }

    let total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);

    $.ajax({
        url: "orders.php",
        method: "POST",
        data: {
            customer_name: name,
            table_number: table,
            cart_items: JSON.stringify(cart),
            total_price: total
        },
        dataType: "json",
        success: function(response) {
            Swal.fire({
                title: "Order Placed Successfully!",
                icon: "success",
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                cart = []; // Clear Cart
                updateCart(); // Refresh Cart
                closeCustomerForm(); // Close Modal
                $("#customer-name").val(""); // Clear Name
            });
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Something went wrong!',
            });
        }
    });
}

</script>
</body>
</html>