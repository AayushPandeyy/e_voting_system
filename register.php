<?php
include 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = htmlspecialchars(trim($_POST['full_name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = htmlspecialchars(trim($_POST['password']));
    $citizenship_number = htmlspecialchars(trim($_POST['id_number']));

    // Check if any field is empty
    if (empty($full_name) || empty($email) || empty($password) || empty($citizenship_number)) {
        header("Location: index.php?error=All fields are required.");
        exit;
    }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    if ($stmt->rowCount() > 0) {
        header("Location: index.php?error=Email already registered. Please Login.");
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, citizenship_number) VALUES (:full_name, :email, :password, :citizenship_number)");
    if ($stmt->execute([
        'full_name' => $full_name,
        'email' => $email,
        'password' => $hashed_password,
        'citizenship_number' => $citizenship_number
    ])) {
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['email'] = $email;
        header("Location: userDashboard.php"); 
        exit;
    } else {
        header("Location: index.php?error=Registration failed.");
        exit;
    }
}
?>
