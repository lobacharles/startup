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

<!DOCTYPE html>
<html>
<head>
    <title>Movie Ticketing Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        h1 {
            color: #333;
        }
        .movie-list, .booking-list {
            margin-bottom: 30px;
        }
        .movie, .booking {
            background: #fff;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .movie h3, .booking h3 {
            margin: 0 0 10px;
        }
        .movie p, .booking p {
            margin: 5px 0;
        }
        .seat-selection {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .seat {
            padding: 10px;
            background-color: #28a745;
            color: #fff;
            border-radius: 4px;
            cursor: pointer;
        }
        .seat.booked {
            background-color: #ccc;
            cursor: not-allowed;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Welcome, <?php echo $_SESSION['username']; ?>!</h1>
    <p><a href="logout.php">Logout</a></p>

    <!-- Available Movies -->
    <div class="movie-list">
        <h2>Available Movies</h2>
        <?php
        $movies_result = $conn->query("SELECT * FROM movies");
        while ($movie = $movies_result->fetch_assoc()) {
            echo "<div class='movie'>";
            echo "<h3>" . $movie['title'] . "</h3>";
            echo "<p>" . $movie['description'] . "</p>";
            echo "<p>Showtime: " . $movie['showtime'] . "</p>";

            // Fetch available seats for this movie
            $seats_result = $conn->query("SELECT * FROM seats WHERE movie_id = " . $movie['id'] . " AND is_booked = FALSE");
            echo "<div class='seat-selection'>";
            while ($seat = $seats_result->fetch_assoc()) {
                echo "<form method='POST' action='' style='display: inline;'>";
                echo "<input type='hidden' name='movie_id' value='" . $movie['id'] . "'>";
                echo "<input type='hidden' name='seat_number' value='" . $seat['seat_number'] . "'>";
                echo "<button type='submit' name='book_ticket' class='seat'>" . $seat['seat_number'] . "</button>";
                echo "</form>";
            }
            echo "</div>";
            echo "</div>";
        }
        ?>
    </div>

    <!-- User Bookings -->
    <div class="booking-list">
        <h2>Your Bookings</h2>
        <?php
        $bookings_result = $conn->query("
            SELECT bookings.*, movies.title 
            FROM bookings 
            JOIN movies ON bookings.movie_id = movies.id 
            WHERE bookings.user_id = $user_id
        ");
        while ($booking = $bookings_result->fetch_assoc()) {
            echo "<div class='booking'>";
            echo "<h3>" . $booking['title'] . "</h3>";
            echo "<p>Seat: " . $booking['seat_number'] . "</p>";
            echo "<p>Booking Time: " . $booking['booking_time'] . "</p>";
            echo "</div>";
        }
        ?>
    </div>
</body>
</html>