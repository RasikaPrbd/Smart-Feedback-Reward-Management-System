<?php

/*
 * -------------------------------------------------------
 * Page    : Database Connection
 *
 * Purpose : Establishes a secure MySQL connection using MySQLi. All modules include this file to interact with the database.
 * -------------------------------------------------------
 */

$conn = new mysqli("localhost", "root", "", "feedback_system");

// Stop execution immediately if the database connection fails.
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>