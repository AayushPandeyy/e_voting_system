<?php
session_start();
include 'db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

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

// Handle profile picture upload
if (isset($_POST['update_profile_picture']) && isset($_FILES['profile_picture'])) {
    $candidate_id = $_POST['candidate_id'];
    $file = $_FILES['profile_picture'];

    // Configure upload settings
    $upload_dir = 'uploads/candidates/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB

    try {
        // Validate file type
        if (!in_array($file['type'], $allowed_types)) {
            throw new Exception('Invalid file type. Only JPG, PNG, and GIF are allowed.');
        }

        // Validate file size
        if ($file['size'] > $max_size) {
            throw new Exception('File size too large. Maximum size is 5MB.');
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('candidate_', true) . '.' . $extension;
        $upload_path = $upload_dir . $filename;

        // Move the uploaded file
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            // Update the database with the new profile picture path
            $updateQuery = "UPDATE Candidate SET ProfilePicture = ? WHERE CandidateID = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("si", $filename, $candidate_id);
            $stmt->execute();

            $_SESSION['success_message'] = "Profile picture updated successfully.";
        } else {
            throw new Exception('Failed to upload file.');
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error updating profile picture: " . $e->getMessage();
    }

    // Redirect to refresh the page
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}


// Handle candidate deletion
if (isset($_POST['delete_candidate'])) {
    $candidate_id = $_POST['candidate_id'];
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // First delete votes for this candidate
        $deleteVotesQuery = "DELETE FROM Votes WHERE CandidateID = ?";
        $stmt = $conn->prepare($deleteVotesQuery);
        $stmt->bind_param("i", $candidate_id);
        $stmt->execute();
        
        // Then delete the candidate
        $deleteQuery = "DELETE FROM Candidate WHERE CandidateID = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param("i", $candidate_id);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        $_SESSION['success_message'] = "Candidate and associated votes deleted successfully.";
        
    } catch (Exception $e) {
        // Rollback in case of error
        $conn->rollback();
        $_SESSION['error_message'] = "Error deleting candidate: " . $e->getMessage();
    }
    
    // Redirect to refresh the page
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}




// Fetch all candidates with their election information
$candidatesQuery = "
    SELECT 
        c.*,
        e.Title as ElectionTitle,
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
            font-family: 'Roboto', sans-serif;
    background: #f4f5f7;
    color: #2d3748;
    margin: 0;
    padding: 0;
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
            min-height: 100vh;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 10px 0 0 0;
        }

        .header {
            margin-bottom: 2rem;
        }

        /* Profile Picture Styles */
        .profile-picture-container {
            position: relative;
            margin-bottom: 1rem;
            text-align: center;
        }

        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 1rem;
            border: 3px solid #4299e1;
        }

        .profile-picture-form {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }

        .profile-picture-input {
            display: none;
        }

        .profile-picture-label {
            cursor: pointer;
            padding: 0.5rem 1rem;
            background: #4299e1;
            color: white;
            border-radius: 5px;
            font-size: 0.875rem;
            transition: background-color 0.2s ease;
        }

        .profile-picture-label:hover {
            background: #2b6cb0;
        }

        .profile-picture-submit {
            padding: 0.5rem 1rem;
            background: #48bb78;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.875rem;
            transition: background-color 0.2s ease;
        }

        .profile-picture-submit:hover {
            background: #38a169;
        }

        /* Candidate Grid Styling */
        .candidate-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
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

        .btn-edit, 
        .btn-delete {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            text-align: center;
            border-radius: 5px;
            text-decoration: none;
            color: #ffffff;
        }

        .btn-edit {
            background: #4299e1;
            border: none;
        }

        .btn-delete {
            background: #e53e3e;
            border: none;
        }

        .btn-edit:hover {
            background: #2b6cb0;
        }

        .btn-delete:hover {
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
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="electionManagement.php">Elections</a></li>
                <li><a href="candidateManagement.php" class="active">Candidates</a></li>
                <li><a href="voterManagement.php">Voter Management</a></li>
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

            <!-- Display success/error messages if any -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <?php 
                        echo $_SESSION['success_message'];
                        unset($_SESSION['success_message']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger">
                    <?php 
                        echo $_SESSION['error_message'];
                        unset($_SESSION['error_message']);
                    ?>
                </div>
            <?php endif; ?>

            <!-- Candidates Grid -->
            <div class="candidate-grid">
                <?php while ($candidate = $candidates->fetch_assoc()): ?>
                    <div class="candidate-card">
                        <div class="profile-picture-container">
                            <?php if (!empty($candidate['ProfilePicture'])): ?>
                                <img src="uploads/candidates/<?php echo htmlspecialchars($candidate['ProfilePicture']); ?>" 
                                     alt="<?php echo htmlspecialchars($candidate['Name']); ?>'s profile picture"
                                     class="profile-picture">
                            <?php else: ?>
                                <img src="images/default-avatar.png" 
                                     alt="Default profile picture"
                                     class="profile-picture">
                            <?php endif; ?>
                            
                            <form method="POST" enctype="multipart/form-data" class="profile-picture-form">
                                <input type="hidden" name="candidate_id" value="<?php echo $candidate['CandidateID']; ?>">
                                <input type="file" name="profile_picture" id="profile_picture_<?php echo $candidate['CandidateID']; ?>" 
                                       class="profile-picture-input" accept="image/*">
                                <label for="profile_picture_<?php echo $candidate['CandidateID']; ?>" class="profile-picture-label">
                                    Change Photo
                                </label>
                                <button type="submit" name="update_profile_picture" class="profile-picture-submit">Upload</button>
                            </form>
                        </div>
                        
                        <h3><?php echo htmlspecialchars($candidate['Name']); ?></h3>
                        <div class="candidate-info">
                            <p>Election: <?php echo htmlspecialchars($candidate['ElectionTitle']); ?></p>
                            <p>Party: <?php echo htmlspecialchars($candidate['Party']); ?></p>
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