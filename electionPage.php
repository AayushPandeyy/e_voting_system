<?php
include 'db.php';
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
            <h1><?php echo $election['Title']; ?></h1>
            <p class="election-info"><strong>Description:</strong> <?php echo $election['Description']; ?></p>
            <p class="election-info"><strong>Start Date:</strong> <?php echo date('F j, Y', strtotime($election['StartDate'])); ?></p>
            <p class="election-info"><strong>End Date:</strong> <?php echo date('F j, Y', strtotime($election['EndDate'])); ?></p>
        </header>

        <h2>Candidates</h2>
        <div class="candidate-grid">
            <?php while ($candidate = $candidates->fetch_assoc()): ?>
                <div class="candidate-card">
                    <img src="<?php echo $candidate['ProfilePicture']; ?>" alt="<?php echo $candidate['Name']; ?>" class="candidate-image">
                    <h3><?php echo $candidate['Name']; ?></h3>
                    <p class="party-name"><?php echo $candidate['Party']; ?></p>
                    <p><strong>Votes:</strong> <?php echo $candidate['VotesCount']; ?></p>
                    <button class="vote-button" onclick="alert('You voted for <?php echo $candidate['Name']; ?>!')">Vote</button>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Your Election Platform. All rights reserved.</p>
    </footer>
    

</body>
</html>
