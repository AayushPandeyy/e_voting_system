<?php
session_start();
include 'db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Handle election deletion
if (isset($_POST['delete_election'])) {
    $election_id = $_POST['election_id'];
    $deleteQuery = "DELETE FROM Election WHERE ElectionID = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $election_id);
    $stmt->execute();
}

// Handle election status toggle
if (isset($_POST['toggle_status'])) {
    $election_id = $_POST['election_id'];
    $status = $_POST['current_status'] == 'Active' ? 'Inactive' : 'Active';
    $updateQuery = "UPDATE Election SET Status = ? WHERE ElectionID = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("si", $status, $election_id);
    $stmt->execute();
}

// Fetch all elections
$electionsQuery = "
    SELECT 
        e.*,
        COUNT(v.ID) as total_votes,
        CASE 
            WHEN CURDATE() < e.StartDate THEN 'Upcoming'
            WHEN CURDATE() > e.EndDate THEN 'Completed'
            ELSE 'Active'
        END as current_status
    FROM Election e
    LEFT JOIN Votes v ON e.ElectionID = v.ElectionID
    GROUP BY e.ElectionID
    ORDER BY e.StartDate DESC";

$elections = $conn->query($electionsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Management</title>
    <link rel="stylesheet" href="./css/dashboardStyles.css">
    <style>
    /* General */
    /* General Styles */
body {
    font-family: 'Roboto', sans-serif;
    background: #f4f5f7;
    color: #2d3748;
    margin: 0;
    padding: 0;
}

.dashboard-container {
    display: flex;
    min-height: 100vh;
}

/* Sidebar Styles */
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
    padding: 0;
    margin: 0;
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

/* Main Content Styles */
.main-content {
    flex-grow: 1;
    margin-left: 250px;
    padding: 2rem;
    transition: all 0.3s ease;
}

.header h1 {
    margin-bottom: 1rem;
    font-size: 1.8rem;
    color: #2d3748;
}

.create-election a {
    display: inline-block;
    font-size: 0.9rem;
    font-weight: bold;
    color: white;
    background: #4299e1;
    padding: 0.75rem 1.25rem;
    border-radius: 6px;
    text-decoration: none;
    transition: background-color 0.2s ease;
}

.create-election a:hover {
    background: #2b6cb0;
}

/* Election Grid Styles */
.election-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.election-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.election-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.election-card h3 {
    font-size: 1.2rem;
    font-weight: bold;
    margin-bottom: 1rem;
    color: #2d3748;
}

.election-status {
    display: inline-block;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: bold;
    text-align: center;
}

.status-active {
    background: #c6f6d5;
    color: #22543d;
}

.status-upcoming {
    background: #bee3f8;
    color: #2a4365;
}

.status-completed {
    background: #e9d8fd;
    color: #44337a;
}

.election-card p {
    margin: 0.5rem 0;
    font-size: 0.9rem;
    color: #4a5568;
}

.election-actions {
    margin-top: 1rem;
    display: flex;
    gap: 0.5rem;
}

/* Button Styles */
.btn {
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-size: 0.9rem;
    font-weight: bold;
    border: none;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.btn-edit {
    background: #4299e1;
    color: white;
}

.btn-edit:hover {
    background: #2b6cb0;
}

.btn-delete {
    background: #f56565;
    color: white;
}

.btn-delete:hover {
    background: #c53030;
}

.btn-toggle {
    background: #48bb78;
    color: white;
}

.btn-toggle:hover {
    background: #2f855a;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .sidebar {
        position: relative;
        width: 100%;
        height: auto;
    }

    .main-content {
        margin-left: 0;
    }
}

</style>

</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>E-Voting App</h2>
            <ul>
                <li><a href="dashboard.php" >Dashboard</a></li>
                <li><a href="electionManagement.php" class="active">Elections</a></li>
                <li><a href="candidateManagement.php">Candidates</a></li>
                <li><a href="voterManagement.php">Voter Management</a></li>
                <li><a href="adminSettings.php">Settings</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Election Management</h1>
            </div>

            <div class="create-election">
                <a href="createElection.php" class="btn btn-edit">Create New Election</a>
            </div>

            <div class="election-grid">
                <?php while ($election = $elections->fetch_assoc()): ?>
                    <div class="election-card">
                        <h3><?php echo htmlspecialchars($election['Title']); ?></h3>
                        <span class="election-status status-<?php echo strtolower($election['current_status']); ?>">
                            <?php echo $election['current_status']; ?>
                        </span>
                        <div>
                            <p>Start: <?php echo date('M d, Y', strtotime($election['StartDate'])); ?></p>
                            <p>End: <?php echo date('M d, Y', strtotime($election['EndDate'])); ?></p>
                            <p>Total Votes: <?php echo $election['total_votes']; ?></p>
                        </div>
                        <div class="election-actions">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="election_id" value="<?php echo $election['ElectionID']; ?>">
                                <input type="hidden" name="current_status" value="<?php echo $election['Status']; ?>">
                                <button type="submit" name="toggle_status" class="btn btn-toggle">
                                    <?php echo $election['Status'] == 'Active' ? 'Deactivate' : 'Activate'; ?>
                                </button>
                            </form>
                            <a href="editElection.php?id=<?php echo $election['ElectionID']; ?>" class="btn btn-edit">Edit</a>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this election?');">
                                <input type="hidden" name="election_id" value="<?php echo $election['ElectionID']; ?>">
                                <button type="submit" name="delete_election" class="btn btn-delete">Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</body>
</html>

<?php $conn->close(); ?>