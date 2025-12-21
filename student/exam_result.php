<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

requireStudent();

$attempt_id = (int) $_GET['attempt_id'] ?? 0;

$stmt = $pdo->prepare("
    SELECT ea.*, e.exam_title
    FROM exam_attempts ea
    JOIN exams e ON ea.exam_id=e.id
    WHERE ea.id=? AND ea.user_id=?
");
$stmt->execute([$attempt_id, $_SESSION['user_id']]);
$attempt = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$attempt) die("Exam attempt not found.");

// Calculate score
$answers_stmt = $pdo->prepare("
    SELECT sa.selected_option, q.correct_option
    FROM student_answers sa
    JOIN questions q ON sa.question_id=q.id
    WHERE sa.attempt_id=?
");
$answers_stmt->execute([$attempt_id]);
$answers = $answers_stmt->fetchAll(PDO::FETCH_ASSOC);

$total_questions = count($answers);
$correct_count = 0;
foreach ($answers as $a) {
    if ($a['selected_option'] === $a['correct_option']) $correct_count++;
}

$percentage = $total_questions ? round(($correct_count/$total_questions)*100,2) : 0;
$result_status = $percentage >= ($attempt['pass_mark'] ?? 50) ? 'pass' : 'fail';

// Save results table
$insert_result = $pdo->prepare("
    INSERT INTO results (user_id, exam_id, score, total_questions, percentage, result_status)
    VALUES (?, ?, ?, ?, ?, ?)
");
$insert_result->execute([
    $_SESSION['user_id'],
    $attempt['exam_id'],
    $correct_count,
    $total_questions,
    $percentage,
    $result_status
]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Exam Result</title>
<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="container">
<h2>Exam Result: <?= htmlspecialchars($attempt['exam_title']) ?></h2>
<p>Score: <?= $correct_count ?> / <?= $total_questions ?></p>
<p>Percentage: <?= $percentage ?>%</p>
<p>Status: <strong><?= strtoupper($result_status) ?></strong></p>
<a href="exams.php">Back to Exams</a>
</div>
</body>
</html>
