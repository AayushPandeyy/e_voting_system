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
$election_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';

    if (empty($title)) $errors[] = "Title is required";
    if (empty($start_date)) $errors[] = "Start date is required";
    if (empty($end_date)) $errors[] = "End date is required";
    if (strtotime($end_date) <= strtotime($start_date)) {
        $errors[] = "End date must be after start date";
    }

    if (empty($errors)) {
        $updateQuery = "UPDATE Election SET Title = ?, StartDate = ?, EndDate = ? WHERE ElectionID = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("sssi", $title, $start_date, $end_date, $election_id);
        
        if ($stmt->execute()) {
            header("Location: electionManagement.php?success=1");
            exit;
        } else {
            $errors[] = "Error updating election: " . $conn->error;
        }
    }
} else {
    $query = "SELECT * FROM Election WHERE ElectionID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $election_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $election = $result->fetch_assoc();

    if (!$election) {
        header("Location: electionManagement.php?error=1");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Election</title>
    <link rel="stylesheet" href="./css/dashboardStyles.css">
</head>
<style>
    /* General Styles */
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f7f8fa;
    color: #333;
}

.dashboard-container {
    display: flex;
    flex-direction: row;
}

a {
    text-decoration: none;
    color: inherit;
}

/* Sidebar Styles */
.sidebar {
    width: 240px;
    height: 100vh;
    background-color: #2c3e50;
    color: #ecf0f1;
    position: fixed;
    top: 0;
    left: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px 0;
}

.sidebar h2 {
    margin-bottom: 30px;
    font-size: 22px;
    color: #f39c12;
    text-align: center;
}

.sidebar ul {
    list-style: none;
    padding: 0;
    width: 100%;
}

.sidebar ul li {
    width: 100%;
    text-align: center;
}

.sidebar ul li a {
    display: block;
    padding: 15px 20px;
    font-size: 16px;
    color: #ecf0f1;
    border-left: 3px solid transparent;
    transition: all 0.3s ease-in-out;
}

.sidebar ul li a.active, 
.sidebar ul li a:hover {
    background-color: #34495e;
    border-left: 3px solid #f39c12;
    color: #f39c12;
}

/* Main Content Styles */
.main-content {
    margin-left: 240px;
    padding: 30px;
    width: calc(100% - 240px);
    background-color: #ffffff;
}

.main-content .header {
    margin-bottom: 20px;
    border-bottom: 2px solid #ecf0f1;
    padding-bottom: 10px;
}

.main-content h1 {
    font-size: 24px;
    color: #2c3e50;
    margin: 0;
}

/* Form Styles */
.add-candidate-form {
    background-color: #ffffff;
    padding: 20px 30px;
    border: 1px solid #ddd;
    border-radius: 5px;
    max-width: 700px;
    margin: auto;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-size: 16px;
    color: #2c3e50;
    margin-bottom: 5px;
}

.form-group input[type="text"], 
.form-group input[type="date"], 
.form-group select {
    width: 100%;
    padding: 10px;
    font-size: 14px;
    border: 1px solid #ddd;
    border-radius: 3px;
    box-sizing: border-box;
}

input:focus, select:focus {
    border-color: #f39c12;
    outline: none;
    box-shadow: 0 0 5px rgba(243, 156, 18, 0.5);
}

/* Alert Styles */
.alert {
    background-color: #e74c3c;
    color: #ffffff;
    padding: 10px 20px;
    border-radius: 4px;
    margin-bottom: 20px;
    font-size: 14px;
}

.alert p {
    margin: 0;
}

/* Button Styles */
.form-actions {
    display: flex;
    justify-content: space-between;
    gap: 15px;
}

.btn {
    display: inline-block;
    padding: 10px 15px;
    font-size: 14px;
    border: none;
    border-radius: 3px;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-edit {
    background-color: #3498db;
    color: #ffffff;
}

.btn-edit:hover {
    background-color: #2980b9;
}

.btn-delete {
    background-color: #e74c3c;
    color: #ffffff;
    text-align: center;
}

.btn-delete:hover {
    background-color: #c0392b;
}

/* Responsive Design */
@media (max-width: 768px) {
    .sidebar {
        width: 200px;
    }

    .main-content {
        margin-left: 200px;
    }

    .sidebar ul li a {
        font-size: 14px;
        padding: 10px 15px;
    }

    .add-candidate-form {
        padding: 15px;
    }
}
   
</style>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <h2>E-Voting App</h2>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="electionManagement.php" class="active">Elections</a></li>
                <li><a href="candidateManagement.php">Candidates</a></li>
                <li><a href="voterManagement.php">Voter Management</a></li>
                <li><a href="adminSettings.php">Settings</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="header">
                <h1>Edit Election</h1>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="add-candidate-form">
                <form method="POST" action="editElection.php?id=<?php echo $election_id; ?>">
                    <div class="form-group">
                        <label for="title">Title:</label>
                        <input type="text" id="title" name="title" 
                            value="<?php echo htmlspecialchars($election['Title'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
    <label for="start_date">Start Date:</label>
    <input type="date" id="start_date" name="start_date" 
        value="<?php echo isset($election['StartDate']) ? date('Y-m-d', strtotime($election['StartDate'])) : ''; ?>" required>
</div>

<div class="form-group">
    <label for="end_date">End Date:</label>
    <input type="date" id="end_date" name="end_date" 
        value="<?php echo isset($election['EndDate']) ? date('Y-m-d', strtotime($election['EndDate'])) : ''; ?>" required>
</div>

                    

                    <div class="form-actions">
                        <button type="submit" class="btn btn-edit">Update Election</button>
                        <a href="electionManagement.php" class="btn btn-delete">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

<?php $conn->close(); ?>