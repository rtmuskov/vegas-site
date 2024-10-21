<?php
$db_host = 'localhost';
$db_username = 'u2565237_default';
$db_password = '5TJuL3Vlh1fme5f4';
$db_name = 'u2565237_default';

$conn = new mysqli($db_host, $db_username, $db_password, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
