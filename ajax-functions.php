<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}



function wp_rental_get_booking_details()
{
    write_log("Check 0");
    check_ajax_referer('wvr_admin_nonce', 'nonce'); // Verify nonce

    write_log("Check 1");

    global $wpdb;

    if (!isset($_GET['booking_id'])) {
        wp_send_json_error(['message' => 'Missing booking ID']);
    }

    write_log("Check 2");

    $booking_id = intval($_GET['booking_id']);

    // Get booking details
    $booking = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM {$wpdb->prefix}wp_rental_bookings WHERE id = %d", $booking_id),
        ARRAY_A
    );

    if (!$booking) {
        wp_send_json_error(['message' => 'Booking not found']);
    }

    write_log("Check 3");

    // Get client details
    $client_id = $booking['client_id'];
    $client = get_user_by('ID', $client_id);
    $client_data = [
        'id'      => $client->ID,
        'name'    => $client->display_name,
        'email'   => $client->user_email,
        'phone'   => get_user_meta($client->ID, 'phone_number', true),
        'address' => get_user_meta($client->ID, 'address', true),
    ];

    // Get vehicle details
    $vehicle_id = intval($booking['vehicle_id']);

    $vehicle_data = [
        'id'           => "Unknown",
        'name'         => "Unknown",
        'description'        => "Unknown",
        'type' => "Unknown",
        'availability' => "Unknown"
    ];

    $vehicle = get_post($vehicle_id);

    if ($vehicle && $vehicle->post_type === 'water_vehicle') {
        $vehicle_data = [
            'id'                 => $vehicle->ID,
            'name'               => get_the_title($vehicle->ID),
            'description'        => get_the_excerpt($vehicle->ID),
            'type'               => get_post_meta($vehicle->ID, 'vehicle_type', true) ?: 'N/A',
            'availability'       => get_post_meta($vehicle->ID, 'availability_status', true) ?: 'Unknown',

        ];
    }

    // Return response
    wp_send_json_success([
        'booking' => $booking,
        'client'  => $client_data,
        'vehicle' => $vehicle_data
    ]);
}

add_action('wp_ajax_get_booking_details', 'wp_rental_get_booking_details');

// Common function to update booking status
function update_booking_status($booking_id, $status)
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Permission denied.']);
    }

    global $wpdb;

    if (!$booking_id) {
        wp_send_json_error(['message' => 'Missing booking ID']);
    }

    // Get booking details
    $booking = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM {$wpdb->prefix}wp_rental_bookings WHERE id = %d", $booking_id),
        ARRAY_A
    );

    if (!$booking) {
        wp_send_json_error(['message' => 'Booking not found']);
    }

    // Update the booking status
    $updated = $wpdb->update(
        "{$wpdb->prefix}wp_rental_bookings", // Table name
        ['status' => $status], // Data to update
        ['id' => $booking_id], // Where clause
        ['%s'], // Data format (status is a string)
        ['%d']  // Where format (booking_id is an integer)
    );

    // Check if the update was successful
    if ($updated === false) {
        wp_send_json_error(['message' => 'Failed to update booking status']);
    }

    if ($status == "completed") {
        // Send a Completion Email
        $current_user = wp_get_current_user();
        $client = get_userdata($booking["client_id"]);
        $to = $current_user->user_email;
        $subject = 'Booking Completion';
        $message = "Dear {$client->display_name},\n\nYour booking has been completed. Thank you for choosing us!\n\nBooking ID: {$booking_id}\nVehicle: " . get_the_title($booking["vehicle_id"]) . "\n\nBest regards,\nDSmart Cruise Team";
        wp_mail($to, $subject, $message);

        update_post_meta($booking["vehicle_id"], 'availability_status', "Available");
    }

    wp_send_json_success(['message' => "Booking marked as $status successfully.", 'new_label' => ucfirst($status)]);
}

// 1. Mark Booking as Paid
function wp_rental_mark_as_paid()
{
    check_ajax_referer('wvr_admin_nonce', 'nonce');
    $booking_id = intval($_POST['booking_id']);
    update_booking_status($booking_id, 'paid');
}
add_action('wp_ajax_mark_as_paid', 'wp_rental_mark_as_paid');

// 2. Mark Booking as Completed
function wp_rental_mark_as_completed()
{
    check_ajax_referer('wvr_admin_nonce', 'nonce');
    $booking_id = intval($_POST['booking_id']);
    update_booking_status($booking_id, 'completed');
}
add_action('wp_ajax_mark_as_completed', 'wp_rental_mark_as_completed');

