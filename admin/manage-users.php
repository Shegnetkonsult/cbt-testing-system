<?php
/**
 * =========================================================
 * MANAGE USERS (ADMIN)
 * Project: CBT Testing System
 * Author: Adigun Joseph
 * =========================================================
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';

// Ensure only admin can access
requireAdmin();

/**
 * HANDLE FORM ACTIONS
 */
$message = '';
$error   = '';

// ADD USER
if (isset($_POST['add_user'])) {

    $name     = sanitize($_POST['name']);
    $email    = sanitize($_POST['email']);
    $role     = sanitize($_POST['role']);
    $password = $_POST['password'];

    if (empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } else {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO users (full_name, email, password, role, status)
                VALUES (:full_name, :email, :password, :role, 'active')
            ");
            $stmt->execute([
                ':full_name'     => $name,
                ':email'    => $email,
                ':password' => $hash,
                ':role'     => $role
            ]);

            $message = "User added successfully.";
        } catch (PDOException $e) {
            $error = "Email already exists or database error.";
        }
    }
}

// UPDATE USER ROLE / STATUS
if (isset($_POST['update_user'])) {

    $user_id = (int)$_POST['user_id'];
    $role    = sanitize($_POST['role']);
    $status  = sanitize($_POST['status']);

    $stmt = $pdo->prepare("
        UPDATE users SET role = :role, status = :status WHERE id = :id
    ");
    $stmt->execute([
        ':role' => $role,
        ':status' => $status,
        ':id' => $user_id
    ]);

    $message = "User updated successfully.";
}

/**
 * FETCH USERS
 */
$stmt = $pdo->query("SELECT id, full_name, email, role, status, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users | CBT Admin</title>
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
        }
        header a {
            color: #fff;
            text-decoration: none;
        }
        .container {
            padding: 30px;
        }
        h2 {
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            margin-bottom: 30px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background: #f0f0f0;
        }
        form.inline {
            display: inline;
        }
        select, input {
            padding: 6px;
        }
        .success {
            background: #d4edda;
            padding: 10px;
            margin-bottom: 15px;
        }
        .error {
            background: #f8d7da;
            padding: 10px;
            margin-bottom: 15px;
        }
        .add-user {
            background: #fff;
            padding: 20px;
            border-radius: 6px;
        }
        button {
            padding: 6px 12px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<header>
    <div><strong>CBT Admin Panel</strong></div>
    <div>
        <a href="dashboard.php">Dashboard</a> |
        <a href="../public/logout.php">Logout</a>
    </div>
</header>

<div class="container">

    <h2>Manage Users</h2>

    <?php if ($message): ?><div class="success"><?= $message ?></div><?php endif; ?>
    <?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>

    <!-- USERS TABLE -->
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Registered</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['full_name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= ucfirst($user['role']) ?></td>
                <td><?= ucfirst($user['status']) ?></td>
                <td><?= date('Y-m-d', strtotime($user['created_at'])) ?></td>
                <td>
                    <form method="post" class="inline">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <select name="role">
                            <option value="student" <?= $user['role']=='student'?'selected':'' ?>>Student</option>
                            <option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>Admin</option>
                        </select>
                        <select name="status">
                            <option value="active" <?= $user['status']=='active'?'selected':'' ?>>Active</option>
                            <option value="inactive" <?= $user['status']=='inactive'?'selected':'' ?>>Inactive</option>
                        </select>
                        <button type="submit" name="update_user">Update</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- ADD USER -->
    <div class="add-user">
        <h3>Add New User</h3>
        <form method="post">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <select name="role">
                <option value="student">Student</option>
                <option value="admin">Admin</option>
            </select>
            <br><br>
            <button type="submit" name="add_user">Add User</button>
        </form>
    </div>

</div>

</body>
</html>
