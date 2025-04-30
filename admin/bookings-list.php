<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

$paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$per_page = 20;

$table_bookings = $wpdb->prefix . "wp_rental_bookings";
$table_users = $wpdb->prefix . "users";
$table_posts = $wpdb->prefix . "posts";

// Step 1: Get the total number of records
$total_records = $wpdb->get_var(
    "SELECT COUNT(*) 
    FROM $table_bookings 
    b
    INNER JOIN $table_users u ON b.client_id = u.ID
    INNER JOIN $table_posts v ON b.vehicle_id = v.ID
    "
);

// Step 2: Calculate the total number of pages
$total_pages = ceil($total_records / $per_page);

// Step 3: Ensure the current page doesn't exceed the total number of pages
if ($paged > $total_pages) {
    $paged = $total_pages;
}

// Step 4: Calculate the correct OFFSET
$offset = ($paged - 1) * $per_page;

// Step 5: Fetch bookings with pagination
$bookings = $wpdb->get_results($wpdb->prepare(
    "SELECT b.*, u.display_name AS client_name, v.post_title AS vehicle_name 
    FROM $table_bookings b
    INNER JOIN $table_users u ON b.client_id = u.ID
    INNER JOIN $table_posts v ON b.vehicle_id = v.ID
    ORDER BY b.created_at DESC 
    LIMIT %d OFFSET %d",
    $per_page, $offset
));

// Debugging (optional)
// write_log("Total Records: $total_records");
// write_log("Total Pages: $total_pages");
// write_log("Current Page: $paged");
// write_log("Offset: $offset");


echo '<div class="wrap">';
echo '<h1>Manage Bookings</h1>';
echo '<table class="widefat fixed" cellspacing="0">
        <thead>
            <tr>
                <th>Client</th>
                <th>Vehicle</th>
                <th>Booking Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>';

if (!empty($bookings)) {
    foreach ($bookings as $booking) {
        $actions = '<a href="#" class="button view-details" data-booking-id="' . esc_attr($booking->id) . '" id="view-booking-details">View Details</a>';
        
        echo '<tr>
                <td>' . esc_html($booking->client_name) . '</td>
                <td>' . esc_html($booking->vehicle_name) . '</td>
                <td>' . esc_html(format_time_to_readable($booking->booking_date)) . '</td>
                <td>' . ucfirst(esc_html($booking->status)) . '</td>
                <td>' . $actions . '</td>
            </tr>';
    }
} else {
    echo '<tr><td colspan="5">No bookings found.</td></tr>';
}

echo '</tbody></table>';

// Pagination links
if ($total_pages > 1) {
    echo '<div class="pagination-container">';
    
    if ($paged > 1) {
        echo '<a class="page-numbers" href="?page=wp_rental_bookings&paged=' . ($paged - 1) . '">&laquo; Previous</a>';
    }

    for ($i = 1; $i <= $total_pages; $i++) {
        $current_page_class = ($i == $paged) ? 'class="page-numbers current"' : 'class="page-numbers"';
        echo '<a ' . $current_page_class . ' href="?page=wp_rental_bookings&paged=' . $i . '">' . $i . '</a>';
    }

    if ($paged < $total_pages) {
        echo '<a class="page-numbers" href="?page=wp_rental_bookings&paged=' . ($paged + 1) . '">Next &raquo;</a>';
    }

    echo '</div>';
}

echo '</div>';

// Include modal for booking details
echo '<div id="booking-details-modal" style="display:none;">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <div id="booking-details"></div>
        </div>
      </div>';

?>