// 3. Cancel Booking
function wp_rental_cancel_booking()
{
    check_ajax_referer('wvr_admin_nonce', 'nonce');
    $booking_id = intval($_POST['booking_id']);
    update_booking_status($booking_id, 'cancelled');
    write_log("Cancel Booking clicked");
}
add_action('wp_ajax_cancel_booking', 'wp_rental_cancel_booking');

// 4. Delete Booking
function wp_rental_delete_booking()
{
    check_ajax_referer('wvr_admin_nonce', 'nonce');
    $booking_id = intval($_POST['booking_id']);

    if (!current_user_can('delete_posts')) {
        wp_send_json_error(['message' => 'You do not have permission to delete this booking.']);
    }



    if (!$booking_id) {
        wp_send_json_error(['message' => 'Invalid booking ID.']);
    }

    global $wpdb;

    if ($booking_id > 0) {
        // Delete all payments for that booking
        $result = delete_payments_for_booking($booking_id);

        if (isset($result["payments_deleted"])) {
            $deleted = $wpdb->delete(
                "{$wpdb->prefix}wp_rental_bookings",
                ['id' => $booking_id],
                ['%d']
            );
        }

        if ($deleted && isset($result["payments_deleted"])) {
            wp_send_json_success(['message' => 'Booking and all associated payments deleted successfully.', 'new_label' => 'Deleted']);
        } else {
            wp_send_json_error(['message' => 'Failed to delete booking.']);
        }
    } else {
        wp_send_json_error(['message' => 'Invalid booking ID.']);
    }



    // write_log("Check 3");

    // wp_delete_post($booking_id, true);
    // wp_send_json_success(['message' => 'Booking deleted successfully.', 'new_label' => 'Deleted']);
}
add_action('wp_ajax_delete_booking', 'wp_rental_delete_booking');


add_action('wp_ajax_get_payment_details', 'wp_rental_get_payment_details');
// add_action('wp_ajax_nopriv_get_payment_details', 'wp_rental_get_payment_details'); // Allow non-logged-in users if necessary

function wp_rental_get_payment_details()
{
    // Check for nonce security if needed
    if (!isset($_POST['payment_id']) || !is_numeric($_POST['payment_id'])) {
        wp_send_json_error(['message' => 'Invalid payment ID.']);
        wp_die();
    }

    global $wpdb;

    $payment_id = intval($_POST['payment_id']);
    // write_log("Payment ID = " . $payment_id);

    $table_payments = $wpdb->prefix . "wp_rental_payments";
    $table_bookings = $wpdb->prefix . "wp_rental_bookings";
    $table_users = $wpdb->prefix . "users";
    $table_posts = $wpdb->prefix . "posts";

    // Fetch payment details including booking and client info
    $payment = $wpdb->get_row($wpdb->prepare(
        "SELECT p.*, 
                b.vehicle_id, 
                b.booking_date, 
                b.status AS booking_status, 
                u.display_name AS client_name, 
                v.post_title AS vehicle_name 
         FROM $table_payments p
         INNER JOIN $table_bookings b ON p.booking_id = b.id
         INNER JOIN $table_users u ON b.client_id = u.ID
         INNER JOIN $table_posts v ON b.vehicle_id = v.ID
         WHERE p.id = %d",
        $payment_id
    ));

    // write_log($payment);

    if (!$payment) {
        wp_send_json_error(['message' => 'Payment not found.']);
        wp_die();
    }

    // Prepare action buttons
    $actions = '<button class="button button-primary process-refund" data-payment-id="' . esc_attr($payment->id) . '">Process Refund</button> ';
    if ($payment->payment_status !== 'completed') {
        $actions .= '<button class="button mark-paid" data-payment-id="' . esc_attr($payment->id) . '">Mark as Paid</button>';
    }

    // Build response
    $response = [
        'order_id'       => $payment->id,
        'client_name'    => esc_html($payment->client_name),
        'vehicle_name'   => esc_html($payment->vehicle_name),
        'booking_date'   => esc_html($payment->booking_date),
        'booking_status' => esc_html($payment->booking_status),
        'amount'         => esc_html($payment->amount),
        'transaction_id'         => esc_html($payment->transaction_id),
        'currency'       => "$", // esc_html($payment->currency),
        'payment_method' => esc_html($payment->payment_method),
        'payment_status' => esc_html($payment->payment_status),
        'actions'        => $actions
    ];

    wp_send_json_success($response);
    wp_die();
}


add_action('wp_ajax_mark_payment_as_paid', 'wp_rental_mark_payment_as_paid');

