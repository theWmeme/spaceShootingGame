<?php
$servername = "db.fr-pari1.bengt.wasmernet.com:10272";
$username = "user_795eccd1";
$password = "pw_190e1852";
$dbname = "spaceShooting";


$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo "connection failed";
}

?>