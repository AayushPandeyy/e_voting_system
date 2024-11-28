<?php 
session_start();
include 'db.php';
$sql = "SELECT * FROM Election WHERE EndDate >= CURDATE()";
$result = $conn->query($sql);
$userId = $_SESSION['user_id'];

// Fetch the username from the database
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$username = null;
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $fullname = $row['full_name'];
    $email = $row['email'];
    $idNumber = $row['id_number'];
} else {
    $username = "Unknown User";
}

// Close the connection
$stmt->close();
$conn->close();



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
        .instructions-container {
  background-color: #f9f9f9;
  border: 2px solid #ddd;
  border-radius: 10px;
  padding: 20px;
  margin: 20px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.instructions-container h2 {
  color: #333;
  font-size: 24px;
  margin-bottom: 15px;
  text-align: center;
}

.instructions-container ol {
  color: #555;
  font-size: 18px;
}

.instructions-container li {
  margin-bottom: 10px;
}

    </style>
    
</head>
<body>
    <div class="user-dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>Welcome, <?php echo htmlspecialchars($fullname); ?></h2>
            <ul>
                <li><a href="#" onclick="showPage('instructions')">Instructions</a></li>
                <li><a href="#" onclick="showPage('available-polls')">Available Polls</a></li>
                <li><a href="#" onclick="showPage('my-votes')">My Votes</a></li>
                <li><a href="#" onclick="showPage('profile')">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
                
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div id="instructions" class="page active">
            <div class="instructions-container">
    <h2>How to Use the E-Voting System</h2>
    <ol>
        <li>Login to your account using your credentials.</li>
        <li>Navigate to the "Available Polls" section to view active elections.</li>
        <li>Click "Vote Now" on the election you wish to participate in.</li>
        <li>Enter your password to confirm your identity.</li>
        <li>Submit your vote and view your voting history in the "My Votes" section.</li>
        <li>Ensure your profile details are correct in the "Profile" section.</li>
    </ol>
</div>
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

            <div class="profile-card page" id="profile">
        <img src="/api/placeholder/150/150" alt="Profile Picture" class="profile-image">
        
        <div class="profile-name" id="fullname"><?php echo htmlspecialchars($fullname); ?></div>
        
        <div class="profile-details">
            <div class="profile-label">ID Number</div>
            <div id="idnumber" class="profile-value"><?php echo htmlspecialchars($idNumber); ?></div>
            
            <div class="profile-label">Email Address</div>
            <div id="email" class="profile-value"><?php echo htmlspecialchars($email); ?></div>
        </div>
        
        <div class="social-links">
            <a href="#" class="social-icon">✉️</a>
            <a href="#" class="social-icon">📱</a>
            <a href="#" class="social-icon">🌐</a>
        </div>
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
