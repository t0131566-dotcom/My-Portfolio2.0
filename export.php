<?php
$conn = new mysqli("localhost", "root", "", "restaurant");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM reservations ORDER BY date DESC, time DESC";
$result = $conn->query($sql);

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="reservations.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, array('ID','Name','Email','Phone','Date','Time','Guests','Message'));

if($result->num_rows > 0){
    while($row = $result->fetch_assoc()){
        fputcsv($output, $row);
    }
}
fclose($output);
exit;
?>
