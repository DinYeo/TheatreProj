<!-- For Database connection-->

<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "apex_theatre";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Can't Connect to Database: ".mysqli_connect_error());
}
?>