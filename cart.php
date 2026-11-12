<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

/* add a booking */
if (isset($_POST['perf_id'])) {
    $perf_id = (int) $_POST['perf_id'];

    // copy the ticked seat ids into a clean array of numbers and count them
    $seat_ids = array();
    $count = 0;
    if (isset($_POST['seats'])) {
        foreach ($_POST['seats'] as $id) {
            $seat_ids[] = (int) $id;
            $count++;
        }
    }

    // validation check 1: between 1 and 8 seats
    if ($count < 1 || $count > 8) {
        header("Location: booking_form.php?perf_id=$perf_id&error=count");
        exit;
    }

    // validation check 2: every seat belongs to this performance and is still free
    foreach ($seat_ids as $seat_id) {
        $query = "SELECT seat_id FROM seats
                  WHERE seat_id = $seat_id AND perf_id = $perf_id AND status = 'available'";
        $check = mysqli_query($conn, $query);
        if (mysqli_num_rows($check) == 0) {
            header("Location: booking_form.php?perf_id=$perf_id&error=taken");
            exit;
        }
    }

    $found = false;
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['perf_id'] == $perf_id) {
            $_SESSION['cart'][$key]['seats'] = $seat_ids;
            $found = true;
        }
    }
    if (!$found) {
        $_SESSION['cart'][] = array('perf_id' => $perf_id, 'seats' => $seat_ids);
    }

    // reload the page so pressing refresh does not send the form again
    header('Location: cart.php');
    exit;
}

/* removing a booking */
if (isset($_GET['remove'])) {
    $remove = (int) $_GET['remove'];
    // rebuild the list without that booking, so the numbers stay 0, 1, 2 ...
    $new_cart = array();
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($key != $remove) {
            $new_cart[] = $item;
        }
    }
    $_SESSION['cart'] = $new_cart;
    header('Location: cart.php');
    exit;
}

/* empty the list */
if (isset($_GET['empty'])) {
    unset($_SESSION['cart']);
    header('Location: cart.php');
    exit;
}

$all_shows = mysqli_query($conn, "SELECT * FROM shows");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Bookings | Apex Theatre</title>
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
        <h1 class="seat-title">My Bookings</h1>
        <p class="cart-intro">Your bookings are listed in your order of preference.
           Tick the ones you want to confirm now.</p>

        <?php if ($_SESSION['cart'] == array()) { ?>

            <p class="cart-empty">You have no bookings yet.
               <a href="home.php" class="text-link">Browse shows</a></p>

        <?php } else { ?>

        <form action="confirmation.php" method="post" id="checkoutForm">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Confirm</th>
                        <th>Preference</th>
                        <th>Show</th>
                        <th>Date &amp; time</th>
                        <th>Seats</th>
                        <th>Tickets</th>
                        <th>Subtotal ($)</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($_SESSION['cart'] as $key => $item) {
                    $perf_id = $item['perf_id'];

                    // seat names, number of tickets, and whether every seat is still free
                    $seat_list = "";
                    $tickets = 0;
                    $available = true;
                    foreach ($item['seats'] as $seat_id) {
                        $seat_result = mysqli_query($conn, "SELECT seat_number, status FROM seats WHERE seat_id = $seat_id");
                        $seat = mysqli_fetch_assoc($seat_result);
                        if ($seat_list != "") {
                            $seat_list = $seat_list . ", ";
                        }
                        $seat_list = $seat_list . $seat['seat_number'];
                        if ($seat['status'] == 'booked') {
                            $available = false;    // someone else booked it in the meantime
                        }
                        $tickets++;
                    }

                    // show + session details; MySQL works out the subtotal (keeps 2 decimal places)
                    $query = "SELECT performances.perf_date, performances.perf_time,
                                     shows.title, shows.price * $tickets AS subtotal
                              FROM performances, shows
                              WHERE performances.show_id = shows.show_id
                              AND performances.perf_id = $perf_id";
                    $info = mysqli_fetch_assoc(mysqli_query($conn, $query));
                ?>
                    <tr>
                        <td>
                            <?php if ($available) { ?>
                                <input type="checkbox" name="selected[]" class="booking-box"
                                       id="pick_<?php echo $perf_id; ?>" value="<?php echo $perf_id; ?>" checked>
                                <input type="hidden" id="subtotal_<?php echo $perf_id; ?>"
                                       value="<?php echo $info['subtotal']; ?>">
                            <?php } else { ?>
                                <span class="unavailable">Unavailable</span>
                            <?php } ?>
                        </td>
                        <td><?php echo $key + 1; ?></td>
                        <td><?php echo $info['title']; ?></td>
                        <td><?php echo date('D, d M Y', strtotime($info['perf_date'])) . '<br>' . date('g:i A', strtotime($info['perf_time'])); ?></td>
                        <td><?php echo $seat_list; ?></td>
                        <td><?php echo $tickets; ?></td>
                        <td><?php echo $info['subtotal']; ?></td>
                        <td><a href="cart.php?remove=<?php echo $key; ?>" class="text-link">Remove</a></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>

            <p class="cart-links">
                <a href="home.php" class="text-link">+ Add another show or session</a>
                &nbsp;|&nbsp;
                <a href="cart.php?empty=1" class="text-link">Clear all</a>
            </p>

            <!-- left column: customer details -->
            <div class="seat-map-col">
                <fieldset class="checkout-box">
                    <legend>Your details</legend>

                    <label for="name">Full name</label>
                    <input type="text" id="name" name="name" class="form-input" value="<?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : ''; ?>" required>

                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-input" value="<?php echo isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : ''; ?>" required>

                    <label for="phone">Mobile number (8 digits)</label>
                    <input type="text" id="phone" name="phone" class="form-input" maxlength="8" required>

                    <label for="payment">Payment result (from the payment portal)</label>
                    <select id="payment" name="payment" class="form-input" required>
                        <option value="">-- Select --</option>
                        <option value="success">Payment successful</option>
                        <option value="failed">Payment failed</option>
                    </select>
                </fieldset>
            </div>

            <!-- right column: total of the ticked bookings -->
            <div class="seat-summary-col">
                <h2 class="seat-heading">Summary</h2>
                <table class="summary-table">
                    <tr>
                        <th><label for="bookingCount">Bookings ticked</label></th>
                        <td><input type="text" id="bookingCount" class="summary-box" value="0" readonly></td>
                    </tr>
                    <tr class="summary-total">
                        <th><label for="cartTotal">Total ($)</label></th>
                        <td><input type="text" id="cartTotal" class="summary-box" value="0.00" readonly></td>
                    </tr>
                </table>
                <input type="submit" value="Confirm and pay" class="btn btn-primary summary-btn">
            </div>

            <div class="clear"></div>
        </form>

        <?php } ?>
    </div>

    <?php include "footer.php"; ?>

    <script type="text/javascript" src="form_validation.js?v=<?php echo time(); ?>"></script>
</body>
</html>
