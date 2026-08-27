<?php

header("Content-Type: application/json");

// Database settings
$host = "localhost";
$username = "root";
$password = "";
$database = "smart_greenhouse";

// Connect to MySQL
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed"
    ]);
    exit;
}

// Get sensor values
$temperature = $_GET['temperature'] ?? null;
$humidity = $_GET['humidity'] ?? null;
$soil_moisture = $_GET['soil_moisture'] ?? null;
$light_intensity = $_GET['light_intensity'] ?? null;

// Check that all values were received
if ($temperature === null ||
    $humidity === null ||
    $soil_moisture === null ||
    $light_intensity === null) {

    echo json_encode([
        "status" => "error",
        "message" => "Missing sensor data"
    ]);
    exit;
}

// Insert sensor data
$sql = "INSERT INTO sensor_data
        (temperature, humidity, soil_moisture, light_intensity)
        VALUES (?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "diii",
    $temperature,
    $humidity,
    $soil_moisture,
    $light_intensity
);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Sensor data saved"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to save sensor data"
    ]);
}

$stmt->close();
$conn->close();

?>