<?php
session_start();

/* ================== SECURITY HEADERS ================== */
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

/* ================== CSRF TOKEN ================== */
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "restaurant_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if($conn->connect_error){
    die("Connection failed: ".$conn->connect_error);
}

// Fetch menu items
$menu_sql = "SELECT * FROM menu_items ORDER BY id ASC";
$menu_result = $conn->query($menu_sql);
if(!$menu_result){
    die("Query Error: ".$conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Delicious Bites Restaurant</title>

<style>
/* ================== GENERAL STYLES ================== */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin:0; padding:0;
    background:linear-gradient(135deg,#f2f2f2,#fff);
    color:#333;
    scroll-behavior:smooth;
}

/* ================== HEADER (Box Removed, Simple Animation Added) ================== */
header {
    background: rgba(255,255,255,0.7); 
    color: #000;
    padding: 10px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 100;
    backdrop-filter: blur(8px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

header img {
    height: 50px;
    width: auto;
    max-width: 160px;
}

nav ul {
    list-style: none;
    display: flex;
    gap: 15px;
    margin: 0;
    padding: 0;
}

nav ul li a {
    display: inline-block;
    padding: 8px 15px;
    color: #333; /* Default black-ish text */
    text-decoration: none;
    font-weight: 600;
    position: relative;
    transition: color 0.3s;
    cursor: pointer;
    user-select: none;
}

/* Simple Underline Animation for Header Links */
nav ul li a::after {
    content: '';
    position: absolute;
    width: 0;
    height: 2px;
    bottom: 5px;
    left: 15px;
    background: #FF7E5F;
    transition: width 0.3s ease;
}

nav ul li a:hover {
    color: #FF7E5F;
}

nav ul li a:hover::after {
    width: calc(100% - 30px);
}

/* ================== HERO SECTION ================== */
.hero {
    position: relative;
    width: 100%;
    height: 100vh;
    overflow: hidden;
}
.hero img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: brightness(0.5);
    transition: transform 1.2s ease;
}
.hero:hover img {
    transform: scale(1.05);
}
.hero-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    text-align: center;
    padding: 30px 50px;
    border-radius: 25px;
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(12px);
    box-shadow: 0 10px 35px rgba(0,0,0,0.5);
    max-width: 90%;
}
.hero-text h1 {
    font-size: 3rem;
    margin-bottom: 15px;
    text-shadow: 2px 2px 15px rgba(0,0,0,0.5);
}
.hero-text p {
    font-size: 1.5rem;
    margin-bottom: 25px;
    text-shadow: 1px 1px 8px rgba(0,0,0,0.5);
}
.hero-text .reserve-btn {
    display: inline-block;
    padding: 15px 45px;
    font-size: 1.2rem;
    color: white;
    background: linear-gradient(135deg,#FF7E5F,#FEB47B);
    border-radius: 50px;
    text-decoration: none;
    box-shadow: 0 8px 25px rgba(0,0,0,0.4);
    transition: transform 0.3s, background 0.3s;
}
.hero-text .reserve-btn:hover {
    transform: scale(1.1) rotate(-2deg);
    background: linear-gradient(135deg,#FEB47B,#FF7E5F);
}

/* ================== MENU SECTION ================== */
#menu {
    padding: 80px 20px;
    text-align: center;
    background: #f0fbff; 
}
#menu h2 {
    font-size: 3rem;
    margin-bottom: 50px;
    color: #FF7E5F;
    text-transform: uppercase;
    letter-spacing: 2px;
    position: relative;
}
#menu h2::after {
    content: '';
    width: 60px;
    height: 3px;
    background: #FF7E5F;
    display: block;
    margin: 10px auto 0;
    border-radius: 2px;
}

.menu-items {
    display: grid;
    grid-template-columns: repeat(4,1fr);
    gap: 25px;
    max-width: 1280px;
    margin: auto;
}
.item {
    background: #fff7ef;
    border-radius: 25px;
    overflow: hidden;
    position: relative;
    cursor: pointer;
    /* Subtle Entry Animation */
    transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.4s;
}
.item:hover {
    transform: translateY(-15px) scale(1.02);
    box-shadow: 0 20px 35px rgba(255,126,95,0.2);
}
.item img {
    width: 100%;
    height: 180px;
    object-fit: cover;
    border-radius: 25px 25px 0 0;
    transition: transform 0.5s ease;
}
.item:hover img {
    transform: scale(1.1);
}
.item-content {
    padding: 15px;
}
.item-content h3 {
    color: #FF7E5F;
    margin-bottom: 8px;
}
.item-content .description {
    font-size: 0.95rem;
    color: #555;
    margin-bottom: 10px;
    min-height: 45px;
    opacity: 0;
    transform: translateY(10px);
    transition: opacity 0.4s, transform 0.4s;
}
.item:hover .description {
    opacity: 1;
    transform: translateY(0);
}
.item-content .price {
    font-weight: bold;
    margin-bottom: 10px;
}
.item form {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.item input, .item select, .item button {
    padding: 8px 10px;
    border-radius: 20px;
    border: 1px solid #ccc;
}
.item input:focus, .item select:focus {
    border-color: #FF7E5F;
    outline: none;
}
.item button.order-btn {
    background: #FF7E5F;
    color: white;
    border: none;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}
.item button.order-btn:hover {
    background: #FEB47B;
    letter-spacing: 1px;
}

/* ================== ABOUT & CONTACT ================== */
#about, #contact {
    padding: 80px 20px;
    text-align: center;
    background: #fff;
}
.about-box {
    max-width: 800px;
    margin: 0 auto;
    line-height: 1.8;
}
#about h2, #contact h2 {
    font-size: 2.5rem;
    color: #FF7E5F;
    margin-bottom: 25px;
}

