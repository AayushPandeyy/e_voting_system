<?php
session_start();
include 'db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Handle voter deletion
if (isset($_POST['delete_voter'])) {
    $voter_id = $_POST['voter_id'];
    $deleteQuery = "DELETE FROM users WHERE id = ? AND role != 'admin'";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $voter_id);
    $stmt->execute();
}

// Handle bulk voter import
if (isset($_POST['import_voters']) && isset($_FILES['voter_file'])) {
    $file = $_FILES['voter_file']['tmp_name'];
    if (($handle = fopen($file, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $email = $data[0];
            $full_name = $data[1];
            $citizenship_number = $data[2];
            $password = password_hash($data[3], PASSWORD_DEFAULT);
            
            $insertQuery = "INSERT INTO users (email, full_name, citizenship_number, password, role) 
                           VALUES (?, ?, ?, ?, 'voter')";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("ssss", $email, $full_name, $citizenship_number, $password);
            $stmt->execute();
        }
        fclose($handle);
    }
}

// Pagination settings
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_condition = '';
if ($search) {
    $search_condition = "AND (full_name LIKE ? OR email LIKE ? OR citizenship_number LIKE ?)";
    $search_param = "%$search%";
}

// Fetch voters with their voting history
$votersQuery = "
    SELECT 
        u.id,
        u.full_name,
        u.email,
        u.citizenship_number,
        COUNT(DISTINCT v.ElectionID) as elections_voted,
        MAX(v.VotedAt) as last_vote
    FROM users u
    LEFT JOIN Votes v ON u.id = v.VoterID
    WHERE u.role = 'voter' 
    $search_condition
    GROUP BY u.id
    ORDER BY u.id DESC
    LIMIT ? OFFSET ?";

$stmt = $conn->prepare($votersQuery);
if ($search) {
    $stmt->bind_param("ssssii", $search_param, $search_param, $search_param, $records_per_page, $offset);
} else {
    $stmt->bind_param("ii", $records_per_page, $offset);
}
$stmt->execute();
$voters = $stmt->get_result();

// Get total number of voters for pagination
$totalQuery = "SELECT COUNT(*) as total FROM users WHERE role = 'voter'";
if ($search) {
    $totalQuery .= " AND (full_name LIKE ? OR email LIKE ? OR citizenship_number LIKE ?)";
}
$stmt = $conn->prepare($totalQuery);
if ($search) {
    $stmt->bind_param("sss", $search_param, $search_param, $search_param);
}
$stmt->execute();
$total_voters = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_voters / $records_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voter Management</title>
    <link rel="stylesheet" href="./css/dashboardStyles.css">
    <style>
        .voter-management {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .management-tools {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .search-box {
            flex: 1;
            max-width: 300px;
            margin-right: 1rem;
        }

        .search-box input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
        }

        .import-form {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        th {
            background: #f7fafc;
            font-weight: 600;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }

        .page-link {
            padding: 0.5rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            text-decoration: none;
            color: #4a5568;
        }

        .page-link.active {
            background: #4299E1;
            color: white;
            border-color: #4299E1;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 0.875rem;
            transition: background-color 0.2s;
        }

        .btn-edit { background: #4299E1; color: white; }
        .btn-delete { background: #F56565; color: white; }
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
                <h1>Voter Management</h1>
            </div>

            <div class="voter-management">
                

                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Citizenship Number</th>
                            <th>Elections Voted</th>
                            <th>Last Vote</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($voter = $voters->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($voter['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($voter['email']); ?></td>
                                <td><?php echo htmlspecialchars($voter['citizenship_number']); ?></td>
                                <td><?php echo $voter['elections_voted']; ?></td>
                                <td><?php echo $voter['last_vote'] ? date('M d, Y', strtotime($voter['last_vote'])) : 'Never'; ?></td>
                                <td>
    <div class="candidate-actions">
        <a href="editVoter.php?id=<?php echo $voter['id']; ?>" class="btn btn-edit">Edit</a>
        <form method="POST" style="display: inline;" 
              onsubmit="return confirm('Are you sure you want to delete this voter?');">
            <input type="hidden" name="voter_id" value="<?php echo $voter['id']; ?>">
            <button type="submit" name="delete_voter" class="btn btn-delete">Delete</button>
        </form>
    </div>
</td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                           class="page-link <?php echo $page == $i ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php $conn->close(); ?>