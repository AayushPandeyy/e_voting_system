<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $password = $_POST['password'];
    $electionId = $_POST['election_id'];

    // First fetch the hashed password from database
    $sql = "SELECT password FROM Election WHERE ElectionID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $electionId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $hashedPassword = $row['password'];

        // Verify the password
        if (password_verify($password, $hashedPassword)) {
            // Password is correct
            $_SESSION['authenticated_elections'][] = $electionId;
            header("Location: electionPage.php?id=" . htmlspecialchars($electionId));
            exit();
        } else {
            // Password is incorrect
            $_SESSION['error'] = "Incorrect password for this election.";
            header("Location: userDashboard.php");
            exit();
        }
    } else {
        // Election not found
        $_SESSION['error'] = "Election not found.";
        header("Location: userDashboard.php");
        exit();
    }
    $stmt->close();
}

?>