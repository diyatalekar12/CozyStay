<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

include('db.php');

$user_id = $_SESSION['user']['id'];  // Assuming user info is stored in session

// Fetch booked rooms from the database
$sql_booked_rooms = "SELECT r.id as room_id, r.hotel_id FROM bookings b
                     JOIN rooms r ON b.room_id = r.id
                     WHERE b.users_id = ?";
$stmt = $conn->prepare($sql_booked_rooms);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if any rooms are booked
$booked_rooms = [];
while ($row = $result->fetch_assoc()) {
    $booked_rooms[] = $row;
}

// Fetch customer details
$sql_customer = "SELECT name, email, phone FROM users WHERE id = ?";
$stmt = $conn->prepare($sql_customer);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$customer_details = $result->fetch_assoc();

// Fetch booking and payment details
$booking_details = [];
$total_price = 0;

foreach ($booked_rooms as $room) {
    $room_id = $room['room_id'];
    $hotel_id = $room['hotel_id'];

    // Fetch detailed booking and payment info
    $sql_booking = "SELECT h.name as hotel_name, r.type as room_type, b.checkin as check_in, b.checkout as check_out, p.amount as total_price
                    FROM rooms r
                    JOIN hotels h ON r.hotel_id = h.id
                    JOIN bookings b ON b.room_id = r.id
                    JOIN payments p ON b.id = p.booking_id
                    WHERE r.id = ? AND r.hotel_id = ? AND b.users_id = ?";
    $stmt = $conn->prepare($sql_booking);
    $stmt->bind_param("iii", $room_id, $hotel_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $room_details = $result->fetch_assoc();
        $booking_details[] = $room_details;
        $total_price += $room_details['total_price'];
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
    font-family: Arial, sans-serif;
    background-color: #f9f9f9; /* Very Light Gray */
    margin: 0;
    padding: 0;
    background-image: url('./images/bg9.jpg'); /* Add your moving background image URL */
    background-size: cover;
    background-attachment: fixed;
    background-position: center;
    animation: moveBackground 10s linear infinite;
}

@keyframes moveBackground {
    0% {
        background-position: 0% 0%;
    }
    100% {
        background-position: 100% 100%;
    }
}

header {
    text-align: center;
    padding: 30px;
    background-color: #2c3e50; /* Dark Blue-Gray */
    color: white;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.container {
    display: flex;
    justify-content: space-around;
    margin: 40px 0;
}

.card {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    width: 45%;
}

.card h3 {
    color: #2c3e50; /* Dark Blue-Gray */
    margin-bottom: 15px;
    font-size: 24px;
    font-weight: bold;
}

.card ul {
    list-style-type: none;
    padding: 0;
}

.card li {
    padding: 12px 0;
    border-bottom: 1px solid #ddd;
    font-size: 16px;
    color: #34495e; /* Dark Gray */
}

.card li:last-child {
    border-bottom: none;
}

.back-button {
    display: block;
    text-align: center;
    margin-top: 20px;
}

.back-button a {
    text-decoration: none;
    background-color: #007bff; /* Blue */
    color: white;
    padding: 12px 20px;
    border-radius: 5px;
    font-size: 16px;
    font-weight: bold;
}

.back-button a:hover {
    background-color: #0056b3; /* Darker Blue */
}

.logout-button {
    display: block;
    text-align: center;
    margin-top: 20px;
}

.logout-button a {
    text-decoration: none;
    background-color: #dc3545; /* Red */
    color: white;
    padding: 12px 20px;
    border-radius: 5px;
    font-size: 16px;
    font-weight: bold;
}

.logout-button a:hover {
    background-color: #c82333; /* Darker Red */
}

.profile-logo {
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: white; /* Blue background for profile logo */
}

.profile-logo img {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    object-fit: cover;
}

/* Dropdown Menu */
.dropdown-menu {
    display: none;
    position: absolute;
    top: 50px;
    right: 0;
    background-color: #ffffff;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    width: 150px;
    z-index: 10;
}

.dropdown-menu a {
    display: block;
    padding: 10px;
    text-decoration: none;
    color: #34495e;
    font-size: 14px;
    border-bottom: 1px solid #ddd;
}

.dropdown-menu a:hover {
    background-color: #f1f1f1;
}

/* Show dropdown when active */
.dropdown-menu.show {
    display: block;
}

.profile-container {
    position: fixed;
    top: 20px;
    right: 20px;
    display: inline-block;
    z-index: 100;
}
html, body {
    height: 100%; /* Ensure the body and html take up full height */
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column; /* Stack the elements vertically */
}
/* Footer Styles */
footer {
    background-color: #2c3e50; /* Dark Blue-Gray */
    color: white;
    padding: 10px 0;
    width: 100%;
    margin-top: auto;
    font-size: 12px; /* Small font size */
}

.footer-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 10px;
}

.footer-center {
    flex: 1;
    text-align: center;
}

.footer-center p {
    font-size: 12px;
    color: #bdc3c7;
    margin: 0;
}
    </style>
</head>
<body>
<div class="profile-container">
        <div class="profile-logo" onclick="toggleDropdown()">
            <img src="./images/bg1.jpg" alt="Profile" class="profile-pic">
        </div>
        <div class="dropdown-menu" id="dropdownMenu">
        <a href="user_details.php">Profile</a>
        </div>
    </div>
    <header>
        <h1>Booking Confirmation</h1>
    </header>

    <div class="container">
        <!-- Customer Information Card -->
        <div class="card">
            <h3>Customer Information</h3>
            <ul>
                <li><strong>Name:</strong> <?php echo htmlspecialchars($customer_details['name']); ?></li>
                <li><strong>Email:</strong> <?php echo htmlspecialchars($customer_details['email']); ?></li>
                <li><strong>Phone:</strong> <?php echo htmlspecialchars($customer_details['phone']); ?></li>
            </ul>
        </div>

        <!-- Booking Information Card -->
        <div class="card">
            <h3>Booking Information</h3>
            <ul>
                <?php if (count($booking_details) > 0): ?>
                    <?php foreach ($booking_details as $booking) : ?>
                        <li><strong>Hotel Name:</strong> <?php echo htmlspecialchars($booking['hotel_name']); ?></li>
                        <li><strong>Room Type:</strong> <?php echo htmlspecialchars($booking['room_type']); ?></li>
                        <li><strong>Check-in:</strong> <?php echo htmlspecialchars($booking['check_in']); ?></li>
                        <li><strong>Check-out:</strong> <?php echo htmlspecialchars($booking['check_out']); ?></li>
                        <li><strong>Total Price:</strong> ₹ <?php echo htmlspecialchars($booking['total_price']); ?></li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>No booking details available.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <div class="logout-button">
        <a href="logout.php">Logout</a>
    </div>
    <script>
        function toggleDropdown() {
            var menu = document.getElementById("dropdownMenu");
            menu.classList.toggle("show");
        }
    </script>
    <footer>
        <div class="footer-center">
            <p>&copy; 2025 Your Website. All Rights Reserved.</p>
        </div>
</footer>
</body>
</html>
