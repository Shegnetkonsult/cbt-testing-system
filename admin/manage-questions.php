<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

requireAdmin();

/**
 * FETCH ALL EXAMS FOR DROPDOWN
 */
$exams_stmt = $pdo->query("SELECT id, exam_title, exam_code FROM exams ORDER BY created_at DESC");
$exams = $exams_stmt->fetchAll(PDO::FETCH_ASSOC);

/**
 * HANDLE QUESTION CREATION / UPDATE
 */
$editing = false;
if (isset($_GET['edit'], $_GET['id'])) {
    $editing = true;
    $edit_id = (int) $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE id = ?");
    $stmt->execute([$edit_id]);
    $question_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $exam_id        = (int) $_POST['exam_id'];
    $question_text  = trim($_POST['question']);
    $option_a       = trim($_POST['option_a']);
    $option_b       = trim($_POST['option_b']);
    $option_c       = trim($_POST['option_c']);
    $option_d       = trim($_POST['option_d']);
    $correct_option = $_POST['correct_option'];

    if (empty($question_text) || empty($option_a) || empty($option_b) || empty($option_c) || empty($option_d) || !in_array($correct_option, ['A','B','C','D'])) {
        $error = "Please fill all fields and select a valid correct option.";
    } else {
        if (isset($_POST['update_question'])) {
            $id = (int) $_POST['id'];
            $stmt = $pdo->prepare("
                UPDATE questions
                SET exam_id=?, question=?, option_a=?, option_b=?, option_c=?, option_d=?, correct_option=?
                WHERE id=?
            ");
            $stmt->execute([$exam_id, $question_text, $option_a, $option_b, $option_c, $option_d, $correct_option, $id]);
            $success = "Question updated successfully.";
        } elseif (isset($_POST['create_question'])) {
            $stmt = $pdo->prepare("
                INSERT INTO questions
                (exam_id, question, option_a, option_b, option_c, option_d, correct_option)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$exam_id, $question_text, $option_a, $option_b, $option_c, $option_d, $correct_option]);
            $success = "Question added successfully.";
        }
    }
}

/**
 * HANDLE QUESTION DELETION
 */
if (isset($_GET['delete'], $_GET['id'])) {
    $id = (int) $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: manage-questions.php");
    exit;
}

/**
 * FETCH ALL QUESTIONS
 */
$questions_stmt = $pdo->query("
    SELECT q.*, e.exam_title, e.exam_code
    FROM questions q
    JOIN exams e ON q.exam_id = e.id
    ORDER BY q.id DESC
");
$questions = $questions_stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Questions</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="container">

<h2>Manage Questions</h2>
<p> You can also <a href='import-questions.php'>import questions</a></p>

<?php if (!empty($success)): ?>
    <p class="success"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<!-- ================= ADD / EDIT QUESTION FORM ================= -->
<h3><?= $editing ? "Edit Question" : "Add New Question" ?></h3>
<form method="post">
    <?php if ($editing): ?>
        <input type="hidden" name="id" value="<?= $question_to_edit['id'] ?>">
    <?php endif; ?>

    <label>Select Exam:</label>
    <select name="exam_id" required>
        <option value="">-- Select Exam --</option>
        <?php foreach ($exams as $exam): ?>
            <option value="<?= $exam['id'] ?>"
                <?= $editing && $exam['id'] == $question_to_edit['exam_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($exam['exam_title']) ?> (<?= htmlspecialchars($exam['exam_code']) ?>)
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Question Text:</label>
    <textarea name="question" required><?= $editing ? htmlspecialchars($question_to_edit['question']) : '' ?></textarea><br><br>

    <label>Option A:</label>
    <input type="text" name="option_a" value="<?= $editing ? htmlspecialchars($question_to_edit['option_a']) : '' ?>" required><br><br>

    <label>Option B:</label>
    <input type="text" name="option_b" value="<?= $editing ? htmlspecialchars($question_to_edit['option_b']) : '' ?>" required><br><br>

    <label>Option C:</label>
    <input type="text" name="option_c" value="<?= $editing ? htmlspecialchars($question_to_edit['option_c']) : '' ?>" required><br><br>

    <label>Option D:</label>
    <input type="text" name="option_d" value="<?= $editing ? htmlspecialchars($question_to_edit['option_d']) : '' ?>" required><br><br>

    <label>Correct Option:</label>
    <select name="correct_option" required>
        <option value="">-- Select Correct Option --</option>
        <?php foreach (['A','B','C','D'] as $opt): ?>
            <option value="<?= $opt ?>" <?= $editing && $opt == $question_to_edit['correct_option'] ? 'selected' : '' ?>><?= $opt ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <button type="submit" name="<?= $editing ? 'update_question' : 'create_question' ?>">
        <?= $editing ? 'Update Question' : 'Add Question' ?>
    </button>
    <?php if ($editing): ?>
        <a href="manage-questions.php" class="action">Cancel</a>
    <?php endif; ?>
</form>

<hr>

<!-- ================= QUESTIONS LIST ================= -->
<h3>Existing Questions</h3>
<table>
    <tr>
        <th>#</th>
        <th>Exam</th>
        <th>Question</th>
        <th>Options</th>
        <th>Correct</th>
        <th>Action</th>
    </tr>
    <?php foreach ($questions as $index => $q): ?>
        <tr>
            <td><?= $index + 1 ?></td>
            <td><?= htmlspecialchars($q['exam_title']) ?> (<?= htmlspecialchars($q['exam_code']) ?>)</td>
            <td><?= htmlspecialchars($q['question']) ?></td>
            <td>
                A: <?= htmlspecialchars($q['option_a']) ?><br>
                B: <?= htmlspecialchars($q['option_b']) ?><br>
                C: <?= htmlspecialchars($q['option_c']) ?><br>
                D: <?= htmlspecialchars($q['option_d']) ?>
            </td>
            <td><?= $q['correct_option'] ?></td>
            <td>
                <a class="action" href="manage-questions.php?edit=1&id=<?= $q['id'] ?>">Edit</a> |
                <a class="action danger" href="manage-questions.php?delete=1&id=<?= $q['id'] ?>" onclick="return confirm('Delete this question?')">Delete</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

</div>
</body>
</html>
