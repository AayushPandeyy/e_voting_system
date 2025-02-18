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

// If the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate name
    $name = trim($_POST['name'] ?? '');
    if (empty($name)) {
        $errors['name'] = "Name is required";
    } elseif (strlen($name) > 255) {
        $errors['name'] = "Name must be less than 255 characters";
    }

    // Validate election
    $electionId = filter_var($_POST['electionId'] ?? '', FILTER_VALIDATE_INT);
    if ($electionId === false || $electionId <= 0) {
        $errors['electionId'] = "Valid election must be selected";
    }

    // Validate party (optional)
    $party = trim($_POST['party'] ?? '');
    if (strlen($party) > 100) {
        $errors['party'] = "Party name must be less than 100 characters";
    }

    // Handle profile picture upload
    $profilePicture = null;
if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] === UPLOAD_ERR_OK) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if (!in_array($_FILES['profilePicture']['type'], $allowedTypes)) {
        $errors['profilePicture'] = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
    } elseif ($_FILES['profilePicture']['size'] > $maxSize) {
        $errors['profilePicture'] = "File size too large. Maximum size is 5MB.";
    } else {
        // Generate a unique filename
        $extension = pathinfo($_FILES['profilePicture']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('candidate_', true) . '.' . $extension; // Only the filename
        $uploadPath = 'uploads/candidates/' . $filename; // Full path for file operations

        // Ensure the directory exists
        if (!file_exists('uploads/candidates/')) {
            mkdir('uploads/candidates/', 0777, true);
        }

        // Move the uploaded file to the target directory
        if (move_uploaded_file($_FILES['profilePicture']['tmp_name'], $uploadPath)) {
            $profilePicture = $filename; // Save only the filename for database
        } else {
            $errors['profilePicture'] = "Failed to upload file.";
        }
    }
}


    // If no errors, proceed with database operation
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("INSERT INTO Candidate (Name, Party, ElectionID, ProfilePicture) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssis", $name, $party, $electionId, $profilePicture);
            
            if ($stmt->execute()) {
                $successMessage = "Candidate added successfully!";
                $_POST = array(); // Clear form after successful submission
                header("Location:candidateManagement.php");
            } else {
                $errors['database'] = "Error adding candidate: " . $stmt->error;
            }
        } catch (Exception $e) {
            $errors['database'] = "Database error: " . $e->getMessage();
        }
    }
}

// Fetch available elections for dropdown
$elections = [];
$stmt = $conn->prepare("SELECT ElectionID, Title FROM Election ORDER BY Title");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $elections[] = $row;
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Candidate</title>
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

        input, select {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e1e1e1;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--primary-color);
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
            <h1>
                <i class="fas fa-user-plus"></i> Add Candidate
            </h1>

            <?php if (isset($successMessage)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $successMessage; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors['database'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $errors['database']; ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" 
                  method="POST" 
                  enctype="multipart/form-data" 
                  novalidate>
                
                <div class="form-group">
                    <label for="name">
                        <i class="fas fa-user"></i> Name
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" 
                           required 
                           placeholder="Enter candidate name"
                           class="<?php echo isset($errors['name']) ? 'input-error' : ''; ?>">
                    <?php if (isset($errors['name'])): ?>
                        <div class="error-message"><?php echo $errors['name']; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="election">
                        <i class="fas fa-vote-yea"></i> Election
                    </label>
                    <select id="election" 
                            name="electionId" 
                            required
                            class="<?php echo isset($errors['electionId']) ? 'input-error' : ''; ?>">
                        <option value="">Select an election</option>
                        <?php foreach ($elections as $election): ?>
                            <option value="<?php echo $election['ElectionID']; ?>"
                                    <?php echo (($_POST['electionId'] ?? '') == $election['ElectionID']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($election['Title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['electionId'])): ?>
                        <div class="error-message"><?php echo $errors['electionId']; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="party">
                        <i class="fas fa-flag"></i> Party (Optional)
                    </label>
                    <input type="text" 
                           id="party" 
                           name="party" 
                           value="<?php echo htmlspecialchars($_POST['party'] ?? ''); ?>" 
                           placeholder="Enter party name"
                           class="<?php echo isset($errors['party']) ? 'input-error' : ''; ?>">
                    <?php if (isset($errors['party'])): ?>
                        <div class="error-message"><?php echo $errors['party']; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="profilePicture">
                        <i class="fas fa-image"></i> Profile Picture (Optional)
                    </label>
                    <input type="file" 
                           id="profilePicture" 
                           name="profilePicture" 
                           accept="image/jpeg,image/png,image/gif"
                           class="<?php echo isset($errors['profilePicture']) ? 'input-error' : ''; ?>">
                    <?php if (isset($errors['profilePicture'])): ?>
                        <div class="error-message"><?php echo $errors['profilePicture']; ?></div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn">
                    <i class="fas fa-plus-circle"></i> Add Candidate
                </button>
            </form>
        </div>
    </div>

    <script>
    document.querySelector('form').addEventListener('submit', function(e) {
        let hasError = false;
        const name = document.getElementById('name').value.trim();
        const electionId = document.getElementById('election').value;

        // Clear previous error messages
        document.querySelectorAll('.error-message').forEach(el => el.remove());
        document.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));

        // Name validation
        if (!name) {
            showError('name', 'Name is required');
            hasError = true;
        }

        // Election validation
        if (!electionId) {
            showError('election', 'Please select an election');
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