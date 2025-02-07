<?php
include('db.php');

// Function to get rooms based on hotel_id
function getRooms($hotel_id) {
    global $conn;
    $sql = "SELECT * FROM rooms WHERE hotel_id = $hotel_id";
    $result = $conn->query($sql);
    $rooms = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $rooms[] = $row;
        }
    }
    return $rooms;
}

if (isset($_GET['hotel_id'])) {
    $hotel_id = $_GET['hotel_id'];
    $rooms = getRooms($hotel_id);
    echo json_encode($rooms);
}

$conn->close();
?>
