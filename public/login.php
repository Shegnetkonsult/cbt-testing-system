<?php
/**
 * =========================================================
 * LOGIN PAGE
 * Project: CBT Testing System
 * Author: Adigun Joseph
 * =========================================================
 */

require_once '../includes/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'All fields are required';
    } else {
        $login = loginUser($email, $password);

        if ($login['success']) {

            // Redirect based on role
            if ($_SESSION['role'] === 'admin') {
                header('Location: ../admin/dashboard.php');
            } else {
                header('Location: ../student/dashboard.php');
            }
            exit;

        } else {
            $error = $login['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | CBT System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Minimal professional styling -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-box {
            background: #fff;
            padding: 30px;
            width: 100%;
            max-width: 400px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h2>CBT System Login</h2>

    <?php if (!empty($error)): ?>
        <div class="error"><?= $error; ?></div>
    <?php endif; ?>

    <form method="post" autocomplete="off">
        <input type="email" name="email" placeholder="Email address" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
</div>

</body>
</html>
