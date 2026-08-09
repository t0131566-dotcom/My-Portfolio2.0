<?php
$conn = new mysqli("localhost","root","","restaurant_db");
if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}

if(isset($_GET['id'])){
    $id = intval($_GET['id']);
    $conn->query("DELETE FROM orders WHERE id = $id");
    header("Location: admin_orders.php");
    exit();
}else{
    echo "No order ID provided!";
}

$conn->close();
?>