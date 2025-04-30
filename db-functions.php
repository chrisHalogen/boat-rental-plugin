<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}


function calculate_user_booking_stats($user_id)
{
    global $wpdb;

    // Define table names
    $table_payments = $wpdb->prefix . "wp_rental_payments";
    $table_bookings = $wpdb->prefix . "wp_rental_bookings";

    // Validate user ID
    if (!is_numeric($user_id)) {
        return new WP_Error('invalid_user_id', 'Invalid user ID.');
    }

    // Calculate total number of bookings
    $total_bookings = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_bookings WHERE client_id = %d",
            $user_id
        )
    );

    // Calculate total amount spent
    $total_amount_spent = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT SUM(p.amount) 
             FROM $table_payments p
             INNER JOIN $table_bookings b ON p.booking_id = b.id
             WHERE b.client_id = %d AND p.payment_status = 'Paid'",
            $user_id
        )
    );

    // If no bookings or payments, set defaults
    $total_bookings = $total_bookings ? intval($total_bookings) : 0;
    $total_amount_spent = $total_amount_spent ? floatval($total_amount_spent) : 0.00;

    // Return results
    return [
        'total_bookings' => $total_bookings,
        'total_amount_spent' => $total_amount_spent,
    ];
}
