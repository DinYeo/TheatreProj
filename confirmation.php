<?php
session_start();
require_once 'db.php';
require_once 'mail_patch.php';

// this page can only be reached by submitting the form on cart.php
if (!isset($_POST['payment']) || !isset($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

$name    = trim($_POST['name']);
$email   = trim($_POST['email']);
$phone   = trim($_POST['phone']);
$payment = $_POST['payment'];

// the ticked bookings (each value is a perf_id)
$selected = array();
if (isset($_POST['selected'])) {
    foreach ($_POST['selected'] as $value) {
        $selected[] = (int) $value;
    }
}

$error = "";
if ($selected == array()) {
    $error = "You did not tick any booking to confirm.";
} else if (!$name || !$email || !$phone) {
    $error = "You have not entered all the required details.";
} else if ((int) $phone < 60000000 || (int) $phone > 99999999) {
    $error = "Mobile number must be 8 digits and start with 6, 8 or 9.";
} else if ($payment != 'success' && $payment != 'failed') {
    $error = "Please select the payment result.";
}

// book seats if payment confirmed
$confirmed   = array();   // bookings saved -> shown in the receipt and the email
$not_booked  = array();   // bookings whose seats were taken by someone else
$total_cents = 0;

if ($error == "" && $payment == 'success') {
    // addslashes before putting the customer's text into SQL (as in the notes)
    $name_sql  = addslashes($name);
    $email_sql = addslashes($email);
    $phone_sql = addslashes($phone);

    $new_cart = array();     // bookings that stay in the cart afterwards

    foreach ($_SESSION['cart'] as $item) {
        $perf_id = $item['perf_id'];

        // was this booking ticked?
        $ticked = false;
        foreach ($selected as $value) {
            if ($value == $perf_id) {
                $ticked = true;
            }
        }

        if (!$ticked) {
            $new_cart[] = $item; 
        } else {
            $done = array();
            $all_ok = true;
            foreach ($item['seats'] as $seat_id) {
                mysqli_query($conn, "UPDATE seats SET status = 'booked'
                                     WHERE seat_id = $seat_id AND status = 'available'");
                if (mysqli_affected_rows($conn) == 1) {
                    $done[] = $seat_id;
                } else {
                    $all_ok = false;
                }
            }

            // seat names and number of tickets
            $seat_list = "";
            $tickets = 0;
            foreach ($item['seats'] as $seat_id) {
                $seat = mysqli_fetch_assoc(mysqli_query($conn, "SELECT seat_number FROM seats WHERE seat_id = $seat_id"));
                if ($seat_list != "") {
                    $seat_list = $seat_list . ", ";
                }
                $seat_list = $seat_list . $seat['seat_number'];
                $tickets++;
            }

            // show + session details, subtotal worked out by MySQL
            $query = "SELECT performances.perf_date, performances.perf_time,
                             shows.title, shows.price * $tickets AS subtotal
                      FROM performances, shows
                      WHERE performances.show_id = shows.show_id
                      AND performances.perf_id = $perf_id";
            $info = mysqli_fetch_assoc(mysqli_query($conn, $query));
            $when = date('D, d M Y', strtotime($info['perf_date'])) . ', ' . date('g:i A', strtotime($info['perf_time']));

            if (!$all_ok) {
                foreach ($done as $seat_id) {
                    mysqli_query($conn, "UPDATE seats SET status = 'available' WHERE seat_id = $seat_id");
                }
                $not_booked[] = $info['title'] . ' (' . $when . ')';
                $new_cart[] = $item;    //unavailable
            } else {
                // Determine user_id: use logged-in session, or lookup by email if registered
                $user_id_val = "NULL";
                if (isset($_SESSION['user_id'])) {
                    $user_id_val = intval($_SESSION['user_id']);
                } else {
                    $user_check = mysqli_query($conn, "SELECT user_id FROM users WHERE email = '$email_sql'");
                    if ($u_row = mysqli_fetch_assoc($user_check)) {
                        $user_id_val = intval($u_row['user_id']);
                    }
                }

                // save the booking
                $subtotal = $info['subtotal'];
                $query = "INSERT INTO bookings (user_id, customer_name, customer_email, customer_phone,
                                                perf_id, seats_booked, total_price)
                          VALUES ($user_id_val, '$name_sql', '$email_sql', '$phone_sql',
                                  $perf_id, '$seat_list', $subtotal)";
                mysqli_query($conn, $query);

                // booking reference = the id MySQL just gave this booking
                $ref = mysqli_fetch_assoc(mysqli_query($conn, "SELECT MAX(booking_id) AS ref FROM bookings"));

                $confirmed[] = array('ref' => $ref['ref'], 'title' => $info['title'], 'when' => $when,
                                     'seats' => $seat_list, 'tickets' => $tickets, 'subtotal' => $subtotal);
                $total_cents = $total_cents + (int) ($subtotal * 100 + 0.5);
            }
        }
    }

    // confirmed bookings leave the cart
    $_SESSION['cart'] = $new_cart;
}


$dollars = (int) ($total_cents / 100);
$rest = $total_cents % 100;
if ($rest < 10) {
    $rest = "0" . $rest;
}
$total_text = $dollars . "." . $rest;

// Email acknowledgement
$mail_sent = false;
if ($confirmed != array()) {
    $to      = 'f32ee@localhost';
    $subject = 'Apex Theatre - Booking Confirmation';
    $message = "Dear " . $name . ",\n\n"
             . "Thank you for booking with Apex Theatre. Your booking is confirmed.\n\n";
    foreach ($confirmed as $b) {
        $message = $message
                 . "Booking #" . $b['ref'] . ": " . $b['title'] . "\n"
                 . "  " . $b['when'] . "\n"
                 . "  Seats: " . $b['seats'] . " (" . $b['tickets'] . " tickets) - $" . $b['subtotal'] . "\n\n";
    }
    $message = $message
             . "Total paid: $" . $total_text . "\n\n"
             . "Contact details: " . $email . ", " . $phone . "\n"
             . "Please show your booking number at the door.\n\n"
             . "Apex Theatre, Singapore";
    $headers = 'From: f32ee@localhost' . "\r\n" .
               'Reply-To: f32ee@localhost' . "\r\n" .
               'X-Mailer: PHP/' . phpversion();
    if (function_exists('mail_patch\mail')) {
        $mail_sent = \mail_patch\mail($to, $subject, $message, $headers, '-ff32ee@localhost');
    } else {
        $mail_sent = @mail($to, $subject, $message, $headers, '-ff32ee@localhost');
    }
}

$all_shows = mysqli_query($conn, "SELECT * FROM shows");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Confirmation | Apex Theatre</title>
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

    <?php if ($error != "") { ?>
        <!-- invalid form details -->
        <h1 class="seat-title">Something is missing</h1>
        <p class="error-msg"><?php echo $error; ?></p>
        <p><a href="cart.php" class="text-link">&lt; Back to My Bookings</a></p>

    <?php } else if ($payment == 'failed') { ?>
        <!-- payment failed -->
        <h1 class="seat-title">Payment unsuccessful</h1>
        <p class="error-msg">Your payment did not go through, so no seats were booked and you have not been charged.</p>
        <p>Your bookings are still saved in My Bookings. You can try again.</p>
        <p><a href="cart.php" class="btn btn-primary">Back to My Bookings</a></p>

    <?php } else { ?>
        <!-- payment successful -->
        <?php if ($confirmed != array()) { ?>
            <h1 class="seat-title">Booking confirmed</h1>
            <p class="cart-intro">Thank you, <?php echo htmlspecialchars($name); ?>.
                <?php if ($mail_sent) { ?>
                    A confirmation email has been sent.
                <?php } else { ?>
                    (The confirmation email could not be sent from this server.)
                <?php } ?>
            </p>

            <table class="cart-table">
                <caption class="receipt-caption">Receipt</caption>
                <thead>
                    <tr>
                        <th>Booking no.</th>
                        <th>Show</th>
                        <th>Date &amp; time</th>
                        <th>Seats</th>
                        <th>Tickets</th>
                        <th>Subtotal ($)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($confirmed as $b) { ?>
                        <tr>
                            <td>#<?php echo $b['ref']; ?></td>
                            <td><?php echo $b['title']; ?></td>
                            <td><?php echo $b['when']; ?></td>
                            <td><?php echo $b['seats']; ?></td>
                            <td><?php echo $b['tickets']; ?></td>
                            <td><?php echo $b['subtotal']; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr class="receipt-total">
                        <td colspan="5">Total paid</td>
                        <td>$<?php echo $total_text; ?></td>
                    </tr>
                </tfoot>
            </table>
        <?php } else { ?>
            <h1 class="seat-title">Nothing was booked</h1>
        <?php } ?>

        <?php if ($not_booked != array()) { ?>
            <p class="error-msg">Sorry, these bookings could not be confirmed because some of their
               seats were just taken by another customer. You have not been charged for them:</p>
            <ul class="not-booked">
                <?php foreach ($not_booked as $title) { ?>
                    <li><?php echo $title; ?></li>
                <?php } ?>
            </ul>
        <?php } ?>

        <p class="cart-links">
            <a href="home.php" class="text-link">Back to home</a>
            <?php if ($_SESSION['cart'] != array()) { ?>
                &nbsp;|&nbsp; <a href="cart.php" class="text-link">You still have bookings in My Bookings</a>
            <?php } ?>
        </p>
    <?php } ?>

    </div>

    <?php include "footer.php"; ?>

    <script src="form_validation.js?v=<?php echo time(); ?>"></script>
</body>
</html>
