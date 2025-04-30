<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WP_Rental_Notifications {
    public static function send_booking_confirmation($customer_email, $vehicle_name) {
        $subject = "Booking Confirmation - $vehicle_name";
        $message = "Hello,\n\nYour booking for $vehicle_name has been received and is pending approval.";
        wp_mail($customer_email, $subject, $message);
    }

    public static function notify_admin_new_booking($booking_id) {
        $admin_email = get_option('admin_email');
        $subject = "New Rental Booking #$booking_id";
        $message = "A new booking has been made. Please check the admin dashboard.";
        wp_mail($admin_email, $subject, $message);
    }
}
