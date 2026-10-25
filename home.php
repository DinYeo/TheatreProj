<?php
require_once 'db.php';

$sql = "SELECT * FROM shows";
$result = mysqli_query($conn, $sql);

$shows = [];
// checks that data is retrieved properly
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result) > 0) {
        $shows[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset = "UTF-8">
    <title>Apex Theatre | Singapore</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- navigation menu -->
    <nav id="drawerMenu" class="drawer-menu">
        <button id="closeDrawer" class="close-btn">&times;</button>
        <ul>
            <li><a href="home.php">Home</a></li>
            <li><a href="shows.php">What's On</a></li>
            <li><a href="my_tickets.php">My Account</a></li>
        </ul>
    </nav>

    <!-- top sticky header-->
     <header class = "top-heaader">
        <div class = "header-left">
            <button id="openDrawer" class="menu-btn">☰ Menu</button>
            <span class="brand-title">APEX THEATRE</span> 
        </div>
        <div class = "header-right">
        <!-- Will Continue -->
        </div>
     </header>
</body>
</html>