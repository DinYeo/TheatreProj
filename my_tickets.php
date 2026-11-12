<?php
session_start();
include "db.php";

$error_msg = "";
$success_msg = "";

// handle logout
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: my_tickets.php");
    exit;
}

// login form
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_type']) && $_POST['form_type'] == 'login') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error_msg = "Please enter both email and password.";
    } else {
        $login_sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
        $result = mysqli_query($conn, $login_sql);

        if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
        } else {
            $error_msg = "Invalid email or password. Please try again.";
        }
    }
}

// registration form
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_type']) && $_POST['form_type'] == 'register') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($name) || empty($email) || empty($password)) {
        $error_msg = "All registration fields are required.";
    } else {
        // check if email already registered
        $check_sql = "SELECT * FROM users WHERE email = '$email'";
        $check_res = mysqli_query($conn, $check_sql);

        if (mysqli_num_rows($check_res) > 0) {
            $error_msg = "An account with this email already exists.";
        } else {
            $insert_user = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')";
            if (mysqli_query($conn, $insert_user)) {
                $_SESSION['user_id'] = mysqli_insert_id($conn);
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
            } else {
                $error_msg = "Could not register account. Please try again.";
            }
        }
    }
}

// header dropdown shows list
$shows = array();
$all_shows_res = mysqli_query($conn, "SELECT * FROM shows");
while ($r = mysqli_fetch_assoc($all_shows_res)) {
    $shows[] = $r;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account & Bookings | Apex Theatre</title>
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

    <div class="container" style="padding-top: 40px; padding-bottom: 60px;">

        <?php if (!empty($error_msg)) { ?>
            <div class="alert-error" style="max-width: 600px; margin: 0 auto 20px auto; background-color: var(--accent-red); color: white; padding: 12px 16px; border-radius: 4px; text-align: center;">
                <?php echo $error_msg; ?>
            </div>
        <?php } ?>

        <?php if (isset($_SESSION['user_email'])) { ?>
            <!-- logged in -->
            <div class="account-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 1px solid var(--border-color); padding-bottom: 15px;">
                <div>
                    <h2>My Bookings</h2>
                    <p style="color: var(--text-muted); margin-top: 5px;">
                        Logged in as <strong><?php echo $_SESSION['user_name']; ?></strong> (<?php echo $_SESSION['user_email']; ?>)
                    </p>
                </div>
                <a href="my_tickets.php?action=logout" class="btn btn-secondary" style="background: transparent; border: 1px solid var(--border-color); color: var(--text-main); padding: 8px 16px; text-decoration: none; border-radius: 4px;">Log Out</a>
            </div>

            <?php
            // fetch bookings for this user's email or user_id
            $current_email = $_SESSION['user_email'];
            $current_uid = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
            $bookings_query = "SELECT b.booking_id, b.customer_name, b.seats_booked, b.total_price,
                                      p.perf_date, p.perf_time, s.title
                               FROM bookings b
                               JOIN performances p ON b.perf_id = p.perf_id
                               JOIN shows s ON p.show_id = s.show_id
                               WHERE b.customer_email = '$current_email' OR b.user_id = $current_uid
                               ORDER BY b.booking_id DESC";
            $bookings_res = mysqli_query($conn, $bookings_query);
            ?>

            <?php if (mysqli_num_rows($bookings_res) > 0) { ?>
                <table class="account-table">
                    <thead>
                        <tr>
                            <th>Ref #</th>
                            <th>Show</th>
                            <th>Date & Time</th>
                            <th>Seats</th>
                            <th>Total Paid</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($b = mysqli_fetch_assoc($bookings_res)) { 
                            $formatted_date = date('D, j M Y', strtotime($b['perf_date']));
                            $formatted_time = date('g:i A', strtotime($b['perf_time']));
                        ?>
                            <tr>
                                <td><strong>#<?php echo $b['booking_id']; ?></strong></td>
                                <td><?php echo $b['title']; ?></td>
                                <td><?php echo $formatted_date . ', ' . $formatted_time; ?></td>
                                <td><span class="seat-badge"><?php echo $b['seats_booked']; ?></span></td>
                                <td>$<?php echo number_format($b['total_price'], 2); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <div style="background-color: var(--bg-card); padding: 40px; text-align: center; border: 1px solid var(--border-color); border-radius: 6px;">
                    <p style="color: var(--text-muted); font-size: 18px; margin-bottom: 20px;">No bookings found under this email address yet.</p>
                    <a href="home.php" class="btn btn-primary" style="text-decoration: none; display: inline-block;">Browse Available Shows</a>
                </div>
            <?php } ?>

        <?php } else { ?>
            <!-- not logged in -->
            <div class="auth-container">
                <div class="auth-col">
                    <h2>Sign In</h2>
                    <p class="auth-subtext">Log in with your email to view your booked tickets.</p>

                    <form action="my_tickets.php" method="POST" class="auth-form">
                        <input type="hidden" name="form_type" value="login">
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" required placeholder="e.g. tan@gmail.com">
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" required placeholder="Enter password">
                        </div>
                        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 15px;">Sign In</button>
                    </form>
                </div>

                <div class="auth-divider"></div>

                <div class="auth-col">
                    <h2>New User? Register</h2>
                    <p class="auth-subtext">Create an account to manage bookings and view receipts.</p>

                    <form action="my_tickets.php" method="POST" class="auth-form">
                        <input type="hidden" name="form_type" value="register">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="name" required placeholder="e.g. John Tan">
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" required placeholder="e.g. tan@gmail.com">
                        </div>
                        <div class="form-group">
                            <label>Create Password</label>
                            <input type="password" name="password" required placeholder="Choose a password">
                        </div>
                        <button type="submit" class="btn btn-secondary" style="width: 100%; margin-top: 15px;">Create Account</button>
                    </form>
                </div>
            </div>
        <?php } ?>

    </div>

    <?php include "footer.php"; ?>

    <script src="form_validation.js?v=<?php echo time(); ?>"></script>
</body>
</html>

