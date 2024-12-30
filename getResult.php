<?php
session_start();
include 'db.php';

if (!isset($_GET['id'])) {
    header("Location: userDashboard.php");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$electionId = $_GET['id'];

// Get election details
$electionQuery = "SELECT * FROM Election WHERE ElectionID = ?";
$stmt = $conn->prepare($electionQuery);
$stmt->bind_param("i", $electionId);
$stmt->execute();
$election = $stmt->get_result()->fetch_assoc();

// Get candidates and their vote counts
$candidateQuery = "SELECT c.*, 
                   (SELECT COUNT(*) FROM Votes v WHERE v.CandidateID = c.CandidateID) as TotalVotes,
                   (SELECT COUNT(*) FROM Votes WHERE ElectionID = ?) as TotalBallots
                   FROM Candidate c 
                   WHERE c.ElectionID = ?
                   ORDER BY TotalVotes DESC";
$stmtCandidates = $conn->prepare($candidateQuery);
$stmtCandidates->bind_param("ii", $electionId, $electionId);
$stmtCandidates->execute();
$candidates = $stmtCandidates->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Results - <?php echo htmlspecialchars($election['Title']); ?></title>
    <link rel="stylesheet" href="./css/resultStyles.css">
</head>
<style>
    body {
    font-family: 'Roboto', sans-serif;
    background-color: #f5f5f5;
    margin: 0;
    padding: 20px;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.back-button {
    display: inline-block;
    padding: 10px 20px;
    background-color: #f0f0f0;
    color: #333;
    text-decoration: none;
    border-radius: 5px;
    margin-bottom: 20px;
}

.back-button:hover {
    background-color: #e0e0e0;
}

.results-header {
    text-align: center;
    margin-bottom: 40px;
}

.results-header h1 {
    color: #333;
    margin-bottom: 10px;
}

.winner-section {
    text-align: center;
    margin-bottom: 40px;
}

.winner-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    display: inline-block;
    min-width: 300px;
}

.winner-card img {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    margin-bottom: 15px;
}

.candidates-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.candidate-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.candidate-card img {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    margin-bottom: 15px;
}

.party {
    color: #666;
    margin: 5px 0;
}

.progress-bar {
    background: #f0f0f0;
    height: 20px;
    border-radius: 10px;
    margin: 10px 0;
    overflow: hidden;
}

.progress {
    background: #4CAF50;
    height: 100%;
    border-radius: 10px;
    transition: width 0.5s ease-in-out;
}

.votes {
    display: flex;
    justify-content: space-between;
    margin-top: 10px;
}

.vote-count {
    color: #666;
}

.vote-percentage {
    font-weight: bold;
    color: #333;
}
</style>
<body>
    <div class="container">
        <a href="userDashboard.php" class="back-button">← Back to Dashboard</a>
        
        <div class="results-header">
            <h1><?php echo htmlspecialchars($election['Title']); ?> - Results</h1>
            <p>Election ended on: <?php echo date('F d, Y', strtotime($election['EndDate'])); ?></p>
        </div>

        <div class="results-container">
            <?php 
            $winner = $candidates->fetch_assoc();
            if ($winner): 
                $totalBallots = $winner['TotalBallots'];
            ?>
                <div class="winner-section">
                    <h2>Winner</h2>
                    <div class="winner-card">
                        <img src="<?php echo htmlspecialchars($winner['ProfilePicture']); ?>" alt="Winner Image">
                        <h3><?php echo htmlspecialchars($winner['Name']); ?></h3>
                        <p class="party"><?php echo htmlspecialchars($winner['Party']); ?></p>
                        <div class="votes">
                            <span class="vote-count"><?php echo $winner['TotalVotes']; ?> votes</span>
                            <span class="vote-percentage">
                                <?php echo $totalBallots > 0 ? round(($winner['TotalVotes'] / $totalBallots) * 100, 1) : 0; ?>%
                            </span>
                        </div>
                    </div>
                </div>

                <h2>All Candidates Results</h2>
                <div class="candidates-grid">
                    <?php
                    // Reset pointer to include winner in complete results
                    $candidates->data_seek(0);
                    while ($candidate = $candidates->fetch_assoc()):
                        $percentage = $totalBallots > 0 ? ($candidate['TotalVotes'] / $totalBallots) * 100 : 0;
                    ?>
                        <div class="candidate-card">
                            <img src="<?php echo htmlspecialchars($candidate['ProfilePicture']); ?>" alt="Candidate Image">
                            <h3><?php echo htmlspecialchars($candidate['Name']); ?></h3>
                            <p class="party"><?php echo htmlspecialchars($candidate['Party']); ?></p>
                            <div class="progress-bar">
                                <div class="progress" style="width: <?php echo $percentage; ?>%"></div>
                            </div>
                            <div class="votes">
                                <span class="vote-count"><?php echo $candidate['TotalVotes']; ?> votes</span>
                                <span class="vote-percentage"><?php echo round($percentage, 1); ?>%</span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>No results available for this election.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>