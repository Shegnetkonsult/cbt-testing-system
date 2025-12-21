<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

requireAdmin();

/**
 * Handle file upload
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['questions_file'])) {

    $file = $_FILES['questions_file']['tmp_name'];
    $filename = pathinfo($_FILES['questions_file']['name'], PATHINFO_FILENAME); // Course code from file name

    if (!file_exists($file)) {
        $error = "File not found!";
    } else {

        // Get exam by course code, auto-create if not exists
        $stmt = $pdo->prepare("SELECT id FROM exams WHERE exam_code = ?");
        $stmt->execute([$filename]);
        $exam = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$exam) {
            // Auto-create new exam
            $stmt = $pdo->prepare("
                INSERT INTO exams (exam_title, exam_code, duration_minutes, total_questions, status)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                "Auto-created Exam for $filename",
                $filename,
                60,       // default duration in minutes
                0,        // total_questions will be updated later
                'inactive'
            ]);
            $exam_id = $pdo->lastInsertId();
            $created_new_exam = true;
        } else {
            $exam_id = $exam['id'];
            $created_new_exam = false;
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $inserted = 0;
        $skipped = 0;
        $errors = [];

        for ($i = 0; $i < count($lines); $i += 6) {
            if (!isset($lines[$i+5])) {
                $errors[] = "Incomplete question at line ".($i+1);
                continue;
            }

            $question    = trim($lines[$i]);
            $option_a    = trim($lines[$i+1]);
            $option_b    = trim($lines[$i+2]);
            $option_c    = trim($lines[$i+3]);
            $option_d    = trim($lines[$i+4]);
            $correct_txt = trim($lines[$i+5]);

            // Determine correct option letter
            $correct_option = '';
            if ($correct_txt === $option_a) $correct_option = 'A';
            elseif ($correct_txt === $option_b) $correct_option = 'B';
            elseif ($correct_txt === $option_c) $correct_option = 'C';
            elseif ($correct_txt === $option_d) $correct_option = 'D';
            else {
                $errors[] = "Correct answer mismatch at question: '$question'";
                continue;
            }

            // Check for duplicate in the same exam
            $stmt = $pdo->prepare("SELECT id FROM questions WHERE exam_id = ? AND question = ?");
            $stmt->execute([$exam_id, $question]);
            if ($stmt->fetch()) {
                $skipped++;
                continue; // skip duplicate
            }

            // Insert question
            $stmt = $pdo->prepare("
                INSERT INTO questions
                (exam_id, question, option_a, option_b, option_c, option_d, correct_option)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$exam_id, $question, $option_a, $option_b, $option_c, $option_d, $correct_option]);
            $inserted++;
        }

        // Update total_questions in exams table
        if ($inserted > 0) {
            $stmt = $pdo->prepare("UPDATE exams SET total_questions = total_questions + ? WHERE id = ?");
            $stmt->execute([$inserted, $exam_id]);
        }

        $success = "$inserted questions imported successfully.";
        if ($skipped > 0) {
            $success .= " $skipped duplicate question(s) skipped.";
        }
        if ($created_new_exam) {
            $success .= " New exam '$filename' created automatically.";
        }
        if (!empty($errors)) {
            $error = implode("<br>", $errors);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Import Questions</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="container">

<h2>Import Questions from Text File</h2>

<?php if (!empty($success)): ?>
    <p class="success"><?= $success ?></p>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <p class="error"><?= $error ?></p>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
    <label>Upload Text File (6 lines per question):</label>
    <input type="file" name="questions_file" accept=".txt" required><br><br>
    <button type="submit">Import Questions</button>
</form>

<p><strong>File format:</strong> Each question must be 6 lines:</p>
<ol>
    <li>Question text</li>
    <li>Option A</li>
    <li>Option B</li>
    <li>Option C</li>
    <li>Option D</li>
    <li>Correct answer text (must exactly match one of the options above)</li>
</ol>

</div>
</body>
</html>
