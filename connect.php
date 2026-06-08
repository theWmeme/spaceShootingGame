<?php
$servername = "dpg-d8esfa4p3tds738ugv9g-a";
$username = "space_shooting_game_database_user";
$password = "wp4FWdE17I0TWScFDYXyD8E8L5xSuf90";
$dbname = "space_shooting_game_database";


$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo "connection failed";
}

?>