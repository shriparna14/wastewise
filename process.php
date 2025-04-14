<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'datetime' => htmlspecialchars($_POST['datetime']),
        'address' => htmlspecialchars($_POST['address']),
        'phone' => htmlspecialchars($_POST['phone']),
        'waste_type' => htmlspecialchars($_POST['waste_type'])
    ];

    // Save to database (example using MySQLi)
    $conn = new mysqli('localhost', 'username', 'password', 'database');
    $stmt = $conn->prepare("INSERT INTO pickups 
        (datetime, address, phone, waste_type) 
        VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", 
        $data['datetime'],
        $data['address'],
        $data['phone'],
        $data['waste_type']);
    $stmt->execute();
    
    header('Location: thank-you.html');
    exit();
}
?>