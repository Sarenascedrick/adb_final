<?php

// Function to establish a database connection
function get_db_connection()
{
// Database configuration
    $host = 'localhost';
    $username = 'root';
    $password = 'Arvy@123';
    $database = 'netflix_database';

    $conn = new mysqli($host, $username, $password, $database);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}