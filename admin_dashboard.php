<?php
session_start();
if(!isset($_SESSION['admin_logged_in'])){
    header("Location: admin_login.php");
    exit;
}

$conn = new mysqli("localhost","root","","restaurant_db");
if($conn->connect_error){
    die("Connection failed: ".$conn->connect_error);
}

/* Update Order Status */
if(isset($_POST['update_status'])){
    $order_id = (int)$_POST['order_id'];
    $order_status = $_POST['order_status'];
    $conn->query("UPDATE orders SET order_status='$order_status' WHERE id=$order_id");
}

/* Fetch Data */
$res_result   = $conn->query("SELECT * FROM reservations ORDER BY id DESC");
$order_result = $conn->query("SELECT * FROM orders ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>
<meta charset="UTF-8">

<style>
body{
    font-family:Segoe UI, Tahoma;
    background:#fffaf0;
    padding:30px;
}
h1{
    text-align:center;
    color:#8b4513;
}
.logout-btn{
    float:right;
    background:#ff8c00;
    color:#fff;
    padding:10px 25px;
    text-decoration:none;
    border-radius:25px;
}
.section-title{
    margin-top:40px;
    color:#8b4513;
}
.search-input{
    padding:10px;
    border-radius:20px;
    width:300px;
}
.table-wrapper{
    margin-top:15px;
    background:#fff5e6;
    padding:15px;
    border-radius:20px;
}
table{
    width:100%;
    border-collapse:collapse;
}
th,td{
    padding:10px;
    text-align:center;
}
th{
    background:#ff8c00;
    color:white;
}
tr:nth-child(even){
    background:#fff0d4;
}
.delete-btn{
    background:#ff6347;
    color:white;
    padding:6px 14px;
    border-radius:20px;
    text-decoration:none;
}
select{
    padding:6px;
    border-radius:15px;
}
.update-btn{
    padding:6px 12px;
    border-radius:15px;
    background:#28a745;
    color:white;
    border:none;
}
</style>

<script>
function searchTable(inputId, tableId){
    let filter = document.getElementById(inputId).value.toLowerCase();
    let rows = document.getElementById(tableId).getElementsByTagName("tr");
    for(let i=1;i<rows.length;i++){
        rows[i].style.display = rows[i].innerText.toLowerCase().includes(filter) ? "" : "none";
    }
}
</script>
</head>

<body>

<h1>Welcome, <?php echo $_SESSION['admin_name']; ?></h1>
<a href="admin_logout.php" class="logout-btn">Logout</a>

<!-- ================= RESERVATIONS ================= -->
<h2 class="section-title">Reservations</h2>
<input type="text" class="search-input" placeholder="Search..." id="resSearch" onkeyup="searchTable('resSearch','resTable')">

<div class="table-wrapper">
<table id="resTable">
<tr>
<th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Guests</th><th>Date</th><th>Time</th><th>Message</th><th>Action</th>
</tr>
<?php $i=1; while($r=$res_result->fetch_assoc()){ ?>
<tr>
<td><?= $i++ ?></td>
<td><?= htmlspecialchars($r['name']) ?></td>
<td><?= htmlspecialchars($r['email']) ?></td>
<td><?= htmlspecialchars($r['phone']) ?></td>
<td><?= htmlspecialchars($r['guests']) ?></td>
<td><?= htmlspecialchars($r['res_date']) ?></td>
<td><?= htmlspecialchars($r['res_time']) ?></td>
<td><?= htmlspecialchars($r['message']) ?></td>
<td><a href="delete_reservation.php?id=<?= $r['id'] ?>" class="delete-btn">Delete</a></td>
</tr>
<?php } ?>
</table>
</div>

<!-- ================= ORDERS ================= -->
<h2 class="section-title">Orders</h2>
<input type="text" class="search-input" placeholder="Search..." id="orderSearch" onkeyup="searchTable('orderSearch','orderTable')">

<div class="table-wrapper">
<table id="orderTable">
<tr>
<th>#</th>
<th>Menu</th>
<th>Name</th>
<th>Phone</th>
<th>Address</th>
<th>Qty</th>
<th>Total</th>
<th>Payment</th>
<th>Status</th>
<th>Update</th>
<th>Delete</th>
</tr>

<?php $i=1; while($o=$order_result->fetch_assoc()){ ?>
<tr>
<td><?= $i++ ?></td>
<td><?= htmlspecialchars($o['menu_name']) ?></td>
<td><?= htmlspecialchars($o['customer_name']) ?></td>
<td><?= htmlspecialchars($o['customer_phone']) ?></td>
<td><?= htmlspecialchars($o['customer_address']) ?></td>
<td><?= $o['quantity'] ?></td>
<td>$<?= $o['total_price'] ?></td>
<td><?= htmlspecialchars($o['payment_method']) ?></td>

<td>
<form method="POST">
<input type="hidden" name="order_id" value="<?= $o['id'] ?>">
<select name="order_status">
<option <?= $o['order_status']=="Pending"?"selected":"" ?>>Pending</option>
<option <?= $o['order_status']=="Processing"?"selected":"" ?>>Processing</option>
<option <?= $o['order_status']=="Delivered"?"selected":"" ?>>Delivered</option>
</select>
</td>

<td>
<button name="update_status" class="update-btn">Save</button>
</td>

<td>
<a href="delete_order.php?id=<?= $o['id'] ?>" class="delete-btn">Delete</a>
</td>
</form>
</tr>
<?php } ?>

</table>
</div>

</body>
</html>
<?php $conn->close(); ?>