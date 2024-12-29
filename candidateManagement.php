<?php
session_start();
include 'db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Handle candidate deletion
if (isset($_POST['delete_candidate'])) {
    $candidate_id = $_POST['candidate_id'];
    $deleteQuery = "DELETE FROM Candidate WHERE CandidateID = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $candidate_id);
    $stmt->execute();
}

// Fetch all candidates with their election information
$candidatesQuery = "
    SELECT 
        c.*,
        e.Title as ElectionTitle,
        e.Status as ElectionStatus,
        COUNT(v.ID) as vote_count
    FROM Candidate c
    JOIN Election e ON c.ElectionID = e.ElectionID
    LEFT JOIN Votes v ON c.CandidateID = v.CandidateID
    GROUP BY c.CandidateID
    ORDER BY e.StartDate DESC, c.Name";

$candidates = $conn->query($candidatesQuery);

// Fetch elections for the add candidate form
$electionsQuery = "SELECT ElectionID, Title FROM Election WHERE EndDate >= CURDATE()";
$elections = $conn->query($electionsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Management</title>
    <link rel="stylesheet" href="./css/dashboardStyles.css">
    <style>
        /* General Styling */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f0f4f8;
    margin: 0;
    padding: 0;
    color: #2d3748;
    line-height: 1.6;
}

h1, h2 {
    font-weight: 700;
    color: #2c5282;
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

/* Main Content Styling */
.dashboard-container {
    display: flex;
}

.main-content {
    margin-left: 250px;
    padding: 2rem;
    flex: 1;
    background: #ffffff;
    min-height: 100vh;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
    border-radius: 10px 0 0 0;
}

.header {
    margin-bottom: 2rem;
}

.add-candidate-form, 
.candidate-grid {
    background: #ffffff;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    margin-bottom: 2rem;
}

.add-candidate-form h2, 
.candidate-card h3 {
    margin-bottom: 1rem;
}

/* Form Elements */
form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

label {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.5rem;
}

input, 
textarea, 
select {
    padding: 0.8rem;
    border: 1px solid #cbd5e0;
    border-radius: 5px;
    outline: none;
    transition: all 0.3s ease;
}

input:focus, 
textarea:focus, 
select:focus {
    border-color: #3182ce;
    box-shadow: 0 0 0 2px rgba(66, 153, 225, 0.4);
}

button {
    padding: 0.8rem 1.5rem;
    background: #3182ce;
    color: #ffffff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s ease;
}

button:hover {
    background: #2c5282;
}

.create-candidate  a {
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

.create-candidate a:hover {
    background: #2b6cb0;
}

/* Candidate Grid Styling */
.candidate-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
}

.candidate-card {
    background: #edf2f7;
    padding: 1rem;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.candidate-card:hover {
    transform: scale(1.03);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
}

.candidate-info p {
    color: #4a5568;
    font-size: 0.875rem;
}

.candidate-actions {
    margin-top: 1rem;
    display: flex;
    justify-content: space-around;
}

.candidate-actions .btn-edit, 
.candidate-actions .btn-delete {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    font-weight: 600;
    text-align: center;
    border-radius: 5px;
    text-decoration: none;
    color: #ffffff;
}

.candidate-actions .btn-edit {
    background: #4299e1;
    border: none;
}

.candidate-actions .btn-delete {
    background: #e53e3e;
    border: none;
}

.candidate-actions .btn-edit:hover {
    background: #2b6cb0;
}

.candidate-actions .btn-delete:hover {
    background: #9b2c2c;
}

/* Responsive Styling */
@media (max-width: 768px) {
    .sidebar {
        position: relative;
        width: 100%;
        height: auto;
        padding: 1rem;
    }

    .main-content {
        margin-left: 0;
        padding: 1rem;
    }

    .candidate-grid {
        grid-template-columns: 1fr;
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
                <li><a href="adminDashboard.php">Dashboard</a></li>
                <li><a href="electionManagement.php">Elections</a></li>
                <li><a href="candidateManagement.php" class="active">Candidates</a></li>
                <li><a href="voterManagement.php">Voter Management</a></li>
                <li><a href="adminSettings.php">Settings</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Candidate Management</h1>
            </div>

            <div class="create-candidate">
                <a href="addCandidate.php" class="btn btn-edit">Add New Candidate</a>
            </div>

            

            <!-- Candidates Grid -->
            <div class="candidate-grid">
                <?php while ($candidate = $candidates->fetch_assoc()): ?>
                    <div class="candidate-card">
                        <h3><?php echo htmlspecialchars($candidate['Name']); ?></h3>
                        <div class="candidate-info">
                            <p>Election: <?php echo htmlspecialchars($candidate['ElectionTitle']); ?></p>
                            <p>Status: <?php echo $candidate['ElectionStatus']; ?></p>
                            <p>Votes Received: <?php echo $candidate['vote_count']; ?></p>
                        </div>
                        <div class="candidate-actions">
                            <a href="editCandidate.php?id=<?php echo $candidate['CandidateID']; ?>" class="btn btn-edit">Edit</a>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this candidate?');">
                                <input type="hidden" name="candidate_id" value="<?php echo $candidate['CandidateID']; ?>">
                                <button type="submit" name="delete_candidate" class="btn btn-delete">Delete</button>
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