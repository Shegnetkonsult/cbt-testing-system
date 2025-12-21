<?php
require_once '../includes/auth.php';
requireStudent();
require_once '../includes/config.php';

$student_id = $_SESSION['user_id'];
$attempt_id = (int)($_POST['attempt_id'] ?? 0);

if (!$attempt_id) die("Invalid attempt.");

// Mark exam completed
$stmt = $pdo->prepare("UPDATE exam_attempts SET status='completed', end_time=NOW() WHERE id=?");
$stmt->execute([$attempt_id]);

// Redirect to result
header("Location: exam_result.php?attempt_id=$attempt_id");
exit;
