<?php
/**
 * =========================================================
 * CONFIGURATION FILE
 * Project: CBT Testing System
 * Author: Adigun Joseph
 * Stack: PHP + MySQL (PDO)
 * =========================================================
 */

/* ---------------------------------------------------------
 | ENVIRONMENT SETTINGS
 |----------------------------------------------------------
 | Change APP_ENV to 'production' on live server
 */
define('APP_ENV', 'development'); // development | production

/* ---------------------------------------------------------
 | DATABASE CONFIGURATION
 |----------------------------------------------------------
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'cbt_system');
define('DB_USER', 'root');
define('DB_PASS', ''); // Set password in production
define('DB_CHARSET', 'utf8mb4');

/* ---------------------------------------------------------
 | ERROR REPORTING
 |----------------------------------------------------------
 */
if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

/* ---------------------------------------------------------
 | TIMEZONE
 |----------------------------------------------------------
 */
date_default_timezone_set('Africa/Lagos');

/* ---------------------------------------------------------
 | SECURE SESSION SETTINGS
 |----------------------------------------------------------
 */
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_strict_mode', 1);

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => false, // true in HTTPS production
        'httponly' => true,
        'samesite' => 'Strict'
    ]);

    session_start();
}

/* ---------------------------------------------------------
 | DATABASE CONNECTION (PDO)
 |----------------------------------------------------------
 */
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

} catch (PDOException $e) {

    if (APP_ENV === 'development') {
        die("Database Connection Failed: " . $e->getMessage());
    } else {
        error_log($e->getMessage());
        die("System temporarily unavailable. Please try again later.");
    }
}

/* ---------------------------------------------------------
 | GLOBAL SECURITY HEADERS
 |----------------------------------------------------------
 */
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

/* ---------------------------------------------------------
 | HELPER FUNCTION: SANITIZE INPUT
 |----------------------------------------------------------
 */
function sanitize($data)
{
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/* ---------------------------------------------------------
 | HELPER FUNCTION: CHECK LOGIN
 |----------------------------------------------------------
 */
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

/* ---------------------------------------------------------
 | HELPER FUNCTION: CHECK ADMIN
 |----------------------------------------------------------
 */
function isAdmin()
{
    return (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
}
