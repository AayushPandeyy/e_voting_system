<?php
$servername = "localhost"; // Your database server name
$username = "root"; // Your database username
$password = "Iamaprogram"; // Your database password
$dbname = "evoting_db"; // Your database name

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
