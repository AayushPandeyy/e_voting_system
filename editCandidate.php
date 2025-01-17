<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Check if user is admin
$checkAdminQuery = "SELECT role FROM users WHERE id = ?";
$stmt = $conn->prepare($checkAdminQuery);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// If user is not admin, redirect them
if (!$result || $user['role'] !== 'admin') {
  header("Location: index.php");
  exit;
}

$errors = [];
$candidate_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $election_id = (int)($_POST['election_id'] ?? 0);
    $party = trim($_POST['party'] ?? '');
    
    // Handle file upload
    

    if (empty($name)) $errors[] = "Name is required";
    if ($election_id <= 0) $errors[] = "Valid election selection is required";
    
    if (empty($errors)) {
        $updateQuery = $profile_picture ? 
            "UPDATE Candidate SET Name = ?, ElectionID = ?, Party = ?, ProfilePicture = ? WHERE CandidateID = ?" :
            "UPDATE Candidate SET Name = ?, ElectionID = ?, Party = ? WHERE CandidateID = ?";
        
        $stmt = $conn->prepare($updateQuery);
        
        if ($profile_picture) {
            $stmt->bind_param("sissi", $name, $election_id, $party, $profile_picture, $candidate_id);
        } else {
            $stmt->bind_param("sisi", $name, $election_id, $party, $candidate_id);
        }
        
        if ($stmt->execute()) {
            header("Location: candidateManagement.php?success=1");
            exit;
        } else {
            $errors[] = "Error updating candidate: " . $conn->error;
        }
    }
} else {
    $query = "SELECT * FROM Candidate WHERE CandidateID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $candidate_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $candidate = $result->fetch_assoc();

    if (!$candidate) {
        header("Location: candidateManagement.php?error=1");
        exit;
    }
}

$electionsQuery = "SELECT ElectionID, Title FROM Election";
$elections = $conn->query($electionsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Candidate</title>
</head>
<style>
    /* General Styles */
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f4f4f9;
    color: #333;
}

a {
    text-decoration: none;
    color: inherit;
}

/* Sidebar Styles */
.sidebar {
    width: 250px;
    height: 100vh;
    background-color: #344955;
    color: #fff;
    position: fixed;
    top: 0;
    left: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px 0;
}

.sidebar h2 {
    margin-bottom: 20px;
    font-size: 22px;
    text-align: center;
    color: #ffcc00;
}

.sidebar ul {
    list-style: none;
    padding: 0;
    width: 100%;
}

.sidebar ul li {
    width: 100%;
}

.sidebar ul li a {
    display: block;
    padding: 10px 20px;
    font-size: 16px;
    color: #fff;
    border-left: 3px solid transparent;
    transition: all 0.3s;
}

.sidebar ul li a:hover, 
.sidebar ul li a.active {
    background-color: #232f34;
    border-left: 3px solid #ffcc00;
    color: #ffcc00;
}

/* Main Content Styles */
.main-content {
    margin-left: 250px;
    padding: 30px;
    min-height: 100vh;
    background-color: #ffffff;
}

.main-content .header {
    margin-bottom: 20px;
}

.main-content h1 {
    font-size: 28px;
    margin: 0;
    color: #344955;
}

/* Form Styles */
.add-candidate-form {
    background-color: #ffffff;
    padding: 20px 30px;
    border: 1px solid #ddd;
    border-radius: 5px;
    max-width: 600px;
    margin: auto;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    font-size: 16px;
    margin-bottom: 5px;
    color: #344955;
}

.form-group input[type="text"], 
.form-group textarea, 
.form-group select {
    width: 100%;
    padding: 8px 12px;
    font-size: 14px;
    color: #333;
    border: 1px solid #ddd;
    border-radius: 3px;
    box-sizing: border-box;
}

textarea {
    resize: vertical;
}

textarea:focus, 
input:focus, 
select:focus {
    outline: none;
    border-color: #ffcc00;
    box-shadow: 0 0 4px rgba(255, 204, 0, 0.5);
}

/* Button Styles */
.form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.form-actions .btn {
    text-align: center;
    padding: 10px 15px;
    font-size: 14px;
    border: none;
    border-radius: 3px;
    cursor: pointer;
    transition: all 0.3s;
}

.form-actions .btn-edit {
    background-color: #344955;
    color: #fff;
}

.form-actions .btn-edit:hover {
    background-color: #ffcc00;
    color: #344955;
}

.form-actions .btn-delete {
    background-color: #e74c3c;
    color: #fff;
}

.form-actions .btn-delete:hover {
    background-color: #c0392b;
}

/* Alert Styles */
.alert {
    background-color: #e74c3c;
    color: #fff;
    padding: 10px 15px;
    border-radius: 3px;
    margin-bottom: 15px;
    font-size: 14px;
}

.alert-danger p {
    margin: 0;
}

/* Responsive Design */
@media (max-width: 768px) {
    .sidebar {
        width: 200px;
    }

    .sidebar h2 {
        font-size: 20px;
    }

    .sidebar ul li a {
        font-size: 14px;
    }

    .main-content {
        margin-left: 200px;
        padding: 20px;
    }

    .add-candidate-form {
        padding: 15px 20px;
    }
}

</style>
<body>
    <div class="dashboard-container">
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

        <div class="main-content">
            <div class="header">
                <h1>Edit Candidate</h1>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="add-candidate-form">
                <form method="POST" action="editCandidate.php?id=<?php echo $candidate_id; ?>" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($candidate['Name'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="election_id">Election:</label>
                        <select id="election_id" name="election_id" required>
                            <?php while ($election = $elections->fetch_assoc()): ?>
                                <option value="<?php echo $election['ElectionID']; ?>" 
                                    <?php echo ($candidate['ElectionID'] == $election['ElectionID']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($election['Title']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="party">Party:</label>
                        <input type="text" id="party" name="party" value="<?php echo htmlspecialchars($candidate['Party'] ?? ''); ?>">
                    </div>

                    

                    <div class="form-actions">
                        <button type="submit" class="btn btn-edit">Update Candidate</button>
                        <a href="candidateManagement.php" class="btn btn-delete">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

<?php $conn->close(); ?>