function wp_rental_mark_payment_as_paid()
{
    if (!isset($_POST['payment_id']) || !is_numeric($_POST['payment_id'])) {
        wp_send_json_error(['message' => 'Invalid payment ID.']);
        wp_die();
    }

    global $wpdb;
    $payment_id = intval($_POST['payment_id']);
    $table_payments = $wpdb->prefix . "wp_rental_payments";

    // Update payment status to completed
    $update = $wpdb->update(
        $table_payments,
        ['payment_status' => 'Paid'],
        ['id' => $payment_id],
        ['%s'],
        ['%d']
    );

    if ($update !== false) {
        wp_send_json_success(['message' => 'Payment marked as paid.']);
    } else {
        wp_send_json_error(['message' => 'Failed to update payment status.']);
    }

    wp_die();
}


add_action('wp_ajax_process_payment_refund', 'wp_rental_process_payment_refund');

function wp_rental_process_payment_refund()
{
    if (!isset($_POST['payment_id']) || !is_numeric($_POST['payment_id'])) {
        wp_send_json_error(['message' => 'Invalid payment ID.']);
        wp_die();
    }

    global $wpdb;
    $payment_id = intval($_POST['payment_id']);
    $table_payments = $wpdb->prefix . "wp_rental_payments";

    // Fetch current payment status
    $payment = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_payments WHERE id = %d", $payment_id));

    if (!$payment) {
        wp_send_json_error(['message' => 'Payment not found.']);
        wp_die();
    }

    if ($payment->payment_status !== 'Paid') {
        wp_send_json_error(['message' => 'Only completed payments can be refunded.']);
        wp_die();
    }

    // REFUND PAYMENT
    $refund = process_payment_refund($payment->transaction_id, $payment->amount * 100);

    // Update payment status to refunded
    $update = false;

    if ($refund) {

        $update = $wpdb->update(
            $table_payments,
            ['payment_status' => 'Refunded'],
            ['id' => $payment_id],
            ['%s'],
            ['%d']
        );
    }


    if ($update !== false && $refund) {
        $table_bookings = $wpdb->prefix . 'wp_rental_bookings';
        // Retrieve booking details
        $booking = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_bookings WHERE id = %d",
            $payment->booking_id
        ));

        $client = get_userdata($booking->client_id);
        $to = $client->user_email;
        $subject = 'Payment Refunded Successfully';

        $message = "Dear {$client->display_name},\n\nA sum of {$payment->amount} from your payment with the Transaction ID of {$payment->transaction_id} have been refunded. Thank you for choosing us!\n\n\n\nBest regards,\nDSmart Cruise Team";

        wp_mail($to, $subject, $message);

        wp_send_json_success(['message' => 'Refund processed successfully.']);
    } else {
        wp_send_json_error(['message' => 'Failed to process refund.']);
    }

    wp_die();
}

// Handle AJAX user registration
function handle_custom_registration()
{
    check_ajax_referer('custom_register_action', 'custom_register_nonce'); // Security check

    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $phone = sanitize_text_field($_POST['phone']);
    $password = $_POST['password'];

    if (empty($name) || empty($email) || empty($phone) || empty($password)) {
        wp_send_json(['success' => false, 'message' => 'All fields are required.']);
    }

    if (!is_email($email)) {
        wp_send_json(['success' => false, 'message' => 'Invalid email format.']);
    }

    if (email_exists($email)) {
        $existing_user = get_user_by('email', $email);
        $is_active = get_user_meta($existing_user->ID, 'is_active', true);

        if ($is_active == 0) {
            wp_send_json(['success' => false, 'message' => 'Your account is inactive. Please check your email for the activation link.']);
        } else {
            wp_send_json(['success' => false, 'message' => 'Email already registered.']);
        }
    }

    // Create inactive user
    $user_id = wp_insert_user([
        'user_login' => $email,
        'user_email' => $email,
        'user_pass'  => $password,
        'display_name' => $name,
        'role'       => 'client',
    ]);

    if (is_wp_error($user_id)) {
        wp_send_json(['success' => false, 'message' => 'Error creating account.']);
    }

    // Store phone number and activation key
    update_user_meta($user_id, 'phone_number', $phone);
    update_user_meta($user_id, 'address', "");
    update_user_meta($user_id, 'total_orders', 0);
    update_user_meta($user_id, 'total_spent', 0);
    $activation_key = wp_generate_password(20, false);
    update_user_meta($user_id, 'activation_key', $activation_key);
    update_user_meta($user_id, 'is_active', 0);

    // Send activation email
    $activation_link = add_query_arg([
        'activation_key' => $activation_key,
        'user_id' => $user_id
    ], get_permalink(get_option('wp_rental_register_page')));

    $subject = 'Activate Your Account';
    $message = "Hi $name,\n\nPlease activate your account using this link:\n\n$activation_link\n\nThank you!";
    wp_mail($email, $subject, $message);

    wp_send_json(['success' => true, 'message' => 'Registration successful! Check your email for activation.']);
}
add_action('wp_ajax_custom_register', 'handle_custom_registration');
add_action('wp_ajax_nopriv_custom_register', 'handle_custom_registration');

