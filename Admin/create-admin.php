<?php

// create-admin.php
include('dbConnection.php');
$hashedPass = password_hash('securepassword123', PASSWORD_BCRYPT);
$stmt = $conn->prepare("INSERT INTO admin (admin_email, admin_pass) VALUES (?, ?)");
$stmt->bind_param("ss", 'admin@codekids.com', $hashedPass);
$stmt->execute();

?>