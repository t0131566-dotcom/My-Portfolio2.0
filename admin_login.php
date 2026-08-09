<?php
session_start();
if(isset($_SESSION['admin_logged_in'])){
    header("Location: admin_dashboard.php");
    exit;
}

$error = '';
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Database connection
    $conn = new mysqli("localhost", "root", "", "restaurant_db");
    if($conn->connect_error){
        die("Connection failed: ".$conn->connect_error);
    }

    $password_hash = md5($password); // যদি MD5 ব্যবহার করে password save করে থাকে
    $sql = "SELECT * FROM admin_users WHERE username=? AND password=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password_hash);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows === 1){
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_name'] = $username;
        header("Location: admin_dashboard.php");
        exit;
    } else {
        $error = "Invalid username or password!";
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login</title>
<style>
body { font-family: Arial; margin:0; padding:0; height:100vh; display:flex; justify-content:center; align-items:center; background: linear-gradient(135deg,#FF7F50,#FF4500);}
.login-box { background:white; padding:40px; border-radius:20px; box-shadow:0 10px 30px rgba(0,0,0,0.2); width:350px; text-align:center; }
h2 { color:#FF4500; margin-bottom:30px;}
input[type=text], input[type=password] { width:100%; padding:12px; margin-bottom:20px; border-radius:50px; border:1px solid #ccc; }
button { width:100%; padding:12px; border:none; border-radius:50px; background:#FF4500; color:white; font-size:1.1rem; cursor:pointer; transition:.3s; }
button:hover { background:#cf0000; transform:scale(1.05);}
.error { color:red; margin-bottom:15px; font-weight:bold;}
</style>
</head>
<body>
<div class="login-box">
<h2>Admin Login</h2>
<?php if($error) echo "<div class='error'>$error</div>"; ?>
<form method="POST">
<input type="text" name="username" placeholder="Username" required>
<input type="password" name="password" placeholder="Password" required>
<button type="submit">Login</button>
</form>
</div>
</body>
</html>