// Handle User Activation
function activate_user_account()
{
    $user_id = intval($_POST['user_id']);
    $activation_key = sanitize_text_field($_POST['activation_key']);

    $stored_key = get_user_meta($user_id, 'activation_key', true);

    if ($activation_key !== $stored_key) {
        wp_send_json(['success' => false, 'message' => 'Invalid activation key.']);
    }

    update_user_meta($user_id, 'is_active', 1);
    delete_user_meta($user_id, 'activation_key');

    wp_send_json(['success' => true, 'message' => 'Account activated successfully! Redirecting to login...']);
}
add_action('wp_ajax_activate_user', 'activate_user_account');
add_action('wp_ajax_nopriv_activate_user', 'activate_user_account');


function wp_rental_recover_account()
{
    // write_log("Check 1");
    // Verify nonce for security
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'account_recovery_action')) {
        wp_send_json_error(['message' => 'Invalid request. Please refresh and try again.']);
    }

    // write_log("Check 2");

    // Get user email from AJAX request
    $email = sanitize_email($_POST['email']);

    // Check if user exists
    $user = get_user_by('email', $email);
    if (!$user) {
        wp_send_json_error(['message' => 'No account found with this email.']);
    }

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
    wp_mail($email, $subject, $message);

    wp_send_json_success(['message' => 'A password reset link has been sent to your email.']);
}


function wp_rental_reset_password()
{
    // Verify nonce
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'reset_password_action')) {
        wp_send_json_error(['message' => 'Invalid request. Please refresh and try again.']);
    }

    // Get user ID, token, and new password from AJAX request
    $user_id = intval($_POST['user_id']);
    $token = sanitize_text_field($_POST['token']);
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate passwords
    if ($new_password !== $confirm_password) {
        wp_send_json_error(['message' => 'Passwords do not match.']);
    }

    // Get stored recovery token
    $saved_token = get_user_meta($user_id, 'wp_rental_recovery_token', true);

    // Validate token
    if (!$saved_token || $saved_token !== $token) {
        wp_send_json_error(['message' => 'Invalid or expired recovery link.']);
    }

    // Update password and remove recovery token
    wp_set_password($new_password, $user_id);
    delete_user_meta($user_id, 'wp_rental_recovery_token');

    $user_data = get_userdata($user_id);
    $email = $user_data->user_email;

    // Get login page URL and redirect
    $login_page_id = get_option('wp_rental_login_page');
    $login_url = get_permalink($login_page_id);

    // Send Success with recovery link
    $subject = "Password Reset Successful";
    $message = "Hello, \n\nYour password was reset successfully. Click the link below to login to your account:\n\n$login_url \n\nIf you did not request this, ignore this email.";

    if ($email) {
        wp_mail($email, $subject, $message);
    }


    wp_send_json_success(['message' => 'Password changed successfully. Redirecting to login...', 'redirect' => $login_url]);
}

add_action('wp_ajax_recover_account', 'wp_rental_recover_account');
add_action('wp_ajax_nopriv_recover_account', 'wp_rental_recover_account');

add_action('wp_ajax_reset_password', 'wp_rental_reset_password');
add_action('wp_ajax_nopriv_reset_password', 'wp_rental_reset_password');


// Handle login request
add_action('wp_ajax_custom_login', 'handle_custom_login');
add_action('wp_ajax_nopriv_custom_login', 'handle_custom_login'); // For non-logged-in users

