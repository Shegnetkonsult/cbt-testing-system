<?php
require_once '../includes/auth.php';
requireStudent();
require_once '../includes/config.php';

$student_id = $_SESSION['user_id'];
$exam_id = (int)($_GET['exam_id'] ?? 0);

if (!$exam_id) die("Invalid exam.");

// Fetch active exam
$stmt = $pdo->prepare("SELECT * FROM exams WHERE id=? AND status='active'");
$stmt->execute([$exam_id]);
$exam = $stmt->fetch();
if (!$exam) die("Exam not found or inactive.");

// Check or create attempt
$attempt_stmt = $pdo->prepare("SELECT * FROM exam_attempts WHERE user_id=? AND exam_id=?");
$attempt_stmt->execute([$student_id, $exam_id]);
$attempt = $attempt_stmt->fetch();

if (!$attempt) {
    $insert_attempt = $pdo->prepare("INSERT INTO exam_attempts (user_id, exam_id, start_time) VALUES (?, ?, NOW())");
    $insert_attempt->execute([$student_id, $exam_id]);
    $attempt_id = $pdo->lastInsertId();
} else {
    $attempt_id = $attempt['id'];
}

// Fetch questions in RANDOM order for this student
$questions_stmt = $pdo->prepare("
    SELECT q.*
    FROM questions q
    LEFT JOIN student_answers sa
        ON sa.attempt_id=? AND sa.question_id=q.id
    WHERE q.exam_id=?
    ORDER BY RAND()  -- Random order per exam attempt
");
$questions_stmt->execute([$attempt_id, $exam_id]);
$questions = $questions_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate remaining time
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
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<style>
.question-block { display: none; }
.question-block.active { display: block; }
.nav-buttons { margin-top: 15px; }
</style>
<script>
let remainingSeconds = <?= $remaining_seconds ?>;
let currentIndex = 0;
const totalQuestions = <?= count($questions) ?>;

function startTimer() {
    const timerElem = document.getElementById('timer');
    setInterval(() => {
        if (remainingSeconds <= 0) {
            alert('Time is up! Exam will be submitted automatically.');
            document.getElementById('examForm').submit();
        } else {
            let min = Math.floor(remainingSeconds/60);
            let sec = remainingSeconds % 60;
            timerElem.textContent = min + ":" + (sec<10?"0":"") + sec;
            remainingSeconds--;
        }
    }, 1000);
}

function showQuestion(index) {
    $('.question-block').removeClass('active');
    $('#q'+index).addClass('active');
    $('#currentQ').text(index+1);
    $('#prevBtn').prop('disabled', index === 0);
    $('#nextBtn').prop('disabled', index === totalQuestions-1);
}

function saveAnswer(questionId) {
    const selected = $(`input[name='answers[${questionId}]']:checked`).val() || null;
    $.post('save_answer.php', { attempt_id: <?= $attempt_id ?>, question_id: questionId, selected_option: selected });
}

$(document).ready(function(){
    startTimer();
    showQuestion(currentIndex);

    $('#nextBtn').click(function(){
        saveAnswer($('.question-block.active').data('qid'));
        currentIndex++;
        showQuestion(currentIndex);
    });

    $('#prevBtn').click(function(){
        saveAnswer($('.question-block.active').data('qid'));
        currentIndex--;
        showQuestion(currentIndex);
    });

    $('#examForm input[type=radio]').change(function(){
        const qid = $(this).closest('.question-block').data('qid');
        saveAnswer(qid);
    });

    $('#submitBtn').click(function(){
        saveAnswer($('.question-block.active').data('qid'));
        $('#examForm').submit();
    });
});
</script>
</head>
<body>
<div class="container">
<h2><?= htmlspecialchars($exam['exam_title']) ?> - Exam</h2>
<p>Time Remaining: <span id="timer"></span></p>

<form id="examForm" method="post" action="submit_exam.php">
<?php foreach ($questions as $i => $q): ?>
<div class="question-block" id="q<?= $i ?>" data-qid="<?= $q['id'] ?>">
    <p><strong>Question <?= $i+1 ?>:</strong> <?= htmlspecialchars($q['question']) ?></p>
    <?php foreach (['A','B','C','D'] as $opt): ?>
        <?php $text = $q['option_'.strtolower($opt)]; ?>
        <label>
            <input type="radio" name="answers[<?= $q['id'] ?>]" value="<?= $opt ?>"
            <?php
                $stmt = $pdo->prepare("SELECT selected_option FROM student_answers WHERE attempt_id=? AND question_id=?");
                $stmt->execute([$attempt_id, $q['id']]);
                $ans = $stmt->fetchColumn();
                echo ($ans === $opt) ? 'checked' : '';
            ?>
            > <?= $opt ?>: <?= htmlspecialchars($text) ?>
        </label><br>
    <?php endforeach; ?>
</div>
<?php endforeach; ?>

<div class="nav-buttons">
    <button type="button" id="prevBtn">Previous</button>
    <button type="button" id="nextBtn">Next</button>
    <button type="button" id="submitBtn">Submit Exam</button>
    <span>Question <span id="currentQ">1</span> of <?= count($questions) ?></span>
</div>
</form>
</div>
</body>
</html>
