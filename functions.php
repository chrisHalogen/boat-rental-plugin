<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue plugin styles and scripts for both admin and frontend.
 */
function wvr_enqueue_scripts()
{
    // Define plugin assets path
    $plugin_version = '1.0.0'; // Change as needed
    $plugin_url = plugin_dir_url(__FILE__) . 'assets/';

    // Enqueue Admin Styles & Scripts
    if (is_admin()) {
        wp_enqueue_style('wvr-admin-style', $plugin_url . 'css/admin-style.css', array(), $plugin_version, 'all');
        wp_enqueue_media();
        wp_enqueue_script('wvr-admin-script', $plugin_url . 'js/admin-script.js', array('jquery'), $plugin_version, true);

        wp_enqueue_script('wvr-admin-script-logo', $plugin_url . 'js/admin-script-logo.js', array('jquery'), $plugin_version, true);

        // Inject PHP values into admin script
        wp_localize_script('wvr-admin-script', 'wvr_admin_vars', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('wvr_admin_nonce'),
        ));
    }

    // Enqueue Frontend Styles & Scripts
    else {
        wp_enqueue_style('wvr-frontend-style', $plugin_url . 'css/frontend-style.css', array(), $plugin_version, 'all');
        wp_enqueue_style('wvr-frontend-custom-style', $plugin_url . 'css/custom-styles.css', array(), $plugin_version, 'all');
        // wp_enqueue_script('stripe-js', 'https://js.stripe.com/v3/', array(), null, true);
        wp_enqueue_script('wvr-frontend-script', $plugin_url . 'js/frontend-script.js', array('jquery'), $plugin_version, true);

        // Inject PHP values into frontend script
        wp_localize_script('wvr-frontend-script', 'wvr_frontend_vars', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'home_url' => home_url(),
            'stripe_publishable_key' => "pk_test_51R3ln2Ln0B7MXWT4IQzPvxe9v7mfw9Br0Jf1D1tAtBaIOHbhcpQfukjNI7Ba5VbFRyntr8vSllBH2ZHCxd6ii5Cv00ZrT0mRHc",
            'current_post_id' => get_current_post_id(),
            'logout_url' => wp_logout_url(home_url()),
            'site_url' => get_site_url(),
            'nonce'    => wp_create_nonce('wvr_frontend_nonce'),
            'login_page' => esc_url(get_permalink(get_option('wp_rental_login_page'))),
            'img_path' => plugin_dir_url(__FILE__) . 'assets/images',
            'imageUrls' => [
                plugin_dir_url(__FILE__) . 'assets/images/dsmc-h2.png',
                plugin_dir_url(__FILE__) . 'assets/images/dsmc-h4.png',
                plugin_dir_url(__FILE__) . 'assets/images/dsmc-h6.png',
                plugin_dir_url(__FILE__) . 'assets/images/dsmc-h7.png',
                plugin_dir_url(__FILE__) . 'assets/images/dsmc-h8.png',
                plugin_dir_url(__FILE__) . 'assets/images/dsmc-h12.png',
            ]
        ));
    }
}

// Hook into WordPress
add_action('admin_enqueue_scripts', 'wvr_enqueue_scripts');  // Load for admin
add_action('wp_enqueue_scripts', 'wvr_enqueue_scripts');     // Load for frontend


if (! function_exists('write_log')) {
    function write_log($log)
    {
        if (is_array($log) || is_object($log)) {
            error_log(print_r($log, true));
        } else {
            error_log($log);
        }
    }
}

if (!function_exists('console_log')) {
    function console_log($data)
    {
        echo '<script>';
        if (is_array($data) || is_object($data)) {
            echo 'console.log(' . json_encode($data, JSON_PRETTY_PRINT) . ');';
        } else {
            echo 'console.log("' . addslashes($data) . '");';
        }
        echo '</script>';
    }
}

// Get Vehicle Name by ID
function wp_rental_get_vehicle_name($vehicle_id)
{
    return get_the_title($vehicle_id);
}

// Get Customer Name by Booking ID
function wp_rental_get_customer_name($booking_id)
{
    global $wpdb;
    $table_name = $wpdb->prefix . "wp_rental_bookings";
    $result = $wpdb->get_var("SELECT customer_name FROM $table_name WHERE id = $booking_id");
    return $result;
}

