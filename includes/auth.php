<?php
/**
 * =========================================================
 * AUTHENTICATION MODULE
 * Project: CBT Testing System
 * Author: Adigun Joseph
 * =========================================================
 */

require_once __DIR__ . '/config.php';

/* ---------------------------------------------------------
 | LOGIN USER
 |----------------------------------------------------------
 */
function loginUser($email, $password)
{
    global $pdo;

    $sql = "SELECT id, full_name, email, password, role, status
            FROM users
            WHERE email = :email
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user) {
        return ['success' => false, 'message' => 'Invalid login credentials'];
    }

    if ($user['status'] !== 'active') {
        return ['success' => false, 'message' => 'Account is inactive'];
    }

    if (!password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Invalid login credentials'];
    }

    // Prevent session fixation
    session_regenerate_id(true);

    $_SESSION['user_id']   = $user['id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email']     = $user['email'];
    $_SESSION['role']      = $user['role'];
    $_SESSION['logged_in'] = true;

    return ['success' => true];
}

/* ---------------------------------------------------------
 | REGISTER USER (STUDENT)
 |----------------------------------------------------------
 */
function registerUser($fullName, $email, $password)
{
    global $pdo;

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (full_name, email, password, role)
            VALUES (:full_name, :email, :password, 'student')";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'full_name' => $fullName,
            'email'     => $email,
            'password'  => $hashedPassword
        ]);
        return ['success' => true];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Email already exists'];
    }
}

/* ---------------------------------------------------------
 | LOGOUT USER
 |----------------------------------------------------------
 */
function logoutUser()
{
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}

/* ---------------------------------------------------------
 | ACCESS CONTROL HELPERS
 |----------------------------------------------------------
 */
function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin()
{
    if (!isLoggedIn() || !isAdmin()) {
        header('HTTP/1.1 403 Forbidden');
        die('Access denied');
    }
}

/* ---------------------------------------------------------
 | REQUIRE STUDENT
 |----------------------------------------------------------
 */
function requireStudent()
{
    if (!isLoggedIn() || !isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
        header('HTTP/1.1 403 Forbidden');
        die('Access denied: Students only');
    }
}