function handle_custom_login()
{
    // Verify nonce for security
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'custom_login_action')) {
        wp_send_json_error(['message' => 'Invalid request. Please refresh and try again.']);
    }

    // Get email and password from the request
    $email = sanitize_email($_POST['email']);
    $password = sanitize_text_field($_POST['password']);

    // Validate inputs
    if (empty($email) || empty($password)) {
        wp_send_json_error(['message' => 'Please fill in all fields.']);
    }

    // Attempt to log the user in
    $user = wp_authenticate($email, $password);

    if (is_wp_error($user)) {
        wp_send_json_error(['message' => 'Invalid email or password.']);
    }

    // Log the user in
    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID);

    // Get the dashboard page URL
    $dashboard_page_id = get_option('wp_rental_dashboard_page');
    $dashboard_url = get_permalink($dashboard_page_id);

    // Send success response
    wp_send_json_success([
        'message' => 'Login successful! Redirecting to dashboard...',
        'redirect' => $dashboard_url,
    ]);
}

// Update Profile
function update_profile()
{
    if (!isset($_POST['data'])) {
        wp_send_json_error('Invalid data.');
    }

    parse_str($_POST['data'], $form_data);

    $user_id = get_current_user_id();
    $user_data = array(
        'ID' => $user_id,
        'display_name' => sanitize_text_field($form_data['name']),
        'user_email' => sanitize_email($form_data['email'])
    );

    wp_update_user($user_data);
    update_user_meta($user_id, 'phone_number', sanitize_text_field($form_data['phone']));
    update_user_meta($user_id, 'address', sanitize_text_field($form_data['address']));

    wp_send_json_success('Profile updated successfully.');
}
add_action('wp_ajax_update_profile', 'update_profile');

// Change Password
function change_password()
{
    if (!isset($_POST['data'])) {
        wp_send_json_error('Invalid data.');
    }

    parse_str($_POST['data'], $form_data);

    $user_id = get_current_user_id();
    $user = get_user_by('ID', $user_id);

    if (!wp_check_password($form_data['current_password'], $user->user_pass, $user_id)) {
        wp_send_json_error('Current password is incorrect.');
    }

    if ($form_data['new_password'] !== $form_data['confirm_password']) {
        wp_send_json_error('New passwords do not match.');
    }

    wp_set_password($form_data['new_password'], $user_id);

    wp_send_json_success('Password changed successfully.');
}
add_action('wp_ajax_change_password', 'change_password');

// Delete Account
// Delete Account (Mark as Inactive and Notify Admin)
function delete_account()
{
    $user_id = get_current_user_id();
    $user = get_user_by('ID', $user_id);

    // Mark the user as inactive
    update_user_meta($user_id, 'account_status', 'inactive');

    // Get user details
    $user_name = $user->display_name;
    $user_email = $user->user_email;
    $user_phone = get_user_meta($user_id, 'phone_number', true);

    // Send email to admin
    $admin_email = get_option('wp_rental_admin_email'); // Admin email address
    $subject = 'User Account Deletion Request';
    $message = "A user has requested to delete their account. Here are their details:\n\n";
    $message .= "Name: $user_name\n";
    $message .= "Email: $user_email\n";
    $message .= "Phone: $user_phone\n";

    if ($admin_email) {
        wp_mail($admin_email, $subject, $message);
    }

    // Log the user out
    wp_logout();

    // Redirect to homepage
    wp_send_json_success('Your account has been marked as inactive. The admin has been notified.');
}
add_action('wp_ajax_delete_account', 'delete_account');

// Submit Complaint
function submit_complaint()
{
    if (!isset($_POST['data'])) {
        wp_send_json_error('Invalid data.');
    }

    parse_str($_POST['data'], $form_data);

    $message_title = sanitize_text_field($form_data['message_title']);
    $message_content = sanitize_textarea_field($form_data['message_content']);

    // Get current user details
    $current_user = wp_get_current_user();
    $sender_name = $current_user->display_name;
    $sender_email = $current_user->user_email;
    $sender_phone = get_user_meta($current_user->ID, 'phone_number', true);

    // Get admin email
    $admin_email = get_option('admin_email');

    // Prepare email content
    $subject = 'New Complaint: ' . $message_title;
    $message = "You have received a new complaint:\n\n";
    $message .= "Sender Name: $sender_name\n";
    $message .= "Sender Email: $sender_email\n";
    $message .= "Sender Phone: $sender_phone\n";
    $message .= "Title:\n$message_title\n";
    $message .= "Message:\n$message_content\n";

    // Send email to admin
    if (wp_mail($admin_email, $subject, $message)) {
        wp_send_json_success('Complaint submitted successfully.');
    } else {
        wp_send_json_error('Failed to send complaint.');
    }
}
add_action('wp_ajax_submit_complaint', 'submit_complaint');
add_action('wp_ajax_nopriv_submit_complaint', 'submit_complaint'); // Allow non-logged-in users to submit complaints

