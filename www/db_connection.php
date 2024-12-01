<?php

$servername = "database";
$username = "root";
$password = "xhakla";
$dbname = "station";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
