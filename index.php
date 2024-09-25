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
  <script>
        // Function to show error messages in a popup
        function showError(message) {
            alert(message);
        }

        // Check if there's an error message in the URL
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            const error = urlParams.get('error');
            if (error) {
                showError(error);
            }
        };
    </script>
    <div class="container" id="container">
      <!-- Registration Form -->
      <div class="form-container sign-up-container">
        <form action="register.php" method="POST">
          <h1>Voter Registration</h1>
          <input
            type="text"
            name="full_name"
            placeholder="Full Name"
            required
          />
          <input type="email" name="email" placeholder="Email" required />
          <input
            type="password"
            name="password"
            placeholder="Password"
            required
          />
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
        <form action="login.php" method="POST">
          <h1>Voter Login</h1>
          <input type="email" name="email" placeholder="Email" required />
          <input
            type="password"
            name="password"
            placeholder="Password"
            required
          />
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
              Register to participate in the voting process. It's quick and
              easy.
            </p>
            <button class="ghost" id="signUp">Register</button>
          </div>
        </div>
      </div>
    </div>
    <script src="./js/scripts.js"></script>
  </body>
</html>