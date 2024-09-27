<?php
// $servername = "localhost";
// $username = "root";
// $password = "";
// $db = "jwala";


$servername = "localhost";
$username = "u981628790_b";
$password = "SSss66$$";
$db = "u981628790_a";
// Create connection
$connection = new mysqli($servername, $username, $password, $db);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

?>