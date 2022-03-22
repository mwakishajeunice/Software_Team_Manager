<?php
$servername = "localhost";
$dbname = "tms_db";
$username = "root"; //favour
$password = "";
$port = 3307;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>
