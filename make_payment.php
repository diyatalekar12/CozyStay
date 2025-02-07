<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

include('db.php');

// Get the booked rooms from the session
$booked_rooms = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booked_rooms'])) {
    $booked_rooms = json_decode($_POST['booked_rooms'], true);
}

if (empty($booked_rooms)) {
    die('No rooms to display for payment.');
}

// Fetch hotel, room, and price details
$rooms_details = [];
$total_room_cost = 0;
$service_fee = 100; // Example service fee
$tax_rate = 0.10; // 10% tax rate

foreach ($booked_rooms as $room) {
    $room_id = $room['room_id'];
    $hotel_id = $room['hotel_id'];
    $checkin_date = $room['checkin']; // Check-in date (from session or form)
    $checkout_date = $room['checkout']; // Check-out date (from session or form)

    // Calculate the number of days of the stay
    $checkin = new DateTime($checkin_date);
    $checkout = new DateTime($checkout_date);
    $no_of_days = $checkout->diff($checkin)->days;

    if ($no_of_days <= 0) {
        die('Invalid check-in and check-out dates.');
    }

    $sql = "SELECT h.name as hotel_name, r.type as room_type, r.price 
            FROM rooms r
            JOIN hotels h ON r.hotel_id = h.id
            WHERE r.id = ? AND r.hotel_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('MySQL prepare error: ' . $conn->error);
    }
    $stmt->bind_param("ii", $room_id, $hotel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $room_details = $result->fetch_assoc();
    
    if ($room_details) {
        $room_details['no_of_days'] = $no_of_days;
        $room_details['total_cost'] = $room_details['price'] * $no_of_days; // Total cost for the stay
        $rooms_details[] = $room_details;
        $total_room_cost += $room_details['total_cost'];
    }
}

// Calculate additional costs
$tax = $total_room_cost * $tax_rate;
$total_amount = $total_room_cost + $tax + $service_fee;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    $user_id = $_SESSION['user'];
    $payment_status = 'successful';
    $payment_date = date('Y-m-d');

    // Retrieve the most recent booking_id for this user
    $sql = "SELECT id FROM bookings WHERE users_id = ? ORDER BY id DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('MySQL prepare error: ' . $conn->error);
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();

    if ($booking) {
        $booking_id = $booking['id'];
    } else {
        die('No booking found for this user.');
    }

    // Insert payment record into the payments table
    $sql = "INSERT INTO payments (booking_id, amount, payment_date, payment_status) 
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('MySQL prepare error: ' . $conn->error);
    }

    $stmt->bind_param("idss", $booking_id, $total_amount, $payment_date, $payment_status);
    if ($stmt->execute()) {
        echo "<script>
                alert('Payment Successful');
                window.location.href = 'view_details.php';
              </script>";
        exit;
    } else {
        echo "Error recording payment: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make Payment</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Add your existing styles */
        body {
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 100%;
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        header {
            text-align: center;
            padding: 20px;
            background-color: #2c3e50;
            color: white;
            flex-shrink: 0;
        }
        .payment-details {
            background: #fff;
            margin: 15px 0;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            flex: 1; /* Allow the main content to grow */
    overflow-y: auto; /* Add internal scrolling if content overflows */
        }
        .payment-details h3 {
            margin-bottom: 20px;
            color: #2c3e50;
        }
        .payment-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .payment-details th, .payment-details td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .payment-details th {
            background-color: #f4f4f9;
        }
        .total-amount {
            margin-top: 20px;
            margin: auto;
            padding: 10px;
    border: 1px solid #ccc;
    background-color: #f9f9f9;
            text-align: right;
            font-size: 20px;
        }
        .action-buttons {
            display: flex;
            margin-top: 20px;
            justify-content: center;
            margin: 10px 0;
        }
        .action-buttons button {
            background-color: #2c3e50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 250px;
            margin: 0 10px;
        }
        .action-buttons button:hover {
            background-color: #34495e;
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
/* Profile Container - fixed position */
.profile-container {
    position: fixed;
    top: 20px;
    right: 20px;
    display: inline-block;
    z-index: 100;
}
.total-amount {
        margin-left: 620px; /* Adjust this value to move the container left */
        padding: 10px;
        border: 1px solid #ccc;
        background-color: #f9f9f9;
        width: fit-content;
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
    flex-shrink: 0;
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
    <header>
        <h1>Payment Details</h1>
    </header>

    <div class="payment-details">
        <h3>Room Details</h3>
        <table>
            <thead>
                <tr>
                    <th>Hotel Name</th>
                    <th>Room Type</th>
                    <th>Price per Day</th>
                    <th>Number of Days</th>
                    <th>Total Cost</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rooms_details as $room) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($room['hotel_name']); ?></td>
                        <td><?php echo htmlspecialchars($room['room_type']); ?></td>
                        <td>₹<?php echo htmlspecialchars($room['price']); ?></td>
                        <td><?php echo htmlspecialchars($room['no_of_days']); ?></td>
                        <td>₹<?php echo htmlspecialchars($room['total_cost']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="total-amount">
            <p>Total Room Cost: ₹<?php echo $total_room_cost; ?></p>
            <p>Taxes (10%): ₹<?php echo $tax; ?></p>
            <p>Service Fee: ₹<?php echo $service_fee; ?></p>
            <strong>Total Amount: ₹<?php echo $total_amount; ?></strong>
        </div>

        <form action="make_payment.php" method="POST">
            <input type="hidden" name="booked_rooms" value='<?php echo json_encode($booked_rooms); ?>'>
            <div class="action-buttons">
                <button type="submit" name="confirm_payment">Confirm Payment</button>
            </div>
        </form>
    </div>
    <footer>
        <div class="footer-center">
            <p>&copy; 2025 Your Website. All Rights Reserved.</p>
        </div>
</footer>
</body>
</html>
