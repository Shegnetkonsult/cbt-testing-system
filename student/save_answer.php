<?php
require_once '../includes/auth.php';
requireStudent();
require_once '../includes/config.php';

$attempt_id = (int)($_POST['attempt_id'] ?? 0);
$question_id = (int)($_POST['question_id'] ?? 0);
$selected_option = $_POST['selected_option'] ?? null;

if (!in_array($selected_option, ['A','B','C','D', null])) exit;

// Insert or update
$stmt = $pdo->prepare("
    INSERT INTO student_answers (attempt_id, question_id, selected_option)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE selected_option=VALUES(selected_option)
");
$stmt->execute([$attempt_id, $question_id, $selected_option]);
echo 'ok';
