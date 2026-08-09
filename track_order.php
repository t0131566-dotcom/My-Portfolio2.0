<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "restaurant_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if($conn->connect_error){
    die("Connection failed: ".$conn->connect_error);
}

$order_list = [];
$error = "";

// Only allow GET request with proper input
if(isset($_GET['order_search'])){
    $search_raw = trim($_GET['order_search']);

    if($search_raw === ""){
        $error = "Please enter an Order ID or Phone number.";
    } else {
        $search = $conn->real_escape_string($search_raw);

        $stmt = $conn->prepare("SELECT id, menu_name, customer_name, customer_phone, customer_address, quantity, total_price, order_status FROM orders WHERE id = ? OR customer_phone = ? ORDER BY order_date DESC");
        
        if(is_numeric($search)){
            $stmt->bind_param("is", $search, $search);
        } else {
            $dummy_id = 0;
            $stmt->bind_param("is", $dummy_id, $search);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                // Fix: if NULL or empty, show N/A
                $address = trim($row['customer_address']);
                if($address === "" || $address === null) {
                    $row['customer_address'] = "N/A";
                } else {
                    $row['customer_address'] = $address;
                }
                $order_list[] = $row;
            }
        } else {
            $error = "No order found with this ID or phone number.";
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Track Your Order - Delicious Bites</title>
<link rel="stylesheet" href="styles.css">
<style>
body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin:0; padding:0; background:linear-gradient(135deg,#f2f2f2,#fff); color:#333; scroll-behavior:smooth; }
header { background: rgba(30,30,30,0.95); color:white; padding:15px 30px; display:flex; justify-content:space-between; align-items:center; position:sticky; top:0; z-index:100; backdrop-filter: blur(10px); box-shadow:0 4px 10px rgba(0,0,0,0.3); }
header img { width:160px; }
nav ul { list-style:none; display:flex; gap:25px; margin:0; padding:0; }
nav ul li a { color:white; text-decoration:none; font-weight:600; transition:0.3s; }
nav ul li a:hover { color:#FF7E5F; }

section { padding:70px 20px; text-align:center; }
h2 { font-size:2.5rem; color:#FF7E5F; margin-bottom:25px; }

form input, form button { padding:10px 15px; border-radius:20px; border:1px solid #ccc; font-size:1rem; }
form input:focus { border-color:#FF7E5F; outline:none; }
form button { background:#FF7E5F; color:white; border:none; font-weight:bold; cursor:pointer; transition:0.3s; }
form button:hover { background:#FEB47B; }

table { width:100%; border-collapse: separate; border-spacing:0; margin-top:20px; box-shadow:0 10px 20px rgba(0,0,0,0.1); border-radius:15px; overflow:hidden; }
th, td { padding:15px; text-align:center; }
th { background:#FF7E5F; color:white; font-weight:600; }
tr { background:white; transition:transform 0.2s, box-shadow 0.2s; }
tr:hover { transform:translateY(-3px); box-shadow:0 5px 15px rgba(0,0,0,0.1); }
td { border-bottom:1px solid #eee; }

.status-pending { color:#ff9800; font-weight:bold; }
.status-delivered { color:#28a745; font-weight:bold; }

@media(max-width:768px){
    table, thead, tbody, th, td, tr { display:block; }
    thead tr { display:none; }
    tr { margin-bottom:15px; }
    td { text-align:right; padding-left:50%; position:relative; }
    td::before { content:attr(data-label); position:absolute; left:15px; width:45%; font-weight:bold; text-align:left; }
}
footer { background: rgba(30,30,30,0.95); color:white; text-align:center; padding:30px; font-size:0.95rem; backdrop-filter: blur(10px);}
</style>
</head>
<body>

<header>
    <img src="images/logo.jpg" alt="Delicious Bites Logo">
    <nav>
        <ul>
            <li><a href="index.php#home">Home</a></li>
            <li><a href="index.php#menu">Menu</a></li>
            <li><a href="track_order.php">Track Order</a></li>
            <li><a href="index.php#about">About</a></li>
            <li><a href="index.php#contact">Contact</a></li>
        </ul>
    </nav>
</header>

<section>
    <h2>Track Your Order</h2>
    <form method="GET" action="track_order.php">
        <input type="text" name="order_search" placeholder="Enter Order ID or Phone Number" required>
        <button type="submit">Track Order</button>
    </form>

    <?php if($error): ?>
        <p style="margin-top:20px; color:#FF7E5F;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <?php if(count($order_list) > 0): ?>
        <table>
            <thead>
            <tr>
                <th>SL</th>
                <th>Order ID</th>
                <th>Menu Name</th>
                <th>Customer Name</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Quantity</th>
                <th>Total Price</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            <?php $sl=1; foreach($order_list as $row): ?>
                <tr>
                    <td data-label="SL"><?php echo $sl++; ?></td>
                    <td data-label="Order ID"><?php echo htmlspecialchars($row['id']); ?></td>
                    <td data-label="Menu Name"><?php echo htmlspecialchars($row['menu_name']); ?></td>
                    <td data-label="Customer Name"><?php echo htmlspecialchars($row['customer_name']); ?></td>
                    <td data-label="Phone"><?php echo htmlspecialchars($row['customer_phone']); ?></td>
                    <td data-label="Address"><?php echo htmlspecialchars($row['customer_address']); ?></td>
                    <td data-label="Quantity"><?php echo htmlspecialchars($row['quantity']); ?></td>
                    <td data-label="Total Price">$<?php echo htmlspecialchars($row['total_price']); ?></td>
                    <td data-label="Status" class="<?php echo strtolower($row['order_status'])==='pending' ? 'status-pending' : 'status-delivered'; ?>">
                        <?php echo htmlspecialchars($row['order_status']); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<footer>
<p>&copy; 2023 Delicious Bites. All rights reserved.</p>
</footer>

</body>
</html>