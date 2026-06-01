<?php
$servername = "db.fr-pari1.bengt.wasmernet.com:10272";
$username = "user_c7dd8dab";
$password = "pw_190e1852";
$dbname = "db_02961433";


$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo "connection failed";
}

?>