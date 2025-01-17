<?php
include 'db.php';

// if (!isset($_SESSION['user_id'])) {
//     header("Location: index.php");
//     exit();
// }
$electionId = $_GET["id"];
$query = "SELECT * FROM Election WHERE ElectionID = $electionId";
$stmt = $conn->prepare($query);

$stmt->execute();
$election = $stmt->get_result()->fetch_assoc();

// Fetch candidates for the election
$queryCandidates = "SELECT * FROM Candidate WHERE ElectionID = $electionId";
$stmtCandidates = $conn->prepare($queryCandidates);
$stmtCandidates->execute();
$candidates = $stmtCandidates->get_result();

// Check if user has already voted
$queryVoteCheck = "SELECT COUNT(*) as hasVoted FROM Votes 
                   WHERE ElectionID = ? AND VoterID = ?";
$stmtVoteCheck = $conn->prepare($queryVoteCheck);
$stmtVoteCheck->bind_param("ii", $electionId, $_SESSION['user_id']);
$stmtVoteCheck->execute();
$hasVoted = $stmtVoteCheck->get_result()->fetch_assoc()['hasVoted'] > 0;

session_start();

if (!isset($_SESSION['authenticated_elections'])) {
    $_SESSION['authenticated_elections'] = array();
}

// Check if the user has authenticated for this election
if (!in_array($electionId, $_SESSION['authenticated_elections'])) {
    header("Location: userDashboard.php");
    exit();
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $election['Title']; ?> - Election Details</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/electionPageStyle.css">
    
</head>

<body>
    <style>
        .candidate-card img {
            width: 200px;
            height: 200px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 1rem;
            border: 3px solid #ddd;
        }
        
        .candidate-card img.placeholder {
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: #999;
        }
    </style>
<div class="container">
        <header>
            <h1><?php echo htmlspecialchars($election['Title']); ?></h1>
            <p><strong>Description:</strong> <?php echo htmlspecialchars($election['Description']); ?></p>
            <p><strong>Start Date:</strong> <?php echo htmlspecialchars($election['StartDate']); ?></p>
            <p><strong>End Date:</strong> <?php echo htmlspecialchars($election['EndDate']); ?></p>
        </header>

        <h2>Candidates</h2>
        <div class="candidate-grid">
            <?php while ($candidate = $candidates->fetch_assoc()): ?>
                <div class="candidate-card">
                <?php if (!empty($candidate['ProfilePicture'])): ?>
                                <img src="uploads/candidates/<?php echo htmlspecialchars($candidate['ProfilePicture']); ?>" 
                                     alt="<?php echo htmlspecialchars($candidate['Name']); ?>'s profile picture"
                                     class="profile-picture">
                            <?php else: ?>
                                <img src="images/default-avatar.png" 
                                     alt="Default profile picture"
                                     class="profile-picture">
                            <?php endif; ?>
                    <h3><?php echo htmlspecialchars($candidate['Name']); ?></h3>
                    <p><?php echo htmlspecialchars($candidate['Party']); ?></p>
                    <p><strong>Votes:</strong> <?php echo htmlspecialchars($candidate['VotesCount']); ?></p>
                    <button 
    onclick="vote(<?php echo $electionId; ?>, <?php echo $candidate['CandidateID']; ?>)"
    <?php echo $hasVoted ? 'disabled' : ''; ?>
    class="<?php echo $hasVoted ? 'voted' : ''; ?>"
>
    <?php echo $hasVoted ? 'Already Voted' : 'Vote'; ?>
</button>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> E-Voting. All rights reserved.</p>
    </footer>
    
    <script>
async function vote(electionId, candidateId) {
    try {
        const response = await fetch('castVote.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `electionId=${electionId}&candidateId=${candidateId}`
        });

        const data = await response.json();
        alert(data.message);

        if (data.success) {
            // Reload the page to update vote counts
            window.location.reload();
        }
    } catch (error) {
        alert('An error occurred while casting your vote v: ' + error);
    }
}
</script>
</body>
</html>
