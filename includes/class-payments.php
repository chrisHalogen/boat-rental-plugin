<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WP_Rental_Payments
{
    public static function process_payment($booking_id, $amount, $payment_status)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "wp_rental_payments";

        $wpdb->insert(
            $table_name,
            array(
                'booking_id' => $booking_id,
                'amount' => $amount,
                'status' => sanitize_text_field($payment_status),
                'created_at' => current_time('mysql')
            )
        );

        return $wpdb->insert_id;
    }
}


function generate_fake_payments($count = 10)
{
    global $wpdb;

    if (!class_exists('Faker\Factory')) {
        require_once 'vendor/autoload.php'; // Ensure Faker is available
    }

    $faker = Faker\Factory::create();
    $table_bookings = $wpdb->prefix . "wp_rental_bookings";
    $table_payments = $wpdb->prefix . "wp_rental_payments";

    // Get random bookings
    $bookings = $wpdb->get_results("SELECT id FROM $table_bookings ORDER BY RAND() LIMIT $count");

    if (empty($bookings)) {
        error_log("No bookings found in the database.");
        return;
    }

    $payment_methods = ['Credit Card', 'PayPal', 'Bank Transfer', 'Cash'];
    $payment_statuses = ['Pending', 'Paid', 'Failed', 'Refunded'];

    foreach ($bookings as $booking) {
        $amount = $faker->randomFloat(2, 50, 500); // Random amount between $50 and $500
        $payment_method = $faker->randomElement($payment_methods);
        $transaction_id = strtoupper($faker->unique()->bothify('TXN#######'));
        $payment_status = $faker->randomElement($payment_statuses);
        $payment_date = $faker->dateTimeBetween('-2 months', 'now')->format('Y-m-d H:i:s');

        $wpdb->insert($table_payments, [
            'booking_id'     => $booking->id,
            'amount'         => $amount,
            'payment_method' => $payment_method,
            'transaction_id' => $transaction_id,
            'payment_status' => $payment_status,
            'payment_date'   => $payment_date,
            'created_at'     => current_time('mysql')
        ]);

        error_log("Created fake payment: Booking ID {$booking->id} with amount $amount, status $payment_status");
    }
}
