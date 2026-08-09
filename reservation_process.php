<?php
$conn = new mysqli("localhost","root","","restaurant_db");
if($conn->connect_error){
    die("Connection failed: ".$conn->connect_error);
}

$name = $conn->real_escape_string($_POST['name']);
$email = $conn->real_escape_string($_POST['email']);
$phone = $conn->real_escape_string($_POST['phone']);
$res_date = $conn->real_escape_string($_POST['res_date']); // must match input name
$res_time = $conn->real_escape_string($_POST['res_time']); // must match input name
$guests = $conn->real_escape_string($_POST['guests']);
$message = $conn->real_escape_string($_POST['message']);

$sql = "INSERT INTO reservations (name,email,phone,res_date,res_time,guests,message) 
        VALUES ('$name','$email','$phone','$res_date','$res_time','$guests','$message')";

if($conn->query($sql) === TRUE){
    header("Location: reservation_success.html");
    exit;
}else{
    echo "Error: ".$conn->error;
}

$conn->close();
?>