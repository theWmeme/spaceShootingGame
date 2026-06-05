<?php
$servername = "mysql.railway.internal";
$username = "root";
$password = "TkHfatdjMelXctSkthIVQbatoOCRXlGs";
$dbname = "railway";


$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo "connection failed";
}

?>