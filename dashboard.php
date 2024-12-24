<?php
session_start();
include 'db.php';

// Check if the user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Fetch admin details
$adminQuery = "SELECT full_name, email FROM users WHERE id = ?";
$stmt = $conn->prepare($adminQuery);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

// Get total voters count
$votersQuery = "SELECT COUNT(*) as total FROM users";
$totalVoters = $conn->query($votersQuery)->fetch_assoc()['total'];

// Get total votes cast
$votesQuery = "SELECT COUNT(*) as total FROM Votes";
$totalVotes = $conn->query($votesQuery)->fetch_assoc()['total'];

// Get active polls count
$activePollsQuery = "SELECT COUNT(*) as total FROM Election WHERE EndDate >= CURDATE()";
$activePolls = $conn->query($activePollsQuery)->fetch_assoc()['total'];

// Get recent voting results from the latest election
$recentResultsQuery = "
    SELECT 
        c.Name as Candidate,
        c.VotesCount as Votes,
        (c.VotesCount * 100.0 / (
            SELECT COUNT(*) 
            FROM Votes 
            WHERE ElectionID = e.ElectionID
        )) as Percentage,
        e.Title as ElectionTitle
    FROM Candidate c
    JOIN Election e ON c.ElectionID = e.ElectionID
    WHERE e.EndDate = (
        SELECT MAX(EndDate) 
        FROM Election 
        WHERE EndDate <= CURDATE()
    )
    ORDER BY c.VotesCount DESC
    LIMIT 5";

$recentResults = $conn->query($recentResultsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Voting Admin Dashboard</title>
    <link rel="stylesheet" href="./css/dashboardStyles.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>E-Voting App</h2>
            <ul>
                <li><a href="adminDashboard.php" class="active">Dashboard</a></li>
                <li><a href="electionManagement.php">Elections</a></li>
                <li><a href="candidateManagement.php">Candidates</a></li>
                <li><a href="voterManagement.php">Voter Management</a></li>
                <li><a href="adminSettings.php">Settings</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Admin Dashboard</h1>
                <div class="user-info">
                    <span><?php echo htmlspecialchars($admin['full_name']); ?></span>
                    <img src="/api/placeholder/40/40" alt="Profile Picture">
                </div>
            </div>
            
            <div class="stats-cards">
                <div class="card">
                    <h3>Total Voters</h3>
                    <p id="total-voters"><?php echo number_format($totalVoters); ?></p>
                </div>
                <div class="card">
                    <h3>Total Votes Cast</h3>
                    <p id="total-votes"><?php echo number_format($totalVotes); ?></p>
                </div>
                <div class="card">
                    <h3>Active Polls</h3>
                    <p id="active-polls"><?php echo number_format($activePolls); ?></p>
                </div>
            </div>

            <div class="recent-results">
                <?php if ($recentResults->num_rows > 0): ?>
                    <?php 
                    $firstRow = $recentResults->fetch_assoc();
                    $recentResults->data_seek(0); // Reset pointer
                    ?>
                    <h2>Recent Voting Results - <?php echo htmlspecialchars($firstRow['ElectionTitle']); ?></h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Candidate</th>
                                <th>Votes</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($result = $recentResults->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($result['Candidate']); ?></td>
                                    <td><?php echo number_format($result['Votes']); ?></td>
                                    <td><?php echo number_format($result['Percentage'], 1); ?>%</td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No recent election results available.</p>
                <?php endif; ?>
            </div>

            <!-- Add a new section for quick actions -->
            <div class="quick-actions">
                <h2>Quick Actions</h2>
                <div class="action-buttons">
                    <a href="createElection.php" class="action-button">Create New Election</a>
                    <a href="addCandidate.php" class="action-button">Add Candidate</a>
                    <a href="electionResults.php" class="action-button">View All Results</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>