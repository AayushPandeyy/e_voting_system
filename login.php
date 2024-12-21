<?php
session_start();
include 'db.php'; // Include the database connection


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['email'];
    $pass = $_POST['password'];

    // Prepare and bind
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ? ");
    $stmt->bind_param("s", $user); // Use the same variable for username and email

    // Execute the statement
    $stmt->execute();
    $stmt->store_result();

    // Check if user exists
    if ($stmt->num_rows > 0) {
        // Bind result
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();

        // Verify the password
        if (password_verify($pass, $hashed_password)) {
            // Password is correct, set session variables
            $_SESSION['user_id'] = $id;
            $_SESSION['email'] = $user;

            // Redirect to the dashboard or homepage
            header("Location: userDashboard.php");
            exit();
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "No user found with that username or email.";
    }

    // Close the statement and connection
    $stmt->close();
}
?>
