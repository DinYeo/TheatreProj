window.onload = init;

function init() {
    // Seat selection page
    var seatForm = document.getElementById("seatForm");
    if (seatForm) {
        seatForm.onsubmit = validateSeats;
        var boxes = document.getElementsByClassName("seat-box");
        for (var i = 0; i < boxes.length; i++) {
            boxes[i].onchange = updateSeatTotal;
        }
        updateSeatTotal();
    }

    // Menu Button
    var openBtn = document.getElementById("openDrawer");
    var closeBtn = document.getElementById("closeDrawer");
    if (openBtn) {
        openBtn.onclick = function () {
            document.getElementById("drawerMenu").style.display = "block";
        };
    }
    if (closeBtn) {
        closeBtn.onclick = function () {
            document.getElementById("drawerMenu").style.display = "none";
        };
    }

    // My Bookings page
    var checkoutForm = document.getElementById("checkoutForm");
    if (checkoutForm) {
        checkoutForm.onsubmit = validateCheckout;
        var picks = document.getElementsByClassName("booking-box");
        for (var j = 0; j < picks.length; j++) {
            picks[j].onchange = updateCartTotal;
        }
        updateCartTotal();
    }
}

// count how many seat checkboxes are ticked
function countSeats() {
    var boxes = document.getElementsByClassName("seat-box");
    var count = 0;
    for (var i = 0; i < boxes.length; i++) {
        if (boxes[i].checked) {
            count++;
        }
    }
    return count;
}

// runs every time a seat is ticked or unticked
function updateSeatTotal() {
    var count = countSeats();
    var price = parseFloat(document.getElementById("price").value);

    // go through every seat:
    //  - colour the seat shape (gold if picked, red if not)
    //  - build the list of seat names, e.g. "A1, A2"
    // each checkbox id is "seat_A1", so substring(5) gives "A1"
    var boxes = document.getElementsByClassName("seat-box");
    var list = "";
    for (var i = 0; i < boxes.length; i++) {
        var name = boxes[i].id.substring(5);
        var seatShape = document.getElementById("label_" + name);
        if (boxes[i].checked) {
            seatShape.className = "seat-shape seat-picked";
            if (list != "") {
                list = list + ", ";
            }
            list = list + name;
        } else {
            seatShape.className = "seat-shape seat-free";
        }
    }
    if (list == "") {
        list = "None yet";
    }
    document.getElementById("seatList").value = list;

    // work in cents so the total always shows 2 decimal places, e.g. 89.80
    var cents = Math.round(count * price * 100);
    var dollars = Math.floor(cents / 100);
    var rest = cents % 100;
    if (rest < 10) {
        rest = "0" + rest;
    }

    document.getElementById("seatCount").value = count;
    document.getElementById("seatTotal").value = dollars + "." + rest;
}

// runs when "Add to my bookings" is clicked
function validateSeats() {
    var count = countSeats();
    if (count == 0) {
        alert("Please select at least one seat.");
        return false;               // stop the form from being sent
    }
    if (count > 8) {
        alert("You can book up to 8 seats at a time. You have selected " + count + ".");
        return false;
    }
    return true;
}

// adds up the subtotals of the ticked bookings
function updateCartTotal() {
    var picks = document.getElementsByClassName("booking-box");
    var count = 0;
    var cents = 0;
    for (var i = 0; i < picks.length; i++) {
        if (picks[i].checked) {
            // checkbox id is "pick_0", so substring(5) gives "0"
            var key = picks[i].id.substring(5);
            var subtotal = parseFloat(document.getElementById("subtotal_" + key).value);
            cents = cents + Math.round(subtotal * 100);
            count++;
        }
    }
    var dollars = Math.floor(cents / 100);
    var rest = cents % 100;
    if (rest < 10) {
        rest = "0" + rest;
    }
    document.getElementById("bookingCount").value = count;
    document.getElementById("cartTotal").value = dollars + "." + rest;
}

// runs when "Confirm and pay" is clicked

function validateCheckout() {
    // 1. at least one booking ticked
    var picks = document.getElementsByClassName("booking-box");
    var ticked = 0;
    for (var i = 0; i < picks.length; i++) {
        if (picks[i].checked) {
            ticked++;
        }
    }
    if (ticked == 0) {
        alert("Please tick at least one booking to confirm.");
        return false;
    }

    // 2. name: letters and spaces only
    var name = document.getElementById("name");
    var namePattern = /^[A-Za-z ]+$/;
    if (!namePattern.test(name.value)) {
        alert("Name can only contain letters and spaces.");
        return false;
    }

    // 3. email: something@domain.ext (domain can have up to 3 parts)
    var email = document.getElementById("email");
    var emailPattern = /^[\w.-]+@([\w-]+\.){1,3}[A-Za-z]{2,3}$/;
    if (!emailPattern.test(email.value)) {
        alert("Please enter a valid email, e.g. tan@ntu.edu.sg");
        return false;
    }

    // 4. phone: 8 digits starting with 6, 8 or 9 (Singapore numbers)
    var phone = document.getElementById("phone");
    var phonePattern = /^[689]\d{7}$/;
    if (!phonePattern.test(phone.value)) {
        alert("Mobile number must be 8 digits and start with 6, 8 or 9.");
        return false;
    }

    // 5. payment result chosen
    if (document.getElementById("payment").value == "") {
        alert("Please select the payment result.");
        return false;
    }
    return true;
}
