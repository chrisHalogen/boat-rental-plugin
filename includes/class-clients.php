<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

function wp_register_client_role()
{
    add_role(
        'client',  // Role ID
        'Client',  // Display Name
        [
            'read' => true,           // Allow reading posts
            'edit_posts' => false,    // Can't edit posts
            'delete_posts' => false,  // Can't delete posts
            'upload_files' => false   // Can't upload files
        ]
    );
}
add_action('init', 'wp_register_client_role');

function wp_add_client_role_on_activation()
{
    wp_register_client_role();
}
register_activation_hook(__FILE__, 'wp_add_client_role_on_activation');


function wp_rental_clients_handle_actions()
{
    if (!isset($_GET['action'], $_GET['client_id'])) {
        return;
    }

    $action = $_GET['action'];
    $client_id = intval($_GET['client_id']);

    if ($action === 'delete') {
        $result = delete_user_bookings_and_payments($client_id);

        if (isset($result["bookings_deleted"])) {
            wp_delete_user($client_id);
            wp_redirect(admin_url('admin.php?page=wp_rental_clients&message=deleted'));
            return;
        }

        wp_redirect(admin_url('admin.php?page=wp_rental_clients&message=error'));
        exit;
    }

    if ($action === 'reset_password') {
        $user = get_user_by('id', $client_id);
        if ($user) {
            send_recovery_email_to_client_user($user->user_email);

            // $reset_link = wp_lostpassword_url();
            // wp_mail($user->user_email, 'Password Reset Request', 'Click here to reset your password: ' . $reset_link);
            wp_redirect(admin_url('admin.php?page=wp_rental_clients&message=reset'));
            exit;
        }
    }
}
add_action('admin_init', 'wp_rental_clients_handle_actions');

function wp_rental_clients_admin_notices()
{
    if (!isset($_GET['message'])) {
        return;
    }

    $message = sanitize_text_field($_GET['message']);

    if ($message === 'deleted') {
        echo '<div class="notice notice-success is-dismissible"><p>Client successfully deleted with all bookings and associated payments...</p></div>';
    } elseif ($message === 'reset') {
        echo '<div class="notice notice-success is-dismissible"><p>Password reset email sent successfully.</p></div>';
    }
}
add_action('admin_notices', 'wp_rental_clients_admin_notices');

// Generate Fake Clients
use Faker\Factory;

function generate_fake_clients($count = 10)
{
    if (!function_exists('wp_insert_user')) {
        require_once ABSPATH . 'wp-admin/includes/user.php';
    }

    $faker = Factory::create();

    for ($i = 0; $i < $count; $i++) {
        $fake_name = $faker->name;
        $fake_email = $faker->unique()->safeEmail;
        $fake_phone = $faker->phoneNumber;
        $fake_address = $faker->address;
        $fake_orders = $faker->numberBetween(1, 20);
        $fake_total_spent = $faker->randomFloat(2, 50, 5000); // Between $50 and $5000

        // Create WordPress user with role "client"
        $user_id = wp_insert_user([
            'user_login'    => sanitize_user(str_replace(' ', '_', strtolower($fake_name))),
            'user_pass'     => wp_generate_password(),
            'user_email'    => $fake_email,
            'display_name'  => $fake_name,
            'role'          => 'client'
        ]);

        if (!is_wp_error($user_id)) {
            // Add custom metadata
            update_user_meta($user_id, 'phone_number', $fake_phone);
            update_user_meta($user_id, 'address', $fake_address);
            update_user_meta($user_id, 'total_orders', $fake_orders);
            update_user_meta($user_id, 'total_spent', $fake_total_spent);

            write_log("Created fake client: " . esc_html($fake_name) . " ($fake_email)<br>");
        } else {
            write_log("Error creating client: " . $user_id->get_error_message() . "<br>");
        }
    }
}
