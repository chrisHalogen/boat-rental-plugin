<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$per_page = 20;

// Fetch clients with pagination
$clients = get_users([
    'role' => 'client',
    'number' => $per_page,
    'offset' => ($paged - 1) * $per_page,
]);

// Get total number of clients
$user_count = count_users();
$total_users = isset($user_count['avail_roles']['client']) ? $user_count['avail_roles']['client'] : 0;
$total_pages = ceil($total_users / $per_page);

echo '<div class="wrap">';
echo '<h1>Manage Clients</h1>';
echo '<table class="widefat fixed" cellspacing="0">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Bookings</th>
                <th>Total Spent</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>';

if (!empty($clients)) {
    foreach ($clients as $client) {
        $user_id = $client->ID;
        $name = $client->display_name;
        $email = $client->user_email;
        $bookings = get_user_meta($user_id, 'total_orders', true) ?: 0;
        $total_spent = get_user_meta($user_id, 'total_spent', true) ?: '0.00';
        $stats = calculate_user_booking_stats($user_id);
        $bookings_count = $stats['total_bookings'];
        $total_spent = $stats['total_amount_spent'];

        echo '<tr>
                <td>' . esc_html($name) . '</td>
                <td>' . esc_html($email) . '</td>
                <td>' . esc_html($bookings_count) . '</td>
                <td>$' . esc_html($total_spent) . '</td>
                <td>
                    <a href="?page=wp_rental_clients&action=delete&client_id=' . esc_attr($user_id) . '" class="button button-danger">Delete</a>
                    <a href="?page=wp_rental_clients&action=reset_password&client_id=' . esc_attr($user_id) . '" class="button">Send Password Reset</a>
                </td>
            </tr>';
    }
} else {
    echo '<tr><td colspan="5">No clients found.</td></tr>';
}

echo '</tbody></table>';

// Pagination links with styling
if ($total_pages > 1) {

    echo '<div class="pagination-container">';

    if ($paged > 1) {
        echo '<a class="page-numbers" href="?page=wp_rental_clients&paged=' . ($paged - 1) . '">&laquo; Previous</a>';
    }

    for ($i = 1; $i <= $total_pages; $i++) {
        $current_page_class = ($i == $paged) ? 'class="page-numbers current"' : 'class="page-numbers"';
        echo '<a ' . $current_page_class . ' href="?page=wp_rental_clients&paged=' . $i . '">' . $i . '</a>';
    }

    if ($paged < $total_pages) {
        echo '<a class="page-numbers" href="?page=wp_rental_clients&paged=' . ($paged + 1) . '">Next &raquo;</a>';
    }

    echo '</div>';
}

echo '</div>';
