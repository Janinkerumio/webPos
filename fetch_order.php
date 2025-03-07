<?php
include("navbar.html");
include("database.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background-color: #f4f4f9;
        }
        .container {
            margin-top: 50px;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
            transition: all 0.5s ease;
        }
        table {
            margin-top: 20px;
        }
        th, td {
            text-align: center;
            cursor: pointer; /* Added cursor pointer to all table columns */
        }
        .order-link {
            color: #0d6efd;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .order-link:hover {
            color: #0a58ca;
            text-decoration: underline;
        }
        #order-details-container {
            display: none;
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<h2 class="text-center text-primary">Completed Orders</h2>
<div class="table-responsive">
    <table class="table table-hover" id="orders-table">
        <thead class="table-dark">
            <tr>
                <th>Order ID</th>
                <th>Customer Name</th>
                <th>Table Number</th>
                <th>Total Price</th>
                <th>Order Date</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = $conn->query("SELECT id, customer_name, table_number, total_price, order_date FROM orders ORDER BY order_date DESC");
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr class='order-link' data-order-id='" . $row['id'] . "'>";
                    echo "<td>" . $row['id'] . "</td>";
                    echo "<td>" . htmlspecialchars($row['customer_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['table_number']) . "</td>";
                    echo "<td>" . $row['total_price'] . " pesos</td>";
                    echo "<td>" . $row['order_date'] . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5' class='text-center'>No Completed Orders Found!</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<div class="container" id="order-details-container">
    <div id="order-details" class="mt-4">
        <!-- Order Details Will Display Here -->
    </div>
</div>

<script>
$(document).ready(function() {
    $(document).on("click", "#orders-table tr", function() {
        var order_id = $(this).data("order-id");
        if (order_id) {
            $.ajax({
                url: "",
                type: "POST",
                data: { order_id: order_id },
                success: function(response) {
                    $("#orders-container").fadeOut(500, function() {
                        $("#order-details-container").fadeIn(500);
                        $("#order-details").html(response);
                        $("html, body").animate({ scrollTop: $("#order-details-container").offset().top }, 500);
                    });
                }
            });
        }
    });
});
</script>

</body>
</html>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = $_POST['order_id'];

    $stmt = $conn->prepare("SELECT o.customer_name, o.table_number, i.item_name, i.quantity, i.subtotal, o.total_price 
                            FROM orders o 
                            JOIN order_items i ON o.id = i.order_id 
                            WHERE o.id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        echo "<div class='text-center mb-3'>
                <h4>Customer Name: <b>" . htmlspecialchars($row['customer_name']) . "</b></h4>
                <h4>Table Number: <b>" . htmlspecialchars($row['table_number']) . "</b></h4>
              </div>";

        echo "<table class='table table-striped'>";
        echo "<thead class='table-dark'><tr>
                <th>Item Name</th>
                <th>Quantity</th>
                <th>Subtotal</th>
              </tr></thead><tbody>";

        $overall_total = $row['total_price'];

        do {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['item_name']) . "</td>";
            echo "<td>" . $row['quantity'] . "</td>";
            echo "<td>" . $row['subtotal'] . " pesos</td>";
            echo "</tr>";
        } while ($row = $result->fetch_assoc());

        echo "</tbody></table>";

        echo "<h4 class='text-success text-center mt-3'>Overall Total: <b>" . $overall_total . " pesos</b></h4>";
    } else {
        echo "<div class='alert alert-danger text-center'>No order found with this ID!</div>";
    }
}
?>
