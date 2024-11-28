<?php
session_start();
include 'db.php';

$response = [];

try {
    // Validate request
    if (!isset($_POST['electionId'], $_POST['candidateId'])) {
        throw new Exception("Invalid input.");
    }

    $electionId = intval($_POST['electionId']);
    $candidateId = intval($_POST['candidateId']);
    $voterId = ; // Assuming voter ID is stored in the session

    // Check if the user has already voted in this election
    $checkVoteQuery = "SELECT * FROM Votes WHERE VoterID = ? AND ElectionID = ?";
    $stmt = $conn->prepare($checkVoteQuery);
    $stmt->bind_param("ii", $voterId, $electionId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        throw new Exception("You have already voted in this election.");
    }

    // Insert the vote into the database
    $insertVoteQuery = "INSERT INTO Votes (VoterID, ElectionID, CandidateID) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insertVoteQuery);
    $stmt->bind_param("iii", $voterId, $electionId, $candidateId);

    if ($stmt->execute()) {
        $response = ['status' => 'success', 'message' => 'Vote recorded successfully'];
    } else {
        throw new Exception("Failed to record your vote.");
    }
} catch (Exception $e) {
    $response = ['status' => 'error', 'message' => $e->getMessage()];
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
