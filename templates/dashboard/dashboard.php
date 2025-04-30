<?php
global $wpdb;
$table_bookings = $wpdb->prefix . "wp_rental_bookings";
$table_payments = $wpdb->prefix . "wp_rental_payments";

// Get current user ID
$user_id = get_current_user_id();

// Get key metrics
$total_bookings = $wpdb->get_var(
    $wpdb->prepare("SELECT COUNT(*) FROM $table_bookings WHERE client_id = %d", $user_id)
);
$total_payments = $wpdb->get_var(
    $wpdb->prepare("SELECT SUM(amount) FROM $table_payments p JOIN $table_bookings b ON p.booking_id = b.id WHERE b.client_id = %d AND p.payment_status = 'Paid'", $user_id)
);
$pending_payments = $wpdb->get_var(
    $wpdb->prepare("SELECT COUNT(*) FROM $table_payments p JOIN $table_bookings b ON p.booking_id = b.id WHERE b.client_id = %d AND p.payment_status = 'Pending'", $user_id)
);
$upcoming_bookings = $wpdb->get_var(
    $wpdb->prepare("SELECT COUNT(*) FROM $table_bookings WHERE client_id = %d AND status = 'confirmed' AND booking_date >= CURDATE()", $user_id)
);

$active_bookings = $wpdb->get_var(
    $wpdb->prepare("SELECT COUNT(*) FROM $table_bookings WHERE client_id = %d AND status = 'confirmed'", $user_id)
);

// Get recent payments (last 5)
$recent_payments = $wpdb->get_results(
    $wpdb->prepare("SELECT p.* FROM $table_payments p JOIN $table_bookings b ON p.booking_id = b.id WHERE b.client_id = %d ORDER BY p.payment_date DESC LIMIT 5", $user_id)
);

// Get current bookings
$current_bookings = $wpdb->get_results(
    $wpdb->prepare("SELECT b.*, v.post_title AS vehicle_name FROM $table_bookings b LEFT JOIN {$wpdb->posts} v ON b.vehicle_id = v.ID WHERE b.client_id = %d ORDER BY b.booking_date DESC LIMIT 5", $user_id)
);

// Get payment status overview
$payment_status_overview = $wpdb->get_results(
    $wpdb->prepare("SELECT payment_status, COUNT(*) AS count FROM $table_payments p JOIN $table_bookings b ON p.booking_id = b.id WHERE b.client_id = %d GROUP BY payment_status", $user_id)
);

// Get personalized vehicle recommendations
// $user_top_vehicles = $wpdb->get_results(
//     $wpdb->prepare("SELECT v.ID, v.post_title, COUNT(*) AS booking_count FROM $table_bookings b JOIN {$wpdb->posts} v ON b.vehicle_id = v.ID WHERE b.client_id = %d GROUP BY v.ID ORDER BY booking_count DESC LIMIT 2", $user_id)
// );
// $platform_top_vehicles = $wpdb->get_results(
//     "SELECT v.ID, v.post_title, COUNT(*) AS booking_count FROM $table_bookings b JOIN {$wpdb->posts} v ON b.vehicle_id = v.ID GROUP BY v.ID ORDER BY booking_count DESC LIMIT 2"
// );
// $recommended_vehicles = array_merge($user_top_vehicles, $platform_top_vehicles);
// Get personalized vehicle recommendations
$user_top_vehicles = $wpdb->get_results(
    $wpdb->prepare("SELECT v.ID, v.post_title, COUNT(*) AS booking_count FROM $table_bookings b JOIN {$wpdb->posts} v ON b.vehicle_id = v.ID WHERE b.client_id = %d GROUP BY v.ID ORDER BY booking_count DESC LIMIT 2", $user_id)
);
$platform_top_vehicles = $wpdb->get_results(
    "SELECT v.ID, v.post_title, COUNT(*) AS booking_count FROM $table_bookings b JOIN {$wpdb->posts} v ON b.vehicle_id = v.ID GROUP BY v.ID ORDER BY booking_count DESC LIMIT 2"
);

// Combine the arrays and filter duplicates
$recommended_vehicles = $user_top_vehicles; // Start with user's top vehicles
$vehicle_ids = array_column($user_top_vehicles, 'ID'); // Track vehicle IDs

foreach ($platform_top_vehicles as $vehicle) {
    if (!in_array($vehicle->ID, $vehicle_ids) && count($recommended_vehicles) < 4) {
        $recommended_vehicles[] = $vehicle;
        $vehicle_ids[] = $vehicle->ID;
    }
}

// Get page IDs for quick links
$payments_page_id = get_option('wp_rental_payments_page');
$fleet_page_id = get_option('wp_rental_fleet_page');
$support_page_id = get_option('wp_rental_support_page');
$account_page_id = get_option('wp_rental_account_page');

// Get the dashboard page ID from the database
$dashboard_page_id = get_option('wp_rental_dashboard_page');
?>

