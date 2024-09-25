<?php
include 'db.php'; // Include the database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $full_name = htmlspecialchars($_POST['full_name']);
    $email = htmlspecialchars($_POST['email']);
    $password = htmlspecialchars($_POST['password']);
    $id_number = htmlspecialchars($_POST['id_number']);

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    if ($stmt->rowCount() > 0) {
        header("Location: index.php?error=Email already registered.Please Login.");
        exit;
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user into database
    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, id_number) VALUES (:full_name, :email, :password, :id_number)");
    if ($stmt->execute(['full_name' => $full_name, 'email' => $email, 'password' => $hashed_password, 'id_number' => $id_number])) {
        // Start a session and set session variables
        session_start();
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['email'] = $email;
        header("Location: userDashboard.php"); // Redirect to a secure page
        exit;
    } else {
        header("Location: index.php?error=Registration failed.");
        exit;
    }
}
?>
