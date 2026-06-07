<?php
function envValue(array $names, $default = null) {
    foreach ($names as $name) {
        $value = getenv($name);
        if ($value !== false && $value !== '') {
            return $value;
        }
    }
    return $default;
}

$databaseUrl = envValue(['DATABASE_URL', 'MYSQL_URL', 'MYSQLDATABASE_URL']);
if ($databaseUrl) {
    $parts = parse_url($databaseUrl);
    if ($parts !== false) {
        $servername = $parts['host'] ?? 'localhost';
        $port = $parts['port'] ?? 3306;
        $username = $parts['user'] ?? 'root';
        $password = $parts['pass'] ?? '';
        $dbname = ltrim($parts['path'] ?? 'login', '/');
    }
}

$servername = envValue(['MYSQLHOST', 'MYSQL_HOST'], $servername ?? 'localhost');
$username = envValue(['MYSQLUSER', 'MYSQL_USER'], $username ?? 'root');
$password = envValue(['MYSQLPASSWORD', 'MYSQL_PASSWORD'], $password ?? '');
$dbname = envValue(['MYSQLDATABASE', 'MYSQL_DATABASE'], $dbname ?? 'login');
$port = envValue(['MYSQLPORT', 'MYSQL_PORT'], $port ?? 3306);

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>