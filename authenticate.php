<?php

session_start();

$host = "localhost";
$username = "root";
$password = "";
$database = "smart_greenhouse";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed.");
}

$user = trim($_POST['username'] ?? '');
$pass = $_POST['password'] ?? '';

if ($user === '' || $pass === '') {
    header("Location: login.php?error=required");
    exit;
}

$stmt = $conn->prepare(
    "SELECT id, username, password, role
     FROM users
     WHERE username = ?
     LIMIT 1"
);

$stmt->bind_param("s", $user);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 1) {

    $account = $result->fetch_assoc();

    if (password_verify($pass, $account['password'])) {

        // Login successful
        session_regenerate_id(true);

        $_SESSION['user_id'] = $account['id'];
        $_SESSION['username'] = $account['username'];
        $_SESSION['role'] = $account['role'];

        header("Location: index.php");
        exit;
    }
}

// Login failed
header("Location: login.php?error=invalid");
exit;
?>