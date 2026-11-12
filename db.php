<?php
/* For Database connection */
$host = "127.0.0.1";
$user = "root";
$pass = "";
$dbname = "apex_theatre";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Can't Connect to Database: ".mysqli_connect_error());
}
?>