<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

requireAdmin();

/**
 * HANDLE EXAM CREATION
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_exam'])) {

    $exam_title        = trim($_POST['exam_title']);
    $exam_code         = strtoupper(trim($_POST['exam_code']));
    $description       = trim($_POST['description']);
    $duration_minutes  = (int) $_POST['duration_minutes'];
    $total_questions   = (int) $_POST['total_questions'];
    $pass_mark         = (int) $_POST['pass_mark'];
    $start_time        = !empty($_POST['start_time']) ? $_POST['start_time'] : null;
    $end_time          = !empty($_POST['end_time']) ? $_POST['end_time'] : null;

    if (
        empty($exam_title) ||
        empty($exam_code) ||
        $duration_minutes <= 0 ||
        $total_questions <= 0
    ) {
        $error = "Please fill all required fields correctly.";
    } else {

        $stmt = $pdo->prepare("
            INSERT INTO exams
            (exam_title, exam_code, description, duration_minutes, total_questions, pass_mark, start_time, end_time)
            VALUES
            (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        try {
            $stmt->execute([
                $exam_title,
                $exam_code,
                $description,
                $duration_minutes,
                $total_questions,
                $pass_mark,
                $start_time,
                $end_time
            ]);

            $success = "Exam created successfully.";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "Exam code already exists.";
            } else {
                $error = "Error creating exam.";
            }
        }
    }
}

/**
 * TOGGLE EXAM STATUS
 */
if (isset($_GET['toggle'], $_GET['id'])) {
    $id = (int) $_GET['id'];

    $stmt = $pdo->prepare("
        UPDATE exams
        SET status = IF(status = 'active', 'inactive', 'active')
        WHERE id = ?
    ");
    $stmt->execute([$id]);

    header("Location: manage-exams.php");
    exit;
}

/**
 * FETCH EXAMS
 */
$exams = $pdo->query("
    SELECT *
    FROM exams
    ORDER BY created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Exams</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { padding: 8px; border: 1px solid #ccc; }
        th { background: #f4f4f4; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
<div class="container">

<h2>Manage Exams</h2>

<?php if (!empty($success)): ?>
    <p class="success"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<!-- ================= CREATE EXAM ================= -->
<h3>Create New Exam</h3>

<form method="post">
    <label>Exam Title:</label><br>
    <input type="text" name="exam_title" required><br><br>

    <label>Exam Code:</label><br>
    <input type="text" name="exam_code" required><br><br>

    <label>Description:</label><br>
    <textarea name="description"></textarea><br><br>

    <label>Duration (minutes):</label><br>
    <input type="number" name="duration_minutes" required><br><br>

    <label>Total Questions:</label><br>
    <input type="number" name="total_questions" required><br><br>

    <label>Pass Mark (%):</label><br>
    <input type="number" name="pass_mark" value="50"><br><br>

    <label>Start Time:</label><br>
    <input type="datetime-local" name="start_time"><br><br>

    <label>End Time:</label><br>
    <input type="datetime-local" name="end_time"><br><br>

    <button type="submit" name="create_exam">Create Exam</button>
</form>

<hr>

<!-- ================= EXAMS LIST ================= -->
<h3>Existing Exams</h3>

<table>
    <tr>
        <th>#</th>
        <th>Exam Title</th>
        <th>Code</th>
        <th>Duration</th>
        <th>Total Q</th>
        <th>Status</th>
        <th>Action</th>
    </tr>

    <?php foreach ($exams as $index => $exam): ?>
        <tr>
            <td><?= $index + 1 ?></td>
            <td><?= htmlspecialchars($exam['exam_title']) ?></td>
            <td><?= htmlspecialchars($exam['exam_code']) ?></td>
            <td><?= $exam['duration_minutes'] ?> mins</td>
            <td><?= $exam['total_questions'] ?></td>
            <td><?= ucfirst($exam['status']) ?></td>
            <td>
                <a class="action" href="manage-exams.php?toggle=1&id=<?= $exam['id'] ?>">
                    <?= $exam['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
</div>
</body>
</html>
