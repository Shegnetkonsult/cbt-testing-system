<?php
/**
 * =========================================================
 * ADMIN DASHBOARD
 * Project: CBT Testing System
 * Author: Adigun Joseph
 * =========================================================
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';

// Enforce admin-only access
requireAdmin();

// Fetch dashboard statistics
try {
    $stats = [
        'users'     => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        'students'  => $pdo->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn(),
        'admins'    => $pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn(),
        'exams'     => $pdo->query("SELECT COUNT(*) FROM exams")->fetchColumn(),
        'questions' => $pdo->query("SELECT COUNT(*) FROM questions")->fetchColumn()
    ];
} catch (PDOException $e) {
    die("Dashboard Error: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | CBT System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
        }
        header {
            background: #343a40;
            color: #fff;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        header a {
            color: #fff;
            text-decoration: none;
        }
        .container {
            padding: 30px;
        }
        h2 {
            margin-bottom: 20px;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        .card {
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .card h3 {
            margin: 0;
            font-size: 16px;
            color: #666;
        }
        .card p {
            font-size: 28px;
            margin-top: 10px;
            font-weight: bold;
        }
        nav {
            margin-top: 40px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 15px;
        }
        nav a {
            display: block;
            padding: 15px;
            background: #007bff;
            color: #fff;
            text-align: center;
            text-decoration: none;
            border-radius: 6px;
        }
        nav a:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>

<header>
    <div>
        <strong>CBT Admin Panel</strong>
    </div>
    <div>
        Logged in as <?= htmlspecialchars($_SESSION['email']); ?> |
        <a href="../public/logout.php">Logout</a>
    </div>
</header>

<div class="container">

    <h2>System Overview</h2>

    <div class="grid">
        <div class="card">
            <h3>Total Users</h3>
            <p><?= $stats['users']; ?></p>
        </div>
        <div class="card">
            <h3>Students</h3>
            <p><?= $stats['students']; ?></p>
        </div>
        <div class="card">
            <h3>Administrators</h3>
            <p><?= $stats['admins']; ?></p>
        </div>
        <div class="card">
            <h3>Examinations</h3>
            <p><?= $stats['exams']; ?></p>
        </div>
        <div class="card">
            <h3>Questions</h3>
            <p><?= $stats['questions']; ?></p>
        </div>
    </div>

    <h2>Administration</h2>

    <nav>
        <a href="users.php">Manage Users</a>
        <a href="exams.php">Manage Exams</a>
        <a href="questions.php">Manage Questions</a>
        <a href="results.php">View Results</a>
        <a href="settings.php">System Settings</a>
    </nav>

</div>

</body>
</html>
