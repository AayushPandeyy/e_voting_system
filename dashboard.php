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
<style>
    /* Reset and base styles */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: "Inter", -apple-system, BlinkMacSystemFont, sans-serif;
}

body {
  background-color: #f5f7fb;
  color: #2d3748;
}

/* Dashboard container */
.dashboard-container {
  display: flex;
  min-height: 100vh;
}

/* Sidebar styles */
.sidebar {
  width: 250px;
  background: #2c3e50;
  color: #fff;
  padding: 2rem 0;
  position: fixed;
  height: 100vh;
  transition: all 0.3s ease;
}

.sidebar h2 {
  padding: 0 1.5rem;
  margin-bottom: 2rem;
  font-size: 1.5rem;
  font-weight: 600;
  color: #fff;
}

.sidebar ul {
  list-style: none;
}

.sidebar ul li {
  margin-bottom: 0.5rem;
}

.sidebar ul li a {
  display: block;
  padding: 0.8rem 1.5rem;
  color: #cbd5e0;
  text-decoration: none;
  transition: all 0.3s ease;
}

.sidebar ul li a:hover,
.sidebar ul li a.active {
  background: #34495e;
  color: #fff;
  border-left: 4px solid #3498db;
}

/* Main content styles */
.main-content {
  flex: 1;
  margin-left: 250px;
  padding: 2rem;
}

/* Header styles */
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #e2e8f0;
}

.header h1 {
  font-size: 1.8rem;
  color: #2d3748;
  font-weight: 600;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-info span {
  font-weight: 500;
  color: #4a5568;
}

.user-info img {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}

/* Stats cards */
.stats-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.card {
  background: #fff;
  padding: 1.5rem;
  border-radius: 10px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.06);
  transition: transform 0.2s ease;
}

.card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.card h3 {
  color: #718096;
  font-size: 0.875rem;
  font-weight: 500;
  margin-bottom: 0.5rem;
}

.card p {
  font-size: 1.5rem;
  font-weight: 600;
  color: #2d3748;
}

/* Recent results table */
.recent-results {
  background: #fff;
  padding: 1.5rem;
  border-radius: 10px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.06);
  margin-bottom: 2rem;
}

.recent-results h2 {
  margin-bottom: 1.5rem;
  color: #2d3748;
  font-size: 1.25rem;
}

.recent-results table {
  width: 100%;
  border-collapse: collapse;
}

.recent-results th,
.recent-results td {
  padding: 0.75rem 1rem;
  text-align: left;
  border-bottom: 1px solid #e2e8f0;
}

.recent-results th {
  background: #f8fafc;
  font-weight: 500;
  color: #4a5568;
}

.recent-results tr:hover {
  background: #f8fafc;
}

/* Quick actions */
.quick-actions {
  background: #fff;
  padding: 1.5rem;
  border-radius: 10px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.06);
}

.quick-actions h2 {
  margin-bottom: 1.5rem;
  color: #2d3748;
  font-size: 1.25rem;
}

.action-buttons {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.action-button {
  display: inline-block;
  padding: 0.75rem 1.5rem;
  background: #3498db;
  color: #fff;
  text-decoration: none;
  border-radius: 6px;
  text-align: center;
  transition: all 0.3s ease;
}

.action-button:hover {
  background: #2980b9;
  transform: translateY(-1px);
}

/* Responsive design */
@media (max-width: 768px) {
  .sidebar {
    width: 0;
    padding: 0;
  }

  .main-content {
    margin-left: 0;
  }

  .header {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }

  .stats-cards {
    grid-template-columns: 1fr;
  }
}

</style>
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