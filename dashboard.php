<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php"); // Redirect to login page if not logged in
    exit;
}

// Display content for logged-in users
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Voting Dashboard</title>
    <link rel="stylesheet" href="./css/dashboardStyles.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>E-Voting App</h2>
            <ul>
                <li><a href="#">Dashboard</a></li>
                <li><a href="#">Voting Results</a></li>
                <li><a href="#">Candidates</a></li>
                <li><a href="#">Voter Management</a></li>
                <li><a href="#">Settings</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Admin Dashboard</h1>
                <div class="user-info">
                    <span>Admin Name</span>
                    <img src="https://via.placeholder.com/40" alt="Profile Picture">
                </div>
            </div>
            
            <div class="stats-cards">
                <div class="card">
                    <h3>Total Voters</h3>
                    <p id="total-voters">1200</p>
                </div>
                <div class="card">
                    <h3>Total Votes</h3>
                    <p id="total-votes">800</p>
                </div>
                <div class="card">
                    <h3>Active Polls</h3>
                    <p id="active-polls">3</p>
                </div>
            </div>

            <div class="recent-results">
                <h2>Recent Voting Results</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Candidate</th>
                            <th>Votes</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>John Doe</td>
                            <td>350</td>
                            <td>43.75%</td>
                        </tr>
                        <tr>
                            <td>Jane Smith</td>
                            <td>290</td>
                            <td>36.25%</td>
                        </tr>
                        <tr>
                            <td>Michael Johnson</td>
                            <td>160</td>
                            <td>20%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="./js/dashboardScript.js"></script>
</body>
</html>
