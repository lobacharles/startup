<?php
session_start();
include 'db.php';

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        echo json_encode(["success" => false, "error" => "Please fill in all fields."]);
        exit();
    }

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['username'] = $username;
            $_SESSION['user_id'] = $user['id'];
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => "Invalid username or password."]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "Invalid username or password."]);
    }
}
?>
