1. seat selection (booking_form.php)

test 1: no seat selected
test method: go to seat booking page and click add to my bookings without clicking any seat
input data: no seats clicked
expected output: alert popup saying please select at least one seat and form should not submit
test result: pass. alert showed up and stopped form from submitting

test 2: selecting more than 8 seats
test method: click 9 seats on the seat map and try to submit
input data: seats a1 to a9 selected
expected output: alert popup saying user can only book up to 8 seats
test result: pass. alert appeared saying 9 seats were selected and stopped the form

test 3: normal seat selection
test method: select 2 available seats and check if the total updates
input data: seat a1 and a2 clicked for macbeth ($44.90 each)
expected output: seat list shows a1, a2 and total price updates to 89.80, then redirects to my bookings page
test result: pass. price updated correctly and redirected to cart.php

2. customer details validation (cart.php)

test 4: name with numbers
test method: type numbers in the name field and submit
input data: name = john123, email = tan@gmail.com, phone = 91234567
expected output: alert saying name can only contain letters and spaces
test result: pass. form blocked and alert showed

test 5: invalid email format
test method: enter email without @ or domain and submit
input data: email = tan_gmail.com
expected output: alert saying please enter a valid email e.g. tan@ntu.edu.sg
test result: pass. alert popped up and form stopped

test 6: phone number starting with wrong digit
test method: enter an 8 digit phone number starting with 7
input data: phone = 71234567
expected output: alert saying mobile number must be 8 digits and start with 6, 8 or 9
test result: pass. alert showed and blocked checkout

test 7: phone number with less than 8 digits
test method: enter a phone number with only 6 digits
input data: phone = 912345
expected output: alert saying mobile number must be 8 digits and start with 6, 8 or 9
test result: pass. alert showed

test 8: valid singapore phone number
test method: enter valid 8 digit phone number starting with 9 or 8
input data: phone = 91234567
expected output: form validation passes and submits to confirmation
test result: pass. accepted the phone number

test 9: failed payment option
test method: choose payment failed from dropdown and click confirm and pay
input data: payment result = failed
expected output: page reloads with message saying payment was cancelled or failed and booking not confirmed
test result: pass. showed the error message and booking was not saved

3. database and email confirmation (confirmation.php)

test 10: database seat status update
test method: complete a booking for available seats and check database
input data: booking seats c3 and c4 for macbeth
expected output: seats c3 and c4 in seats table should change from available to booked
test result: pass. checked in phpmyadmin and both seats changed to booked

test 11: confirmation email sending
test method: confirm a booking with mercury mail running in xampp
input data: valid booking submitted to f32ee@localhost
expected output: confirmation page shows a confirmation email has been sent and email file appears in mercury mail folder
test result: pass. email received in xampp mercurymail mail f32ee folder

4. user login and registration (my_tickets.php)

test 12: register with existing email
test method: try to register an account using an email that is already registered
input data: email = kneehow@gmail.com
expected output: error message showing an account with this email already exists
test result: pass. error banner displayed and no duplicate row added

test 13: wrong password login
test method: enter correct email but wrong password in sign in form
input data: email = kneehow@gmail.com, password = wrongpassword
expected output: error message showing invalid email or password
test result: pass. showed invalid login error

test 14: view bookings after login
test method: log in with an account that has previous bookings
input data: email = kneehow@gmail.com, password = 12345678
expected output: user sees their name and a table listing all their past ticket bookings with booking id, show, date, seats and price
test result: pass. bookings table showed the past receipts correctly

5. navigation and header

test 15: select show dropdown
test method: pick a show from top header dropdown and click buy now
input data: selected the clockwork menagerie
expected output: should direct to show_detail.php?id=2
test result: pass. correct show detail page loaded

test 16: menu drawer
test method: click menu button in header and click close button
input data: click openDrawer then click closeDrawer
expected output: drawer slides out when clicked and disappears when close button is clicked
test result: pass. drawer opened and closed properly

test 17: brand title link
test method: click apex theatre logo on any subpage
input data: click brand title link on my_tickets.php
expected output: should take user back to home.php
test result: pass. returned to home page
