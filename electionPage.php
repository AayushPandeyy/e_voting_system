<?php
include 'db.php';

// if (!isset($_SESSION['user_id'])) {
//     header("Location: index.php");
//     exit();
// }
// Assuming a database connection is established here
$electionId = $_GET["id"];
// Fetch election details from the database
$query = "SELECT * FROM Election WHERE ElectionID = $electionId";
$stmt = $conn->prepare($query);

$stmt->execute();
$election = $stmt->get_result()->fetch_assoc();

// Fetch candidates for the election
$queryCandidates = "SELECT * FROM Candidate WHERE ElectionID = $electionId";
$stmtCandidates = $conn->prepare($queryCandidates);
$stmtCandidates->execute();
$candidates = $stmtCandidates->get_result();

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
                    <img src="<?php echo htmlspecialchars($candidate['ProfilePicture']); ?>" alt="Candidate Image">
                    <h3><?php echo htmlspecialchars($candidate['Name']); ?></h3>
                    <p><?php echo htmlspecialchars($candidate['Party']); ?></p>
                    <p><strong>Votes:</strong> <?php echo htmlspecialchars($candidate['VotesCount']); ?></p>
                    <button onclick="vote(<?php echo $electionId; ?>, <?php echo $candidate['CandidateID']; ?>)">Vote</button>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> E-Voting. All rights reserved.</p>
    </footer>
    

</body>
</html>
