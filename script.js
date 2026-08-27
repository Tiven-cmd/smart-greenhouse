const form = document.getElementById("resetForm");
const password = document.getElementById("password");
const confirmPassword = document.getElementById("confirm_password");
const message = document.getElementById("passwordMessage");
const strengthBar = document.getElementById("passwordStrengthBar");

const showPassword = document.getElementById("showPassword");
const showConfirmPassword = document.getElementById("showConfirmPassword");


// --------------------------------------------------
// Password strength
// --------------------------------------------------

function checkPasswordStrength() {

    const value = password.value;

    if (!strengthBar) {
        return;
    }

    if (value.length === 0) {

        strengthBar.style.width = "0%";

        return;
    }

    let strength = 0;

    if (value.length >= 6) {
        strength++;
    }

    if (/[A-Z]/.test(value)) {
        strength++;
    }

    if (/[0-9]/.test(value)) {
        strength++;
    }

    if (/[^A-Za-z0-9]/.test(value)) {
        strength++;
    }


    if (strength === 1) {

        strengthBar.style.width = "25%";

    } else if (strength === 2) {

        strengthBar.style.width = "50%";

    } else if (strength === 3) {

        strengthBar.style.width = "75%";

    } else {

        strengthBar.style.width = "100%";
    }
}


// --------------------------------------------------
// Check passwords
// --------------------------------------------------

function checkPasswordMatch() {

    if (!message) {
        return;
    }

    if (password.value.length === 0) {

        message.textContent = "";

        return;
    }


    if (password.value.length < 6) {

        message.textContent =
            "⚠️ Password must be at least 6 characters.";

        message.style.color = "#a33c3c";

        return;
    }


    if (
        confirmPassword.value.length > 0 &&
        password.value !== confirmPassword.value
    ) {

        message.textContent =
            "⚠️ Passwords do not match.";

        message.style.color = "#a33c3c";

        return;
    }


    if (
        confirmPassword.value.length > 0 &&
        password.value === confirmPassword.value
    ) {

        message.textContent =
            "✓ Passwords match.";

        message.style.color = "#287347";

        return;
    }


    message.textContent =
        "✓ Password looks good.";

    message.style.color = "#287347";
}


// --------------------------------------------------
// Show / hide password
// --------------------------------------------------

if (showPassword) {

    showPassword.addEventListener("click", function () {

        if (password.type === "password") {

            password.type = "text";

            showPassword.textContent = "🙈";

        } else {

            password.type = "password";

            showPassword.textContent = "👁️";
        }

    });
}


if (showConfirmPassword) {

    showConfirmPassword.addEventListener("click", function () {

        if (confirmPassword.type === "password") {

            confirmPassword.type = "text";

            showConfirmPassword.textContent = "🙈";

        } else {

            confirmPassword.type = "password";

            showConfirmPassword.textContent = "👁️";
        }

    });
}


// --------------------------------------------------
// Live checking
// --------------------------------------------------

if (password) {

    password.addEventListener("input", function () {

        checkPasswordStrength();

        checkPasswordMatch();

    });
}


if (confirmPassword) {

    confirmPassword.addEventListener("input", function () {

        checkPasswordMatch();

    });
}


// --------------------------------------------------
// Final check before submitting
// --------------------------------------------------

if (form) {

    form.addEventListener("submit", function (event) {

        if (password.value.length < 6) {

            event.preventDefault();

            message.textContent =
                "⚠️ Password must be at least 6 characters.";

            message.style.color = "#a33c3c";

            password.focus();

            return;
        }


        if (password.value !== confirmPassword.value) {

            event.preventDefault();

            message.textContent =
                "⚠️ Passwords do not match.";

            message.style.color = "#a33c3c";

            confirmPassword.focus();

            return;
        }

    });
}