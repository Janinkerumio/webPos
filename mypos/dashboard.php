<?php
include("database.php");
include("navbar.html");
$sql = "SELECT * FROM sales_report ORDER BY order_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sales Report</title>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h1>Sales Report</h1>
<table>
    <thead>
        <tr>
            <th>Customer Name</th>
            <th>Table Number</th>
            <th>Total Price</th>
            <th>Order Date</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['customer_name']; ?></td>
                <td><?php echo $row['table_number']; ?></td>
                <td><?php echo $row['total_price']; ?> pesos</td>
                <td><?php echo $row['order_date']; ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>
</body>
</html>
