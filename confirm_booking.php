<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
include('db.php');

// Assuming you store user id in session
$user_id = $_SESSION['user'];
$checkin = $_GET['checkin'] ?? '';
$checkout = $_GET['checkout'] ?? '';
$room_id = $_GET['room_id'] ?? '';
$hotel_id = $_GET['hotel_id'] ?? '';

// Handle new booking creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_booking'])) {
    $room_id = $_POST['room_id'];
    $hotel_id = $_POST['hotel_id'];
    $checkin = $_POST['checkin'];
    $checkout = $_POST['checkout'];

    // Insert booking details into the bookings table
    $booking_sql = "INSERT INTO bookings (users_id, hotel_id, room_id, checkin, checkout, status) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($booking_sql);
    $status = 'pending';
    $stmt->bind_param('iiisss', $user_id, $hotel_id, $room_id, $checkin, $checkout, $status);

    if ($stmt->execute()) {
        echo "<script>alert('Booking confirmed successfully!');</script>";
    } else {
        echo "<script>alert('Error in booking: " . $conn->error . "');</script>";
    }
}

// Fetch all bookings for this user
$sql = "SELECT b.*, h.name as hotel_name, r.type as room_type, r.price as daily_price 
        FROM bookings b
        JOIN hotels h ON b.hotel_id = h.id
        JOIN rooms r ON b.room_id = r.id
        WHERE b.users_id = ?";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die('MySQL prepare error: ' . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$bookings = [];
while ($row = $result->fetch_assoc()) {
    // Calculate total price
    $checkin_date = new DateTime($row['checkin']);
    $checkout_date = new DateTime($row['checkout']);
    $stay_duration = $checkin_date->diff($checkout_date)->days; // Calculate number of days
    $row['total_price'] = $row['daily_price'] * $stay_duration;
    $bookings[] = $row;
}

// Handle room deletion
if (isset($_GET['delete_booking'])) {
    $delete_booking_id = $_GET['delete_booking'];
    $delete_stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
    if ($delete_stmt === false) {
        die('MySQL prepare error: ' . $conn->error);
    }
    $delete_stmt->bind_param("i", $delete_booking_id);
    $delete_stmt->execute();
    header("Location: confirm_booking.php");
    exit;
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
            margin: 0;
            padding: 0;
            background: url('./images/bg7.jpg') no-repeat center center fixed;
            background-size: cover;
            animation: move-bg 15s linear infinite;
            font-family: Arial, sans-serif;
        }

        @keyframes move-bg {
            0% { background-position: 0 0; }
            100% { background-position: 100% 100%; }
        }

        header {
            text-align: center;
            padding: 15px;
            background-color: #2c3e50;
            color: white;
        }

        .booking-card {
            background: #ffffff;
            margin: 15px 0;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            padding: 15px;
            transition: transform 0.3s ease-in-out;
        }

        .booking-card:hover {
            transform: translateY(-5px);
        }

        .booking-card h3 {
            color: #2c3e50;
        }

        .booking-card p {
            color: #34495e;
        }

        .delete-button {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
        }

        .delete-button:hover {
            background-color: #c0392b;
        }

        .action-buttons {
            display: flex;
            justify-content: space-evenly;
            margin-top: 20px;
        }

        .action-buttons button {
            background-color: #2c3e50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .action-buttons button:hover {
            background-color: #34495e;
        }
        .profile-container {
    position: fixed;
    top: 20px;
    right: 20px;
    display: inline-block;
    z-index: 100;
}
        /* Profile Logo (Icon) */
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
        <h1>Your Bookings</h1>
    </header>

    <div class="bookings-list">
        <?php if (empty($bookings)) : ?>
            <p>No bookings yet! Book a room to see it here.</p>
        <?php else : ?>
            <?php foreach ($bookings as $booking) : ?>
                <div class="booking-card">
                    <h3><?php echo htmlspecialchars($booking['hotel_name']); ?></h3>
                    <p><strong>Room Type:</strong> <?php echo htmlspecialchars($booking['room_type']); ?></p>
                    <p><strong>Daily Price:</strong> ₹<?php echo htmlspecialchars($booking['daily_price']); ?></p>
                    <p><strong>Total Price:</strong> ₹<?php echo htmlspecialchars($booking['total_price']); ?></p>
                    <p><strong>Check-in:</strong> <?php echo htmlspecialchars($booking['checkin']); ?></p>
                    <p><strong>Check-out:</strong> <?php echo htmlspecialchars($booking['checkout']); ?></p>
                    <a href="confirm_booking.php?delete_booking=<?php echo $booking['id']; ?>" onclick="return confirm('Are you sure you want to delete this booking?');">
                        <button class="delete-button">Delete</button>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="action-buttons">
        <a href="rooms.php?hotel_id=<?php echo $hotel_id; ?>&checkin=<?php echo $checkin; ?>&checkout=<?php echo $checkout; ?>">
            <button>Add More Rooms</button>
        </a>
        <form action="make_payment.php" method="POST">
            <input type="hidden" name="booked_rooms" value='<?php echo json_encode($bookings); ?>'>
            <button type="submit" <?php echo empty($bookings) ? 'disabled' : ''; ?>>Make Payments</button>
        </form>
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
