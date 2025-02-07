<?php
$servername = "localhost:3307";
$username = "root"; // Default XAMPP username
$password = "";     // Default XAMPP password (leave blank)
$dbname = "hotel_booking1";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
