<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: userDashboard.php"); // Redirect if already logged in
    exit;
}
include 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>E-Voting - Login & Register</title>
    <link rel="stylesheet" href="./css/styles.css" />
  </head>
  <body>
    <style>
      .error-message {
        color: red;
        font-size: 0.9rem;
        margin-top: 5px;
        display: none;
      }
    </style>
    <script>
      function showError(message) {
        alert(message);
      }

      window.onload = function () {
        const urlParams = new URLSearchParams(window.location.search);
        const error = urlParams.get("error");
        if (error) {
          showError(error);
        }
      };
    </script>
    <div class="container" id="container">
      <!-- Registration Form -->
      <div class="form-container sign-up-container">
        <form id="registerForm" action="register.php" method="POST">
          <h1>Voter Registration</h1>
          <input
            type="text"
            name="full_name"
            placeholder="Full Name"
            required
          />
          <input
          type="email"
          name="email"
          placeholder="Email"
          required
          />
          <span class="error-message" id="register-email-error"></span>
          <input
          type="password"
          name="password"
          placeholder="Password"
          required
          />
          <span class="error-message" id="register-password-error"></span>
          <input
          type="text"
          name="id_number"
          placeholder="ID Number"
          required
          />
          <button type="submit">Register</button>
        </form>
      </div>

      <!-- Login Form -->
      <div class="form-container sign-in-container">
        <form id="loginForm" action="login.php" method="POST">
          <h1>Login</h1>
          <input
            type="email"
            name="email"
            placeholder="Email"
            required
          />
          <span class="error-message" id="login-email-error"></span>
          <input
            type="password"
            name="password"
            placeholder="Password"
            required
          />
          <span class="error-message" id="login-password-error"></span>
          <button type="submit">Login</button>
        </form>
      </div>

      <!-- Overlay Container -->
      <div class="overlay-container">
        <div class="overlay">
          <!-- Left Overlay Panel -->
          <div class="overlay-panel overlay-left">
            <h1>Welcome Back!</h1>
            <p>
              If you are already registered, please log in to access the voting
              system.
            </p>
            <button class="ghost" id="signIn">Login</button>
          </div>
          <!-- Right Overlay Panel -->
          <div class="overlay-panel overlay-right">
            <h1>Join Us</h1>
            <p>
              Register to participate in the voting process. It's quick and easy.
            </p>
            <button class="ghost" id="signUp">Register</button>
          </div>
        </div>
      </div>
    </div>
    <script src="./js/scripts.js"></script>
    <script>
      document.addEventListener("DOMContentLoaded", function () {
        const registerForm = document.getElementById("registerForm");
        const loginForm = document.getElementById("loginForm");

        function validateEmail(email) {
  const regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
  return regex.test(email);
}


        function showError(field, message) {
          const errorSpan = document.getElementById(field);
          errorSpan.innerText = message;
          errorSpan.style.display = "block";
        }

        function clearError(field) {
          const errorSpan = document.getElementById(field);
          errorSpan.innerText = "";
          errorSpan.style.display = "none";
        }

        // Validate Registration Form
        registerForm.addEventListener("submit", function (event) {
          let hasError = false;

          const email = registerForm.querySelector('input[name="email"]').value;
          const password = registerForm.querySelector('input[name="password"]').value;

          if (!validateEmail(email)) {
            showError("register-email-error", "Please enter a valid email");
            hasError = true;
          } else {
            clearError("register-email-error");
          }

          if (password.length <= 8) {
            showError("register-password-error", "Password must be longer than 8 characters.");
            hasError = true;
          } else {
            clearError("register-password-error");
          }

          if (hasError) {
            event.preventDefault(); // Prevent form submission
          }
        });

        // Validate Login Form
        loginForm.addEventListener("submit", function (event) {
          let hasError = false;

          const email = loginForm.querySelector('input[name="email"]').value;
          const password = loginForm.querySelector('input[name="password"]').value;

          if (!validateEmail(email)) {
            showError("login-email-error", "Please enter a valid email");
            hasError = true;
          } else {
            clearError("login-email-error");
          }

          if (password.length < 8) {
            showError("login-password-error", "Password must be longer than 8 characters.");
            hasError = true;
          } else {
            clearError("login-password-error");
          }

          if (hasError) {
            event.preventDefault(); // Prevent form submission
          }
        });
      });
    </script>
  </body>
</html>
