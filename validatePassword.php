<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $password = $_POST['password'];
    $electionId = $_POST['election_id'];

    $sql = "SELECT * FROM Election WHERE ElectionID = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $electionId, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        header("Location: electionPage.php?id=$electionId");
        exit();
    } else {
        echo "Incorrect password. <a href='userDashboard.php'>Try again</a>";
    }
    $stmt->close();
}
?>
