<?php
/**
 * Database Migration Script
 * 
 * This script connects to the database using PDO and executes migration queries 
 * to create necessary tables for a railway management system.
 */

// Database connection settings
$servername = "localhost";
$username = "root";
$password = "xhakla";
$dbname = "station";

try {
    // PDO connection setup
    $dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Enable exception mode for errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Set default fetch mode
    ]);

    // Migration queries to create database schema
    $migrationQueries = <<<SQL
        -- Create 'stations' table
        CREATE TABLE IF NOT EXISTS stations (
            station_id INT AUTO_INCREMENT PRIMARY KEY,
            station_name VARCHAR(100) NOT NULL,
            location VARCHAR(100) NOT NULL
        );

        -- Create 'trains' table
        CREATE TABLE IF NOT EXISTS trains (
            train_id INT AUTO_INCREMENT PRIMARY KEY,
            train_number VARCHAR(50) NOT NULL,
            departure_station_id INT NOT NULL,
            arrival_station_id INT NOT NULL,
            departure_time DATETIME NOT NULL,
            arrival_time DATETIME NOT NULL,
            FOREIGN KEY (departure_station_id) REFERENCES stations(station_id),
            FOREIGN KEY (arrival_station_id) REFERENCES stations(station_id)
        );

        -- Create 'customers' table
        CREATE TABLE IF NOT EXISTS customers (
            customer_id INT AUTO_INCREMENT PRIMARY KEY,
            customer_name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL
        );

        -- Create 'tickets' table
        CREATE TABLE IF NOT EXISTS tickets (
            ticket_id INT AUTO_INCREMENT PRIMARY KEY,
            train_id INT NOT NULL,
            customer_id INT NOT NULL,
            seat_number INT NOT NULL,
            price DECIMAL(10, 2) NOT NULL,
            FOREIGN KEY (train_id) REFERENCES trains(train_id),
            FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
        );
    SQL;

    // Execute migration queries
    $pdo->exec($migrationQueries);
    
    // Success message
    echo <<<HTML
    <div style="
        background-color: #e8f5e9;
        color: #2e7d32;
        padding: 15px;
        border: 1px solid #c8e6c9;
        border-radius: 8px;
        font-family: Arial, sans-serif;
        max-width: 600px;
        margin: 20px auto;
        text-align: center;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    ">
        <h2 style="margin: 0; font-size: 18px;">✅ Migration Completed</h2>
        <p style="margin: 5px 0 0;">The database schema was successfully created or updated.</p>
    </div>
    HTML;

} catch (PDOException $e) {
    // Error message
    echo <<<HTML
    <div style="
        background-color: #ffebee;
        color: #b71c1c;
        padding: 15px;
        border: 1px solid #ffcdd2;
        border-radius: 8px;
        font-family: Arial, sans-serif;
        max-width: 600px;
        margin: 20px auto;
        text-align: center;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    ">
        <h2 style="margin: 0; font-size: 18px;">❌ Migration Failed</h2>
        <p style="margin: 5px 0 0;">Error: {$e->getMessage()}</p>
    </div>
    HTML;
}
?>
