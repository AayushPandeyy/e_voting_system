<?php
include 'db.php';
// Assuming a database connection is established here

// Fetch election details from the database
$query = "SELECT * FROM Election WHERE ElectionID = 1";
$stmt = $conn->prepare($query);

$stmt->execute();
$election = $stmt->get_result()->fetch_assoc();

// Fetch candidates for the election
$queryCandidates = "SELECT * FROM Candidate WHERE ElectionID = 1";
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
    <style>
        * {
    box-sizing: border-box;
}

body {
    font-family: 'Roboto', sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f8f9fa;
    color: #333;
}

.container {
    width: 80%;
    max-width: 1200px;
    margin: auto;
    padding: 20px;
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

header {
    border-bottom: 2px solid #007bff;
    padding-bottom: 20px;
    margin-bottom: 20px;
}

h1 {
    color: #007bff;
    font-size: 2.5em;
}

.election-info {
    font-size: 1.2em;
}

h2 {
    margin-top: 30px;
    font-size: 2em;
    color: #343a40;
}

.candidate-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
}

.candidate-card {
    background: #e9ecef;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    transition: transform 0.3s, box-shadow 0.3s;
    position: relative;
}

.candidate-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.candidate-image {
    width: 100%;
    height: auto;
    border-radius: 50%;
    margin-bottom: 15px;
}

.party-name {
    font-style: italic;
    color: #6c757d;
}

.vote-button {
    background-color: #007bff;
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.vote-button:hover {
    background-color: #0056b3;
}

footer {
    margin-top: 30px;
    text-align: center;
    font-size: 0.9em;
    color: #6c757d;
}

    </style>
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
