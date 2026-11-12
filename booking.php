<?php require_once 'db.php';
// Get Show ID from URL - (int) turns anything that is not a number into 0
$show_id = 0;
if (isset($_GET['show_id'])) {
    $show_id = (int) $_GET['show_id'];
}
$result = mysqli_query($conn, "SELECT * FROM shows WHERE show_id = $show_id");

// no show with that id -> go back to the home page
if (mysqli_num_rows($result) == 0) {
    header('Location: home.php');
    exit;
}
$show = mysqli_fetch_assoc($result);
$perf_result = mysqli_query($conn, "SELECT * FROM performances WHERE show_id = $show_id ORDER BY perf_date, perf_time");
$all_shows = mysqli_query($conn, "SELECT * FROM shows");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Tickets | <?php echo $show['title']; ?></title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
    <nav id="drawerMenu" class="drawer-menu">
        <button id="closeDrawer" class="close-btn">&times;</button>
        <ul>
            <li><a href="home.php">Home</a></li>
            <li><a href="shows.php">What's On</a></li>
            <li><a href="my_tickets.php">My Account</a></li>
        </ul>
    </nav>
    <header class="top-header">
        <div class="header-left">
            <button id="openDrawer" class="menu-btn">☰ Menu</button>
            <a href="home.php" class="brand-link"><span class="brand-title">APEX THEATRE</span></a>
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
    <section class="detail-hero">
        <div class="container">
            <div class="detail-card">
                <img src="<?php echo $show['banner_image']; ?>" alt="<?php echo $show['title']; ?>">
                <h1><?php echo $show['title']; ?> — Select a Session</h1>
            </div>
        </div>
    </section>
    <div class="container booking-sessions">
        <h2>Available Sessions</h2>
        <?php while ($perf = mysqli_fetch_assoc($perf_result)) { ?>
            <div class="session-row">
                <!-- left: date block -->
                <div class="session-date">
                    <span class="session-month"><?php echo date('M', strtotime($perf['perf_date'])); ?></span>
                    <span class="session-day"><?php echo date('d', strtotime($perf['perf_date'])); ?></span>
                </div>
                <!-- middle: day name, time, venue -->
                <div class="session-info">
                    <p class="session-datetime"><?php echo date('D', strtotime($perf['perf_date'])) . ' • ' . date('g:i A', strtotime($perf['perf_time'])); ?></p>
                    <p class="session-venue">Apex Theatre, Singapore</p>
                </div>
                <!-- right: select button -->
                <!-- passes perf_id to the form page via url -->
                <a href="booking_form.php?perf_id=<?php echo $perf['perf_id']; ?>&show_id=<?php echo $show_id; ?>" class="btn btn-primary">
                    Select &rsaquo;
                </a>
            </div>
            <hr>
        <?php } ?>
    </div>

    <?php include "footer.php"; ?>

    <script src="form_validation.js?v=<?php echo time(); ?>"></script>
</body>
</html>
        <!-- to continue -> after clicking select, the user will be directed to the seat selection page -->
