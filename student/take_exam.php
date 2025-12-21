<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

requireStudent();

$student_id = $_SESSION['user_id'];
$exam_id = (int) $_GET['exam_id'] ?? 0;

if (!$exam_id) {
    die("Invalid exam.");
}

// Check if exam exists and active
$stmt = $pdo->prepare("SELECT * FROM exams WHERE id=? AND status='active'");
$stmt->execute([$exam_id]);
$exam = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$exam) die("Exam not found or inactive.");

// Check if student already started/completed
$attempt_stmt = $pdo->prepare("SELECT * FROM exam_attempts WHERE user_id=? AND exam_id=?");
$attempt_stmt->execute([$student_id, $exam_id]);
$attempt = $attempt_stmt->fetch(PDO::FETCH_ASSOC);

if (!$attempt) {
    // Create new attempt
    $insert_attempt = $pdo->prepare("INSERT INTO exam_attempts (user_id, exam_id, start_time) VALUES (?, ?, NOW())");
    $insert_attempt->execute([$student_id, $exam_id]);
    $attempt_id = $pdo->lastInsertId();
} else {
    $attempt_id = $attempt['id'];
}

// Fetch questions
$questions_stmt = $pdo->prepare("SELECT * FROM questions WHERE exam_id=? ORDER BY id ASC");
$questions_stmt->execute([$exam_id]);
$questions = $questions_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['answers'] as $question_id => $selected_option) {
        $q_id = (int)$question_id;
        $selected = in_array($selected_option, ['A','B','C','D']) ? $selected_option : null;

        // Insert or update student_answers
        $stmt = $pdo->prepare("
            INSERT INTO student_answers (attempt_id, question_id, selected_option)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE selected_option=VALUES(selected_option)
        ");
        $stmt->execute([$attempt_id, $q_id, $selected]);
    }

    // Mark exam completed
    $update_attempt = $pdo->prepare("UPDATE exam_attempts SET status='completed', end_time=NOW() WHERE id=?");
    $update_attempt->execute([$attempt_id]);

    header("Location: exam_result.php?attempt_id=$attempt_id");
    exit;
}

// Calculate time left
$start_time = new DateTime($attempt['start_time'] ?? date('Y-m-d H:i:s'));
$duration = new DateInterval("PT{$exam['duration_minutes']}M");
$end_time = clone $start_time;
$end_time->add($duration);
$remaining_seconds = max(0, $end_time->getTimestamp() - time());
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($exam['exam_title']) ?> - Exam</title>
<link rel="stylesheet" href="../assets/css/admin.css">
<script>
let remainingSeconds = <?= $remaining_seconds ?>;

function startTimer() {
    const timerElem = document.getElementById('timer');
    const interval = setInterval(() => {
        if (remainingSeconds <= 0) {
            clearInterval(interval);
            alert('Time is up! Exam will be submitted automatically.');
            document.getElementById('examForm').submit();
        } else {
            let minutes = Math.floor(remainingSeconds / 60);
            let seconds = remainingSeconds % 60;
            timerElem.textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
            remainingSeconds--;
        }
    }, 1000);
}

window.onload = startTimer;
</script>
</head>
<body>
<div class="container">
<h2><?= htmlspecialchars($exam['exam_title']) ?> - Exam</h2>
<p>Time Remaining: <span id="timer"></span></p>

<form id="examForm" method="post">
<?php foreach ($questions as $index => $q): ?>
<div class="question-block">
    <p><strong>Question <?= $index+1 ?>:</strong> <?= htmlspecialchars($q['question']) ?></p>
    <?php foreach (['A','B','C','D'] as $opt): ?>
        <?php $opt_text = $q["option_" . strtolower($opt)]; ?>
        <label>
            <input type="radio" name="answers[<?= $q['id'] ?>]" value="<?= $opt ?>"
            <?php
                // preselect if already answered
                $stmt = $pdo->prepare("SELECT selected_option FROM student_answers WHERE attempt_id=? AND question_id=?");
                $stmt->execute([$attempt_id, $q['id']]);
                $ans = $stmt->fetchColumn();
                echo ($ans === $opt) ? 'checked' : '';
            ?>
            > <?= $opt ?>: <?= htmlspecialchars($opt_text) ?>
        </label><br>
    <?php endforeach; ?>
</div>
<hr>
<?php endforeach; ?>

<button type="submit">Submit Exam</button>
</form>

</div>
</body>
</html>
