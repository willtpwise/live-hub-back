<?php
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "live_hub";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$options = [
    'cost' => 12,
];
$password = password_hash("anzacs22", PASSWORD_DEFAULT);
$sql = "INSERT INTO users (username, password)
VALUES ('will', '$password')";

if ($conn->query($sql) === TRUE) {
    echo "New record created successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
