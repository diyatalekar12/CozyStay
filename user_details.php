<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

include('db.php');

$user_id = $_SESSION['user']['id']; // Assuming user info is stored in session

// Fetch user details
$sql = "SELECT name, email, phone, username, password FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die('MySQL prepare error: ' . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_details = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $field = $_POST['field'];
    $value = $_POST['value'];

    // Update user details in the database
    $update_sql = "UPDATE users SET $field = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    if ($update_stmt === false) {
        die('MySQL prepare error: ' . $conn->error);
    }
    $update_stmt->bind_param("si", $value, $user_id);
    if ($update_stmt->execute()) {
        // Update the session or reload details after successful update
        $user_details[$field] = $value;
        echo "<script>
                alert('$field updated successfully.');
                window.location.href = 'user_details.php';
              </script>";
        exit;
    } else {
        echo "Error updating $field: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
    font-family: Arial, sans-serif;
    background-color: #f9f9f9; /* Very Light Gray Background */
    margin: 0;
    padding: 20px;
}

.container {
    max-width: 800px;
    margin: 0 auto;
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
}

.container h1 {
    text-align: center;
    color: #2c3e50; /* Dark Blue-Gray */
}

.details {
    margin-top: 20px;
}

.details div {
    margin-bottom: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.details div span {
    font-size: 18px;
    color: #34495e; /* Dark Gray */
}

.details div button {
    background-color: #e74c3c; /* Ruby Red */
    color: white;
    padding: 8px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.details div button:hover {
    background-color: #c0392b; /* Darker Ruby Red */
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    justify-content: center;
    align-items: center;
}

.modal-content {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    width: 400px;
    text-align: center;
}

.modal-content form input {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    border: 1px solid #ddd;
    border-radius: 5px;
}

.modal-content form button {
    background-color: #e74c3c; /* Ruby Red */
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.modal-content form button:hover {
    background-color: #c0392b; /* Darker Ruby Red */
}

.close {
    background: none;
    border: none;
    font-size: 18px;
    color: #333;
    cursor: pointer;
    position: absolute;
    top: 10px;
    right: 10px;
}

.go-back {
    margin-top: 20px;
    text-align: center;
}

.go-back .button {
    text-decoration: none;
    color: white;
    background-color: #2c3e50; /* Dark Blue-Gray */
    padding: 10px 20px;
    border-radius: 5px;
    font-size: 16px;
    font-weight: bold;
}

.go-back .button:hover {
    background-color: #34495e; /* Slightly darker Dark Blue-Gray */
}

    </style>
</head>
<body>

<div class="container">
    <h1>User Details</h1>
    <div class="details">
        <div>
            <span>Name: <?php echo htmlspecialchars($user_details['name']); ?></span>
            <button onclick="openModal('name', 'Change Name')">Change Name</button>
        </div>
        <div>
            <span>Email: <?php echo htmlspecialchars($user_details['email']); ?></span>
            <button onclick="openModal('email', 'Change Email')">Change Email</button>
        </div>
        <div>
            <span>Phone: <?php echo htmlspecialchars($user_details['phone']); ?></span>
            <button onclick="openModal('phone', 'Change Phone Number')">Change Phone Number</button>
        </div>
        <div>
            <span>Username: <?php echo htmlspecialchars($user_details['username']); ?></span>
        </div>
        <div>
            <span>Password: ********</span>
        </div>
    </div>
</div>

<div class="go-back">
    <a href="javascript:void(0);" onclick="goBack()" class="button">Go Back</a>
</div>

<script>
function goBack() {
    window.history.back();
}
</script>


<!-- Modal -->
<div id="modal" class="modal">
    <div class="modal-content">
        <button class="close" onclick="closeModal()">×</button>
        <h2 id="modal-title"></h2>
        <form method="POST" action="user_details.php">
            <input type="hidden" name="field" id="field-name">
            <input type="text" name="value" id="field-value" required>
            <button type="submit">Save</button>
        </form>
    </div>
</div>

<script>
    function openModal(field, title) {
        document.getElementById('modal').style.display = 'flex';
        document.getElementById('modal-title').innerText = title;
        document.getElementById('field-name').value = field;
        document.getElementById('field-value').placeholder = `Enter new ${field}`;
    }

    function closeModal() {
        document.getElementById('modal').style.display = 'none';
    }
</script>

</body>
</html>
