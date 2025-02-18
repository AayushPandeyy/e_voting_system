<?php
include "db.php";
session_start();

// Check if user is admin
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

// Initialize error array
$errors = [];

// Validation function
function validateDate($date, $format = 'Y-m-d\TH:i') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// Check database connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// If the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate title
    $title = trim($_POST['title'] ?? '');
    if (empty($title)) {
        $errors['title'] = "Title is required";
    } elseif (strlen($title) > 255) {
        $errors['title'] = "Title must be less than 255 characters";
    }

    // Validate description
    $description = trim($_POST['description'] ?? '');
    if (empty($description)) {
        $errors['description'] = "Description is required";
    }

    // Validate dates
    $startDate = $_POST['startDate'] ?? '';
    $endDate = $_POST['endDate'] ?? '';
    
    if (!validateDate($startDate)) {
        $errors['startDate'] = "Invalid start date format";
    }
    
    if (!validateDate($endDate)) {
        $errors['endDate'] = "Invalid end date format";
    }
    
    if (validateDate($startDate) && validateDate($endDate)) {
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $now = new DateTime();
        
        if ($start > $end) {
            $errors['dateRange'] = "End date must be after start date";
        }
        if ($start < $now && empty($errors['startDate'])) {
            $errors['startDate'] = "Start date cannot be in the past";
        }
    }

    // Validate password
    $password = $_POST['password'] ?? '';
    if (empty($password)) {
        $errors['password'] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors['password'] = "Password must be at least 8 characters long";
    }

    // If no errors, proceed with insertion
    if (empty($errors)) {
        try {
            // Prepare the SQL statement
            $stmt = $conn->prepare("INSERT INTO Election (Title, Description, StartDate, EndDate, password) VALUES (?, ?, ?, ?, ?)");
            
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            
            // Bind parameters
            $stmt->bind_param("sssss", 
                $title,
                $description,
                $startDate,
                $endDate,
                $hashedPassword
            );
            
            if ($stmt->execute()) {
                $successMessage = "Election created successfully!";
                // Clear form data after successful submission
                $_POST = array();
                header("Location:electionManagement.php");
            } else {
                $errors['database'] = "Error creating election: " . $stmt->error;
            }
            
            $stmt->close();
        } catch (Exception $e) {
            $errors['database'] = "Database error: " . $e->getMessage();
        }
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

        input, textarea {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e1e1e1;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        input:focus, textarea:focus {
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

        .error-message {
            color: var(--error-color);
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        .input-error {
            border-color: var(--error-color) !important;
        }

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

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> Please correct the errors below.
                </div>
            <?php endif; ?>

            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" novalidate>
                <div class="form-group">
                    <label for="title">
                        <i class="fas fa-heading"></i> Title
                    </label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title ?? ''); ?>" 
                           class="<?php echo isset($errors['title']) ? 'input-error' : ''; ?>">
                    <?php if (isset($errors['title'])): ?>
                        <div class="error-message"><?php echo $errors['title']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="description">
                        <i class="fas fa-align-left"></i> Description
                    </label>
                    <textarea id="description" name="description" 
                              class="<?php echo isset($errors['description']) ? 'input-error' : ''; ?>"
                    ><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                    <?php if (isset($errors['description'])): ?>
                        <div class="error-message"><?php echo $errors['description']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="startDate">
                        <i class="fas fa-calendar-plus"></i> Start Date
                    </label>
                    <input type="datetime-local" id="startDate" name="startDate" 
                           value="<?php echo htmlspecialchars($startDate ?? ''); ?>"
                           class="<?php echo isset($errors['startDate']) ? 'input-error' : ''; ?>">
                    <?php if (isset($errors['startDate'])): ?>
                        <div class="error-message"><?php echo $errors['startDate']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="endDate">
                        <i class="fas fa-calendar-minus"></i> End Date
                    </label>
                    <input type="datetime-local" id="endDate" name="endDate" 
                           value="<?php echo htmlspecialchars($endDate ?? ''); ?>"
                           class="<?php echo isset($errors['endDate']) ? 'input-error' : ''; ?>">
                    <?php if (isset($errors['endDate'])): ?>
                        <div class="error-message"><?php echo $errors['endDate']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <input type="password" id="password" name="password"
                           class="<?php echo isset($errors['password']) ? 'input-error' : ''; ?>">
                    <?php if (isset($errors['password'])): ?>
                        <div class="error-message"><?php echo $errors['password']; ?></div>
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="btn">
                    <i class="fas fa-plus-circle"></i> Create Election
                </button>
            </form>
        </div>
    </div>

    <script>
    document.querySelector('form').addEventListener('submit', function(e) {
        let hasError = false;
        const title = document.getElementById('title').value.trim();
        const description = document.getElementById('description').value.trim();
        const startDate = new Date(document.getElementById('startDate').value);
        const endDate = new Date(document.getElementById('endDate').value);
        const password = document.getElementById('password').value;

        // Clear previous error messages
        document.querySelectorAll('.error-message').forEach(el => el.remove());
        document.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));

        // Title validation
        if (!title) {
            showError('title', 'Title is required');
            hasError = true;
        }

        // Description validation
        if (!description) {
            showError('description', 'Description is required');
            hasError = true;
        }

        // Date validation
        if (startDate >= endDate) {
            showError('endDate', 'End date must be after start date');
            hasError = true;
        }

        // Password validation
        if (password.length < 8) {
            showError('password', 'Password must be at least 8 characters long');
            hasError = true;
        }

        if (hasError) {
            e.preventDefault();
        }
    });

    function showError(fieldId, message) {
        const field = document.getElementById(fieldId);
        field.classList.add('input-error');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        field.parentNode.appendChild(errorDiv);
    }
    </script>
</body>
</html>