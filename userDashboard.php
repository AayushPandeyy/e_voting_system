<?php 
session_start();
include 'db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
  }
  if (isset($_SESSION['error'])) {
    $error_message = $_SESSION['error'];
    unset($_SESSION['error']); // Clear the error message after displaying
    echo "<script>alert('$error_message');</script>";
}
  
  

$sql = "SELECT * FROM Election WHERE EndDate >= CURDATE()";
$result = $conn->query($sql);
$sql = "SELECT * FROM Election WHERE EndDate < CURDATE()";
$pastPolls = $conn->query($sql);
$userId = $_SESSION['user_id'];


// Fetch the username from the database
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$userData = $stmt->get_result();

// Add this query after your other SQL queries at the top of the file
$votingHistoryQuery = "SELECT e.Title, v.VotedAt, c.Name as CandidateName 
                      FROM Votes v
                      JOIN Election e ON v.ElectionID = e.ElectionID
                      JOIN Candidate c ON v.CandidateID = c.CandidateID
                      WHERE v.VoterID = ?
                      ORDER BY v.VotedAt DESC";
$stmtHistory = $conn->prepare($votingHistoryQuery);
$stmtHistory->bind_param("i", $userId);
$stmtHistory->execute();
$votingHistory = $stmtHistory->get_result();

$fullname = null;
if ($userData->num_rows > 0) {
    $row = $userData->fetch_assoc();
    $fullname = $row['full_name'];
    $email = $row['email'];
    $idNumber = $row['citizenship_number'];
} else {
    $fullname = "Unknown User";
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

/* Profile Screen css */
.profile-card {
    margin:auto;
  background: white;
  border-radius: 20px;
  box-shadow: 0 15px 35px rgba(50, 50, 93, 0.1), 0 5px 15px rgba(0, 0, 0, 0.07);
  padding: 40px;
  width: 100%;
  max-width: 500px;
  text-align: center;
  transition: all 0.3s ease;
}
.profile-card:hover {
  transform: translateY(-10px);
}
.profile-image {
  width: 150px;
  height: 150px;
  border-radius: 50%;
  object-fit: cover;
  border: 5px solid #f3f3f3;
  margin: 0 auto 20px;
}
.profile-name {
  font-size: 24px;
  font-weight: 700;
  color: #4a4a4a;
  margin-bottom: 10px;
}
.profile-details {
  background: #f9f9f9;
  border-radius: 10px;
  padding: 20px;
  margin-top: 20px;
}
.profile-label {
  font-weight: 600;
  color: #667;
  margin-bottom: 5px;
  text-transform: uppercase;
  font-size: 12px;
  letter-spacing: 1px;
}
.profile-value {
  font-size: 18px;
  color: #333;
  margin-bottom: 15px;
  font-weight: 300;
}
.social-links {
  display: flex;
  justify-content: center;
  margin-top: 20px;
}
.social-icon {
  margin: 0 10px;
  color: #764ba2;
  font-size: 24px;
  text-decoration: none;
  transition: color 0.3s ease;
}
.social-icon:hover {
  color: #667eea;
}
#my-votes table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background: white;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

#my-votes th,
#my-votes td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

#my-votes th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #333;
}

#my-votes tr:hover {
    background-color: #f5f5f5;
}

#my-votes p {
    text-align: center;
    padding: 20px;
    color: #666;
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
                <li><a href="#" onclick="showPage('past-polls')">Past Polls</a></li>
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
            <div id="past-polls" class="page">
    <h2>Past Polls</h2>
    <?php
    if ($pastPolls->num_rows > 0) {
        while($row = $pastPolls->fetch_assoc()) {
            echo '<div class="poll-card">';
            echo '<h3>' . htmlspecialchars($row["Title"]) . '</h3>';
            echo '<p>Ended On : ' . htmlspecialchars($row["EndDate"]) . '</p>';
            echo '<button onclick="window.location.href=\'getResult.php?id=' . $row["ElectionID"] . '\'">See Result</button>';
            echo '</div>';
        }
    } else {
        echo "<p>No past elections available.</p>";
    }
    ?>
</div>

            <div id="my-votes" class="page">
    <h2>My Voting History</h2>
    <?php if ($votingHistory->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Poll Name</th>
                    <th>Date</th>
                    <th>Voted For</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($vote = $votingHistory->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($vote['Title']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($vote['VotedAt'])); ?></td>
                        <td><?php echo htmlspecialchars($vote['CandidateName']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>You haven't voted in any elections yet.</p>
    <?php endif; ?>
</div>

            <div class="profile-card page" id="profile">
        
        
        <div class="profile-name" id="fullname"><?php echo htmlspecialchars($fullname); ?></div>
        
        <div class="profile-details">
            <div class="profile-label">ID Number</div>
            <div id="idnumber" class="profile-value"><?php echo htmlspecialchars($idNumber); ?></div>
            
            <div class="profile-label">Email Address</div>
            <div id="email" class="profile-value"><?php echo htmlspecialchars($email); ?></div>
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
