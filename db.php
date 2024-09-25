<?php
$servername = "localhost"; // Your database server name
$username = "root"; // Your database username
$password = "Iamaprogram"; // Your database password
$dbname = "evoting_db"; // Your database name

$conn = new mysqli($servername, $username, $password, $dbname);
if($conn->connect_error){
    die("Error connecting to daatabase");
}
?>
