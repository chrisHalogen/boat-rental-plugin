<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

function wp_rental_create_tables()
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $table_bookings = $wpdb->prefix . "wp_rental_bookings";
    $table_payments = $wpdb->prefix . "wp_rental_payments";

    // SQL to create the bookings table
    $sql_bookings = "
    CREATE TABLE $table_bookings (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        vehicle_id BIGINT UNSIGNED NOT NULL,
        client_id BIGINT UNSIGNED NOT NULL,
        booking_date DATETIME NOT NULL,
        package VARCHAR(255) NOT NULL DEFAULT 'Unknown for 1 hour',
        status VARCHAR(50) DEFAULT 'pending',
        processed TINYINT(1) DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (client_id) REFERENCES {$wpdb->users}(ID) ON DELETE CASCADE,
        FOREIGN KEY (vehicle_id) REFERENCES {$wpdb->posts}(ID) ON DELETE CASCADE
    ) $charset_collate;";

    // SQL to create the payments table
    $sql_payments = "
    CREATE TABLE $table_payments (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        booking_id BIGINT UNSIGNED NOT NULL,
        amount DECIMAL(10,2) NOT NULL CHECK (amount >= 0),
        payment_method VARCHAR(50) NOT NULL,
        transaction_id VARCHAR(255) UNIQUE NOT NULL,
        payment_status ENUM('Pending', 'Paid', 'Failed', 'Refunded') NOT NULL DEFAULT 'Pending',
        payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (booking_id) REFERENCES $table_bookings(id) ON DELETE CASCADE
    ) $charset_collate;";

    // Include the upgrade file for dbDelta
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    // Create the bookings table
    dbDelta($sql_bookings);

    // Create the payments table
    dbDelta($sql_payments);

    // Log success
    error_log("Plugin Installed Successfully");
}

register_activation_hook(__FILE__, 'wp_rental_create_tables');
