<?php

session_start();

$error = "";
$success = "";

/*
|--------------------------------------------------------------------------
| Make sure an OTP reset session exists
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION["reset_username"]) ||
    !isset($_SESSION["reset_otp_hash"]) ||
    !isset($_SESSION["reset_otp_expires"])
) {
    header("Location: forgot_password.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Check OTP
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $otp = trim($_POST["otp"] ?? "");

    if ($otp === "") {

        $error = "Please enter the verification code.";

    } elseif (!preg_match('/^[0-9]{6}$/', $otp)) {

        $error = "Please enter the 6-digit verification code.";

    } elseif (time() > $_SESSION["reset_otp_expires"]) {

        $error = "This verification code has expired. Please request a new one.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | Limit incorrect attempts
        |--------------------------------------------------------------------------
        */

        $_SESSION["reset_otp_attempts"] =
            ($_SESSION["reset_otp_attempts"] ?? 0) + 1;


        if ($_SESSION["reset_otp_attempts"] > 5) {

            unset(
                $_SESSION["reset_username"],
                $_SESSION["reset_email"],
                $_SESSION["reset_otp_hash"],
                $_SESSION["reset_otp_expires"],
                $_SESSION["reset_otp_attempts"],
                $_SESSION["reset_verified"]
            );

            $error =
                "Too many incorrect attempts. Please request a new verification code.";

        } elseif (
            password_verify(
                $otp,
                $_SESSION["reset_otp_hash"]
            )
        ) {

            /*
            |--------------------------------------------------------------------------
            | OTP is correct
            |--------------------------------------------------------------------------
            */

            $_SESSION["reset_verified"] = true;

            header("Location: reset_password.php");
            exit;

        } else {

            $remaining =
                5 - $_SESSION["reset_otp_attempts"];

            $error =
                "Incorrect verification code. "
                . $remaining
                . " attempt(s) remaining.";
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

<title>Verify Code | Smart Greenhouse</title>


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

    margin-bottom: 22px;

    color: #7a8d81;

    font-size: 12px;

    line-height: 1.6;
}


.email-box {

    background: #edf8f0;

    padding: 12px;

    border-radius: 11px;

    margin-bottom: 22px;

    font-size: 11px;

    color: #287347;

    word-break: break-word;
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

    height: 58px;

    border: 1px solid #dce8e0;

    border-radius: 12px;

    padding: 0 14px;

    outline: none;

    font-size: 22px;

    font-weight: 800;

    letter-spacing: 8px;

    text-align: center;

    color: #183b29;

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

    line-height: 1.5;
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
        🔐
    </div>


    <h1>
        Verify Your Email
    </h1>


    <p class="subtitle">
        We've sent a 6-digit verification code to your
        registered email address.
    </p>


    <div class="email-box">

        📧 Code sent to:

        <strong>
            <?php
                echo htmlspecialchars(
                    $_SESSION["reset_email"]
                );
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


    <form method="POST">


        <label>
            VERIFICATION CODE
        </label>


        <input
            type="text"
            name="otp"
            maxlength="6"
            inputmode="numeric"
            pattern="[0-9]{6}"
            autocomplete="one-time-code"
            placeholder="000000"
            required
        >


        <button type="submit">
            Verify Code →
        </button>


    </form>


    <a
        class="back"
        href="forgot_password.php"
    >
        ← Request a New Code
    </a>


    <div class="security">

        🔒 Smart Greenhouse • Secure Password Reset

    </div>


</div>


</body>

</html>