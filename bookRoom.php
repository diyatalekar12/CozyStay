<?php
include ('db.php'); // Ensure correct database connection

// Enable error reporting to debug issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve data from the request
    $hotel_id = intval($_POST['hotel_id']);
    $room_id = intval($_POST['room_id']);
    $guest_name = $_POST['guest_name'];
    $guest_email = $_POST['guest_email'];
    $guest_phone = $_POST['guest_phone'];
    $checkin = $_POST['checkin'];
    $checkout = $_POST['checkout'];
    $status = "Confirmed";

    try {
        $conn->begin_transaction();

        // Insert guest details
        $guest_sql = "INSERT INTO guests (name, email, phone) VALUES (?, ?, ?)";
        $guest_stmt = $conn->prepare($guest_sql);
        $guest_stmt->bind_param("sss", $guest_name, $guest_email, $guest_phone);
        $guest_stmt->execute();
        $guest_id = $conn->insert_id;

        // Insert booking details
        $booking_sql = "INSERT INTO bookings (guest_id, hotel_id, room_id, checkin, checkout, status) VALUES (?, ?, ?, ?, ?, ?)";
        $booking_stmt = $conn->prepare($booking_sql);
        $booking_stmt->bind_param("iiisss", $guest_id, $hotel_id, $room_id, $checkin, $checkout, $status);
        $booking_stmt->execute();
        $booking_id = $conn->insert_id;

        // Insert payment details (assuming full payment)
        $payment_amount = 500; // Example value, replace with actual calculation
        $payment_status = "Paid";
        $payment_sql = "INSERT INTO payments (booking_id, amount, payment_date, payment_status) VALUES (?, ?, NOW(), ?)";
        $payment_stmt = $conn->prepare($payment_sql);
        $payment_stmt->bind_param("ids", $booking_id, $payment_amount, $payment_status);
        $payment_stmt->execute();

        $conn->commit();

        // Return success response
        echo json_encode(["success" => true, "message" => "Booking successful!", "booking_id" => $booking_id]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(["success" => false, "message" => "Booking failed. " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}

$conn->close();
?>
