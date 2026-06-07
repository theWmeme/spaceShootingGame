<?php
$servername = "MySQL";
$username = "root";
$password = "JLYwVqacrARydohXcDOWAemsPWUdNvCX";
$dbname = "railway";


$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo "connection failed";
}

?>