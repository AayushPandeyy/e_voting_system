<?php
$servername = "localhost"; // Your database server name
$username = "root"; // Your database username
$password = "Iamaprogram"; // Your database password
$dbname = "evoting_db"; // Your database name

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Create a PDO instance with error mode set to exception
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Ensure persistent connections and disable emulated prepared statements
    $pdo->setAttribute(PDO::ATTR_PERSISTENT, true);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

} catch(PDOException $e) {
    // Log the error instead of displaying sensitive information
    error_log("Database Connection Error: " . $e->getMessage());
    
    // Display a generic error message
    die("Sorry, there was a problem connecting to the database.");
}
if($conn->connect_error){
    die("Error connecting to daatabase");
}
?>
