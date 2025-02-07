<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
include('db.php');

// Get the destination and dates from the query parameters
$destination = $_GET['destination'] ?? '';
$checkin = $_GET['checkin'] ?? '';
$checkout = $_GET['checkout'] ?? '';


// Fetch hotels in the selected destination
function getHotelsByDestination($destination) {
    global $conn;
    $sql = "SELECT * FROM hotels WHERE location = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $destination);
    $stmt->execute();
    $result = $stmt->get_result();
    $hotels = [];
    while ($row = $result->fetch_assoc()) {
        $hotels[] = $row;
    }
    return $hotels;
}

// Function to get the full image path or a default image
function getImagePath($imagePath) {
    // Construct the base URL dynamically
    $baseURL = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);

    // Check if the image path is valid
    $filePath = $_SERVER['DOCUMENT_ROOT'] . '/DBMS PROJECT' . $imagePath;
    if (!empty($imagePath) && file_exists($filePath)) {
        return $baseURL . $imagePath; // Return the valid image URL
    }
    return $baseURL . '/images/default.jpg'; // Return the default image URL
}

$hotels = getHotelsByDestination($destination);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotels in <?php echo htmlspecialchars($destination); ?></title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
    background-color: #f9f9f9; /* Very Light Gray Background */
    color: #34495e; /* Dark Gray Text Color */
    font-family: Arial, sans-serif;
    background-image: url('./images/bg7.jpg'); /* Set the background image */
    background-size: cover; /* Make sure the image covers the entire viewport */
    background-position: center; /* Keep the image centered */
    background-attachment: fixed; /* Ensure the image stays fixed during scrolling */
    background-repeat: no-repeat; /* Prevent repeating the image */
    min-height: 100vh; /* Ensure the body is at least 100% of the viewport height */
    margin: 0; /* Remove any default margin */
    padding: 0; /* Remove any default padding */
}


        header {
            background-color: #2c3e50; /* Dark Blue-Gray */
            color: white;
            padding: 20px;
            text-align: center;
        }

        .hotel-list {
            display: flex;
            flex-wrap: wrap; /* Allow cards to wrap on smaller screens */
            justify-content: center; /* Center align the cards */
            gap: 20px; /* Add spacing between cards */
            margin: 20px;
        }

        .hotel-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            width: 500px;
            height: 450px;
            padding: 15px;
            background-color: #ffffff; /* Clean white background */
            border: 1px solid #ddd; /* Subtle border for separation */
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Slight shadow for depth */
            overflow: hidden;
            text-align: center;
        }

        .hotel-card h3 {
            font-size: 1.5em;
            color: #2c3e50; /* Dark Blue-Gray for headings */
            margin: 10px 0;
        }

        .hotel-card p {
            font-size: 1em;
            color: #555; /* Neutral color for descriptions */
            margin: 5px 0;
        }

        .hotel-card button {
            padding: 10px 20px;
            font-size: 1em;
            color: #ffffff;
            background-color: #e74c3c; /* Ruby Red for buttons */
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .hotel-card button:hover {
            background-color: #c0392b; /* Darker red on hover */
        }

        .hotel-card img {
            max-width: 100%; /* Make the image responsive */
            height: auto; /* Maintain aspect ratio */
            border-radius: 8px; /* Add smooth corners */
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2); /* Add a subtle shadow */
            margin-bottom: 15px; /* Add some spacing below the image */
            object-fit: cover; /* Ensure the image fits the container neatly */
            max-height: 200px; /* Limit the height to ensure consistency */
            width: auto;
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
        <h1>Hotels in <?php echo htmlspecialchars($destination); ?></h1>
    </header>
    <div class="hotel-list">
        <?php if (!empty($hotels)) : ?>
            <?php foreach ($hotels as $hotel) : ?>
                <div class="hotel-card">
                    <img src="<?php echo htmlspecialchars(getImagePath($hotel['image_path'])); ?>" alt="<?php echo htmlspecialchars($hotel['name']); ?>">
                    <h3><?php echo htmlspecialchars($hotel['name']); ?></h3>
                    <p><?php echo htmlspecialchars($hotel['description']); ?></p>
                    <p><strong>Starting Price:</strong> ₹ <?php echo htmlspecialchars($hotel['starting_price']); ?></p>
                    <form action="rooms.php" method="GET">
                        <input type="hidden" name="hotel_id" value="<?php echo htmlspecialchars($hotel['id']); ?>">
                        <input type="hidden" name="checkin" value="<?php echo htmlspecialchars($checkin); ?>">
                        <input type="hidden" name="checkout" value="<?php echo htmlspecialchars($checkout); ?>">
                        <button type="submit">Book Hotel</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p>No hotels found in this destination.</p>
        <?php endif; ?>
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
