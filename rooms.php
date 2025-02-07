<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

include('db.php');

// Get hotel ID and booking dates
$hotel_id = $_GET['hotel_id'] ?? '';
$checkin = $_GET['checkin'] ?? '';
$checkout = $_GET['checkout'] ?? '';

// Fetch room details for the hotel
function getRoomsByHotel($hotel_id) {
    global $conn;
    $sql = "SELECT * FROM rooms WHERE hotel_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $hotel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $rooms = [];
    while ($row = $result->fetch_assoc()) {
        $rooms[] = $row;
    }
    return $rooms;
}

// Count total rooms available
function getTotalRoomsAvailable($hotel_id) {
    global $conn;
    $sql = "SELECT SUM(availability) AS total_available FROM rooms WHERE hotel_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $hotel_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['total_available'] ?? 0;
}

// Count today's bookings for the hotel
function getTodayBookings($hotel_id) {
    global $conn;
    $today = date('Y-m-d');
    $sql = "SELECT COUNT(*) AS bookings_today FROM bookings WHERE hotel_id = ? AND DATE(created_at) = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('is', $hotel_id, $today);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['bookings_today'] ?? 0;
}

// Handle booking logic
// Handle booking logic
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['room_id'])) {
    $room_id = $_GET['room_id'];
    $user_id = $_SESSION['user']; // User ID from session

    // Fetch room availability
    $roomQuery = $conn->prepare("SELECT availability FROM rooms WHERE id = ?");
    if ($roomQuery === false) {
        // Handle query preparation failure
        die('Error preparing query: ' . $conn->error);
    }
    $roomQuery->bind_param('i', $room_id);
    $roomQuery->execute();
    $roomResult = $roomQuery->get_result()->fetch_assoc();

    if ($roomResult['availability'] > 0) {
        // Insert booking into the bookings table
        $stmt = $conn->prepare("INSERT INTO bookings (users_id, hotel_id, room_id, checkin, checkout, status) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt === false) {
            // Handle query preparation failure
            die('Error preparing booking query: ' . $conn->error);
        }
        $status = 'pending';
        $stmt->bind_param('iiisss', $user_id, $hotel_id, $room_id, $checkin, $checkout, $status);

        if ($stmt->execute()) {
            // Reduce room availability by 1
            $updateRoom = $conn->prepare("UPDATE rooms SET availability = availability - 0.5 WHERE id = ?");
            if ($updateRoom === false) {
                // Handle query preparation failure
                die('Error preparing room update query: ' . $conn->error);
            }
            $updateRoom->bind_param('i', $room_id);
            $updateRoom->execute();

            // Redirect back to the same page to update data
            header("Location: rooms.php?hotel_id=$hotel_id&checkin=$checkin&checkout=$checkout");
            exit;
        } else {
            echo "Error in booking: " . $conn->error;
        }
    } else {
        echo "<p style='color:red;'>Room not available. Please choose a different room.</p>";
    }
}
$total_rooms_available = getTotalRoomsAvailable($hotel_id);
$today_bookings = getTodayBookings($hotel_id);
$rooms = getRoomsByHotel($hotel_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Rooms</title>
    <link rel="stylesheet" href="styles.css">
    <style>

/* Room Cards Style */
.room-cards {
    display: flex;
    flex-wrap: wrap;
    gap: 15px; /* Reduce the gap between cards */
    justify-content: center;
}

.room-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    width: 250px; /* Reduce the width of the cards */
    padding: 20px; /* Reduce the padding inside the card */
    text-align: center;
    background: #ffffff; /* Clean white background */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.room-card img {
    max-width: 100%;
    height: auto;
    border-radius: 5px;
}

.room-card h3 {
    margin: 8px 0; /* Reduce the margin between the title and other content */
    color: #2c3e50; /* Dark Blue-Gray */
    font-size: 1.2em; /* Reduce the font size of the title */
}

.room-card p {
    margin: 4px 0; /* Reduce the margin between description and price */
    color: #555; /* Neutral color for descriptions */
    font-size: 0.9em; /* Reduce the font size of the description */
}

.room-card button {
    padding: 8px 16px; /* Reduce the padding of the button */
    background: #e74c3c; /* Ruby Red for button */
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.room-card button:hover {
    background: #c0392b; /* Darker red on hover */
}


/* Go Back Button Style */
.go-back {
    position: fixed;
    left: 20px;
    bottom: 45px;
    padding: 10px 20px;
    background-color: #2c3e50; /* Dark Blue-Gray */
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    font-weight: bold;
    text-align: center; /* Ensures the text is centered */
    display: inline-block; /* Ensures button behavior */
    text-decoration: none; /* Remove link styles */
}


.go-back:hover {
    background-color: #34495e; /* Slightly lighter blue-gray on hover */
}

/* Submit Button Style */
.submit {
    position: fixed;
    left: 50%;
    bottom: 45px;
    transform: translateX(-50%);
    padding: 10px 20px;
    background-color: #e74c3c; /* Ruby Red */
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.submit:hover {
    background-color: #c0392b; /* Darker red on hover */
}
/* Profile Logo (Icon) */
.profile-logo {
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
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
/* Profile Container - fixed position */
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
            <a href="logout.php">Logout</a>
        </div>
    </div>
    <header>
        <h1>Available Rooms</h1>
    </header>
    <div class="room-cards">
        <?php if (!empty($rooms)) : ?>
            <?php foreach ($rooms as $room) : ?>
                <div class="room-card">
                <?php
$imagePath = '/images/default.jpg'; // Default image
if ($room['type'] === 'Standard Room') {
    $imagePath = '/images/standard.jpg';
} elseif ($room['type'] === 'Deluxe Room') {
    $imagePath = '/images/deluxe.jpg';
} elseif ($room['type'] === 'Suite Room') {
    $imagePath = '/images/suite.jpg';
}
?>
<img src="<?php echo htmlspecialchars('http://localhost/DBMS PROJECT' . $imagePath); ?>" alt="Room Image">

                    <h3><?php echo htmlspecialchars($room['type']); ?></h3>
                    <p><strong>Price:</strong> ₹ <?php echo htmlspecialchars($room['price']); ?> / day</p>
                    <p><strong>Availability:</strong> <?php echo htmlspecialchars($room['availability']); ?></p>
                    <form action="rooms.php" method="GET">
                        <input type="hidden" name="hotel_id" value="<?php echo htmlspecialchars($hotel_id); ?>">
                        <input type="hidden" name="room_id" value="<?php echo htmlspecialchars($room['id']); ?>">
                        <input type="hidden" name="checkin" value="<?php echo htmlspecialchars($checkin); ?>">
                        <input type="hidden" name="checkout" value="<?php echo htmlspecialchars($checkout); ?>">
                        <button type="submit">Add this Room</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p>No rooms available for this hotel.</p>
        <?php endif; ?>
    </div>
    <button class="submit" onclick="location.href='confirm_booking.php'">Submit</button>
    <!-- Go Back Button -->
    <div class="go-back">
    <a href="javascript:void(0);" onclick="goBack()" class="button">Go Back</a>
</div>
</script>
    <script>
        function toggleDropdown() {
            var menu = document.getElementById("dropdownMenu");
            menu.classList.toggle("show");
        }
        function goBack() {
            window.history.back();
        }
    </script>
    <footer>
        <div class="footer-center">
            <p>&copy; 2025 Your Website. All Rights Reserved.</p>
        </div>
</footer>
</body>
</html>
