<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WP_Rental_Bookings {
    public static function add_booking($vehicle_id, $customer_name, $customer_email, $booking_date) {
        global $wpdb;
        $table_name = $wpdb->prefix . "wp_rental_bookings";

        $wpdb->insert(
            $table_name,
            array(
                'vehicle_id' => $vehicle_id,
                'customer_name' => sanitize_text_field($customer_name),
                'customer_email' => sanitize_email($customer_email),
                'booking_date' => $booking_date,
                'status' => 'pending',
                'created_at' => current_time('mysql')
            )
        );

        return $wpdb->insert_id;
    }
}

// use Faker\Factory;

function generate_fake_bookings($count = 10) {
    global $wpdb;

    if (!class_exists('Faker\Factory')) {
        require_once 'vendor/autoload.php'; // Ensure Faker is available
    }

    $faker = Faker\Factory::create();
    $table_name = $wpdb->prefix . "wp_rental_bookings";

    // Get random vehicles from the custom post type "water_vehicle"
    $vehicles = $wpdb->get_results("SELECT ID FROM {$wpdb->posts} WHERE post_type = 'water_vehicle' AND post_status = 'publish' ORDER BY RAND() LIMIT $count");

    // Get random clients (users with role "client")
    $clients = get_users([
        'role'    => 'client',
        'orderby' => 'rand',
        'number'  => $count
    ]);

    if (empty($vehicles) || empty($clients)) {
        error_log("No vehicles or clients found in the database.");
        return;
    }

    $statuses = ['pending', 'paid', 'completed', 'cancelled'];

    for ($i = 0; $i < $count; $i++) {
        $vehicle = $vehicles[array_rand($vehicles)];
        $client = $clients[array_rand($clients)];

        $booking_date = $faker->dateTimeBetween('-2 months', 'now')->format('Y-m-d H:i:s');
        $status = $faker->randomElement($statuses);

        $wpdb->insert($table_name, [
            'vehicle_id'   => $vehicle->ID, 
            'client_id'    => $client->ID, 
            'booking_date' => $booking_date,
            'status'       => $status,
            'created_at'   => current_time('mysql')
        ]);

        error_log("Created fake booking: Vehicle {$vehicle->ID} booked by Client ID {$client->ID} with status $status");
    }
}
