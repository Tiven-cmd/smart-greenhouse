<?php

header("Content-Type: application/json");

$host = "localhost";
$username = "root";
$password = "";
$database = "smart_greenhouse";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed"
    ]);
    exit;
}

$sql = "SELECT temperature, humidity, soil_moisture, light_intensity, created_at
        FROM sensor_data
        ORDER BY id DESC
        LIMIT 1";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {

    $data = $result->fetch_assoc();

    echo json_encode([
        "status" => "success",
        "data" => $data
    ]);

} else {

    echo json_encode([
        "status" => "error",
        "message" => "No sensor data"
    ]);
}

$conn->close();
?>