<?php
header('Content-Type: application/json');
session_start();
include('../dbConnection.php');

if(!isset($_SESSION['is_login'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to save results']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if(!isset($data['user_id']) || !isset($data['game_id']) || !isset($data['score'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required data']);
    exit();
}

$user_id = $data['user_id'];
$game_id = $data['game_id'];
$score = $data['score'];

// Check if user already completed this game
$check_sql = "SELECT * FROM user_game_progress WHERE user_id = ? AND game_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $user_id, $game_id);
$check_stmt->execute();

if($check_stmt->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'You have already completed this game']);
    exit();
}

// Insert new game progress
$insert_sql = "INSERT INTO user_game_progress (user_id, game_id, score) VALUES (?, ?, ?)";
$insert_stmt = $conn->prepare($insert_sql);
$insert_stmt->bind_param("iii", $user_id, $game_id, $score);

if($insert_stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}
?>