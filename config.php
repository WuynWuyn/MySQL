<?php
$servername = "localhost";
$username = "root";
$password = "Dohau17022005@";
$dbname = "winmart";
$socket = "/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock";

$conn = new mysqli($servername, $username, $password, $dbname, null, $socket);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
?>
