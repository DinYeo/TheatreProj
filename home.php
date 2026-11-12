<?php
require_once 'db.php';

$sql = "SELECT * FROM shows";
$result = mysqli_query($conn, $sql);

$shows = [];
// checks that data is retrieved properly
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $shows[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset = "UTF-8">
    <title>Apex Theatre | Singapore</title>
    <!-- need ?v=1 version tag to reflect changes when using 127.0.0.1 instead of localhost -->
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
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
    <header class = "top-header">
        <div class = "header-left">
            <button id="openDrawer" class="menu-btn">☰ Menu</button>
            <a href="home.php" class="brand-link"><span class="brand-title">APEX THEATRE</span></a>
        </div>
        <div class = "header-right">
            <!-- retrieve show details-->
            <form action="show_detail.php" method="GET" class="header-select-form">
                <select name="id" required>
                    <option value="">Select show</option>
                    <?php foreach ($shows as $s) { ?>
                        <option value="<?php echo $s['show_id']; ?>">
                            <?php echo $s['title']; ?>
                    </option>
                    <?php } ?>
                </select>
                <button type="submit" class="btn btn-primary">Buy now</button>
            </form>
        </div>
    </header>

    <section class = "hero-banner">
        <div class = "container">
            <h1>Welcome To Apex Theatre</h1>
            <p>Singapore's most iconic venue for film, theatre and musicals</p>
        </div>
    </section>
    <div class="container">
    <h2>What's On</h2>
    <div class="shows-grid">
        <?php foreach ($shows as $show) { ?>
            <a href="show_detail.php?id=<?php echo $show['show_id']; ?>" class="show-card">
                <img src="<?php echo $show['image']; ?>" alt="<?php echo $show['title']; ?>">
                <div class="card-info">
                    <span class="card-tag"><?php echo $show['genre']; ?></span>
                    <h3><?php echo $show['title']; ?></h3>
                    <p class="card-price">From $<?php echo $show['price']; ?></p>
                </div>
            </a>
        <?php } ?>
    </div>
</div>

    <?php include "footer.php"; ?>

    <script src="form_validation.js?v=<?php echo time(); ?>"></script>
</body>
</html>
