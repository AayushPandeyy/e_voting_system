<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'User not authenticated']));
}

// Validate input
if (!isset($_POST['electionId']) || !isset($_POST['candidateId'])) {
    die(json_encode(['success' => false, 'message' => 'Invalid input']));
}

$userId = $_SESSION['user_id'];
$electionId = intval($_POST['electionId']);
$candidateId = intval($_POST['candidateId']);

try {
    // Start transaction
    $conn->begin_transaction();

    // Check if user has already voted
    $checkVote = "SELECT COUNT(*) as voteCount FROM Votes 
                  WHERE ElectionID = ? AND VoterID = ?";
    $stmt = $conn->prepare($checkVote);
    $stmt->bind_param("ii", $electionId, $userId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result['voteCount'] > 0) {
        $conn->rollback();
        die(json_encode(['success' => false, 'message' => 'You have already voted in this election']));
    }

    // Insert vote
    $insertVote = "INSERT INTO Votes (ElectionID, CandidateID, VoterID) 
                   VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insertVote);
    $stmt->bind_param("iii", $electionId, $candidateId, $userId);
    $stmt->execute();

    // Update candidate vote count
    $updateCount = "UPDATE Candidate 
                    SET VotesCount = VotesCount + 1 
                    WHERE CandidateID = ? AND ElectionID = ?";
    $stmt = $conn->prepare($updateCount);
    $stmt->bind_param("ii", $candidateId, $electionId);
    $stmt->execute();

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Vote cast successfully']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}

$conn->close();
?>