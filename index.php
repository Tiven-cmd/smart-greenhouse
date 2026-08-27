<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}



// ========================================
// DATABASE CONNECTION
// ========================================

$host = "localhost";
$username = "root";
$password = "";
$database = "smart_greenhouse";

$conn = new mysqli(
    $host,
    $username,
    $password,
    $database
);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}


// ========================================
// GET LATEST SENSOR DATA
// ========================================

$sql_latest = "
    SELECT *
    FROM sensor_data
    ORDER BY id DESC
    LIMIT 1
";

$result_latest = $conn->query($sql_latest);

if ($result_latest && $result_latest->num_rows > 0) {
    $data = $result_latest->fetch_assoc();
} else {
    $data = [
        "temperature" => 0,
        "humidity" => 0,
        "soil_moisture" => 0,
        "light_intensity" => 0,
        "created_at" => "No sensor data"
    ];
}


// ========================================
// SENSOR HISTORY
// ========================================

$sql_history = "
    SELECT
        id,
        temperature,
        humidity,
        soil_moisture,
        light_intensity,
        created_at
    FROM sensor_data
    ORDER BY id DESC
    LIMIT 10
";

$result_history = $conn->query($sql_history);

$conn->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Smart Greenhouse | Dashboard</title>

    <style>

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            font-family:
                Inter,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                Arial,
                sans-serif;
            background: #f5f8f6;
            color: #17221b;
        }

        button {
            font-family: inherit;
        }

        /* ========================================
           TOP HEADER
        ======================================== */

        .top-header {
            background:
                linear-gradient(
                    135deg,
                    #103d26 0%,
                    #17663c 55%,
                    #21804b 100%
                );
            color: white;
            padding: 34px 24px 70px;
            position: relative;
            overflow: hidden;
        }

        .top-header::before {
            content: "";
            position: absolute;
            width: 330px;
            height: 330px;
            border-radius: 50%;
            background: rgba(255,255,255,.055);
            right: -100px;
            top: -180px;
        }

        .top-header::after {
            content: "";
            position: absolute;
            width: 230px;
            height: 230px;
            border-radius: 50%;
            background: rgba(255,255,255,.04);
            left: -100px;
            bottom: -170px;
        }

        .header-inner {
            width: 92%;
            max-width: 1250px;
            margin: auto;
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 25px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .brand-mark {
            width: 54px;
            height: 54px;
            border-radius: 17px;
            background: rgba(255,255,255,.13);
            border: 1px solid rgba(255,255,255,.16);
            display: grid;
            place-items: center;
            font-size: 28px;
            box-shadow: 0 10px 30px rgba(0,0,0,.12);
        }

        .brand h1 {
            margin: 0;
            font-size: 27px;
            letter-spacing: -.5px;
        }

        .brand p {
            margin: 5px 0 0;
            color: #c7dfd0;
            font-size: 13px;
        }

        .device-pill {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(255,255,255,.10);
            border: 1px solid rgba(255,255,255,.14);
            font-size: 13px;
            font-weight: 700;
        }

        .status-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: #65e69a;
            box-shadow: 0 0 0 5px rgba(101,230,154,.13);
        }

        /* ========================================
           MAIN
        ======================================== */

        .container {
            width: 92%;
            max-width: 1250px;
            margin: -42px auto 45px;
            position: relative;
            z-index: 2;
        }

        .welcome {
            background: white;
            border: 1px solid #e3ebe6;
            border-radius: 22px;
            padding: 22px 25px;
            box-shadow: 0 14px 40px rgba(26,56,39,.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
        }

        .welcome h2 {
            margin: 0;
            font-size: 21px;
        }

        .welcome p {
            margin: 6px 0 0;
            color: #718078;
            font-size: 13px;
        }

        .live-label {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: #1e7546;
            background: #e9f7ee;
            padding: 9px 13px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .5px;
        }

        /* ========================================
           SENSOR CARDS
        ======================================== */

        .cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 17px;
        }

        .card {
            background: white;
            border: 1px solid #e3ebe6;
            border-radius: 21px;
            padding: 22px;
            min-height: 190px;
            box-shadow: 0 10px 30px rgba(26,56,39,.065);
            position: relative;
            overflow: hidden;
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 36px rgba(26,56,39,.11);
        }

        .card::after {
            content: "";
            position: absolute;
            width: 115px;
            height: 115px;
            border-radius: 50%;
            background: #eff8f2;
            right: -48px;
            top: -48px;
        }

        .card-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .card-icon {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            background: #edf8f1;
            display: grid;
            place-items: center;
            font-size: 22px;
        }

        .card-tag {
            font-size: 9px;
            font-weight: 800;
            color: #7b8a82;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .card h2 {
            margin: 21px 0 4px;
            font-size: 12px;
            color: #74827a;
            text-transform: uppercase;
            letter-spacing: .8px;
        }

        .value {
            font-size: 34px;
            line-height: 1;
            font-weight: 850;
            color: #173e29;
            letter-spacing: -1.2px;
        }

        .unit {
            color: #6f7e75;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0;
        }

        .card-note {
            margin-top: 13px;
            font-size: 11px;
            color: #8a968f;
        }

        /* ========================================
           SECTION
        ======================================== */

        .section {
            margin-top: 22px;
            background: white;
            border: 1px solid #e3ebe6;
            border-radius: 22px;
            box-shadow: 0 10px 30px rgba(26,56,39,.065);
            overflow: hidden;
        }

        .section-head {
            padding: 21px 23px;
            border-bottom: 1px solid #edf1ee;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }

        .section-head h2 {
            margin: 0;
            font-size: 17px;
        }

        .section-head p {
            margin: 5px 0 0;
            color: #7b8880;
            font-size: 12px;
        }

        /* ========================================
           LAST UPDATE
        ======================================== */

        .time-content {
            padding: 19px 23px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }

        .time-label {
            color: #7b8880;
            font-size: 12px;
        }

        .time-value {
            font-weight: 800;
            color: #244b34;
            font-size: 13px;
        }

        /* ========================================
           CONTROLS
        ======================================== */

        .control-container {
            padding: 20px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        .control-card {
            background: #f8faf9;
            border: 1px solid #e5ece7;
            border-radius: 18px;
            padding: 20px;
            position: relative;
        }

        .control-card h2 {
            margin: 0 0 4px;
            font-size: 16px;
        }

        .control-description {
            color: #7a8780;
            font-size: 11px;
            margin-bottom: 18px;
        }

        .control-buttons {
            display: flex;
            gap: 8px;
        }

        .on-button,
        .off-button {
            flex: 1;
            border: 0;
            padding: 11px 14px;
            border-radius: 11px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 800;
            transition: transform .15s ease, opacity .15s ease;
        }

        .on-button {
            background: #238b57;
            color: white;
        }

        .off-button {
            background: #f0dede;
            color: #a33c3c;
        }

        .on-button:hover,
        .off-button:hover {
            transform: translateY(-1px);
            opacity: .9;
        }

        .status {
            margin: 14px 0 0;
            font-size: 11px;
            color: #6f7d75;
            font-weight: 700;
        }

        /* ========================================
           HISTORY
        ======================================== */

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 680px;
        }

        th {
            background: #f6f9f7;
            color: #75837b;
            padding: 13px 16px;
            text-align: center;
            font-size: 10px;
            letter-spacing: .7px;
            text-transform: uppercase;
            border-bottom: 1px solid #e7eee9;
        }

        td {
            padding: 13px 16px;
            text-align: center;
            border-bottom: 1px solid #edf1ee;
            color: #304238;
            font-size: 12px;
            font-weight: 600;
        }

        tbody tr:first-child {
            background: #f7fcf9;
        }

        tbody tr:hover {
            background: #f1f8f3;
        }

        tbody tr:last-child td {
            border-bottom: 0;
        }

        /* ========================================
           FOOTER
        ======================================== */

        .footer {
            text-align: center;
            color: #8a968f;
            font-size: 11px;
            padding: 26px 0 4px;
        }

        /* ========================================
           MOBILE
        ======================================== */

        @media (max-width: 950px) {

            .cards {
                grid-template-columns: repeat(2, 1fr);
            }

            .control-container {
                grid-template-columns: 1fr;
            }

        }

        @media (max-width: 600px) {

            .top-header {
                padding: 25px 17px 60px;
            }

            .header-inner {
                width: 100%;
                align-items: flex-start;
            }

            .device-pill {
                font-size: 11px;
                padding: 8px 10px;
            }

            .brand-mark {
                width: 45px;
                height: 45px;
                font-size: 22px;
            }

            .brand h1 {
                font-size: 20px;
            }

            .brand p {
                font-size: 11px;
            }

            .container {
                width: 94%;
                margin-top: -35px;
            }

            .welcome {
                padding: 18px;
                align-items: flex-start;
                flex-direction: column;
            }

            .cards {
                grid-template-columns: 1fr 1fr;
                gap: 11px;
            }

            .card {
                padding: 16px;
                min-height: 165px;
                border-radius: 17px;
            }

            .card-icon {
                width: 39px;
                height: 39px;
                border-radius: 12px;
                font-size: 19px;
            }

            .value {
                font-size: 27px;
            }

            .section-head {
                padding: 18px;
            }

            .control-container {
                padding: 13px;
            }

            .time-content {
                padding: 17px 18px;
                align-items: flex-start;
                flex-direction: column;
            }

        }

        @media (max-width: 390px) {

            .cards {
                grid-template-columns: 1fr;
            }

        }

    
        /* ========================================
           AUTOMATIC ALERTS
        ======================================== */

        .alert-panel {
            display: none;
            margin-top: 22px;
            border: 1px solid #efc3c3;
            border-radius: 22px;
            background: #fffafa;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(120,38,38,.07);
            animation: alertIn .25s ease;
        }

        .alert-panel.show {
            display: block;
        }

        @keyframes alertIn {
            from {
                opacity: 0;
                transform: translateY(-5px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-header {
            padding: 17px 20px;
            background: #fff1f1;
            border-bottom: 1px solid #f3dddd;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .alert-title {
            display: flex;
            align-items: center;
            gap: 9px;
            color: #8b3030;
            font-size: 14px;
            font-weight: 850;
        }

        .alert-count {
            min-width: 24px;
            height: 24px;
            padding: 0 7px;
            border-radius: 999px;
            background: #d94b4b;
            color: #fff;
            display: grid;
            place-items: center;
            font-size: 11px;
        }

        .alert-list {
            padding: 6px 20px 14px;
        }

        .alert-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            color: #633838;
            font-size: 12px;
        }

        .alert-item + .alert-item {
            border-top: 1px solid #f4e7e7;
        }

        .alert-icon {
            width: 36px;
            height: 36px;
            flex: 0 0 36px;
            border-radius: 11px;
            background: #ffe6e6;
            display: grid;
            place-items: center;
            font-size: 17px;
        }

        .alert-item strong {
            display: block;
            color: #7e2e2e;
            margin-bottom: 2px;
        }

        .alert-item span {
            color: #916c6c;
            font-size: 11px;
        }

        .normal-alert {
            display: none;
            align-items: center;
            gap: 9px;
            margin-top: 22px;
            padding: 13px 17px;
            border: 1px solid #d9ebdf;
            border-radius: 15px;
            background: #f4fbf6;
            color: #276b43;
            font-size: 12px;
            font-weight: 700;
        }

        .normal-alert.show {
            display: flex;
        }

        /* ========================================
           AUTOMATIC DEVICE STATUS
        ======================================== */

        .auto-status {
            display: flex;
            align-items: center;
            gap: 9px;
            margin-top: 17px;
            padding: 11px 12px;
            border-radius: 12px;
            background: #eef8f2;
            color: #246a43;
            font-size: 11px;
            font-weight: 800;
        }

        .auto-status-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: #35a765;
            box-shadow: 0 0 0 4px rgba(53,167,101,.12);
        }

        .auto-trigger {
            margin-top: 10px;
            color: #87938c;
            font-size: 10px;
        }

        .auto-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 10px;
            border-radius: 999px;
            background: #e9f7ee;
            color: #1e7546;
            font-size: 9px;
            font-weight: 850;
            letter-spacing: .6px;
        }

        .card.warning {
            border-color: #e6aaaa;
            box-shadow: 0 10px 30px rgba(160,55,55,.10);
        }

        .card.warning .card-icon {
            background: #fff0f0;
        }

        @media (max-width: 600px) {
            .alert-header {
                padding: 15px;
            }

            .alert-list {
                padding-left: 15px;
                padding-right: 15px;
            }
        }

    </style>

</head>

<body>


<script>
    // ========================================
    // ALERT LIMITS
    // Change these values anytime for your FYP.
    // ========================================
    const ALERT_LIMITS = {
        temperatureMax: 35,
        soilMoistureMin: 30,
        humidityMin: 40,
        lightIntensityMin: 300
    };
</script>


<!-- ========================================
     HEADER
======================================== -->

<header class="top-header">

    <div class="header-inner">

        <div class="brand">

            <div class="brand-mark">
                🌱
            </div>

            <div>

                <h1>
                    Smart Greenhouse
                </h1>

                <p>
                    Environmental Monitoring & Control System
                </p>

            </div>

        </div>

        <div class="device-pill">

            <span class="status-dot"></span>

            ESP32 System

        </div>

    </div>

</header>


<div class="container">

    <!-- ========================================
         WELCOME
    ======================================== -->

    <div class="welcome">

        <div>

            <h2>
                Greenhouse Overview
            </h2>

            <p>
                Monitor your environment and manage your greenhouse devices.
            </p>

            <p style="margin-top:8px; font-size:11px; color:#557064;">
                👤 Logged in as:
                <strong>
                    <?php echo htmlspecialchars($_SESSION['username']); ?>
                </strong>
                &nbsp;•&nbsp;
                <a href="logout.php"
                   style="color:#1e7546; font-weight:800; text-decoration:none;">
                    Logout
                </a>
            </p>

        </div>

        <div class="live-label">
            <span>●</span>
            LIVE MONITORING
        </div>

    </div>



    <!-- ========================================
         AUTOMATIC ALERT CENTER
    ======================================== -->

    <div class="alert-panel" id="alertPanel">

        <div class="alert-header">

            <div class="alert-title">
                <span>⚠️</span>
                <span>Greenhouse Alerts</span>
                <span class="alert-count" id="alertCount">0</span>
            </div>

            <span class="card-tag">
                LIVE CHECK
            </span>

        </div>

        <div class="alert-list" id="alertList"></div>

    </div>

    <div class="normal-alert" id="normalAlert">
        <span>✓</span>
        <span>All greenhouse conditions are within the configured limits.</span>
    </div>

    <!-- ========================================
         SENSOR CARDS
    ======================================== -->

    <div class="cards">


        <!-- TEMPERATURE -->

        <div class="card">

            <div class="card-top">

                <div class="card-icon">
                    🌡️
                </div>

                <div class="card-tag">
                    SENSOR 01
                </div>

            </div>

            <h2>
                Temperature
            </h2>

            <div class="value">

                <span id="temperatureValue">
                    <?php
                    echo htmlspecialchars(
                        $data['temperature']
                    );
                    ?>
                </span>

                <span class="unit">
                    °C
                </span>

            </div>

            <div class="card-note">
                Current greenhouse temperature
            </div>

        </div>


        <!-- HUMIDITY -->

        <div class="card">

            <div class="card-top">

                <div class="card-icon">
                    💧
                </div>

                <div class="card-tag">
                    SENSOR 02
                </div>

            </div>

            <h2>
                Humidity
            </h2>

            <div class="value">

                <span id="humidityValue">
                    <?php
                    echo htmlspecialchars(
                        $data['humidity']
                    );
                    ?>
                </span>

                <span class="unit">
                    %
                </span>

            </div>

            <div class="card-note">
                Relative air humidity
            </div>

        </div>


        <!-- SOIL MOISTURE -->

        <div class="card">

            <div class="card-top">

                <div class="card-icon">
                    🌱
                </div>

                <div class="card-tag">
                    SENSOR 03
                </div>

            </div>

            <h2>
                Soil Moisture
            </h2>

            <div class="value">

                <span id="soilValue">
                    <?php
                    echo htmlspecialchars(
                        $data['soil_moisture']
                    );
                    ?>
                </span>

            </div>

            <div class="card-note">
                Soil moisture level
            </div>

        </div>


        <!-- LIGHT INTENSITY -->

        <div class="card">

            <div class="card-top">

                <div class="card-icon">
                    ☀️
                </div>

                <div class="card-tag">
                    SENSOR 04
                </div>

            </div>

            <h2>
                Light Intensity
            </h2>

            <div class="value">

                <span id="lightValue">
                    <?php
                    echo htmlspecialchars(
                        $data['light_intensity']
                    );
                    ?>
                </span>

            </div>

            <div class="card-note">
                Current light level
            </div>

        </div>


    </div>


    <!-- ========================================
         LAST SENSOR UPDATE
    ======================================== -->

    <div class="section">

        <div class="section-head">

            <div>

                <h2>
                    System Activity
                </h2>

                <p>
                    Latest communication received from the sensor system.
                </p>

            </div>

            <div class="live-label">
                ● ONLINE
            </div>

        </div>

        <div class="time-content">

            <div class="time-label">
                Last sensor update
            </div>

            <div
                class="time-value"
                id="lastUpdate"
            >

                <?php
                echo htmlspecialchars(
                    $data['created_at']
                );
                ?>

            </div>

        </div>

    </div>


    <!-- ========================================
         GREENHOUSE CONTROLS
    ======================================== -->

    <div class="section">

        <div class="section-head">

            <div>

                <h2>
                    🌿 Greenhouse Device Control
                </h2>

                <p>
                    Devices are controlled automatically by the ESP32.
                </p>

            </div>

            <div class="auto-badge">
                🤖 AUTO MODE
            </div>

        </div>

        <div class="control-container">

            <!-- WATER PUMP -->

            <div class="control-card">

                <h2>
                    💦 Water Pump
                </h2>

                <div class="control-description">
                    Automatic irrigation system
                </div>

                <div class="auto-status">
                    <span class="auto-status-dot"></span>
                    ESP32 CONTROLLED
                </div>

                <div class="auto-trigger">
                    Trigger: soil moisture below configured level
                </div>

            </div>


            <!-- FAN -->

            <div class="control-card">

                <h2>
                    🌬️ Ventilation Fan
                </h2>

                <div class="control-description">
                    Automatic greenhouse ventilation
                </div>

                <div class="auto-status">
                    <span class="auto-status-dot"></span>
                    ESP32 CONTROLLED
                </div>

                <div class="auto-trigger">
                    Trigger: temperature above configured level
                </div>

            </div>


            <!-- GROW LIGHT -->

            <div class="control-card">

                <h2>
                    💡 Grow Light
                </h2>

                <div class="control-description">
                    Automatic supplemental lighting
                </div>

                <div class="auto-status">
                    <span class="auto-status-dot"></span>
                    ESP32 CONTROLLED
                </div>

                <div class="auto-trigger">
                    Trigger: light intensity below configured level
                </div>

            </div>

        </div>

    </div>


    <!-- ========================================
         SENSOR HISTORY
    ======================================== -->

    <div class="section">

        <div class="section-head">

            <div>

                <h2>
                    📊 Sensor History
                </h2>

                <p>
                    Latest 10 readings from your greenhouse.
                </p>

            </div>

            <div class="live-label">
                ● LIVE
            </div>

        </div>


        <div class="table-container">

            <table>

                <thead>

                    <tr>

                        <th>
                            Date & Time
                        </th>

                        <th>
                            Temperature
                        </th>

                        <th>
                            Humidity
                        </th>

                        <th>
                            Soil Moisture
                        </th>

                        <th>
                            Light Intensity
                        </th>

                    </tr>

                </thead>


                <tbody id="historyBody">

                <?php

                if (
                    $result_history &&
                    $result_history->num_rows > 0
                ) {

                    while (
                        $row =
                        $result_history->fetch_assoc()
                    ) {

                ?>

                    <tr>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $row['created_at']
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $row['temperature']
                            );
                            ?>
                            °C
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $row['humidity']
                            );
                            ?>
                            %
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $row['soil_moisture']
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $row['light_intensity']
                            );
                            ?>
                        </td>

                    </tr>

                <?php

                    }

                } else {

                ?>

                    <tr>

                        <td colspan="5">
                            No sensor history available.
                        </td>

                    </tr>

                <?php

                }

                ?>

                </tbody>

            </table>

        </div>

    </div>


    <div class="footer">
        Smart Greenhouse • ESP32 IoT Monitoring System
    </div>

