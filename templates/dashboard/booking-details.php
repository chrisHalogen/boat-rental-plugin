<?php
/*
Template Name: Booking Details
*/

// require_once 'vendor/autoload.php';
\Stripe\Stripe::setApiKey(get_stripe_api_key());

$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
$session_id = isset($_GET['session_id']) ? sanitize_text_field($_GET['session_id']) : '';

global $wpdb;
$table_bookings = $wpdb->prefix . 'wp_rental_bookings';
$table_payments = $wpdb->prefix . 'wp_rental_payments';

// Retrieve booking and payment details
$booking = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_bookings WHERE id = %d", $booking_id));
$payment = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_payments WHERE booking_id = %d", $booking_id));

$dashboard_page_id = get_option('wp_rental_dashboard_page');

// Retrieve the Checkout Session
$session = \Stripe\Checkout\Session::retrieve($session_id);

if ($session_id && $booking && $booking->processed == 0) {
    try {


        // Update booking and payment status
        $wpdb->update($table_bookings, [
            'status' => 'paid',
            'processed' => 1 // Mark the booking as processed
        ], ['id' => $booking_id]);

        $method = $session->payment_method_types[0] == "card" ? "credit_card" : $session->payment_method_types[0];

        $wpdb->update($table_payments, [
            'payment_status' => 'Paid',
            'transaction_id' => $session->payment_intent,
            'payment_method' => $method,
            'payment_date' => current_time('mysql')
        ], ['booking_id' => $booking_id]);

        // Set the Vehicle to rented
        update_post_meta($booking->vehicle_id, 'availability_status', "Rented");

        // Send email notification
        $user = get_userdata($booking->client_id);
        $to = $user->user_email;
        $subject = 'Booking Confirmation';
        $message = "Dear {$user->display_name},\n\nYour booking has been confirmed. Thank you for choosing us!\n\nBooking ID: {$booking_id}\nVehicle: " . get_the_title($booking->vehicle_id) . "\nAmount: $" . $payment->amount . "\n\nBest regards,\nDSmart Cruise Team";
        wp_mail($to, $subject, $message);

        // Display success message
        // echo '<div class="booking-success-notice">Your booking was successful! A confirmation email has been sent to your email address.</div>';
    } catch (\Stripe\Exception\ApiErrorException $e) {
        // Handle errors
        echo '<p>Error retrieving payment details: ' . $e->getMessage() . '</p>';
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'download_receipt') {
    $booking_id = intval($_GET['booking_id']);
    $payment_id = intval($_GET['payment_id']);
    $client_id = intval($_GET['client_id']);

    $pdfService = new PDFService();
    $pdfService->generatePDF($client_id, $booking_id, $payment_id);

    // generate_receipt_pdf_dom($booking_id, $payment_id, $client_id);
    // exit;
}

$payment->transaction_id = $session->payment_intent;
$payment->payment_method = $session->payment_method_types[0];
// $payment->payment_date = current_time('mysql');

// Retrieve vehicle details
$vehicle = get_post($booking->vehicle_id);
$thumbnail = has_post_thumbnail($vehicle->ID) ? get_the_post_thumbnail_url($vehicle->ID, 'large') : get_option('wp_rental_business_logo');
$vehicle_features = get_post_meta($vehicle->ID, 'vehicle_features', true);
?>

<div class="booking-details">
    <h1>Booking Details</h1>
    <div class="vehicle-details">
        <img src="<?php echo esc_url($thumbnail); ?>" alt="Vehicle Thumbnail">
        <h2><?php echo esc_html($vehicle->post_title); ?></h2>
        <p><?php echo esc_html($vehicle->post_excerpt); ?></p>
        <p><strong>Vehicle Type:</strong> <?php echo esc_html(get_post_meta($vehicle->ID, 'vehicle_type', true)); ?></p>
    </div>
    <div class="payment-details">
        <h2>Payment Details</h2>
        <p><strong>Package:</strong> <?php echo esc_html($booking->package); ?></p>
        <p><strong>Transaction ID:</strong> <?php echo esc_html($payment->transaction_id); ?></p>
        <p><strong>Amount:</strong> $<?php echo esc_html($payment->amount); ?></p>
        <p><strong>Payment Method:</strong> <?php echo esc_html($payment->payment_method); ?></p>
        <p><strong>Payment Time:</strong> <?php echo esc_html(format_time_to_readable($payment->payment_date)); ?></p>
    </div>
    <div class="quick-links">
        <h2>Quick Links</h2>
        <div class="links">
            <a href="<?php echo get_permalink($dashboard_page_id); ?>bookings" class="btn-secondary">Bookings</a>
            <a href="<?php echo get_permalink($dashboard_page_id); ?>payments" class="btn-secondary">Payments</a>
            <a href="<?php echo get_post_type_archive_link('water_vehicle'); ?>" class="btn-secondary" target="_blank">Browse Our Fleet</a>
            <a href="<?php echo add_query_arg(['action' => 'download_receipt', 'booking_id' => $booking_id, 'payment_id' => $payment->id, 'client_id' => $booking->client_id]); ?>" class="btn-secondary" target="_blank">Download Receipt</a>
            <a href="<?php echo get_permalink($booking->vehicle_id); ?>" class="btn-secondary" target="_blank">Visit Vehicle Page</a>
        </div>
    </div>
</div>