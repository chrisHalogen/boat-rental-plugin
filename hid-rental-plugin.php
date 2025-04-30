<?php

/**
 * Plugin Name: HID Vehicle Rental
 * Plugin URI:  https://github.com/chrisHalogen
 * Description: A rental management plugin for boats, jet skis, and water sports cars with Stripe payments.
 * Version:     1.0.0
 * Author:      Christian Chi Nwikpo
 * Author URI:  https://github.com/chrisHalogen
 * License:     GPL-2.0+
 * Text Domain: hid-vehicle-rental
 * Domain Path: /languages
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants.
define('WVR_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WVR_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WVR_VERSION', '1.0.0');

// COmposer Packaged
require_once __DIR__ . '/vendor/autoload.php';

// Include Functions file.
require_once WVR_PLUGIN_PATH . 'functions.php';
require_once WVR_PLUGIN_PATH . 'db-functions.php';
require_once WVR_PLUGIN_PATH . 'ajax-functions.php';
require_once WVR_PLUGIN_PATH . 'reciept-generator.php';
require_once WVR_PLUGIN_PATH . 'reciept-maker.php';


// Include necessary files.
require_once WVR_PLUGIN_PATH . 'includes/recieptMaker/PDFService.php';
require_once WVR_PLUGIN_PATH . 'includes/class-bookings.php';
require_once WVR_PLUGIN_PATH . 'includes/class-clients.php';
// require_once WVR_PLUGIN_PATH . 'includes/admin-scripts.php';
require_once WVR_PLUGIN_PATH . 'includes/class-dashboard.php';
require_once WVR_PLUGIN_PATH . 'includes/class-helpers.php';
require_once WVR_PLUGIN_PATH . 'includes/class-notifications.php';
require_once WVR_PLUGIN_PATH . 'includes/class-payments.php';
require_once WVR_PLUGIN_PATH . 'includes/class-vehicles.php';
require_once WVR_PLUGIN_PATH . 'includes/install.php';
require_once WVR_PLUGIN_PATH . 'includes/uninstall.php';
require_once WVR_PLUGIN_PATH . 'includes/meta-boxes.php';

// Admin Menu Files
require_once WVR_PLUGIN_PATH . 'admin/admin-menu.php';



// Activation Hook
function wvr_plugin_activate()
{
    wp_rental_create_tables(); // Calls function from install.php
}
register_activation_hook(__FILE__, 'wvr_plugin_activate');

// Deactivation Hook (optional: perform cleanups when deactivating but keeping data)
function wvr_plugin_deactivate()
{
    // You may perform cleanup tasks here if necessary.
}
register_deactivation_hook(__FILE__, 'wvr_plugin_deactivate');
