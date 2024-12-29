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
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $status = $conn->real_escape_string($_POST['status']);
    $createdBy = intval($_POST['createdBy']);

    // Insert data into the Election table
    $sql = "INSERT INTO Election (Title, Description, StartDate, EndDate, password, Status, CreatedBy) 
            VALUES ('$title', '$description', '$startDate', '$endDate', '$password', '$status', $createdBy)";
    
    if ($conn->query($sql) === TRUE) {
        $successMessage = "Election created successfully!";
    } else {
        $errorMessage = "Error: " . $conn->error;
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #2c3e50;
            --success-color: #27ae60;
            --error-color: #e74c3c;
            --background-color: #f5f6fa;
            --card-background: #ffffff;
            --text-color: #2c3e50;
            --border-radius: 8px;
            --box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            background-color: var(--background-color);
            color: var(--text-color);
            padding: 2rem;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .card {
            background-color: var(--card-background);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 2rem;
        }

        h1 {
            color: var(--secondary-color);
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--secondary-color);
        }

        input, textarea, select {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e1e1e1;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .btn {
            display: inline-block;
            padding: 1rem 2rem;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn:hover {
            background-color: #357abd;
            transform: translateY(-2px);
        }

        .alert {
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
            text-align: center;
        }

        .alert-success {
            background-color: #d4edda;
            color: var(--success-color);
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: var(--error-color);
            border: 1px solid #f5c6cb;
        }

        .status-option {
            padding: 0.5rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            
            .card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1><i class="fas fa-vote-yea"></i> Create Election</h1>
            
            <?php if (isset($successMessage)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $successMessage; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($errorMessage)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $errorMessage; ?>
                </div>
            <?php endif; ?>

            <form action="createElection.php" method="POST">
                <div class="form-group">
                    <label for="title">
                        <i class="fas fa-heading"></i> Title
                    </label>
                    <input type="text" id="title" name="title" required placeholder="Enter election title">
                </div>
                
                <div class="form-group">
                    <label for="description">
                        <i class="fas fa-align-left"></i> Description
                    </label>
                    <textarea id="description" name="description" required placeholder="Enter election description"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="startDate">
                        <i class="fas fa-calendar-plus"></i> Start Date
                    </label>
                    <input type="datetime-local" id="startDate" name="startDate" required>
                </div>
                
                <div class="form-group">
                    <label for="endDate">
                        <i class="fas fa-calendar-minus"></i> End Date
                    </label>
                    <input type="datetime-local" id="endDate" name="endDate" required>
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <input type="password" id="password" name="password" required placeholder="Enter secure password">
                </div>
                
                <div class="form-group">
                    <label for="status">
                        <i class="fas fa-info-circle"></i> Status
                    </label>
                    <select id="status" name="status" required>
                        <option value="" disabled selected>Select election status</option>
                        <option value="Upcoming" class="status-option">Upcoming</option>
                        <option value="Ongoing" class="status-option">Ongoing</option>
                        <option value="Completed" class="status-option">Completed</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="createdBy">
                        <i class="fas fa-user"></i> Created By (User ID)
                    </label>
                    <input type="number" id="createdBy" name="createdBy" required placeholder="Enter your user ID">
                </div>
                
                <button type="submit" class="btn">
                    <i class="fas fa-plus-circle"></i> Create Election
                </button>
            </form>
        </div>
    </div>
</body>
</html>