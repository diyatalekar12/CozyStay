<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
include('db.php');

// Function to get all unique locations from the Hotels table
function getHotelLocations() {
    global $conn;
    $sql = "SELECT DISTINCT location FROM hotels";
    $result = $conn->query($sql);
    $locations = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $locations[] = $row['location'];
        }
    }
    return $locations;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['destination'])) {
    $destination = $_GET['destination'];
    $checkin = $_GET['checkin'];
    $checkout = $_GET['checkout'];

    // Validate dates
    if (strtotime($checkin) >= strtotime($checkout)) {
        echo "<script>alert('Check-in date must be earlier than check-out date.'); window.location.href='hotels.php';</script>";
        exit;
    }

    // Insert search details into bookings table
    $insert_sql = "INSERT INTO bookings (users_id, hotel_id, checkin, checkout, status, search_time) VALUES (?, ?, ?, ?, ?, ?)";
    $status = 'searched'; // Assuming 'searched' means the user initiated a search
    $search_time = date('Y-m-d H:i:s'); // Storing the time of the search
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param('iissss', $_SESSION['user'], $hotel_id, $checkin, $checkout, $status, $search_time);

    if ($stmt->execute()) {
        echo "Search details recorded!";
    } else {
        echo "Error recording search: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Booking System</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Moving background effect */
        /* Ensure full viewport height */
html, body {
    height: 100%; /* Set both to 100% of the screen */
    margin: 0;
    padding: 0;
    overflow: hidden; /* Prevent scrolling */
}

body {
    background: url('./images/bg1.jpg') no-repeat center center fixed;
    background-size: cover;
    animation: move-bg 10s linear infinite;
    font-family: Arial, sans-serif;
}

/* Prevent background scrolling */
@keyframes move-bg {
    0% { background-position: 0 0; }
    100% { background-position: 100% 100%; }
}

/* Header styling */
header {
    text-align: center;
    padding: 10px 0; /* Reduce the padding for a smaller header */
    font-family: 'Times New Roman', Times, serif;
    background: none;
    position: absolute; /* Fix the header at the top */
    width: 100%;
    top: 0;
    background-color: #1a2634;
}

header h1 {
    font-size: 3rem; /* Smaller font size */
    margin: 0;
    color: white;
}

header ul {
    list-style-type: disc; /* Add bullet points */
    padding-left: 10px;
    margin: 10px 0;
    text-align: center;
    color: white;
    font-size: 1rem; /* Smaller font size for list items */
}


/* Center the form and style it */
.form-container {
    display: flex;
    justify-content: center;
    align-items: center; /* Align the form in the center of the screen */
    height: 100%; /* Full height */
    flex-direction: column;
    padding-top: 10px; /* Optional: adjust padding to center form better */
}

.subtitle {
    display: inline-flex;  /* Keep items in a line */
    gap: 20px; /* Space between the items */
    align-items: center;  /* Vertically align the text */
}

.subtitle span::before {
    content: "•";  /* Bullet symbol */
    color:white; /* Optional: color for the bullet */
    font-size: 1.5rem; /* Optional: size of the bullet */
    margin-right: 10px; /* Space between bullet and text */
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

/* Booking form style */
.booking-form {
    border: 2px solid #2c3e50; /* Primary Color */
    border-radius: 10px;
    padding: 20px;
    background-color: #ecf0f1; /* Secondary Color */
    width: 50%;
    max-width: 400px;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    margin-top: 160px;
}

.booking-form h2 {
    text-align: center;
    color: #2c3e50; /* Primary Color */
    margin-bottom: 20px;
}

.booking-form label {
    display: block;
    margin: 10px 0 5px;
    color: #34495e; /* Text Color */
    font-size: 14px;
    font-weight: bold;
}

.booking-form select,
.booking-form input[type="date"],
.booking-form button {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #34495e; /* Text Color */
    border-radius: 5px;
    background-color: #ecf0f1; /* Secondary Color */
    color: #34495e; /* Text Color */
    font-size: 14px;
}

.booking-form button {
    background-color: #2c3e50; /* Primary Color */
    color: #ecf0f1; /* Secondary Color */
    border: none;
    cursor: pointer;
    font-size: 16px;
    font-weight: bold;
    transition: background-color 0.3s ease-in-out;
}

.booking-form button:hover {
    background-color: #e74c3c; /* Accent Color */
}

/* General Text Color for Error or Success Messages */
.booking-form .message {
    color: #e74c3c; /* Accent Color */
    font-size: 12px;
    text-align: center;
}


/* Profile Container - fixed position */
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
        <h1>CozyStay</h1>
        <div class="subtitle">
            <span>Resorts</span>
            <span>Hotel</span>
            <span>Apartment</span>
            <span>Lodge</span>
        </div>
    </header>
    <div class="form-container">
    <form class="booking-form" method="GET" action="hotels.php" onsubmit="return validateForm()">
        <h2>Search Hotels</h2>

        <label for="destination">Destination:</label>
        <select id="destination" name="destination" required>
            <option value="" disabled selected>Select a destination</option>
            <?php
            $locations = getHotelLocations();
            foreach ($locations as $location) {
                echo "<option value='" . htmlspecialchars($location) . "'>" . htmlspecialchars($location) . "</option>";
            }
            ?>
        </select>

        <label for="checkin">Check-In Date:</label>
        <input type="date" id="checkin" name="checkin" required>

        <label for="checkout">Check-Out Date:</label>
        <input type="date" id="checkout" name="checkout" required>

        <button type="submit">Search Hotels</button>
    </form>
</div>

    
    <script>
        function toggleDropdown() {
            var menu = document.getElementById("dropdownMenu");
            menu.classList.toggle("show");
        }

        function validateForm() {
    const checkin = document.getElementById('checkin').value;
    const checkout = document.getElementById('checkout').value;

    if (new Date(checkin) >= new Date(checkout)) {
        alert('Check-in date must be earlier than check-out date.');
        return false; // Prevent form submission
    }
    return true; // Allow form submission
}

    </script>
    <footer>
        <div class="footer-center">
            <p>&copy; 2025 Your Website. All Rights Reserved.</p>
        </div>
</footer>
</body>
</html>
