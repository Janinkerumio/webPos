<?php
    include("database.php");
    include("navbar.html");

    $sql = "SELECT * FROM items_stock";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die("Query failed: " . mysqli_error($conn));
    }

    if(mysqli_num_rows($result) == 0){
        $sql = "ALTER TABLE items_stock AUTO_INCREMENT = 1";
        $conn->query($sql);
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Management - List of Items</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f3f4f6;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            height: 100vh;
            padding: 20px;
            margin: 0;
            color: #333;
        }

        h2 {
            margin-bottom: 20px;
            color: #333;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-align: center;
        }

        table {
            width: 90%;
            max-width: 1000px;
            margin-bottom: 40px;
            background: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        th, td {
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background: #FF8C00;
            color: white;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        tr:hover {
            background: #f9f9f9;
            transition: background 0.3s ease;
        }

        img {
            width: 80px;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .container {
            width: 100%;
            max-width: 400px;
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-bottom: 30px;
        }

        .labeltable {
            font-size: 22px;
            font-weight: bold;
            background: #FF8C00;
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        input, button {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            outline: none;
            transition: 0.3s ease;
        }

        button {
            background: #FF8C00;
            color: white;
            border: none;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background:rgb(107, 109, 108);
        }

        .delete-btn {
            background: #dc3545;
        }

        .delete-btn:hover {
            background: #ff4d4d;
        }

        #preview {
            max-width: 150px;
            margin-top: 10px;
            border: 1px solid #ccc;
            padding: 5px;
            border-radius: 5px;
            display: block;
        }
    </style>
</head>
<body>

<h2>List of Items</h2>
<?php if (mysqli_num_rows($result) > 0): ?>
<table>
    <tr>
        <th>Item Name</th>
        <th>Price</th>
        <th>Image</th>
        <th></th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr id="row-<?php echo $row['id']; ?>">
            <td><?php echo htmlspecialchars($row['item_name']); ?></td>
            <td>₱<?php echo number_format((float)$row['price'], 2); ?></td>
            <td>
                <?php if (!empty($row['image'])): ?>
                    <img src="<?php echo htmlspecialchars($row['image']); ?>" alt="Item Image">
                <?php else: ?>
                    No Image
                <?php endif; ?>
            </td>
            <td>
                <button class="delete-btn" data-id="<?php echo $row['id']; ?>">Delete</button>
            </td>
        </tr>
    <?php endwhile; ?>
</table>
<?php else: ?>
    <p>No items found in stock.</p>
<?php endif; ?>

<div class="container">
    <div class="labeltable">Add an Item</div>
    <form id="stockForm">
        <label for="name">Name:</label>
        <input type="text" id="name" placeholder="Enter Item Name" required>

        <label for="price">Price:</label>
        <input type="number" id="price" placeholder="Enter Price" required>

        <label for="image">Image:</label>
        <input type="file" id="image" accept="image/*" onchange="previewImage(event)" required>
        <img id="preview" src="#" alt="Image Preview" style="display:none;">

        <button type="submit">Insert</button>
    </form>
</div>
    <script>
        function previewImage(event) {
            const preview = document.getElementById("preview");
            preview.src = URL.createObjectURL(event.target.files[0]);
            preview.style.display = "block";
        }

        $(document).ready(function () {
            $("#stockForm").on("submit", function (e) {
                e.preventDefault();

                let formData = new FormData();
                formData.append("name", $("#name").val());
                formData.append("price", $("#price").val());
                let imageFile = $("#image")[0].files.length > 0 ? $("#image")[0].files[0] : null;
                formData.append("image", imageFile);

                $.ajax({
                    url: "insert.php",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        alert(response);
                        location.reload();
                    }
                });
            });

            $(".delete-btn").on("click", function () {
                let itemId = $(this).data('id');
                let row = $("#row-" + itemId);

                if (confirm("Are you sure you want to delete this item?")) {
                    $.ajax({
                        url: "delete.php",
                        type: "POST",
                        data: { id: itemId },
                        success: function (response) {
                            if (response === "success") {
                                alert("Item and image deleted successfully");
                                location.reload();
                            } else {
                                alert("Error deleting item.");
                            }
                        }
                    });
                }
            });
        });
    </script>

</body>
</html>

<?php
mysqli_close($conn);
?>