</div>


<script>

// ========================================
// CONTROL DEVICE
// ========================================

// ========================================
// LIVE SENSOR DATA
// ========================================

async function updateSensorData() {

    try {

        const response =
            await fetch(
                "get_latest.php?ts=" + Date.now(),
                {
                    cache: "no-store"
                }
            );

        const result =
            await response.json();


        if (
            result.status === "success"
        ) {

            const data =
                result.data;


            document.getElementById(
                "temperatureValue"
            ).innerHTML =
                data.temperature;


            document.getElementById(
                "humidityValue"
            ).innerHTML =
                data.humidity;


            document.getElementById(
                "soilValue"
            ).innerHTML =
                data.soil_moisture;


            document.getElementById(
                "lightValue"
            ).innerHTML =
                data.light_intensity;


            if (
                document.getElementById(
                    "lastUpdate"
                )
            ) {

                document.getElementById(
                    "lastUpdate"
                ).innerHTML =
                    data.created_at;

            }


            checkAlerts(data);

        }

    } catch (error) {

        console.error(
            "Sensor update error:",
            error
        );

    }

}



// ========================================
// AUTOMATIC ALERT CHECK
// ========================================

function checkAlerts(data) {

    const temperature = Number(data.temperature);
    const humidity = Number(data.humidity);
    const soil = Number(data.soil_moisture);
    const light = Number(data.light_intensity);

    const alerts = [];

    if (!Number.isNaN(temperature) &&
        temperature > ALERT_LIMITS.temperatureMax) {

        alerts.push({
            icon: "🌡️",
            title: "Temperature is too high",
            detail:
                temperature +
                " °C — limit is " +
                ALERT_LIMITS.temperatureMax +
                " °C"
        });
    }

    if (!Number.isNaN(soil) &&
        soil < ALERT_LIMITS.soilMoistureMin) {

        alerts.push({
            icon: "🌱",
            title: "Soil moisture is too low",
            detail:
                soil +
                " — minimum is " +
                ALERT_LIMITS.soilMoistureMin
        });
    }

    if (!Number.isNaN(humidity) &&
        humidity < ALERT_LIMITS.humidityMin) {

        alerts.push({
            icon: "💧",
            title: "Humidity is too low",
            detail:
                humidity +
                "% — minimum is " +
                ALERT_LIMITS.humidityMin +
                "%"
        });
    }

    if (!Number.isNaN(light) &&
        light < ALERT_LIMITS.lightIntensityMin) {

        alerts.push({
            icon: "☀️",
            title: "Light intensity is too low",
            detail:
                light +
                " — minimum is " +
                ALERT_LIMITS.lightIntensityMin
        });
    }

    const panel =
        document.getElementById("alertPanel");

    const normal =
        document.getElementById("normalAlert");

    const list =
        document.getElementById("alertList");

    const count =
        document.getElementById("alertCount");

    document
        .querySelectorAll(".card")
        .forEach(function(card) {
            card.classList.remove("warning");
        });

    if (temperature > ALERT_LIMITS.temperatureMax) {

        document
            .getElementById("temperatureValue")
            .closest(".card")
            .classList.add("warning");
    }

    if (humidity < ALERT_LIMITS.humidityMin) {

        document
            .getElementById("humidityValue")
            .closest(".card")
            .classList.add("warning");
    }

    if (soil < ALERT_LIMITS.soilMoistureMin) {

        document
            .getElementById("soilValue")
            .closest(".card")
            .classList.add("warning");
    }

    if (light < ALERT_LIMITS.lightIntensityMin) {

        document
            .getElementById("lightValue")
            .closest(".card")
            .classList.add("warning");
    }

    count.textContent =
        alerts.length;

    if (alerts.length === 0) {

        panel.classList.remove("show");
        normal.classList.add("show");
        list.innerHTML = "";

        return;
    }

    normal.classList.remove("show");
    panel.classList.add("show");

    list.innerHTML =
        alerts.map(function(alert) {

            return `
                <div class="alert-item">

                    <div class="alert-icon">
                        ${alert.icon}
                    </div>

                    <div>

                        <strong>
                            ${escapeHtml(alert.title)}
                        </strong>

                        <span>
                            ${escapeHtml(alert.detail)}
                        </span>

                    </div>

                </div>
            `;

        }).join("");
}


