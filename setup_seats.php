<?php
/* creates 50 seats (A1-E10) for every performance */
require_once 'db.php';

// stop if seats were already created, so running it twice doesn't duplicate them
$check = mysqli_query($conn, "SELECT seat_id FROM seats");
if (mysqli_num_rows($check) > 0) {
    echo "<p>Seats already exist. Nothing was added.</p>";
    exit;
}

$rows = array('A', 'B', 'C', 'D', 'E');
$perf_result = mysqli_query($conn, "SELECT perf_id FROM performances");
$total = 0;

while ($perf = mysqli_fetch_assoc($perf_result)) {
    $perf_id = $perf['perf_id'];
    foreach ($rows as $letter) {
        for ($num = 1; $num <= 10; $num++) {
            $seat_number = $letter . $num;          // e.g. "A" . 7 = "A7"
            $query = "INSERT INTO seats (perf_id, seat_number, status)
                      VALUES ($perf_id, '$seat_number', 'available')";
            mysqli_query($conn, $query);
            $total++;
        }
    }
}
echo "<p>Done: " . $total . " seats created.</p>";
?>
