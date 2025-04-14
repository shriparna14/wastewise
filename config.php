<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "wastewise";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<?php
// session_start();

// $db_host = 'localhost';
// $db_user = 'root';
// $db_pass = '';
// $db_name = 'wastewise';

// try {
//     $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
//     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// } catch(PDOException $e) {
//     die("Connection failed: " . $e->getMessage());
// }

// function sanitizeInput($data) {
//     return htmlspecialchars(stripslashes(trim($data)));
// }
 ?>
 <?php
// $conn->query("CREATE TABLE IF NOT EXISTS users (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     username VARCHAR(50) NOT NULL,
//     email VARCHAR(100) UNIQUE NOT NULL,
//     password VARCHAR(255) NOT NULL,
//     role ENUM('resident', 'collector', 'admin') NOT NULL,
//     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
// )");
?>

<?php
// session_start();

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'wastewise';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create table with role column
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('resident', 'collector', 'admin') NOT NULL DEFAULT 'resident',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>