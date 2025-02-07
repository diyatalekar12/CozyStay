<?php
session_start();
include('db.php');

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "User not found!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <style>
        body {
    margin: 0;
    padding: 0;
    font-family: Arial, sans-serif;
    overflow: hidden;
    background-color: #f9f9f9; /* Very Light Gray */
}

body::before {
    content: "";
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: url('./images/bg9.jpg') no-repeat center center fixed;
    background-size: cover;
    z-index: -1;
    animation: moveBackground 20s linear infinite;
}

@keyframes moveBackground {
    0% {
        background-position: 0 0;
    }
    100% {
        background-position: -100px -100px;
    }
}

header {
    background-color: #2c3e50; /* Dark Blue-Gray */
    color: white;
    padding: 10px;
    text-align: center;
    font-size: 20px;
    font-weight: bold;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.form-container {
    position: absolute;
    top: 60%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(255, 255, 255, 0.9);
    padding: 20px; /* Reduced padding */
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    width: 280px; /* Reduced width */
}

.form-container h2 {
    text-align: center;
    margin-bottom: 15px; /* Reduced margin */
    color: #2c3e50; /* Dark Blue-Gray */
    font-size: 20px; /* Reduced font size */
}

.form-container label {
    display: block;
    margin-bottom: 8px;
    color: #34495e; /* Dark Gray */
    font-size: 14px; /* Reduced font size */
}

.form-container input {
    width: 100%;
    padding: 8px; /* Reduced padding */
    margin-bottom: 12px; /* Reduced margin */
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 14px; /* Reduced font size */
    color: #34495e; /* Dark Gray */
}

.form-container button {
    width: 100%;
    padding: 10px; /* Reduced padding */
    background: #e74c3c; /* Blue */
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px; /* Reduced font size */
    font-weight: bold;
}

.form-container button:hover {
    background: rgb(230, 60, 41); /* Darker Blue */
}

.form-container p {
    text-align: center;
    font-size: 14px; /* Reduced font size */
    color: #34495e; /* Dark Gray */
}

.form-container a {
    color: #e74c3c; /* Blue */
    text-decoration: none;
    font-size: 14px; /* Reduced font size */
}

.error {
    color: #e74c3c; /* Ruby Red */
    text-align: center;
    margin-bottom: 10px;
    font-weight: bold;
    font-size: 14px; /* Reduced font size */
}

/* Ensuring the footer is at the bottom of the page */
/* Ensuring the footer is at the bottom of the page */
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
    <header>
        <h1>Hotel Booking System</h1>
    </header>
    <div class="form-container">
        <form method="POST">
            <h2>Login</h2>
            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required placeholder="Enter your username">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required placeholder="Enter your password">
            <button type="submit" name="login">Login</button>
            <p>Don't have an account? <br><a href="register.php">Register here</a></p>
        </form>
    </div>
    <footer>
        <div class="footer-center">
            <p>&copy; 2025 Your Website. All Rights Reserved.</p>
        </div>
</footer>
</body>
</html>