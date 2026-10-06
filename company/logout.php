<?php

/*
 * -------------------------------------------------------
 * Page    : Company Logout
 *
 * Purpose : Destroys the company session and redirects to the company login page.
 * -------------------------------------------------------
 */

// Initialize the session to track user authentication state.
session_start();

// Destroy all session data to completely log out the user.
session_destroy();

// Redirect the user to the appropriate page after the action completes.
header("Location: login.php");

?>