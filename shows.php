<?php
include "db.php";

// fetch all shows for the header dropdown
$dropdown_shows = array();
$all_shows_res = mysqli_query($conn, "SELECT * FROM shows");
while ($r = mysqli_fetch_assoc($all_shows_res)) {
    $dropdown_shows[] = $r;
}

// fetch all shows with their first performance date
$featured_sql = "SELECT s.*, MIN(p.perf_date) AS first_date 
                 FROM shows s 
                 LEFT JOIN performances p ON s.show_id = p.show_id 
                 GROUP BY s.show_id 
                 ORDER BY s.show_id ASC 
                 LIMIT 3";
$featured_res = mysqli_query($conn, $featured_sql);
$featured_shows = array();
while ($f = mysqli_fetch_assoc($featured_res)) {
    $featured_shows[] = $f;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>What's On | Apex Theatre</title>
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

    <!-- top sticky header -->
    <header class="top-header">
        <div class="header-left">
            <button id="openDrawer" class="menu-btn">☰ Menu</button>
            <a href="home.php" class="brand-link"><span class="brand-title">APEX THEATRE</span></a>
        </div>
        <div class="header-right">
            <form action="show_detail.php" method="GET" class="header-select-form">
                <select name="id" required>
                    <option value="">Select show</option>
                    <?php foreach ($dropdown_shows as $s) { ?>
                        <option value="<?php echo $s['show_id']; ?>">
                            <?php echo $s['title']; ?>
                        </option>
                    <?php } ?>
                </select>
                <button type="submit" class="btn btn-primary">Buy now</button>
            </form>
        </div>
    </header>

    <!-- page title bar -->
    <section class="whats-on-titlebar">
        <h1>WHAT'S ON AT THE APEX</h1>
    </section>

    <!-- alternating cinematic showcase banners -->
    <main class="whats-on-container">
        <?php foreach ($featured_shows as $index => $show) { 
            // format start date or use fallback text
            $date_str = "SEASON NOW OPEN";
            if (!empty($show['first_date'])) {
                $date_str = "FROM " . strtoupper(date('j M Y', strtotime($show['first_date'])));
            }
            // alternate left vs right layout
            $is_reversed = ($index % 2 != 0);
            // replace single quotes in path so css url() doesn't break on names like macbeth a king's descent
            $bg_url = str_replace("'", "%27", $show['banner_image']);
        ?>
            <section class="spotlight-banner <?php echo $is_reversed ? 'banner-right' : 'banner-left'; ?>" style="background-image: url('<?php echo $bg_url; ?>');">
                <div class="banner-overlay">
                    <div class="banner-content">
                        <span class="spotlight-genre"><?php echo $show['genre']; ?></span>
                        <h2 class="spotlight-title"><?php echo $show['title']; ?></h2>
                        <p class="spotlight-text">
                            <?php echo $show['description']; ?>
                        </p>
                        <div class="banner-action">
                            <a href="show_detail.php?id=<?php echo $show['show_id']; ?>" class="btn btn-primary btn-banner-buy">Buy Tickets</a>
                        </div>
                        <div class="banner-meta">
                            <span class="banner-meta-date"><?php echo $date_str; ?></span>
                            <span class="banner-meta-dot">&bull;</span>
                            <span class="banner-meta-price">Tickets from $<?php echo $show['price']; ?></span>
                            <span class="banner-meta-dot">&bull;</span>
                            <a href="show_detail.php?id=<?php echo $show['show_id']; ?>" class="spotlight-link">View Performance Dates &rsaquo;</a>
                        </div>
                    </div>
                </div>
            </section>
        <?php } ?>
    </main>

    <?php include "footer.php"; ?>

    <script src="form_validation.js?v=<?php echo time(); ?>"></script>
</body>
</html>

