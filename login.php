<?php
session_start();
include 'db.php'; // Include the database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $email = htmlspecialchars($_POST['email']);
    $password = htmlspecialchars($_POST['password']);

    // Retrieve user from database
    $stmt = $pdo->prepare("SELECT id, password FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if user exists and verify password
    if ($user && password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $email;
        header("Location: userDashboard.php"); // Redirect to a secure page
        exit;
    } else {
        header("Location: index.php?error=Invalid Email or Password");
        exit;
    }
}
?>
