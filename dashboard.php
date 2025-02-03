<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

// Get the logged-in user's ID
$username = $_SESSION['username'];
$user_result = $conn->query("SELECT id FROM users WHERE username = '$username'");
$user = $user_result->fetch_assoc();
$user_id = $user['id'];

// Handle ticket booking
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_ticket'])) {
    $movie_id = $_POST['movie_id'];
    $seat_number = $_POST['seat_number'];

    // Check if the seat is available
    $seat_result = $conn->query("SELECT * FROM seats WHERE movie_id = $movie_id AND seat_number = '$seat_number' AND is_booked = FALSE");
    if ($seat_result->num_rows > 0) {
        // Mark the seat as booked
        $conn->query("UPDATE seats SET is_booked = TRUE WHERE movie_id = $movie_id AND seat_number = '$seat_number'");

        // Insert booking record
        $conn->query("INSERT INTO bookings (user_id, movie_id, seat_number) VALUES ($user_id, $movie_id, '$seat_number')");

        echo "<p>Booking successful! Seat: $seat_number</p>";
    } else {
        echo "<p>Seat $seat_number is already booked. Please choose another seat.</p>";
    }
}
?>
