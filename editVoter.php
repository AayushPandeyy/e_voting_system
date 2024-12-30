<?php
session_start();
include 'db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error_msg = '';
$success_msg = '';

// Get voter ID from URL
$voter_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $citizenship_number = trim($_POST['citizenship_number']);
    $new_password = trim($_POST['new_password']);

    // Validate inputs
    if (empty($full_name) || empty($email) || empty($citizenship_number)) {
        $error_msg = "All fields are required except password.";
    } else {
        // Start with base update query
        if (!empty($new_password)) {
            // If password is provided, update it too
            $updateQuery = "UPDATE users SET full_name = ?, email = ?, citizenship_number = ?, password = ? WHERE id = ? AND role = 'voter'";
            $stmt = $conn->prepare($updateQuery);
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt->bind_param("ssssi", $full_name, $email, $citizenship_number, $hashed_password, $voter_id);
        } else {
            // If no password provided, update other fields only
            $updateQuery = "UPDATE users SET full_name = ?, email = ?, citizenship_number = ? WHERE id = ? AND role = 'voter'";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("sssi", $full_name, $email, $citizenship_number, $voter_id);
        }

        if ($stmt->execute()) {
            $success_msg = "Voter information updated successfully.";
            header("Location: voterManagement.php");
        } else {
            $error_msg = "Error updating voter information. Please try again.";
        }
    }
}

// Fetch current voter information
$voterQuery = "SELECT * FROM users WHERE id = ? AND role = 'voter'";
$stmt = $conn->prepare($voterQuery);
$stmt->bind_param("i", $voter_id);
$stmt->execute();
$voter = $stmt->get_result()->fetch_assoc();

// If voter not found, redirect to voter management
if (!$voter) {
    header("Location: voterManagement.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Voter</title>
    <link rel="stylesheet" href="./css/dashboardStyles.css">
    <style>
        .edit-form {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #2d3748;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 1rem;
        }

        .form-group input:focus {
            outline: none;
            border-color: #4299e1;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
        }

        .alerts {
            margin-bottom: 1.5rem;
        }

        .alert {
            padding: 0.75rem 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
        }

        .alert-error {
            background-color: #FED7D7;
            color: #9B2C2C;
        }

        .alert-success {
            background-color: #C6F6D5;
            color: #276749;
        }

        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: #4299E1;
            color: white;
        }

        .btn-primary:hover {
            background-color: #3182CE;
        }

        .btn-secondary {
            background-color: #CBD5E0;
            color: #2D3748;
        }

        .btn-secondary:hover {
            background-color: #A0AEC0;
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
                <li><a href="candidateManagement.php">Candidates</a></li>
                <li><a href="voterManagement.php" class="active">Voter Management</a></li>
                <li><a href="adminSettings.php">Settings</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Edit Voter</h1>
            </div>

            <div class="edit-form">
                <div class="alerts">
                    <?php if ($error_msg): ?>
                        <div class="alert alert-error"><?php echo $error_msg; ?></div>
                    <?php endif; ?>
                    <?php if ($success_msg): ?>
                        <div class="alert alert-success"><?php echo $success_msg; ?></div>
                    <?php endif; ?>
                </div>

                <form method="POST">
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" 
                               value="<?php echo htmlspecialchars($voter['full_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($voter['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="citizenship_number">Citizenship Number</label>
                        <input type="text" id="citizenship_number" name="citizenship_number" 
                               value="<?php echo htmlspecialchars($voter['citizenship_number']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password (leave blank to keep current)</label>
                        <input type="password" id="new_password" name="new_password">
                    </div>

                    <div class="button-group">
                        <button type="submit" class="btn btn-primary">Update Voter</button>
                        <a href="voterManagement.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

<?php $conn->close(); ?>