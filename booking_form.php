<?php require_once 'db.php';
// Get performance ID from URL - (int) turns anything that is not a number into 0
$perf_id = 0;
if (isset($_GET['perf_id'])) {
    $perf_id = (int) $_GET['perf_id'];
}

// get the performance together with its show
$query = "SELECT performances.perf_id, performances.perf_date, performances.perf_time,
                 shows.show_id, shows.title, shows.price
          FROM performances, shows
          WHERE performances.show_id = shows.show_id
          AND performances.perf_id = $perf_id";
$result = mysqli_query($conn, $query);

// no performance with that id -> go back to the home page
if (mysqli_num_rows($result) == 0) {
    header('Location: home.php');
    exit;
}
$perf = mysqli_fetch_assoc($result);

// put every seat of this performance into an associative array, key = seat number
// e.g. $seats['A7'] = the row for seat A7
$seats = array();
$seat_result = mysqli_query($conn, "SELECT * FROM seats WHERE perf_id = $perf_id");
while ($row = mysqli_fetch_assoc($seat_result)) {
    $seats[$row['seat_number']] = $row;
}

$all_shows = mysqli_query($conn, "SELECT * FROM shows");
$row_letters = array('A', 'B', 'C', 'D', 'E');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select Seats | <?php echo $perf['title']; ?></title>
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

    <div class="container seat-page">
        <div class="sub-nav-bar">
            <a href="booking.php?show_id=<?php echo $perf['show_id']; ?>">&lt; BACK TO SESSIONS</a>
        </div>

        <div class="session-row">
            <div class="session-date">
                <span class="session-month"><?php echo date('M', strtotime($perf['perf_date'])); ?></span>
                <span class="session-day"><?php echo date('d', strtotime($perf['perf_date'])); ?></span>
            </div>
            <div class="session-info">
                <h1 class="seat-title"><?php echo $perf['title']; ?></h1>
                <p class="session-datetime"><?php echo date('l', strtotime($perf['perf_date'])) . ' • ' . date('g:i A', strtotime($perf['perf_time'])); ?></p>
                <p class="session-venue">Apex Theatre, Singapore</p>
            </div>
        </div>

        <?php
        // message sent back from cart.php if the seats could not be added
        if (isset($_GET['error']) && $_GET['error'] == 'taken') {
            echo '<p class="error-msg">Sorry, one or more of those seats were just booked by someone else. Please choose again.</p>';
        }
        if (isset($_GET['error']) && $_GET['error'] == 'count') {
            echo '<p class="error-msg">Please select between 1 and 8 seats.</p>';
        }
        ?>

        <form action="cart.php" method="post" id="seatForm">
            <!-- hidden fields: which performance, and the price for the JavaScript total -->
            <input type="hidden" name="perf_id" value="<?php echo $perf_id; ?>">
            <input type="hidden" id="price" value="<?php echo $perf['price']; ?>">

            <!-- left column: the seat map -->
            <div class="seat-map-col">
                <h2 class="seat-heading">Choose your seats</h2>
                <table class="seat-table">
                    <caption>STAGE</caption>
                    <tbody>
                        <?php foreach ($row_letters as $letter) { ?>
                            <tr>
                                <th><?php echo $letter; ?></th>
                                <?php for ($num = 1; $num <= 10; $num++) {
                                    $seat = $seats[$letter . $num];

                                    // empty column in the middle = the aisle
                                    if ($num == 6) {
                                        echo '<td class="aisle"></td>';
                                    }

                                    if ($seat['status'] == 'booked') { ?>
                                        <td class="seat">
                                            <span class="seat-shape seat-booked" title="<?php echo $letter . $num; ?> is booked"></span>
                                        </td>
                                    <?php } else { ?>
                                        <td class="seat">
                                            <!-- the label is the seat you click; it ticks the hidden checkbox -->
                                            <label for="seat_<?php echo $letter . $num; ?>"
                                                   id="label_<?php echo $letter . $num; ?>"
                                                   class="seat-shape seat-free"
                                                   title="Seat <?php echo $letter . $num; ?>"><?php echo $num; ?></label>
                                            <input type="checkbox" name="seats[]" class="seat-box"
                                                   value="<?php echo $seat['seat_id']; ?>"
                                                   id="seat_<?php echo $letter . $num; ?>">
                                        </td>
                                    <?php }
                                } ?>
                                <th><?php echo $letter; ?></th>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

                <table class="seat-legend">
                    <tr>
                        <td><span class="seat-shape seat-free"></span></td>
                        <td>Available</td>
                        <td><span class="seat-shape seat-picked"></span></td>
                        <td>Your seats</td>
                        <td><span class="seat-shape seat-booked"></span></td>
                        <td>Booked</td>
                    </tr>
                </table>
            </div>

            <!-- right column: summary of the selection -->
            <div class="seat-summary-col">
                <h2 class="seat-heading">Your selection</h2>
                <table class="summary-table">
                    <tr>
                        <th>Price / seat</th>
                        <td>$<?php echo $perf['price']; ?></td>
                    </tr>
                    <tr>
                        <th><label for="seatList">Seats</label></th>
                        <td><input type="text" id="seatList" class="summary-box" value="None yet" readonly></td>
                    </tr>
                    <tr>
                        <th><label for="seatCount">No. of seats</label></th>
                        <td><input type="text" id="seatCount" class="summary-box" value="0" readonly></td>
                    </tr>
                    <tr class="summary-total">
                        <th><label for="seatTotal">Total ($)</label></th>
                        <td><input type="text" id="seatTotal" class="summary-box" value="0.00" readonly></td>
                    </tr>
                </table>
                <p class="summary-note">Maximum 8 seats per booking.</p>
                <input type="submit" value="Add to my bookings" class="btn btn-primary summary-btn">
            </div>

            <!-- stops the two columns from overlapping what comes after -->
            <div class="clear"></div>
        </form>
    </div>

    <?php include "footer.php"; ?>

    <script type="text/javascript" src="form_validation.js?v=<?php echo time(); ?>"></script>
</body>
</html>
