<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = $_GET['error'] ?? '';
$reset = $_GET['reset'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Smart Greenhouse | Farm Login</title>

<style>

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    min-height: 100vh;
    font-family: Arial, Helvetica, sans-serif;
    background:
        radial-gradient(circle at 10% 10%, rgba(111, 190, 135, .22), transparent 30%),
        radial-gradient(circle at 90% 90%, rgba(57, 130, 82, .18), transparent 30%),
        linear-gradient(135deg, #eef8f1, #f7fbf8);

    display: flex;
    align-items: center;
    justify-content: center;

    color: #183b29;
    overflow: hidden;
}


/* Decorative background */

.bg-circle {
    position: fixed;
    border-radius: 50%;
    pointer-events: none;
}

.circle-one {
    width: 420px;
    height: 420px;
    background: rgba(61, 142, 86, .08);
    top: -190px;
    left: -170px;
}

.circle-two {
    width: 500px;
    height: 500px;
    background: rgba(44, 118, 68, .07);
    bottom: -250px;
    right: -200px;
}


/* Main container */

.login-container {
    width: 100%;
    max-width: 980px;
    padding: 25px;

    display: grid;
    grid-template-columns: 1fr 1fr;

    position: relative;
    z-index: 2;
}


/* Left branding section */

.brand-section {
    background: linear-gradient(145deg, #176c3b, #0d4f2c);
    color: white;

    border-radius: 28px 0 0 28px;

    padding: 50px 45px;

    display: flex;
    flex-direction: column;
    justify-content: center;

    position: relative;
    overflow: hidden;

    box-shadow: 0 25px 70px rgba(25, 82, 46, .18);
}

.brand-section::after {
    content: "";
    position: absolute;

    width: 300px;
    height: 300px;

    border-radius: 50%;

    background: rgba(255,255,255,.07);

    right: -130px;
    bottom: -120px;
}

.brand-icon {
    width: 75px;
    height: 75px;

    border-radius: 22px;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 39px;

    background: rgba(255,255,255,.14);

    border: 1px solid rgba(255,255,255,.18);

    margin-bottom: 28px;
}

.brand-title {
    font-size: 34px;
    font-weight: 900;
    letter-spacing: -.8px;

    margin-bottom: 12px;
}

.brand-subtitle {
    font-size: 14px;
    line-height: 1.7;

    color: rgba(255,255,255,.78);

    max-width: 340px;
}

.system-status {
    display: inline-flex;
    align-items: center;
    gap: 8px;

    width: fit-content;

    margin-top: 35px;

    padding: 9px 14px;

    border-radius: 999px;

    background: rgba(255,255,255,.11);

    border: 1px solid rgba(255,255,255,.14);

    font-size: 11px;
    font-weight: 800;
}

.status-dot {
    width: 8px;
    height: 8px;

    border-radius: 50%;

    background: #72e49b;

    box-shadow: 0 0 12px rgba(114,228,155,.8);
}


/* Feature list */

.features {
    margin-top: 35px;

    display: flex;
    flex-direction: column;
    gap: 13px;
}

.feature {
    display: flex;
    align-items: center;
    gap: 11px;

    font-size: 11px;

    color: rgba(255,255,255,.82);
}

.feature-icon {
    width: 27px;
    height: 27px;

    border-radius: 9px;

    display: flex;
    align-items: center;
    justify-content: center;

    background: rgba(255,255,255,.11);

    font-size: 13px;
}


/* Login section */

.login-section {
    background: rgba(255,255,255,.97);

    border-radius: 0 28px 28px 0;

    padding: 48px 45px;

    box-shadow: 0 25px 70px rgba(25, 82, 46, .12);

    border: 1px solid rgba(215,230,219,.8);
}

.login-heading {
    margin-bottom: 30px;
}

.login-heading h1 {
    font-size: 27px;
    font-weight: 900;

    color: #173d29;

    margin-bottom: 8px;
}

.login-heading p {
    color: #7a8d81;

    font-size: 12px;

    line-height: 1.6;
}


/* Farm badge */

.farm-badge {
    display: inline-flex;
    align-items: center;
    gap: 7px;

    padding: 7px 11px;

    border-radius: 999px;

    background: #edf8f0;

    color: #287347;

    font-size: 9px;
    font-weight: 900;

    letter-spacing: .7px;

    margin-bottom: 17px;
}


/* Error */

.error {
    display: flex;
    align-items: center;
    gap: 9px;

    padding: 12px 13px;

    margin-bottom: 18px;

    border-radius: 12px;

    background: #fff1f1;

    border: 1px solid #f2cccc;

    color: #a33c3c;

    font-size: 11px;
    font-weight: 700;
}


/* Success message */
.success {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 12px 13px;
    margin-bottom: 18px;
    border-radius: 12px;
    background: #edf8f0;
    border: 1px solid #c9e7d2;
    color: #287347;
    font-size: 11px;
    font-weight: 700;
}

/* Form */

.form-group {
    margin-bottom: 19px;
}

label {
    display: block;

    margin-bottom: 8px;

    color: #355446;

    font-size: 11px;
    font-weight: 850;
}

.input-wrapper {
    position: relative;
}

.input-icon {
    position: absolute;

    left: 14px;
    top: 50%;

    transform: translateY(-50%);

    font-size: 16px;

    opacity: .75;

    pointer-events: none;
}

input {
    width: 100%;
    height: 51px;

    border-radius: 13px;

    border: 1px solid #dce8e0;

    background: #fafdfb;

    padding: 0 15px 0 43px;

    outline: none;

    font-size: 13px;

    color: #193d2a;

    transition: .2s;
}

input:focus {
    background: white;

    border-color: #55a16f;

    box-shadow:
        0 0 0 4px rgba(85,161,111,.11);
}

input::placeholder {
    color: #a1afa7;
}


/* Show password */

.password-toggle {
    position: absolute;

    right: 12px;
    top: 50%;

    transform: translateY(-50%);

    border: none;

    background: transparent;

    cursor: pointer;

    font-size: 16px;

    opacity: .65;

    padding: 5px;
}

.password-toggle:hover {
    opacity: 1;
}


/* Login button */

.login-button {
    width: 100%;
    height: 52px;

    margin-top: 6px;

    border: none;
    border-radius: 14px;

    background: linear-gradient(135deg, #2f8050, #1b6139);

    color: white;

    font-size: 13px;
    font-weight: 900;

    cursor: pointer;

    box-shadow: 0 10px 25px rgba(35,103,61,.20);

    transition: .2s;
}

.login-button:hover {
    transform: translateY(-2px);

    box-shadow: 0 14px 30px rgba(35,103,61,.28);
}

.login-button:active {
    transform: translateY(0);
}


/* Security */

.security {
    display: flex;

    align-items: center;
    justify-content: center;

    gap: 7px;

    margin-top: 22px;

    color: #899990;

    font-size: 10px;
}


/* Footer */

.login-footer {
    text-align: center;

    margin-top: 25px;

    color: #9aa9a0;

    font-size: 9px;

    letter-spacing: .2px;
}


/* Mobile */

@media (max-width: 760px) {

    body {
        overflow: auto;
    }

    .login-container {
        grid-template-columns: 1fr;

        max-width: 470px;

        padding: 18px;
    }

    .brand-section {
        border-radius: 24px 24px 0 0;

        padding: 32px 30px;
    }

    .brand-title {
        font-size: 28px;
    }

    .brand-subtitle {
        font-size: 12px;
    }

    .features {
        display: none;
    }

    .system-status {
        margin-top: 22px;
    }

    .login-section {
        border-radius: 0 0 24px 24px;

        padding: 35px 30px;
    }
}

</style>

</head>

<body>

<div class="bg-circle circle-one"></div>
<div class="bg-circle circle-two"></div>


<div class="login-container">


    <!-- BRANDING -->

    <section class="brand-section">

        <div class="brand-icon">
            🌱
        </div>

        <h2 class="brand-title">
            Smart Greenhouse
        </h2>

        <p class="brand-subtitle">
            Environmental Monitoring &amp; Automatic Control System
            for intelligent greenhouse management.
        </p>


        <div class="system-status">
            <span class="status-dot"></span>
            SYSTEM READY
        </div>


        <div class="features">

            <div class="feature">
                <span class="feature-icon">🌡️</span>
                Real-time environmental monitoring
            </div>

            <div class="feature">
                <span class="feature-icon">💧</span>
                Soil and humidity monitoring
            </div>

            <div class="feature">
                <span class="feature-icon">☀️</span>
                Light intensity monitoring
            </div>

            <div class="feature">
                <span class="feature-icon">🤖</span>
                Automatic greenhouse control
            </div>

        </div>

    </section>



    <!-- LOGIN -->

    <section class="login-section">

        <div class="login-heading">

            <div class="farm-badge">
                🔐 AUTHORIZED FARM ACCESS
            </div>

            <h1>
                Welcome Back
            </h1>

            <p>
                Sign in to access your greenhouse monitoring dashboard.
            </p>

        </div>


        <?php if ($error === 'invalid'): ?>

            <div class="error">
                ⚠️
                <span>Invalid username or password.</span>
            </div>

        <?php elseif ($error === 'required'): ?>

            <div class="error">
                ⚠️
                <span>Please enter your username and password.</span>
            </div>

        <?php endif; ?>

        <?php if ($reset === 'success'): ?>

            <div class="success">
                ✅
                <span>Password reset successfully. Please log in with your new password.</span>
            </div>

        <?php endif; ?>


        <form action="authenticate.php" method="POST">


            <div class="form-group">

                <label for="username">
                    FARM USERNAME
                </label>

                <div class="input-wrapper">

                    <span class="input-icon">👤</span>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Enter farm username"
                        autocomplete="username"
                        required
                    >

                </div>

            </div>



            <div class="form-group">

                <label for="password">
                    PASSWORD
                </label>

                <div class="input-wrapper">

                    <span class="input-icon">🔑</span>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >

                    <button
                        type="button"
                        class="password-toggle"
                        onclick="togglePassword()"
                        id="toggleButton"
                        aria-label="Show password"
                    >
                        👁️
                    </button>

                </div>

            </div>



            <button
                type="submit"
                class="login-button"
            >
                Login to Monitoring Dashboard →
            </button>

        </form>

        <div style="text-align:center; margin-top:16px;">
            <a href="forgot_password.php"
               style="color:#287347; font-size:11px; font-weight:800; text-decoration:none;">
                Forgot Password?
            </a>
        </div>


        <div class="security">
            🔒 Secure access • Authorized farm users only
        </div>

        <div class="login-footer">
            Smart Greenhouse FYP Project
        </div>

    </section>

</div>



<script>

function togglePassword() {

    const password = document.getElementById("password");
    const button = document.getElementById("toggleButton");

    if (password.type === "password") {

        password.type = "text";

        button.textContent = "🙈";
        button.setAttribute("aria-label", "Hide password");

    } else {

        password.type = "password";

        button.textContent = "👁️";
        button.setAttribute("aria-label", "Show password");

    }

}

</script>

</body>
</html>