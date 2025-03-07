<?php
header('Content-Type: application/json');
include("database.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_name = $_POST['customer_name'];
    $table_number = $_POST['table_number'];
    $cart_items = json_decode($_POST['cart_items'], true);
    $total_price = $_POST['total_price'];
    $order_date = date("Y-m-d H:i:s");

    $stmt = $conn->prepare("INSERT INTO orders (customer_name, table_number, total_price, order_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssds", $customer_name, $table_number, $total_price, $order_date);
    if ($stmt->execute()) {
        $order_id = $stmt->insert_id;

        foreach ($cart_items as $item) {
            $item_name = $item['name'];
            $quantity = $item['quantity'];
            $price = $item['price'];
            $subtotal = $price * $quantity;

            $stmt_items = $conn->prepare("INSERT INTO order_items (order_id, item_name, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
            $stmt_items->bind_param("issdd", $order_id, $item_name, $quantity, $price, $subtotal);
            $stmt_items->execute();
        }

        // Insert to Sales Report
        $stmt_sales = $conn->prepare("INSERT INTO sales_report (customer_name, table_number, total_price, order_date) VALUES (?, ?, ?, ?)");
        $stmt_sales->bind_param("ssds", $customer_name, $table_number, $total_price, $order_date);
        $stmt_sales->execute();

        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
}

mysqli_close($conn);
?>