// Register custom templates in the dropdown
add_filter('theme_page_templates', 'my_plugin_register_templates');
function my_plugin_register_templates($templates)
{
    $templates['login-template.php'] = 'Custom Login Page';
    $templates['register-template.php'] = 'Custom Register Page';
    $templates['recover-template.php'] = 'Password Recovery Page';
    $templates['archive-template.php'] = 'Water Vehicles Archive';
    $templates['main-template.php'] = 'New Dashboard Page';
    $templates['dashboard-template.php'] = 'User Dashboard'; // New dashboard template
    $templates['payment_demo-template.php'] = 'Payment Demo Page';
    $templates['payment_checkout-template.php'] = 'Payment checkout Page';
    $templates['payment_success-template.php'] = 'Payment success Page';
    return $templates;
}

// Load the correct template
add_filter('template_include', 'my_plugin_load_template');
function my_plugin_load_template($template)
{
    if (is_page()) {
        $selected_template = get_page_template_slug();
        $plugin_templates = [
            'login-template.php' => plugin_dir_path(__FILE__) . 'templates/login-template.php',
            'register-template.php' => plugin_dir_path(__FILE__) . 'templates/register-template.php',
            'recover-template.php' => plugin_dir_path(__FILE__) . 'templates/recover-template.php',
            'main-template.php' => plugin_dir_path(__FILE__) . 'templates/dashboard/main-template.php',
            'dashboard-template.php' => plugin_dir_path(__FILE__) . 'templates/dashboard-template.php',
            'archive-template.php' => plugin_dir_path(__FILE__) . 'templates/archive-template.php',
            'payment_demo-template.php' => plugin_dir_path(__FILE__) . 'templates/payment_demo-template.php',
            'payment_checkout-template.php' => plugin_dir_path(__FILE__) . 'templates/payment_checkout-template.php',
            'payment_success-template.php' => plugin_dir_path(__FILE__) . 'templates/payment_success-template.php',

        ];

        if (isset($plugin_templates[$selected_template])) {
            return $plugin_templates[$selected_template];
        }
    }
    return $template;
}


function wp_rental_hide_admin_bar_for_clients()
{
    if (current_user_can('client')) { // Replace 'client' with the appropriate role
        show_admin_bar(false);
    }
}
add_action('after_setup_theme', 'wp_rental_hide_admin_bar_for_clients');


function wp_rental_restrict_wp_admin_for_clients()
{
    if (is_admin() && current_user_can('client') && !defined('DOING_AJAX')) {
        wp_redirect(home_url());
        exit;
    }
}
add_action('init', 'wp_rental_restrict_wp_admin_for_clients');


/**
 * Safely redirects to a specified URL.
 *
 * @param string $url The URL to redirect to.
 * @param int $status_code The HTTP status code to use for the redirect (default: 302).
 * @return void
 */
function safe_redirect($url, $status_code = 302)
{
    // Ensure the URL is valid and safe
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        wp_die('Invalid redirect URL.');
    }

    // Ensure no output has been sent
    if (headers_sent()) {
        wp_die('Headers already sent. Redirect failed.');
    }

    // Perform the redirect
    header('Location: ' . $url, true, $status_code);
    exit;
}

function wp_rental_custom_rewrite_rules()
{
    // Get the dashboard page slug
    $dashboard_page_slug = get_post_field('post_name', get_option('wp_rental_dashboard_page'));

    // Add rewrite rules for subpages
    add_rewrite_rule(
        '^' . $dashboard_page_slug . '/bookings$',
        'index.php?pagename=' . $dashboard_page_slug . '&subpage=bookings',
        'top'
    );
    add_rewrite_rule(
        '^' . $dashboard_page_slug . '/payments$',
        'index.php?pagename=' . $dashboard_page_slug . '&subpage=payments',
        'top'
    );
    add_rewrite_rule(
        '^' . $dashboard_page_slug . '/support$',
        'index.php?pagename=' . $dashboard_page_slug . '&subpage=support',
        'top'
    );
    add_rewrite_rule(
        '^' . $dashboard_page_slug . '/account$',
        'index.php?pagename=' . $dashboard_page_slug . '&subpage=account',
        'top'
    );
    add_rewrite_rule(
        '^' . $dashboard_page_slug . '/booking-details$',
        'index.php?pagename=' . $dashboard_page_slug . '&subpage=booking-details',
        'top'
    );

    // Add rewrite rules for pagination
    add_rewrite_rule(
        '^' . $dashboard_page_slug . '/payments/page/([0-9]+)/?$',
        'index.php?pagename=' . $dashboard_page_slug . '/payments&paged=$matches[1]',
        'top'
    );
    add_rewrite_rule(
        '^' . $dashboard_page_slug . '/payments/?$',
        'index.php?pagename=' . $dashboard_page_slug . '/payments',
        'top'
    );
}
add_action('init', 'wp_rental_custom_rewrite_rules');

