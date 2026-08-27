<?php

session_start();

$error = "";

/*
|--------------------------------------------------------------------------
| Security check
|--------------------------------------------------------------------------
| User must successfully verify the OTP before changing the password.
*/

if (
    !isset($_SESSION["reset_username"]) ||
    !isset($_SESSION["reset_verified"]) ||
    $_SESSION["reset_verified"] !== true
) {
    header("Location: forgot_password.php");
    exit;
}

$username = $_SESSION["reset_username"];


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";


    if (strlen($password) < 6) {

        $error = "Password must contain at least 6 characters.";

    } elseif ($password !== $confirm_password) {

        $error = "Passwords do not match.";

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
            | Hash new password
            |--------------------------------------------------------------------------
            */

            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );


            /*
            |--------------------------------------------------------------------------
            | Update password
            |--------------------------------------------------------------------------
            */

            $stmt = $conn->prepare(
                "UPDATE users
                 SET password = ?
                 WHERE username = ?"
            );


            $stmt->bind_param(
                "ss",
                $hashed_password,
                $username
            );


            if ($stmt->execute()) {

                /*
                |--------------------------------------------------------------------------
                | Clear password reset session
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


                /*
                |--------------------------------------------------------------------------
                | Return to login
                |--------------------------------------------------------------------------
                */

                header(
                    "Location: login.php?reset=success"
                );

                exit;

            } else {

                $error = "Unable to update password.";

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

<title>Reset Password | Smart Greenhouse</title>


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

    color: #7a8d81;

    font-size: 12px;

    line-height: 1.6;

    margin-bottom: 25px;
}


.account {

    background: #edf8f0;

    padding: 12px;

    border-radius: 11px;

    margin-bottom: 20px;

    font-size: 11px;

    color: #287347;
}


label {

    display: block;

    margin-bottom: 8px;

    font-size: 11px;

    font-weight: 800;

    color: #355446;
}


.password-wrapper {

    position: relative;

    width: 100%;

    margin-bottom: 18px;
}


.password-wrapper input {

    margin-bottom: 0;

    padding-right: 48px;
}


input {

    width: 100%;

    height: 50px;

    border: 1px solid #dce8e0;

    border-radius: 12px;

    padding: 0 14px;

    margin-bottom: 18px;

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


.show-password {

    position: absolute;

    right: 12px;

    top: 50%;

    transform: translateY(-50%);

    border: none;

    background: transparent;

    color: #6d8375;

    font-size: 16px;

    cursor: pointer;

    padding: 5px;

    margin: 0;

    width: auto;

    height: auto;

    box-shadow: none;
}


.show-password:hover {

    transform:
        translateY(-50%);
    
    box-shadow: none;
}


.password-message {

    min-height: 17px;

    font-size: 11px;

    margin-top: -8px;

    margin-bottom: 15px;

    font-weight: 700;
}


.password-strength {

    height: 5px;

    background: #e5eee8;

    border-radius: 10px;

    margin-top: -10px;

    margin-bottom: 15px;

    overflow: hidden;
}


.password-strength-bar {

    height: 100%;

    width: 0%;

    border-radius: 10px;

    transition: width .25s ease;
}


button.reset-button {

    width: 100%;

    height: 51px;

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


button.reset-button:hover {

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
        🔐
    </div>


    <h1>
        Create New Password
    </h1>


    <p class="subtitle">
        Your email has been verified. Set a new secure
        password for your farm monitoring account.
    </p>


    <div class="account">

        👤 Account:

        <strong>
            <?php
                echo htmlspecialchars($username);
            ?>
        </strong>

    </div>


    <?php if ($error !== ""): ?>

        <div class="error">

            ⚠️

            <?php
                echo htmlspecialchars($error);
            ?>

        </div>

    <?php endif; ?>


    <form method="POST" id="resetForm">


        <label>
            NEW PASSWORD
        </label>


        <div class="password-wrapper">

            <input
                type="password"
                name="password"
                id="password"
                placeholder="Enter new password"
                minlength="6"
                autocomplete="new-password"
                required
            >

            <button
                type="button"
                class="show-password"
                id="showPassword"
                aria-label="Show password"
            >
                👁️
            </button>

        </div>


        <div class="password-strength">

            <div
                class="password-strength-bar"
                id="passwordStrengthBar"
            ></div>

        </div>


        <label>
            CONFIRM PASSWORD
        </label>


        <div class="password-wrapper">

            <input
                type="password"
                name="confirm_password"
                id="confirm_password"
                placeholder="Confirm new password"
                minlength="6"
                autocomplete="new-password"
                required
            >

            <button
                type="button"
                class="show-password"
                id="showConfirmPassword"
                aria-label="Show confirm password"
            >
                👁️
            </button>

        </div>


        <div
            id="passwordMessage"
            class="password-message"
        ></div>


        <button
            type="submit"
            class="reset-button"
        >
            Reset Password →
        </button>


    </form>


    <a
        class="back"
        href="login.php"
    >
        ← Back to Login
    </a>


    <div class="security">

        🔒 Smart Greenhouse • Secure Password Reset

    </div>


</div>


<script src="script.js"></script>


</body>

</html>