// Get Payment Details
function get_payment_details_for_frontend()
{
    if (!isset($_POST['payment_id'])) {
        wp_send_json_error('Invalid payment ID.');
    }

    global $wpdb;
    // $table_payments = $wpdb->prefix . 'rental_payments';
    // $table_bookings = $wpdb->prefix . 'rental_bookings';

    $table_bookings = $wpdb->prefix . "wp_rental_bookings";
    $table_payments = $wpdb->prefix . "wp_rental_payments";

    $payment_id = intval($_POST['payment_id']);
    $payment = $wpdb->get_row($wpdb->prepare(
        "SELECT p.*, b.vehicle_id 
         FROM $table_payments p
         JOIN $table_bookings b ON p.booking_id = b.id
         WHERE p.id = %d",
        $payment_id
    ));

    if (!$payment) {
        wp_send_json_error('Error Fetching Payment. Please try again later.');
    }

    // Get vehicle name
    $vehicle_name = get_the_title($payment->vehicle_id);

    // Prepare details HTML
    $html = "
        <p><strong>Booking ID:</strong> {$payment->booking_id}</p>
        <p><strong>Amount:</strong> " . number_format($payment->amount, 2) . "</p>
        <p><strong>Payment Method:</strong> {$payment->payment_method}</p>
        <p><strong>Status:</strong> {$payment->payment_status}</p>
        <p><strong>Vehicle:</strong> {$vehicle_name}</p>
    ";

    write_log($html);

    wp_send_json_success($html);
}
add_action('wp_ajax_get_payment_details_for_frontend', 'get_payment_details_for_frontend');

// // Process Stripe Payment
// function process_stripe_payment() {
//     if (!isset($_POST['payment_method_id']) || !isset($_POST['payment_id'])) {
//         wp_send_json_error('Invalid data.');
//     }

//     require_once 'path/to/stripe-php/init.php'; // Include Stripe PHP library

//     $stripeSecretKey = get_option('stripe_secret_key');
//     \Stripe\Stripe::setApiKey($stripeSecretKey);

//     try {
//         // Create a PaymentIntent
//         $paymentIntent = \Stripe\PaymentIntent::create([
//             'amount' => $amount * 100, // Amount in cents
//             'currency' => 'usd',
//             'payment_method' => $_POST['payment_method_id'],
//             'confirm' => true,
//         ]);

//         // Update payment status in the database
//         global $wpdb;
//         $table_payments = $wpdb->prefix . 'rental_payments';
//         $wpdb->update(
//             $table_payments,
//             array('payment_status' => 'Paid'),
//             array('id' => intval($_POST['payment_id']))
//         );

//         wp_send_json_success('Payment successful.');
//     } catch (\Stripe\Exception\ApiErrorException $e) {
//         wp_send_json_error($e->getMessage());
//     }
// }
// add_action('wp_ajax_process_stripe_payment', 'process_stripe_payment');

// // Download Invoice
// function download_invoice() {
//     if (!isset($_GET['payment_id'])) {
//         wp_die('Invalid payment ID.');
//     }

//     global $wpdb;
//     $table_payments = $wpdb->prefix . 'rental_payments';
//     $table_bookings = $wpdb->prefix . 'rental_bookings';

//     $payment_id = intval($_GET['payment_id']);
//     $payment = $wpdb->get_row($wpdb->prepare(
//         "SELECT p.*, b.vehicle_id 
//          FROM $table_payments p
//          JOIN $table_bookings b ON p.booking_id = b.id
//          WHERE p.id = %d",
//         $payment_id
//     ));

//     if (!$payment) {
//         wp_die('Payment not found.');
//     }

//     // Get vehicle name
//     $vehicle_name = get_the_title($payment->vehicle_id);

//     // Generate PDF invoice (using a library like FPDF or TCPDF)
//     require_once 'path/to/fpdf/fpdf.php'; // Include FPDF library

//     $pdf = new FPDF();
//     $pdf->AddPage();
//     $pdf->SetFont('Arial', 'B', 16);

//     // Add business logo
//     $business_logo = get_option('wp_rental_business_logo');
//     if ($business_logo) {
//         $pdf->Image($business_logo, 10, 10, 50);
//     }

//     // Add invoice details
//     $pdf->Cell(40, 10, 'Invoice');
//     $pdf->Ln(20);
//     $pdf->Cell(40, 10, 'Booking ID: ' . $payment->booking_id);
//     $pdf->Ln(10);
//     $pdf->Cell(40, 10, 'Amount: ' . number_format($payment->amount, 2));
//     $pdf->Ln(10);
//     $pdf->Cell(40, 10, 'Vehicle: ' . $vehicle_name);
//     $pdf->Ln(10);
//     $pdf->Cell(40, 10, 'Payment Method: ' . $payment->payment_method);
//     $pdf->Ln(10);
//     $pdf->Cell(40, 10, 'Status: ' . $payment->payment_status);

