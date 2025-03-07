<?php
include("database.php");

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["id"])) {
    $id = intval($_POST["id"]);

    // Fetch the image filename before deleting the record
    $query = "SELECT image FROM items_stock WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $imagePath);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    // Delete the record from the database
    $deleteQuery = "DELETE FROM items_stock WHERE id = ?";
    $stmt = mysqli_prepare($conn, $deleteQuery);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        // Delete the image file from the directory if it exists
        if (!empty($imagePath) && file_exists($imagePath)) {
            unlink($imagePath); // Remove the file
        }
        echo "success";
    } else {
        echo "error";
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
} else {
    echo "invalid_request";
}
?>
