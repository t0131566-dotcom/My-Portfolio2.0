<?php
session_start();

/* ================== SECURITY ================== */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Unauthorized access");
}

/* ================== CSRF CHECK ================== */
if (!isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
    $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Invalid request");
}

/* ================== DB CONNECTION ================== */
$conn = new mysqli("localhost", "root", "", "restaurant_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/* ================== INPUT ================== */
$menu_id          = filter_input(INPUT_POST, 'menu_id', FILTER_VALIDATE_INT);
$menu_name        = trim($_POST['menu_name'] ?? '');
$quantity         = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
$customer_name    = trim($_POST['customer_name'] ?? '');
$customer_phone   = trim($_POST['customer_phone'] ?? '');
$customer_address = trim($_POST['customer_address'] ?? '');
$payment_method   = trim($_POST['payment_method'] ?? '');

/* ================== VALIDATION ================== */
if (
    !$menu_id ||
    !$quantity || $quantity < 1 ||
    $menu_name === '' ||
    $customer_name === '' ||
    $customer_phone === '' ||
    $customer_address === '' ||
    $payment_method === ''
) {
    die("Invalid order data");
}

/* ================== FETCH PRICE ================== */
$stmt_price = $conn->prepare("SELECT price FROM menu_items WHERE id = ?");
$stmt_price->bind_param("i", $menu_id);
$stmt_price->execute();
$res = $stmt_price->get_result();

if ($res->num_rows !== 1) {
    die("Invalid menu item");
}

$price = $res->fetch_assoc()['price'];
$total_price = $price * $quantity;

/* ================== INSERT ORDER ================== */
$stmt = $conn->prepare(
    "INSERT INTO orders
    (menu_name, customer_name, customer_phone, customer_address, quantity, total_price, order_status, payment_method, order_date)
    VALUES (?, ?, ?, ?, ?, ?, 'Pending', ?, NOW())"
);

$stmt->bind_param(
    "ssssids",
    $menu_name,
    $customer_name,
    $customer_phone,
    $customer_address,
    $quantity,
    $total_price,
    $payment_method
);

$stmt->execute();

/* ================== GET ORDER ID & DATE ================== */
$order_id   = $stmt->insert_id;
$order_date = date("Y-m-d H:i:s"); // Current date/time

/* ================== CLEANUP ================== */
$stmt->close();
$stmt_price->close();
$conn->close();
unset($_SESSION['csrf_token']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Confirmed | Delicious Bites</title>
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background: linear-gradient(135deg,#ffecd2,#fcb69f);
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    padding: 20px;
}
.card {
    max-width: 700px;
    width: 100%;
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.25);
    animation: fadeIn 0.8s ease;
}
@keyframes fadeIn{
    from{opacity:0; transform:translateY(20px);}
    to{opacity:1; transform:translateY(0);}
}
.icon {
    font-size: 60px;
    margin-bottom: 15px;
}
.btn-gradient {
    background: linear-gradient(135deg,#ff7e5f,#feb47b);
    border: none;
    color: white;
    font-weight: 600;
}
</style>
</head>

<body>

<div class="card text-center">
    <div class="icon">🎉</div>
    <h1 class="text-success mb-3">Order Confirmed!</h1>
    <p class="mb-4">Thank you <strong><?= htmlspecialchars($customer_name) ?></strong>.<br>
    Your delicious meal is being prepared.</p>

    <div class="table-responsive mb-4">
        <table class="table table-bordered table-striped align-middle">
            <tbody>
                <tr>
                    <th scope="row">Order ID</th>
                    <td><?= $order_id ?></td>
                </tr>
                <tr>
                    <th scope="row">Menu Item</th>
                    <td><?= htmlspecialchars($menu_name) ?></td>
                </tr>
                <tr>
                    <th scope="row">Price per Item</th>
                    <td>$<?= number_format($price,2) ?></td>
                </tr>
                <tr>
                    <th scope="row">Quantity</th>
                    <td><?= $quantity ?></td>
                </tr>
                <tr>
                    <th scope="row">Total</th>
                    <td>$<?= number_format($total_price,2) ?></td>
                </tr>
                <tr>
                    <th scope="row">Customer Name</th>
                    <td><?= htmlspecialchars($customer_name) ?></td>
                </tr>
                <tr>
                    <th scope="row">Phone</th>
                    <td><?= htmlspecialchars($customer_phone) ?></td>
                </tr>
                <tr>
                    <th scope="row">Address</th>
                    <td><?= htmlspecialchars($customer_address) ?></td>
                </tr>
                <tr>
                    <th scope="row">Payment Method</th>
                    <td><?= htmlspecialchars($payment_method) ?></td>
                </tr>
                <tr>
                    <th scope="row">Order Date</th>
                    <td><?= $order_date ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <a href="track_order.php" class="btn btn-gradient btn-lg">Track Your Order</a>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>