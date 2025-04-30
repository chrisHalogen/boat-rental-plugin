<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

function wp_rental_generate_fake_clients_page() {
    if (isset($_POST['generate_clients'])) {
        generate_fake_clients(10);
        echo '<div class="updated"><p>10 Fake Clients Generated!</p></div>';
    }

    echo '<div class="wrap"><h1>Generate Fake Clients</h1><br><br>';
    echo '<form method="post">
            <input type="submit" name="generate_clients" class="button-primary" value="Generate 10 Fake Clients">
          </form>';
    echo '</div><br><hr style="border-width:3px;margin-right: 3rem">';
}

function wp_rental_generate_fake_bookings_page() {
    if (isset($_POST['generate_bookings'])) {
        generate_fake_bookings(10);
        echo '<div class="updated"><p>10 Fake Bookings Generated!</p></div>';
    }

    echo '<div class="wrap"><h1>Generate Fake Bookings</h1><br><br>';
    echo '<form method="post">
            <input type="submit" name="generate_bookings" class="button-primary" value="Generate 10 Fake Bookings">
          </form>';
    echo '</div><br><hr style="border-width:3px;margin-right: 3rem">';
}

function wp_rental_generate_fake_payments_page() {
    if (isset($_POST['generate_payments'])) {
        generate_fake_payments(10);
        echo '<div class="updated"><p>10 Fake Payments Generated!</p></div>';
    }

    echo '<div class="wrap"><h1>Generate Fake Payments</h1><br><br>';
    echo '<form method="post">
            <input type="submit" name="generate_payments" class="button-primary" value="Generate 10 Fake Payments">
          </form>';
    echo '</div><br><hr style="border-width:3px;margin-right: 3rem">';
}


// Calling the Functions
wp_rental_generate_fake_clients_page();
wp_rental_generate_fake_bookings_page();
wp_rental_generate_fake_payments_page();