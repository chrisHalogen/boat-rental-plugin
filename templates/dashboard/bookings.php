<?php
global $wpdb;
$table_bookings = $wpdb->prefix . "wp_rental_bookings";
$table_payments = $wpdb->prefix . "wp_rental_payments";

// Get current user ID
$user_id = get_current_user_id();

// Pagination
$per_page = 10;
$current_page = max(1, isset($_GET['pagenumber']) ? intval($_GET['pagenumber']) : 1);
$offset = ($current_page - 1) * $per_page;

// Filters
$search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
$date = isset($_GET['date']) ? sanitize_text_field($_GET['date']) : '';
$payment_method = isset($_GET['payment_method']) ? sanitize_text_field($_GET['payment_method']) : '';

// Base query
$query = "SELECT b.*, p.payment_method 
          FROM $table_bookings b
          LEFT JOIN $table_payments p ON b.id = p.booking_id
          LEFT JOIN {$wpdb->posts} v ON b.vehicle_id = v.ID
          WHERE b.client_id = $user_id";

// Apply filters
if (!empty($search)) {
    $query .= $wpdb->prepare(" AND v.post_title LIKE %s", '%' . $wpdb->esc_like($search) . '%');
}
if (!empty($status)) {
    $query .= $wpdb->prepare(" AND b.status = %s", $status);
}
if (!empty($date)) {
    $query .= $wpdb->prepare(" AND DATE(b.booking_date) = %s", $date);
}
if (!empty($payment_method)) {
    $query .= $wpdb->prepare(" AND p.payment_method = %s", $payment_method);
}

// Count total rows for pagination
$total_query = "SELECT COUNT(1) FROM ($query) AS total";
$total = $wpdb->get_var($total_query);

// Fetch bookings
$query .= $wpdb->prepare(" ORDER BY b.booking_date DESC LIMIT %d OFFSET %d", $per_page, $offset);
$bookings = $wpdb->get_results($query);
?>

<div class="bookings-container" id="booking-container">
    <h1>Bookings</h1>

    <!-- Browse Our Fleet Link -->
    <div class="browse-fleet">
        <a href="<?php echo get_post_type_archive_link('water_vehicle'); ?>" class="btn-primary">Browse Our Fleet</a>
    </div>

    <!-- Filters -->
    <div class="filters">
        <form method="get" action="">
            <input type="text" name="search" placeholder="Search by Vehicle Name" value="<?php echo esc_attr($search); ?>">
            <select name="status">
                <option value="">All Statuses</option>
                <option value="pending" <?php selected($status, 'pending'); ?>>Pending</option>
                <option value="confirmed" <?php selected($status, 'confirmed'); ?>>Confirmed</option>
                <option value="completed" <?php selected($status, 'completed'); ?>>Completed</option>
                <option value="cancelled" <?php selected($status, 'cancelled'); ?>>Cancelled</option>
            </select>
            <select name="payment_method">
                <option value="">All Payment Methods</option>
                <option value="Credit Card" <?php selected($payment_method, 'Credit Card'); ?>>Credit Card</option>
                <option value="PayPal" <?php selected($payment_method, 'PayPal'); ?>>PayPal</option>
                <option value="Bank Transfer" <?php selected($payment_method, 'Bank Transfer'); ?>>Bank Transfer</option>
            </select>
            <input type="date" name="date" value="<?php echo esc_attr($date); ?>">
            <button type="submit" class="btn-primary">Filter</button>
        </form>
    </div>

    <!-- Bookings Table -->
    <?php if (!empty($bookings)) : ?>
        <div class="bookings-table-container">
            <table class="bookings-history">
                <thead>
                    <tr>
                        <th>Vehicle Name</th>
                        <th>Booking Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking) : ?>
                        <tr>
                            <td><?php echo esc_html(get_the_title($booking->vehicle_id) ? get_the_title($booking->vehicle_id) : "Vehicle Unavailable"); ?></td>
                            <td><?php echo esc_html(date('F j, Y', strtotime($booking->booking_date))); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo esc_attr($booking->status); ?>">
                                    <?php echo esc_html(ucfirst($booking->status)); ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn-view-details" data-booking-id="<?php echo esc_attr($booking->id); ?>">View</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php else : ?>
        <div class="no-bookings-found">
            <p>No bookings found.</p>
        </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($total > $per_page) : ?>
        <div class="archive-pagination">
            <?php
            echo paginate_links(array(
                'base' => add_query_arg('pagenumber', '%#%'),
                'format' => '',
                'prev_text' => __('&laquo; Previous'),
                'next_text' => __('Next &raquo;'),
                'total' => ceil($total / $per_page),
                'current' => $current_page,
            ));
            ?>
        </div>
    <?php endif; ?>
</div>

<!-- View Details Popup -->
<div id="booking-details-popup" class="bookings-popup">
    <div class="popup-content">
        <h2>Booking Details</h2>
        <div id="booking-details-content"></div>
        <div class="popup-actions">
            <button id="close-popup-btn" class="btn-danger">Close</button>
        </div>
    </div>
</div>