<?php

session_start();

/*
|--------------------------------------------------------------------------
| PHPMailer
|--------------------------------------------------------------------------
*/

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . "/PHPMailer/src/Exception.php";
require __DIR__ . "/PHPMailer/src/PHPMailer.php";
require __DIR__ . "/PHPMailer/src/SMTP.php";


$error = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");


    if ($username === "") {

        $error = "Please enter your farm username.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | Database connection
        |--------------------------------------------------------------------------
        */

        $conn = new mysqli(
            "localhost",
            "root",
            "",
            "smart_greenhouse"
        );


        if ($conn->connect_error) {

            $error = "Database connection failed.";

        } else {

            /*
            |--------------------------------------------------------------------------
            | Find user and registered email
            |--------------------------------------------------------------------------
            */

            $stmt = $conn->prepare(
                "SELECT id, username, email
                 FROM users
                 WHERE username = ?"
            );

            $stmt->bind_param("s", $username);

            $stmt->execute();

            $result = $stmt->get_result();


            if ($result->num_rows === 1) {

                $user = $result->fetch_assoc();

                $email = trim($user["email"]);


                if ($email === "") {

                    $error = "No recovery email is registered for this account.";

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | Generate 6-digit OTP
                    |--------------------------------------------------------------------------
                    */

                    $otp = (string) random_int(100000, 999999);


                    /*
                    |--------------------------------------------------------------------------
                    | Store reset information in session
                    |--------------------------------------------------------------------------
                    */

                    $_SESSION["reset_username"] = $user["username"];

                    $_SESSION["reset_email"] = $email;

                    $_SESSION["reset_otp_hash"] =
                        password_hash($otp, PASSWORD_DEFAULT);

                    // OTP expires after 10 minutes
                    $_SESSION["reset_otp_expires"] =
                        time() + 600;

                    $_SESSION["reset_otp_attempts"] = 0;

                    $_SESSION["reset_verified"] = false;


                    /*
                    |--------------------------------------------------------------------------
                    | Gmail SMTP configuration
                    |--------------------------------------------------------------------------
                    */

                    $config = require __DIR__ . "/email_config.php";


                    $mail = new PHPMailer(true);


                    try {

                        /*
                        |--------------------------------------------------------------------------
                        | SMTP settings
                        |--------------------------------------------------------------------------
                        */

                        $mail->isSMTP();

                        $mail->Host =
                            $config["smtp_host"];

                        $mail->SMTPAuth = true;

                        $mail->Username =
                            $config["smtp_username"];

                        $mail->Password =
                            $config["smtp_password"];

                        $mail->SMTPSecure =
                            PHPMailer::ENCRYPTION_STARTTLS;

                        $mail->Port =
                            $config["smtp_port"];


                        /*
                        |--------------------------------------------------------------------------
                        | Sender
                        |--------------------------------------------------------------------------
                        */

                        $mail->setFrom(
                            $config["from_email"],
                            $config["from_name"]
                        );


                        /*
                        |--------------------------------------------------------------------------
                        | Receiver
                        |--------------------------------------------------------------------------
                        */

                        $mail->addAddress($email);


                        /*
                        |--------------------------------------------------------------------------
                        | Email content
                        |--------------------------------------------------------------------------
                        */

                        $mail->isHTML(true);

                        $mail->Subject =
                            "Smart Greenhouse Password Reset";


                        $mail->Body = '

                        <div style="
                            font-family:Arial,sans-serif;
                            background:#f3faf5;
                            padding:30px;
                        ">

                            <div style="
                                max-width:500px;
                                margin:auto;
                                background:white;
                                border-radius:18px;
                                padding:30px;
                                box-shadow:0 10px 30px rgba(0,0,0,.08);
                            ">

                                <h2 style="
                                    color:#236b3c;
                                    margin-top:0;
                                ">
                                    🌱 Smart Greenhouse
                                </h2>

                                <p style="color:#555;">
                                    We received a request to reset your
                                    Smart Greenhouse farm monitoring password.
                                </p>

                                <p style="color:#555;">
                                    Your verification code is:
                                </p>

                                <div style="
                                    font-size:32px;
                                    font-weight:bold;
                                    letter-spacing:8px;
                                    color:#236b3c;
                                    text-align:center;
                                    padding:20px;
                                    background:#eef8f1;
                                    border-radius:12px;
                                ">
                                    ' . htmlspecialchars($otp) . '
                                </div>

                                <p style="
                                    color:#777;
                                    margin-top:20px;
                                ">
                                    This verification code will expire
                                    in <strong>10 minutes</strong>.
                                </p>

                                <p style="
                                    color:#999;
                                    font-size:12px;
                                ">
                                    If you did not request a password reset,
                                    you can safely ignore this email.
                                </p>

                            </div>

                        </div>
                        ';


                        $mail->AltBody =
                            "Your Smart Greenhouse verification code is: "
                            . $otp
                            . ". This code expires in 10 minutes.";


                        /*
                        |--------------------------------------------------------------------------
                        | Send email
                        |--------------------------------------------------------------------------
                        */

                        $mail->send();


                        /*
                        |--------------------------------------------------------------------------
                        | Go to OTP verification page
                        |--------------------------------------------------------------------------
                        */

                        header("Location: verify_otp.php");

                        exit;


                    } catch (Exception $e) {

                        /*
                        |--------------------------------------------------------------------------
                        | Clear reset session if email fails
                        |--------------------------------------------------------------------------
                        */

                        unset(
                            $_SESSION["reset_username"],
                            $_SESSION["reset_email"],
                            $_SESSION["reset_otp_hash"],
                            $_SESSION["reset_otp_expires"],
                            $_SESSION["reset_otp_attempts"],
                            $_SESSION["reset_verified"]
                        );


                        $error =
                            "Unable to send verification email. Please try again.";
                    }
                }

            } else {

                $error = "Username not found.";
            }


            $stmt->close();

            $conn->close();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Forgot Password | Smart Greenhouse</title>


<style>

* {
    box-sizing: border-box;
}


body {

    margin: 0;

    min-height: 100vh;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background:
        radial-gradient(
            circle at 10% 10%,
            rgba(111,190,135,.20),
            transparent 30%
        ),

        radial-gradient(
            circle at 90% 90%,
            rgba(57,130,82,.15),
            transparent 30%
        ),

        linear-gradient(
            135deg,
            #eef8f1,
            #f7fbf8
        );

    display: flex;

    align-items: center;

    justify-content: center;

    color: #183b29;
}


.card {

    width: 100%;

    max-width: 440px;

    margin: 20px;

    padding: 40px;

    background: white;

    border-radius: 25px;

    box-shadow:
        0 25px 70px
        rgba(25,82,46,.15);
}


.logo {

    width: 65px;

    height: 65px;

    border-radius: 18px;

    background: #eaf7ee;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 32px;

    margin-bottom: 22px;
}


h1 {

    margin: 0 0 8px;

    font-size: 27px;
}


.subtitle {

    margin-bottom: 28px;

    color: #7a8d81;

    font-size: 12px;

    line-height: 1.6;
}


label {

    display: block;

    margin-bottom: 8px;

    font-size: 11px;

    font-weight: 800;

    color: #355446;
}


input {

    width: 100%;

    height: 50px;

    border: 1px solid #dce8e0;

    border-radius: 12px;

    padding: 0 14px;

    outline: none;

    font-size: 13px;

    background: #fafdfb;
}


input:focus {

    border-color: #55a16f;

    box-shadow:
        0 0 0 4px
        rgba(85,161,111,.11);
}


button {

    width: 100%;

    height: 51px;

    margin-top: 20px;

    border: none;

    border-radius: 13px;

    background:
        linear-gradient(
            135deg,
            #2f8050,
            #1b6139
        );

    color: white;

    font-size: 13px;

    font-weight: 900;

    cursor: pointer;

    transition: .2s;
}


button:hover {

    transform: translateY(-1px);

    box-shadow:
        0 8px 20px
        rgba(31,103,60,.20);
}


.error {

    padding: 12px;

    margin-bottom: 18px;

    border-radius: 11px;

    background: #fff1f1;

    border: 1px solid #f2cccc;

    color: #a33c3c;

    font-size: 11px;

    font-weight: 700;
}


.back {

    display: block;

    text-align: center;

    margin-top: 20px;

    color: #287347;

    font-size: 11px;

    font-weight: 800;

    text-decoration: none;
}


.back:hover {

    text-decoration: underline;
}


.security {

    text-align: center;

    margin-top: 22px;

    color: #9aa9a0;

    font-size: 9px;
}

</style>

</head>


<body>


<div class="card">


    <div class="logo">
        🌱
    </div>


    <h1>
        Forgot Password?
    </h1>


    <p class="subtitle">
        Enter your farm username and we'll send a
        6-digit verification code to your registered email.
    </p>


    <?php if ($error !== ""): ?>

        <div class="error">

            ⚠️
            <?php
                echo htmlspecialchars($error);
            ?>

        </div>

    <?php endif; ?>


    <form method="POST">


        <label>
            FARM USERNAME
        </label>


        <input
            type="text"
            name="username"
            placeholder="Enter your farm username"
            autocomplete="username"
            required
        >


        <button type="submit">
            Send Verification Code →
        </button>


    </form>


    <a
        class="back"
        href="login.php"
    >
        ← Back to Login
    </a>


    <div class="security">

        🔒 Smart Greenhouse • Authorized Farm Access

    </div>


</div>


</body>

</html>