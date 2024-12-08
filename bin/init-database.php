<?php

$dsn = 'mysql:host=127.0.0.1;dbname=UserApp';
$user = 'root';
$password = '';

try {
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = file_get_contents(__DIR__ . '/../schema.sql');
    $pdo->exec($sql);

    echo "Database tables created successfully.\n";
} catch (PDOException $e) {
    echo "Database creation failed: " . $e->getMessage() . "\n";
}
