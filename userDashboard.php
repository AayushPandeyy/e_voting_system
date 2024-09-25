<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - E-Voting App</title>
    <link rel="stylesheet" href="./css/user_styles.css">
    <style>
        /* Modal Styles */
        .modal {
    display: none; /* Hidden by default */
    position: fixed; /* Stay in place */
    z-index: 1000; /* Sit on top */
    left: 0;
    top: 0;
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    overflow: auto; /* Enable scroll if needed */
    background-color: rgba(0, 0, 0, 0.6); /* Black w/ opacity for overlay */
}

/* Modal Content */
.modal-content {
    background: linear-gradient(135deg, #ffffff, #f9f9f9); /* Gradient background */
    margin: 10% auto; /* Centered vertically and horizontally */
    padding: 20px;
    border-radius: 10px; /* Rounded corners */
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); /* Soft shadow for depth */
    width: 90%; /* Responsive width */
    max-width: 500px; /* Maximum width for larger screens */
    animation: fadeIn 0.3s ease; /* Fade-in animation */
}

/* Close Button Styles */
.close-button {
    color: #333;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close-button:hover,
.close-button:focus {
    color: #ff0000; /* Change color on hover */
}

/* Input Field Styles */
input[type="password"] {
    width: calc(100% - 20px); /* Full width minus padding */
    padding: 10px;
    margin-top: 10px;
    margin-bottom:10px;
    border-radius: 5px; /* Rounded corners for input fields */
    border: 1px solid #ccc; /* Light border */
}

/* Submit Button Styles */
button#submit-password {
    background-color: #007bff; /* Primary button color */
    color: white;
    border: none;
    padding: 10px;
    border-radius: 5px; /* Rounded corners for buttons */
    cursor: pointer;
}

button#submit-password:hover {
    background-color: #0056b3; /* Darker shade on hover */
}

/* Animation Keyframes for Fade In Effect */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
    </style>
</head>
<body>
    <div class="user-dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>Welcome, User</h2>
            <ul>
                <li><a href="#">Dashboard</a></li>
                <li><a href="#">Available Polls</a></li>
                <li><a href="#">My Votes</a></li>
                <li><a href="#">Profile</a></li>
                <li><a href="#">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>User Dashboard</h1>
            </div>

            <!-- Available Polls Section -->
            <div class="available-polls">
                <h2>Available Polls</h2>
                <div class="poll-card">
                    <h3>Presidential Election</h3>
                    <p>Ends: Sept 15, 2024</p>
                    <button onclick="openModal()">Vote Now</button>
                </div>
                <div class="poll-card">
                    <h3>Community Development Fund</h3>
                    <p>Ends: Sept 20, 2024</p>
                    <button onclick="openModal()">Vote Now</button>
                </div>
            </div>

            <!-- Password Modal -->
            <div id="password-modal" class="modal">
                <div class="modal-content">
                    <span class="close-button" onclick="closeModal()">&times;</span>
                    <h2>Enter Election Password</h2>
                    <input type="password" id="election-password" placeholder="Enter Password">
                    <button id="submit-password" onclick="submitPassword()">Submit</button>
                </div>
            </div>

            <!-- Voting History Section -->
            <div class="voting-history">
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

        </div> <!-- End of Main Content -->
    </div> <!-- End of User Dashboard Container -->

    <!-- JavaScript for Modal Functionality -->
    <script>
        function openModal() {
            document.getElementById("password-modal").style.display = "block";
        }

        function closeModal() {
            document.getElementById("password-modal").style.display = "none";
        }

        function submitPassword() {
            const password = document.getElementById("election-password").value;

            // Add your password validation logic here
            if (password) {
                alert("Password submitted successfully!"); // Replace with actual voting logic
                closeModal();
                // Here you would typically handle the voting logic (e.g., send to server)
            } else {
                alert("Please enter a password.");
            }
        }

        // Close the modal when clicking outside of it
        window.onclick = function(event) {
          const modal = document.getElementById("password-modal");
          if (event.target == modal) {
              closeModal();
          }
        }
    </script>

</body>
</html>
