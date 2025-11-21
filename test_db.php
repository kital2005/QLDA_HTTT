<?php
include 'config.php';

echo "Testing database connection...\n";

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected successfully.\n";

$result = $conn->query("SHOW TABLES");
if ($result->num_rows > 0) {
    echo "Tables in database:\n";
    while ($row = $result->fetch_array()) {
        echo "- " . $row[0] . "\n";
    }
} else {
    echo "No tables found.\n";
}

$conn->close();
?>
