<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "restaurant_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch orders, newest first
$result = $conn->query("SELECT * FROM orders ORDER BY order_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin - Orders</title>
<style>
body { font-family: Arial, sans-serif; background: #f7f7f7; margin:0; padding:20px;}
h1 { text-align:center; color:#FF7E5F; }
table { width:100%; border-collapse: collapse; margin-top:30px; }
th, td { border:1px solid #ccc; padding:10px; text-align:center; }
th { background:#FF7E5F; color:white; }
tr:nth-child(even) { background:#f2f2f2; }
a.delete-btn { padding:5px 10px; background:red; color:white; text-decoration:none; border-radius:5px; }
a.delete-btn:hover { background:darkred; }
</style>
</head>
<body>

<h1>All Orders</h1>

<table>
<tr>
    <th>Serial</th>
    <th>Menu ID</th>
    <th>Menu Name</th>
    <th>Customer Name</th>
    <th>Phone</th>
    <th>Address</th>
    <th>Quantity</th>
    <th>Total Price</th>
    <th>Order Date</th>
    <th>Action</th>
</tr>

<?php
if($result->num_rows > 0){
    $serial = 1; // Serial number starts from 1
    while($row = $result->fetch_assoc()){
        // Fix address display: show "N/A" if empty
        $customer_address = !empty(trim($row['customer_address'])) ? htmlspecialchars($row['customer_address']) : 'N/A';

        echo '<tr>
            <td>'.$serial.'</td>
            <td>'.htmlspecialchars($row['menu_id']).'</td>
            <td>'.htmlspecialchars($row['menu_name']).'</td>
            <td>'.htmlspecialchars($row['customer_name']).'</td>
            <td>'.htmlspecialchars($row['customer_phone']).'</td>
            <td>'.$customer_address.'</td>
            <td>'.htmlspecialchars($row['quantity']).'</td>
            <td>$'.htmlspecialchars($row['total_price']).'</td>
            <td>'.htmlspecialchars($row['order_date']).'</td>
            <td><a class="delete-btn" href="delete_order.php?id='.$row['id'].'">Delete</a></td>
        </tr>';
        $serial++; // Increment serial for next row
    }
}else{
    echo '<tr><td colspan="10">No orders found.</td></tr>';
}
?>

</table>

</body>
</html>
<?php $conn->close(); ?>