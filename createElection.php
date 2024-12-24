<?php
// Database connection setup
include "db.php";

// If the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $startDate = $conn->real_escape_string($_POST['startDate']);
    $endDate = $conn->real_escape_string($_POST['endDate']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash the password
    $status = $conn->real_escape_string($_POST['status']);
    $createdBy = intval($_POST['createdBy']); // Ensure this is an integer

    // Insert data into the Election table
    $sql = "INSERT INTO Election (Title, Description, StartDate, EndDate, password, Status, CreatedBy) 
            VALUES ('$title', '$description', '$startDate', '$endDate', '$password', '$status', $createdBy)";
    
    if ($conn->query($sql) === TRUE) {
        echo "Election created successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Election</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        form {
            max-width: 500px;
            margin: 0 auto;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, textarea, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            padding: 10px 20px;
            border: none;
            background-color: #007bff;
            color: #fff;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>Create Election</h1>
    <form action="createElection.php" method="POST">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" required>
        
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="5" required></textarea>
        
        <label for="startDate">Start Date</label>
        <input type="datetime-local" id="startDate" name="startDate" required>
        
        <label for="endDate">End Date</label>
        <input type="datetime-local" id="endDate" name="endDate" required>
        
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
        
        <label for="status">Status</label>
        <select id="status" name="status" required>
            <option value="Upcoming">Upcoming</option>
            <option value="Ongoing">Ongoing</option>
            <option value="Completed">Completed</option>
        </select>
        
        <label for="createdBy">Created By (User ID)</label>
        <input type="number" id="createdBy" name="createdBy" required>
        
        <button type="submit">Create Election</button>
    </form>
</body>
</html>