// ========================================
// LIVE SENSOR HISTORY
// ========================================

async function updateSensorHistory() {

    try {

        const response =
            await fetch(
                "get_history.php?ts=" + Date.now(),
                {
                    cache: "no-store"
                }
            );

        const result =
            await response.json();


        if (
            result.status === "success"
        ) {

            const tbody =
                document.getElementById(
                    "historyBody"
                );


            if (!tbody) {
                return;
            }


            if (
                !result.data ||
                result.data.length === 0
            ) {

                tbody.innerHTML =
                    '<tr><td colspan="5">No sensor history available.</td></tr>';

                return;

            }


            tbody.innerHTML = "";


            result.data.forEach(
                function(row) {

                    const tr =
                        document.createElement(
                            "tr"
                        );


                    tr.innerHTML =

                        "<td>" +
                        escapeHtml(
                            row.created_at
                        ) +
                        "</td>" +

                        "<td>" +
                        escapeHtml(
                            row.temperature
                        ) +
                        " °C</td>" +

                        "<td>" +
                        escapeHtml(
                            row.humidity
                        ) +
                        " %</td>" +

                        "<td>" +
                        escapeHtml(
                            row.soil_moisture
                        ) +
                        "</td>" +

                        "<td>" +
                        escapeHtml(
                            row.light_intensity
                        ) +
                        "</td>";


                    tbody.appendChild(tr);

                }
            );

        }

    } catch (error) {

        console.error(
            "History update error:",
            error
        );

    }

}


function escapeHtml(value) {

    const div =
        document.createElement(
            "div"
        );

    div.textContent =
        value ?? "";

    return div.innerHTML;

}


// ========================================
// START LIVE UPDATES
// ========================================

updateSensorData();
updateSensorHistory();

setInterval(
    updateSensorData,
    2000
);

setInterval(
    updateSensorHistory,
    2000
);

</script>

</body>

</html>