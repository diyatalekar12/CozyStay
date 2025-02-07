<?php
include('db.php');

if (isset($_GET['hotel_id'])) {
    $hotel_id = $_GET['hotel_id'];
    
    // Fetch rooms based on hotel_id
    $sql = "SELECT * FROM rooms WHERE hotel_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $hotel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $rooms = [];
    while ($row = $result->fetch_assoc()) {
        $rooms[] = $row;
    }

    // Return rooms as JSON
    echo json_encode($rooms);
} else {
    echo json_encode([]);
}

$conn->close();
?>
