<?php
include('db.php');

// Handle registration
$success = null; // Initialize success message variable
$error = null;   // Initialize error message variable

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $sql = "INSERT INTO users (name, email, phone, username, password) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssss', $name, $email, $phone, $username, $password);

    if ($stmt->execute()) {
        $success = true;
    } else {
        $error = "Error creating account: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Apply the moving background effect */
        body {
            margin: 0;
            padding: 0;
            background: url('./images/bg1.jpg') no-repeat center center fixed;
            background-size: cover;
            animation: move-bg 10s linear infinite;
            font-family: Arial, sans-serif;
        }
        @keyframes move-bg {
            0% { background-position: 0 0; }
            100% { background-position: 100% 100%; }
        }
        /* Center the content */
        .form-container {
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            flex-direction: column;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            margin-bottom: 20px;
        }
        label, input, button {
            display: block;
            width: 100%;
            margin-bottom: 15px;
        }
        input, button {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .error {
            color: red;
        }
        html, body {
    height: 100%; /* Ensure the body and html take up full height */
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column; /* Stack the elements vertically */
}
/* Success Box Styles */
.success-box {
    max-width: 400px;
    margin: 50px auto;
    padding: 20px;
    background-color: #f8f9fa; /* Light grey background */
    color: #343a40; /* Dark grey text */
    border: 1px solid #ced4da; /* Border slightly darker grey */
    border-radius: 8px;
    text-align: center;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    font-family: Arial, sans-serif;
}

.success-box h2 {
    font-size: 24px;
    margin-bottom: 15px;
}

.success-box p {
    font-size: 16px;
    margin-bottom: 20px;
}

.success-button {
    padding: 10px 20px;
    background-color: #6c757d; /* Grey button */
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    font-weight: bold;
    transition: background-color 0.3s ease;
}

.success-button:hover {
    background-color: #495057; /* Dark grey on hover */
}

.success-button:active {
    background-color: #343a40; /* Even darker grey on click */
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
    <div class="form-container">
        <?php if ($success): ?>
            <!-- Display success message -->
            <div class="success-box">
                <h2>Registration Successful</h2>
                <p>Account created successfully! You can now log in.</p>
                <form action="login.php" method="GET">
                    <button type="submit" class="success-button">Login</button>
                </form>
            </div>
        <?php else: ?>
            <!-- Display registration form -->
            <form method="POST">
                <h2>Register</h2>
                <?php if ($error): ?>
                    <p class="error"><?php echo $error; ?></p>
                <?php endif; ?>
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required placeholder="Enter your name">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required placeholder="Enter your email">
                <label for="phone">Phone:</label>
                <input type="text" id="phone" name="phone" required placeholder="Enter your phone number">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required placeholder="Enter your username">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required placeholder="Enter your password">
                <button type="submit" name="register">Register</button>
            </form>
        <?php endif; ?>
    </div>
    <footer>
        <div class="footer-center">
            <p>&copy; 2025 Your Website. All Rights Reserved.</p>
        </div>
</footer>
</body>
</html>
