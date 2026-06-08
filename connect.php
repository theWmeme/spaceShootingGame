<?php
$host     = getenv('DB_HOST') ?: 'localhost';
$user     = getenv('DB_USER') ?: 'postgres';
$password = getenv('DB_PASSWORD') ?: '';
$dbname   = getenv('DB_NAME') ?: 'myapp';
$port     = getenv('DB_PORT') ?: 5432;

$dsn = "pgsql:host={$host};port={$port};dbname={$dbname};user={$user};password={$password}";

try {
    $conn = new PDO($dsn);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}
?>