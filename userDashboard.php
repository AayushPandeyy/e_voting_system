<?php 
include 'db.php';
$sql = "SELECT * FROM Election";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - E-Voting App</title>
    <link rel="stylesheet" href="./css/userStyles.css">
    <style>
        /* General Styles */


    </style>
    
</head>
<body>
    <div class="user-dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>Welcome, User</h2>
            <ul>
                <li><a href="#" onclick="showPage('dashboard')">Dashboard</a></li>
                <li><a href="#" onclick="showPage('available-polls')">Available Polls</a></li>
                <li><a href="#" onclick="showPage('my-votes')">My Votes</a></li>
                <li><a href="#" onclick="showPage('profile')">Profile</a></li>
                <li><a href="#" onclick="logout()">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div id="dashboard" class="page active">
                <h1>User Dashboard</h1>
                <p>Welcome to your dashboard!</p>
            </div>

            <div id="available-polls" class="page">
                <h2>Available Polls</h2>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo '<div class="poll-card">';
                        echo '<h3>' . $row["Title"] . '</h3>';
                        echo '<p>Ends: ' . $row["EndDate"] . '</p>';
                        echo '<button onClick = openModal(' . $row["ElectionID"] . ')>Vote Now</button>';
                        echo '</div>';
                    }
                } else {
                    echo "<p>No elections available at the moment.</p>";
                }
                ?>
            </div>

            <div id="my-votes" class="page">
                <h2>My Voting History</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Poll Name</th>
                            <th>Date</th>
                            <th>Vote</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Presidential Election 2022</td>
                            <td>Jan 10, 2022</td>
                            <td>John Doe</td>
                        </tr>
                        <tr>
                            <td>Local Council Election 2023</td>
                            <td>Feb 5, 2023</td>
                            <td>Jane Smith</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="profile" class="page">
                <h2>User Profile</h2>
                <p>Profile details go here.</p>
            </div>
        </div> <!-- End of Main Content -->

        <!-- Password Modal -->
        <!-- Password Modal -->
<div id="password-modal" class="modal">
    <div class="modal-content">
        <span class="close-button" onclick="closeModal()">&times;</span>
        <h2>Enter Password to Vote</h2>
        <form method="POST" action="validatePassword.php">
            <input type="hidden" name="election_id" id="election-id">
            <input type="password" name="password" id="password" placeholder="Enter your password" required>
            <button type="submit" id="submit-password">Submit</button>
        </form>
    </div>
</div>


    </div>

    <script>
        function showPage(pageId) {
            const pages = document.querySelectorAll('.page');
            pages.forEach(page => {
                page.classList.remove('active');
            });
            document.getElementById(pageId).classList.add('active');
        }

        

window.onclick = function (event) {
  const modal = document.getElementById("password-modal");
  if (event.target == modal) {
    closeModal();
  }
};

    </script>
    <script src="./js/modal.js"></script>
</body>
</html>