<div class="dashboard-container">
    <!-- Welcome Message -->
    <div class="welcome-message">
        <h1>Welcome back, <?php echo esc_html(wp_get_current_user()->display_name); ?>!</h1>
        <p>Here's what's happening with your account.</p>
    </div>

    <!-- Key Metrics -->
    <div class="key-metrics">
        <div class="metric-card">
            <span class="metric-value"><?php echo esc_html($total_bookings); ?></span>
            <span class="metric-label">Total Bookings</span>
        </div>
        <div class="metric-card">
            <span class="metric-value"><?php echo esc_html($total_payments ? number_format($total_payments, 2) : "0.00"); ?></span>
            <span class="metric-label">Total Payments</span>
        </div>
        <div class="metric-card">
            <span class="metric-value"><?php echo esc_html($pending_payments); ?></span>
            <span class="metric-label">Pending Payments</span>
        </div>
        <div class="metric-card">
            <span class="metric-value"><?php echo esc_html($active_bookings); ?></span>
            <span class="metric-label">Active Bookings</span>
        </div>
    </div>

    <!-- Recent Payments -->
    <div class="recent-payments">
        <h2>Recent Payments</h2>
        <table>
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Amount</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recent_payments)) : ?>
                    <?php foreach ($recent_payments as $payment) : ?>
                        <tr>
                            <td><?php echo esc_html($payment->transaction_id); ?></td>
                            <td><?php echo esc_html(number_format($payment->amount, 2)); ?></td>
                            <td><?php echo esc_html($payment->payment_method); ?></td>
                            <td><?php echo esc_html($payment->payment_status); ?></td>
                            <td><?php echo esc_html(date('F j, Y', strtotime($payment->payment_date))); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="5">No recent payments found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <a href="<?php echo esc_url(get_permalink($payments_page_id)); ?>" class="btn-primary" style="margin-bottom: 1rem">View All Payments</a>
    </div>

    <!-- Current Bookings -->
    <div class="current-bookings">
        <h2>Current Bookings</h2>
        <table>
            <thead>
                <tr>
                    <th>Vehicle Name</th>
                    <th>Booking Date</th>
                    <th>Status</th>
                    <!-- <th>Actions</th> -->
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($current_bookings)) : ?>
                    <?php foreach ($current_bookings as $booking) : ?>
                        <tr>
                            <td><?php echo esc_html($booking->vehicle_name ? $booking->vehicle_name : "Vehicle Unavailable"); ?></td>
                            <td><?php echo esc_html(date('F j, Y', strtotime($booking->booking_date))); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo esc_attr($booking->status); ?>">
                                    <?php echo esc_html(ucfirst($booking->status)); ?>
                                </span>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="4">No current bookings found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <a href="<?php echo esc_url(get_permalink($dashboard_page_id)); ?>/bookings" class="btn-primary" style="margin-bottom: 1rem">View All Bookings</a>
    </div>

    <!-- Payment Status Overview -->
    <div class="payment-status-overview">
        <h2>Payment Status Overview</h2>
        <canvas id="payment-status-chart"></canvas>
    </div>

    <!-- Quick Links -->
    <div class="quick-links">
        <h2>Quick Links</h2>
        <div class="links">
            <a href="<?php echo get_permalink($dashboard_page_id); ?>/payments" class="btn-secondary">Payments</a>
            <a href="<?php echo get_post_type_archive_link('water_vehicle'); ?>" class="btn-secondary" target="_blank">Browse Our Fleet</a>
            <a href="<?php echo get_permalink($dashboard_page_id); ?>/support" class="btn-secondary">Support</a>
            <a href="<?php echo get_permalink($dashboard_page_id); ?>/account" class="btn-secondary">Account Settings</a>
        </div>
    </div>

    <!-- Recommended Vehicles -->
    <div class="recommended-vehicles">
        <h2>Recommended for You</h2>
        <div class="vehicle-grid">
            <?php if (!empty($recommended_vehicles)) : ?>
                <?php foreach ($recommended_vehicles as $vehicle) : ?>
                    <div class="vehicle-card">
                        <!-- Vehicle Thumbnail -->
                        <div class="vehicle-thumbnail">
                            <?php if (has_post_thumbnail($vehicle->ID)) : ?>
                                <?php echo get_the_post_thumbnail($vehicle->ID, 'medium'); ?>
                            <?php else : ?>
                                <img src="<?php echo plugin_dir_url(__FILE__); ?>../../assets/images/dsclogo.png" alt="Default Vehicle Image">
                            <?php endif; ?>
                        </div>
                        <h3><?php echo esc_html($vehicle->post_title); ?></h3>
                        <a href="<?php echo esc_url(get_permalink($vehicle->ID)); ?>" class="btn-primary">View Details</a>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p>No recommended vehicles found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- View Details Popup -->
<div id="booking-details-popup" class="popup-home">
    <div class="popup-content">
        <h2>Booking Details</h2>
        <div id="booking-details-content"></div>
        <div class="popup-actions">
            <button id="close-popup-btn" class="btn-danger">Close</button>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Payment Status Chart
    const paymentStatusData = {
        labels: <?php echo json_encode(array_column($payment_status_overview, 'payment_status')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($payment_status_overview, 'count')); ?>,
            backgroundColor: ['#0073e6', '#00cc66', '#ff3333'],
        }]
    };

    const paymentStatusChart = new Chart(document.getElementById('payment-status-chart'), {
        type: 'pie',
        data: paymentStatusData,
    });

    // View Details Popup
    jQuery(document).ready(function($) {
        $('.btn-view-details').on('click', function() {
            const bookingId = $(this).data('booking-id');

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'get_booking_details',
                    booking_id: bookingId
                },
                success: function(response) {
                    if (response.success) {
                        $('#booking-details-content').html(response.data);
                        $('#booking-details-popup').fadeIn();
                    } else {
                        alert('Error: ' + response.data);
                    }
                }
            });
        });

        // Close Popup
        $('#close-popup-btn').on('click', function() {
            $('#booking-details-popup').fadeOut();
        });
    });
</script>