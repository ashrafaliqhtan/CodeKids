<?php
function hasCompletedGame($conn, $user_id, $lesson_id) {
    $sql = "SELECT ugp.* FROM user_game_progress ugp
            JOIN lesson_games lg ON ugp.game_id = lg.game_id
            WHERE ugp.user_id = ? AND lg.lesson_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $lesson_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return ($result->num_rows > 0);
}

function getLessonGameId($conn, $lesson_id) {
    $sql = "SELECT game_id FROM lesson_games WHERE lesson_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $lesson_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return ($result->num_rows > 0) ? $result->fetch_assoc()['game_id'] : null;
}

function getGameQuestions($conn, $game_id) {
    $sql = "SELECT questions FROM lesson_games WHERE game_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $game_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return ($result->num_rows > 0) ? json_decode($result->fetch_assoc()['questions'], true) : null;
}

function saveGameResult($conn, $user_id, $game_id, $score) {
    $sql = "INSERT INTO user_game_progress (user_id, game_id, score) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $user_id, $game_id, $score);
    return $stmt->execute();
}
?>