<?php
session_start();
include 'db.php'; // Include the database connection

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user = $_POST['email'];
    $pass = $_POST['password'];

    // Validate input (optional, but recommended)
    if (empty($user) || empty($pass)) {
        echo "Please enter both email and password.";
        exit();
    }

    // Prepare and bind
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
    if ($stmt) {
        $stmt->bind_param("s", $user);

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
                // Start session and set session variables
                $_SESSION['user_id'] = $id;
                $_SESSION['email'] = $user;

                // Redirect to the dashboard or homepage
                header("Location: userDashboard.php");
                exit();
            } else {
                echo "Invalid password.";
            }
        } else {
            echo "No user found with that email.";
        }
        // Close the statement
        $stmt->close();
    } else {
        echo "Failed to prepare the statement.";
    }
}
$conn->close(); // Close the database connection
?>
