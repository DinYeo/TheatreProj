<?php
echo "PHP is working<br>";
$conn = mysqli_connect("127.0.0.1", "root", "", "apex_theatre");
if (!$conn) {
    echo "DB Error: " . mysqli_connect_error();
} else {
    echo "DB connected OK";
}
?>