// Add custom query variable for subpages
function wp_rental_add_query_vars($vars)
{
    $vars[] = 'subpage';
    return $vars;
}
add_filter('query_vars', 'wp_rental_add_query_vars');

function restrict_inactive_users($user)
{
    if (is_wp_error($user)) {
        return $user;
    }

    // $account_status = get_user_meta($user->ID, 'account_status', true);
    $account_status = get_user_meta($user->ID, 'is_active', 1);
    // delete_user_meta($user_id, 'activation_key');
    if ($account_status === 0) {
        return new WP_Error('account_inactive', 'Your account is inactive. Please contact the admin.');
    }

    return $user;
}
add_filter('authenticate', 'restrict_inactive_users', 30, 1);


function custom_water_vehicle_archive_template($template)
{
    if (is_post_type_archive('water_vehicle')) {
        // Path to your custom template file in the plugin
        $plugin_template = plugin_dir_path(__FILE__) . 'templates/archive-water_vehicle.php';
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }
    return $template;
}
add_filter('template_include', 'custom_water_vehicle_archive_template');

function custom_water_vehicle_single_template($template)
{
    if (is_singular('water_vehicle')) {
        $plugin_template = plugin_dir_path(__FILE__) . 'templates/single-water_vehicle.php';
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }
    return $template;
}
add_filter('template_include', 'custom_water_vehicle_single_template');

function send_recovery_email_to_client_user($email)
{

    $user = get_user_by('email', $email);
    // Generate a unique recovery token
    $recovery_token = wp_generate_password(32, false);
    update_user_meta($user->ID, 'wp_rental_recovery_token', $recovery_token);

    // Get recovery page URL
    $recover_page_id = get_option('wp_rental_recover_page');
    $recover_page_url = get_permalink($recover_page_id);
    $reset_link = add_query_arg(['id' => $user->ID, 'token' => $recovery_token], $recover_page_url);

    // Send email with recovery link
    $subject = "Password Reset Request";
    $message = "Hello, \n\nWe received a request to reset your password. Click the link below to set a new password:\n\n$reset_link \n\nIf you did not request this, ignore this email.";

    if (wp_mail($email, $subject, $message)) {
        write_log("Email sent successfully");
    } else {
        write_log("Error Encountered");
    }
}


// Redirect all login attempts to the custom login page
function redirect_to_custom_login_page($redirect_to, $request, $user)
{
    $login_page_id = get_option('login_page_id');
    if ($login_page_id) {
        $login_page_url = get_permalink($login_page_id);
        return $login_page_url;
    }
    return $redirect_to;
}
add_filter('login_redirect', 'redirect_to_custom_login_page', 10, 3);

// Replace the default login URL with the custom login page URL
function custom_login_url($login_url, $redirect)
{
    $login_page_id = get_option('login_page_id');
    if ($login_page_id) {
        $login_page_url = get_permalink($login_page_id);
        return $login_page_url;
    }
    return $login_url;
}
add_filter('login_url', 'custom_login_url', 10, 2);

// Restrict access to the default login page
function restrict_default_login_page()
{
    if ($GLOBALS['pagenow'] === 'wp-login.php' && !is_user_logged_in()) {
        $login_page_id = get_option('login_page_id');
        if ($login_page_id) {
            $login_page_url = get_permalink($login_page_id);
            safe_redirect($login_page_url);
            exit;
        }
    }
}
add_action('template_redirect', 'restrict_default_login_page');

