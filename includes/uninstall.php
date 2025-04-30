<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

function wp_rental_delete_tables() {
    global $wpdb;

    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wp_rental_bookings");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wp_rental_payments");

    delete_option('wp_rental_stripe_key');
    delete_option('wp_rental_booking_policy');
}

// register_uninstall_hook(__FILE__, 'wp_rental_delete_tables');