//     // Output the PDF
//     $pdf->Output('D', 'invoice_' . $payment_id . '.pdf');
//     exit;
// }
// add_action('wp_ajax_download_invoice', 'download_invoice');

// Get Booking Details
function get_booking_details_for_frontend()
{
    if (!isset($_POST['booking_id'])) {
        wp_send_json_error('Invalid booking ID.');
    }

    global $wpdb;
    $table_bookings = $wpdb->prefix . "wp_rental_bookings";
    $table_payments = $wpdb->prefix . "wp_rental_payments";

    $booking_id = intval($_POST['booking_id']);
    $booking = $wpdb->get_row($wpdb->prepare(
        "SELECT b.*, p.payment_method 
         FROM $table_bookings b
         LEFT JOIN $table_payments p ON b.id = p.booking_id
         WHERE b.id = %d",
        $booking_id
    ));

    if (!$booking) {
        wp_send_json_error('Booking not found.');
    }

    // Get vehicle name
    $vehicle_name = get_the_title($booking->vehicle_id) ? get_the_title($booking->vehicle_id) : "Vehicle Unavailable";

    // Prepare details HTML
    $html = "
        <p><strong>Vehicle Name:</strong> {$vehicle_name}</p>
        <p><strong>Booking Date:</strong> " . date('F j, Y', strtotime($booking->booking_date)) . "</p>
        <p><strong>Status:</strong> " . ucfirst($booking->status) . "</p>
        <p><strong>Payment Method:</strong> {$booking->payment_method}</p>
        <p><strong>Package:</strong> {$booking->package}</p>
    ";

    wp_send_json_success($html);
}
add_action('wp_ajax_get_booking_details_for_frontend', 'get_booking_details_for_frontend');


add_action('wp_ajax_check_user_logged_in', 'check_user_logged_in');
add_action('wp_ajax_nopriv_check_user_logged_in', 'check_user_logged_in');

function check_user_logged_in()
{
    wp_send_json(['logged_in' => is_user_logged_in()]);
}

add_action('wp_ajax_user_login', 'user_login');
add_action('wp_ajax_nopriv_user_login', 'user_login');

function user_login()
{
    $email = $_POST['user_email'];
    $password = $_POST['password'];
    $user = wp_authenticate($email, $password);

    if (is_wp_error($user)) {
        wp_send_json_error('Invalid email or password.');
    } else {
        wp_set_auth_cookie($user->ID);
        wp_send_json_success();
    }
}


add_action('wp_ajax_create_booking', 'create_booking');
add_action('wp_ajax_nopriv_create_booking', 'create_booking');

function create_booking()
{
    if (!is_user_logged_in()) {
        wp_send_json_error('User not logged in.');
    }

    global $wpdb;
    $table_bookings = $wpdb->prefix . 'wp_rental_bookings';
    $table_payments = $wpdb->prefix . 'wp_rental_payments';

    $vehicle_id = get_the_ID();
    $client_id = get_current_user_id();
    $booking_date = current_time('mysql');
    $status = 'pending';

    // Insert booking
    $wpdb->insert($table_bookings, [
        'vehicle_id' => $vehicle_id,
        'client_id' => $client_id,
        'booking_date' => $booking_date,
        'status' => $status
    ]);

    $booking_id = $wpdb->insert_id;

    // Insert payment
    $wpdb->insert($table_payments, [
        'booking_id' => $booking_id,
        'amount' => 0, // Replace with actual amount
        'payment_method' => 'unset',
        'transaction_id' => 'unset',
        'payment_status' => 'Pending'
    ]);

    wp_send_json_success(['booking_id' => $booking_id]);
}

add_action('wp_ajax_create_payment_intent', 'create_payment_intent');
add_action('wp_ajax_nopriv_create_payment_intent', 'create_payment_intent');

function create_payment_intent()
{
    // require_once 'vendor/autoload.php';

    \Stripe\Stripe::setApiKey(get_stripe_api_key());

    $amount = $_POST['amount']; // Replace with actual amount
    $currency = 'usd';
    $payment_method = $_POST['payment_method'];

    try {
        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $amount,
            'currency' => $currency,
            'payment_method_types' => [$payment_method],
        ]);

        wp_send_json_success(['client_secret' => $paymentIntent->client_secret]);
    } catch (\Stripe\Exception\ApiErrorException $e) {
        wp_send_json_error($e->getMessage());
    }
}