// Redirect to the custom login page after logout
function redirect_after_logout()
{
    $login_page_id = get_option('login_page_id');
    if ($login_page_id) {
        $login_page_url = get_permalink($login_page_id);
        safe_redirect($login_page_url);
        exit;
    }
}
add_action('wp_logout', 'redirect_after_logout');


function redirect_to_custom_recover_page($lostpassword_url, $redirect)
{
    // Get the custom recovery page URL
    $recover_page_id = get_option('wp_rental_recover_page');
    if ($recover_page_id) {
        $recover_page_url = get_permalink($recover_page_id);
        return $recover_page_url; // Redirect to the custom recovery page
    }
    return $lostpassword_url; // Fallback to the default recovery URL
}
add_filter('lostpassword_url', 'redirect_to_custom_recover_page', 10, 2);

function restrict_default_recover_page()
{
    // Check if the current page is the default password recovery page
    if ($GLOBALS['pagenow'] === 'wp-login.php' && isset($_GET['action']) && $_GET['action'] === 'lostpassword') {
        // Get the custom recovery page URL
        $recover_page_id = get_option('wp_rental_recover_page');
        if ($recover_page_id) {
            $recover_page_url = get_permalink($recover_page_id);
            wp_redirect($recover_page_url); // Redirect to the custom recovery page
            exit;
        }
    }
}
add_action('template_redirect', 'restrict_default_recover_page');


function display_featured_water_vehicles()
{
    // Start output buffering
    ob_start();

    // Query featured water vehicles
    $args = array(
        'post_type' => 'water_vehicle',
        'posts_per_page' => 3,
        'meta_query' => array(
            array(
                'key' => 'is_featured', // Meta key for featured vehicles
                'value' => 'yes', // Only fetch vehicles marked as featured
                'compare' => '=',
            ),
        ),
    );

    $featured_vehicles = new WP_Query($args);

    if ($featured_vehicles->have_posts()) {
        echo '<div class="archive-content-container"><div class="archive-section">'; // Open the container

        while ($featured_vehicles->have_posts()) {
            $featured_vehicles->the_post();

            // Get post meta data
            $vehicle_type = get_post_meta(get_the_ID(), 'vehicle_type', true);
            $availability_status = get_post_meta(get_the_ID(), 'availability_status', true);
            $thumbnail = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'large') : get_option('wp_rental_business_logo');
            $excerpt = get_the_excerpt() ? wp_trim_words(get_the_excerpt(), 10) : 'Click to Learn More';

            // Format vehicle type for class and display
            $vehicle_type_class = strtolower(str_replace(' ', '-', $vehicle_type));
            $vehicle_type_name = ucfirst($vehicle_type);

            // Output the vehicle card
?>
            <div class="vehicle-card">
                <span class="vehicle-type <?php echo esc_attr($vehicle_type_class); ?>"><?php echo esc_html($vehicle_type_name); ?></span>
                <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php the_title(); ?>" class="thumbnail">
                <div class="text-content">
                    <h3 class="vehicle-name"><?php the_title(); ?></h3>
                    <p class="clamped-text"><?php echo esc_html($excerpt); ?></p>
                    <div class="divider"></div>
                    <div class="action">
                        <p class="<?php echo esc_attr(strtolower($availability_status == "Under Maintenance" ? "under-maintenance" : $availability_status)); ?>"><?php echo esc_html($availability_status); ?></p>
                        <a href="<?php the_permalink(); ?>">View Details</a>
                    </div>
                </div>
            </div>
<?php
        }

        echo '</div></div>'; // Close the container
    } else {
        echo '<p>No featured vehicles found.</p>';
    }

    wp_reset_postdata(); // Reset the query

    // Return the buffered output
    return ob_get_clean();
}

function featured_water_vehicles_shortcode()
{
    return display_featured_water_vehicles();
}
add_shortcode('featured_vehicles', 'featured_water_vehicles_shortcode');

function get_stripe_api_key()
{
    $stripe_mode = get_option('wp_rental_stripe_mode', 'test');

    if ($stripe_mode == "live") {
        return get_option('wp_rental_stripe_key', '');
    }

    return get_option('wp_rental_stripe_test_key', '');
}