/* ================== FOOTER ================== */
footer {
    background: rgba(30,30,30,0.95);
    color: white;
    text-align: center;
    padding: 30px;
    font-size: 0.95rem;
    backdrop-filter: blur(10px);
}

/* ================== RESPONSIVE ================== */
@media(max-width:1024px){
    .menu-items { grid-template-columns: repeat(3,1fr);}
}
@media(max-width:768px){
    .menu-items { grid-template-columns: repeat(2,1fr);}
}
@media(max-width:480px){
    .menu-items { grid-template-columns: 1fr;}
}
</style>
</head>
<body>

<header>
    <img src="images/logo.jpg" alt="Delicious Bites Logo">
    <nav>
        <ul>
            <li><a href="#home">Home</a></li>
            <li><a href="#menu">Menu</a></li>
            <li><a href="track_order.php">Track Order</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="#contact">Contact</a></li>
        </ul>
    </nav>
</header>

<section id="home" class="hero">
    <img src="images/hero.jpg" alt="Hero Image">
    <div class="hero-text">
        <h1>Welcome to Delicious Bites</h1>
        <p>Fresh ingredients, authentic flavors, unforgettable dining.</p>
        <a href="reservation.html" class="reserve-btn">Reserve a Table</a>
    </div>
</section>

<section id="menu">
<h2>Our Menu</h2>
<div class="menu-items">
<?php
if($menu_result->num_rows > 0){
    while($row = $menu_result->fetch_assoc()){
        $name = htmlspecialchars($row["name"]);
        $desc = htmlspecialchars($row["description"]);
        $price = htmlspecialchars($row["price"]);
        $image = htmlspecialchars($row["image"]);
        $id = intval($row["id"]);

        echo '
        <div class="item">
            <img src="images/'.$image.'" alt="'.$name.'">
            <div class="item-content">
                <h3>'.$name.'</h3>
                <div class="description">'.$desc.'</div>
                <p class="price">$'.$price.'</p>
                <form method="POST" action="order_process.php">
                    <input type="hidden" name="menu_id" value="'.$id.'">
                    <input type="hidden" name="menu_name" value="'.$name.'">
                    <input type="hidden" name="csrf_token" value="'.$_SESSION['csrf_token'].'">
                    <input type="number" name="quantity" value="1" min="1" placeholder="Quantity" required>
                    <input type="text" name="customer_name" placeholder="Your Name" required>
                    <input type="text" name="customer_phone" placeholder="Phone" required>
                    <input type="text" name="customer_address" placeholder="Delivery Address" required>
                    <select name="payment_method" required>
                        <option value="">Select Payment Method</option>
                        <option value="Cash on Delivery">Cash on Delivery</option>
                        <option value="Online Banking">Online Banking</option>
                    </select>
                    <button type="submit" class="order-btn">Order Now</button>
                </form>
            </div>
        </div>
        ';
    }
}else{
    echo "<p>No menu items found.</p>";
}
?>
</div>
</section>

<section id="about">
<div class="about-box">
    <h2>About Our Story</h2>
    <p>At <strong>Delicious Bites</strong>, we believe that food is more than just sustenance—it is an experience that brings people together. Established with a passion for authentic culinary arts, we source only the freshest local ingredients to craft dishes that celebrate flavor and tradition. Our team of world-class chefs is dedicated to delivering a premium dining experience, whether you're enjoying a meal in our cozy restaurant or ordering from the comfort of your home. Join us on a journey of taste and discover why we are a favorite destination for food lovers.</p>
</div>
</section>

<section id="contact">
<h2>Contact Us</h2>
<p>Address: Mirpur, Dhaka, Bangladesh</p>
<p>Phone: +880 123 456 7890</p>
<p>Email: info@deliciousbites.com</p>
</section>

<footer>
<p>&copy; 2026 Delicious Bites. All rights reserved.</p>
</footer>

</body>
</html>
<?php $conn->close(); ?>