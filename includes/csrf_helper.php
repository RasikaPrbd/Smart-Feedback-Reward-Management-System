<?php
/*
 * -------------------------------------------------------
 * Page    : CSRF Helper
 *
 * Purpose : Provides functions to generate and validate CSRF tokens, protecting forms against Cross-Site Request Forgery attacks.
 * -------------------------------------------------------
 */

if (session_status() === PHP_SESSION_NONE) {
    // Initialize the session to track user authentication state.
    session_start();
}

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Generate a CSRF token for the current session. Creates a new one if it does not yet exist.
function csrf_token() {
    return generate_csrf_token();
}

function verify_csrf_token($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function csrf_abort() {
    http_response_code(403);
    die("CSRF token validation failed.");
}
?>
