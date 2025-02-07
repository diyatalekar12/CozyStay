<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hotel_id = $_POST['hotel_id'];
    $room_id = $_POST['room_id'];

    if (!isset($_SESSION['temp_bookings'])) {
        $_SESSION['temp_bookings'] = [];
    }
    <?php
    session_start();
    
    // Check if room details are passed via POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['room_id'], $_POST['hotel_id'], $_POST['checkin'], $_POST['checkout'])) {
        $room_id = $_POST['room_id'];
        $hotel_id = $_POST['hotel_id'];
        $checkin = $_POST['checkin'];
        $checkout = $_POST['checkout'];
    
        // Fetch room details from the database
        include('db.php');
        $stmt = $conn->prepare("SELECT * FROM rooms WHERE id = ?");
        $stmt->bind_param('i', $room_id);
        $stmt->execute();
        $room = $stmt->get_result()->fetch_assoc();
    
        if ($room) {
            // Store selected room details in session
            $selected_room = [
                'id' => $room['id'],
                'type' => $room['type'],
                'price' => $room['price'],
                'availability' => $room['availability'],
            ];
    
            // Initialize selected_rooms array in session if not already set
            if (!isset($_SESSION['selected_rooms'])) {
                $_SESSION['selected_rooms'] = [];
            }
    
            // Add the room to selected_rooms array in session
            $_SESSION['selected_rooms'][] = $selected_room;
    
            echo "Room added to booking.";
        } else {
            echo "Room not found.";
        }
    } else {
        echo "Invalid request.";
    }
    
    $_SESSION['temp_bookings'][] = [
        'hotel_id' => $hotel_id,
        'room_id' => $room_id
    ];

    echo "Room added successfully!";
    exit;
}