add_action('rest_api_init', function () {
    register_rest_route('stripe/v1', '/webhook', array(
        'methods' => 'POST',
        'callback' => 'handle_stripe_webhook',
    ));
});

function handle_stripe_webhook($request)
{
    $payload = $request->get_body();
    $event = \Stripe\Event::constructFrom(json_decode($payload, true));

    switch ($event->type) {
        case 'payment_intent.succeeded':
            // Update payment and booking status
            break;
        case 'payment_intent.payment_failed':
            // Update payment status to failed
            break;
    }

    return new WP_REST_Response('Webhook received', 200);
}


add_action('wp_ajax_create_checkout_session', 'create_checkout_session');
add_action('wp_ajax_nopriv_create_checkout_session', 'create_checkout_session');

function create_checkout_session()
{
    if (!is_user_logged_in()) {
        wp_send_json_error('User not logged in.');
    }

    $amount = $_POST['amount']; // Amount in cents
    $currency = $_POST['currency'];
    $vehicle_id = $_POST['vehicle_id'];
    // $package = $_POST['package'];
    $dashboard_page = get_option('wp_rental_dashboard_page', '');
    $current_user = wp_get_current_user();
    $email = $current_user->user_email;

    global $wpdb;
    $table_bookings = $wpdb->prefix . 'wp_rental_bookings';
    $table_payments = $wpdb->prefix . 'wp_rental_payments';

    $client_id = $current_user->ID;
    $booking_date = current_time('mysql');
    $status = 'pending';
    // $amount = floatval($_POST['amount']); // Amount in dollars
    $package = sanitize_text_field($_POST['package']);
    $packageText = sanitize_text_field($_POST['packageText']);

    // Insert booking
    $wpdb->insert($table_bookings, [
        'vehicle_id' => $vehicle_id,
        'client_id' => $client_id,
        'booking_date' => $booking_date,
        'status' => $status,
        'package' => $packageText
    ]);

    if ($wpdb->last_error) {
        error_log('Bookings table error: ' . $wpdb->last_error); // Log bookings table error
    }

    $booking_id = $wpdb->insert_id;

    $unset_value_id = 'unset_' . $booking_id . '_' . time();

    // Insert payment
    $wpdb->insert($table_payments, [
        'booking_id' => $booking_id,
        'amount' => $amount / 100,
        'payment_method' => $unset_value_id,
        'transaction_id' => $unset_value_id,
        'payment_status' => 'Pending'
    ]);

    if ($wpdb->last_error) {
        error_log('Bookings table error: ' . $wpdb->last_error); // Log bookings table error
    }

    // require_once 'vendor/autoload.php';
    \Stripe\Stripe::setApiKey(get_stripe_api_key());

    write_log("Creating Stripe Checkout Session");

    try {
        $session = \Stripe\Checkout\Session::create([
            "payment_method_types" => [
                "card",
                "affirm",
                "afterpay_clearpay",
                "alipay",
                "cashapp",
                "klarna",
                "link",
                "us_bank_account",
                "amazon_pay"

            ],
            'line_items' => [[
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => [
                        'name' => 'Vehicle Rental - ' . get_the_title($vehicle_id),
                        'description' => $package,
                    ],
                    'unit_amount' => $amount,
                ],
                'quantity' => 1,
            ]],
            "customer_email" => $email,
            'mode' => 'payment',
            'success_url' => get_permalink($dashboard_page) . '/booking-details?booking_id=' . $booking_id . '&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => get_permalink($vehicle_id),

        ]);

        write_log($session);

        wp_send_json_success(['url' => $session->url]);
    } catch (\Stripe\Exception\ApiErrorException $e) {
        wp_send_json_error($e->getMessage());
    }
}

function process_payment_refund($transaction_id, $amount = null)
{
    try {
        \Stripe\Stripe::setApiKey(get_stripe_api_key());
        // Create a refund
        $refund = \Stripe\Refund::create([
            'payment_intent' => $transaction_id, // Use the PaymentIntent ID
            'amount' => $amount, // Amount in cents (optional, refunds full amount if null)
            'reason' => "requested_by_customer", // Reason for refund (optional)
        ]);

        // Return the refund object
        return $refund;
    } catch (\Stripe\Exception\ApiErrorException $e) {
        // Handle errors
        error_log('Stripe Refund Error: ' . $e->getMessage());
        return false;
    }
}
