<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

requireStudent();

$student_id = $_SESSION['user_id'];

// Fetch active exams
$stmt = $pdo->query("SELECT * FROM exams WHERE status='active'");
$exams = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if student already attempted
$attempted_stmt = $pdo->prepare("SELECT exam_id FROM exam_attempts WHERE user_id=?");
$attempted_stmt->execute([$student_id]);
$attempted_exams = $attempted_stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Available Exams</title>
<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="container">
<h2>Available Exams</h2>

<table>
<tr>
<th>#</th>
<th>Exam Title</th>
<th>Duration (minutes)</th>
<th>Action</th>
</tr>

<?php foreach ($exams as $index => $exam): ?>
<tr>
<td><?= $index + 1 ?></td>
<td><?= htmlspecialchars($exam['exam_title']) ?></td>
<td><?= $exam['duration_minutes'] ?></td>
<td>
<?php if (in_array($exam['id'], $attempted_exams)): ?>
<span class="info">Already Attempted</span>
<?php else: ?>
<a href="take_exam.php?exam_id=<?= $exam['id'] ?>">Start Exam</a>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>

</table>
</div>
</body>
</html>
