<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Add Menu Pages for the Admin Dashboard
function wp_rental_admin_menu() {
    add_menu_page(
        'Water Vehicle Rentals',
        'Rentals',
        'manage_options',
        'wp-rental-dashboard',
        'wp_rental_dashboard_page',
        'dashicons-admin-generic',
        25
    );

    add_submenu_page(
        'wp-rental-dashboard',
        'Manage Vehicles',
        'Vehicles',
        'manage_options',
        'edit.php?post_type=water_vehicle'
    );

    add_submenu_page(
        'wp-rental-dashboard',  // Parent menu slug
        'Manage Clients',     // Page title
        'Clients',            // Menu title
        'manage_options',       // Capability
        'wp_rental_clients',  // Menu slug
        'wp_rental_clients_page' // Callback function
    );

    add_submenu_page(
        'wp-rental-dashboard',
        'Bookings',
        'Bookings',
        'manage_options',
        'wp_rental_bookings',
        'wp_rental_bookings_page'
    );

    add_submenu_page(
        'wp-rental-dashboard',
        'Payments',
        'Payments',
        'manage_options',
        'wp_rental_payments',
        'wp_rental_payments_page'
    );

    add_submenu_page(
        'wp-rental-dashboard', 
        'Generate Fake Data', 
        'Generate Data', 
        'manage_options', 
        'generate_fake_clients', 
        'wp_rental_generate_fake_data_page'
    );

    add_submenu_page(
        'wp-rental-dashboard',
        'Settings',
        'Settings',
        'manage_options',
        'wp-rental-settings',
        'wp_rental_settings_page'
    );

    
}
add_action('admin_menu', 'wp_rental_admin_menu');

// Placeholder Functions for Pages
function wp_rental_dashboard_page() {
    echo '<h2>Rental Dashboard</h2>';
    echo '<p>Welcome to the Water Vehicle Rental Plugin Dashboard.</p>';
}

function wp_rental_bookings_page() {
    include plugin_dir_path(__FILE__) . 'bookings-list.php';
}

function wp_rental_payments_page() {
    include plugin_dir_path(__FILE__) . 'payments-list.php';
}

function wp_rental_clients_page() {
    include plugin_dir_path(__FILE__) . 'clients-list.php';
}

function wp_rental_settings_page() {
    include plugin_dir_path(__FILE__) . 'settings.php';
}

function wp_rental_generate_fake_data_page() {
    include plugin_dir_path(__FILE__) . 'generate-fake-data.php';
}