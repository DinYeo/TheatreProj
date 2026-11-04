<?php require_once 'db.php';
// Get Show ID from URL
$show_id = $_GET['id'];
$sql = "SELECT * FROM shows WHERE show_id = $show_id";
$result = mysqli_query($conn, $sql);
$show = mysqli_fetch_assoc($result);

$all_shows = mysqli_query($conn, "SELECT * FROM shows");
$perf_sql = "SELECT * FROM performances WHERE show_id = $show_id ORDER BY perf_date, perf_time";
$perf_result = mysqli_query($conn, $perf_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $show['title']; ?> | Apex Theatre</title>
    <link rel="stylesheet" href="style.css?v=1">
</head>
<body>
    <!-- Navigation Menu -->
    <nav id="drawerMenu" class="drawer-menu">
        <button id="closeDrawer" class="close-btn">&times;</button>
        <ul>
            <li><a href="home.php">Home</a></li>
            <li><a href="shows.php">What's On</a></li>
            <li><a href="my_tickets.php">My Account</a></li>
        </ul>
    </nav>

    <!-- Stick Header-->
    <header class="top-header">
        <div class="header-left">
            <button id="openDrawer" class="menu-btn">☰ Menu</button>
            <span class="brand-title">APEX THEATRE</span> 
        </div>
        <div class="header-right">
            <form action="show_detail.php" method="GET" class="header-select-form">
                <select name="id" required>
                    <option value="">Select show</option>
                    <?php while ($s = mysqli_fetch_assoc($all_shows)) { ?>
                        <option value="<?php echo $s['show_id']; ?>">
                            <?php echo $s['title']; ?>
                        </option>
                    <?php } ?>
                </select>
                <button type="submit" class="btn btn-primary">Buy now</button>
            </form>
        </div>
    </header>

    <!-- Centered Show Artwork -->
    <section class="detail-hero">
        <div class="container">
            <div class="detail-card">
                <img src="<?php echo $show['banner_image']; ?>" alt="<?php echo $show['title']; ?>">
                <h1><?php echo $show['title']; ?></h1>
            </div>
        </div>
    </section>

    <!-- Reddish Sub-nav Bar -->
    <div class="container">
        <div class="sub-nav-bar">
            <a href="home.php">&lt; BACK TO OVERVIEW</a>
        </div>
    </div>
    <!-- Side-by-side columns -->
    <div class="container detail-content">
        <div class="synopsis-col">
            <h2>SYNOPSIS</h2>
            <p><?php echo $show['description']; ?></p>
        </div>
        <div class="tickets-col">
            <h2>TICKETS</h2>
            <p><strong>Genre:</strong> <?php echo $show['genre']; ?></p>
            <p><strong>Price:</strong> $<?php echo $show['price']; ?></p>
            <div class="perf-schedule">
                <h3>Available Sessions</h3>
                <?php while ($perf = mysqli_fetch_assoc($perf_result)) { ?>
                    <p><?php echo date('D, d M Y', strtotime($perf['perf_date'])) . ', ' . date('g:i A', strtotime($perf['perf_time'])); ?></p>
                <?php }     ?>
            </div>
            <hr>
            <a href="booking.php?show_id=<?php echo $show['show_id']; ?>" class="btn btn-primary buy-btn-full">
                Buy tickets 
            </a>
        </div>
    </div>
</body>
</html>