function get_current_post_id()
{
    // Check if we're on a singular post, page, or custom post type
    if (is_singular()) {
        return get_queried_object_id(); // Returns the current post ID
    }
    // Return 0 if not displaying a single post
    return 0;
}

function format_time_to_readable($timestamp)
{
    // $timestamp = '2025-03-21 13:40:29';

    // Using date()
    $unix_timestamp = strtotime($timestamp);
    $readable_date = date('D, M j, Y - h:i A', $unix_timestamp); // Fri, Mar 21, 2025 - 01:40 PM

    return $readable_date;
}


function delete_user_bookings_and_payments($user_id)
{
    global $wpdb;

    // Define table names
    $bookings_table = $wpdb->prefix . 'wp_rental_bookings'; // wp_wp_rental_bookings
    $payments_table = $wpdb->prefix . 'wp_rental_payments'; // wp_wp_rental_payments

    // Validate user ID
    if (!is_numeric($user_id) || $user_id <= 0) {
        return new WP_Error('invalid_user_id', 'Invalid user ID.');
    }

    // Start a transaction to ensure data consistency
    $wpdb->query('START TRANSACTION');

    try {
        // Delete all bookings for the user
        // Due to ON DELETE CASCADE, related payments will also be deleted
        $bookings_deleted = $wpdb->delete(
            $bookings_table,
            ['client_id' => $user_id],
            ['%d'] // client_id is an integer
        );

        if ($bookings_deleted === false) {
            throw new Exception('Failed to delete bookings.');
        }

        // Commit the transaction if everything is successful
        $wpdb->query('COMMIT');

        return [
            'bookings_deleted' => $bookings_deleted,
            'message' => 'Bookings and associated payments deleted successfully.',
        ];
    } catch (Exception $e) {
        // Rollback the transaction in case of an error
        $wpdb->query('ROLLBACK');
        return new WP_Error('delete_failed', $e->getMessage());
    }
}

function delete_payments_for_booking($booking_id)
{
    global $wpdb;

    // Define the payments table name
    $payments_table = $wpdb->prefix . 'wp_rental_payments'; // wp_wp_rental_payments

    // Validate booking ID
    if (!is_numeric($booking_id) || $booking_id <= 0) {
        return new WP_Error('invalid_booking_id', 'Invalid booking ID.');
    }

    // Start a transaction to ensure data consistency
    $wpdb->query('START TRANSACTION');

    try {
        // Delete all payments for the given booking ID
        $payments_deleted = $wpdb->delete(
            $payments_table,
            ['booking_id' => $booking_id],
            ['%d'] // booking_id is an integer
        );

        if ($payments_deleted === false) {
            throw new Exception('Failed to delete payments.');
        }

        // Commit the transaction if everything is successful
        $wpdb->query('COMMIT');

        return [
            'payments_deleted' => $payments_deleted,
            'message' => 'Payments deleted successfully.',
        ];
    } catch (Exception $e) {
        // Rollback the transaction in case of an error
        $wpdb->query('ROLLBACK');
        return new WP_Error('delete_failed', $e->getMessage());
    }
}

function get_client_id_from_payment_id($payment_id)
{
    global $wpdb;

    // Define table names
    $payments_table = $wpdb->prefix . 'wp_rental_payments'; // wp_wp_rental_payments
    $bookings_table = $wpdb->prefix . 'wp_rental_bookings'; // wp_wp_rental_bookings

    // Validate payment ID
    if (!is_numeric($payment_id) || $payment_id <= 0) {
        return new WP_Error('invalid_payment_id', 'Invalid payment ID.');
    }

    // Query to get the booking_id from the payment ID
    $booking_id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT booking_id FROM $payments_table WHERE id = %d",
            $payment_id
        )
    );

    if (!$booking_id) {
        return new WP_Error('no_booking_found', 'No booking found for the given payment ID.');
    }

    // Query to get the client_id from the booking_id
    $client_id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT client_id FROM $bookings_table WHERE id = %d",
            $booking_id
        )
    );

    if (!$client_id) {
        return new WP_Error('no_client_found', 'No client found for the given booking ID.');
    }

    return $client_id;
}
