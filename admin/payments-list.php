<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
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

global $wpdb;

$paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$per_page = 20;

$table_payments = $wpdb->prefix . "wp_rental_payments";
$table_bookings = $wpdb->prefix . "wp_rental_bookings";
$table_users = $wpdb->prefix . "users";

// Filters
$search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$filter_status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
$filter_method = isset($_GET['method']) ? sanitize_text_field($_GET['method']) : '';
$filter_start_date = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : '';
$filter_end_date = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : '';

// Build the WHERE clause
$where_clause = "1=1";

if (!empty($search_query)) {
    $where_clause .= $wpdb->prepare(" AND (p.booking_id LIKE %s OR u.display_name LIKE %s OR p.status LIKE %s)", "%{$search_query}%", "%{$search_query}%", "%{$search_query}%");
}

if (!empty($filter_status)) {
    $where_clause .= $wpdb->prepare(" AND p.payment_status = %s", $filter_status);
}

if (!empty($filter_method)) {
    $where_clause .= $wpdb->prepare(" AND p.payment_method = %s", $filter_method);
}

if (!empty($filter_start_date) && !empty($filter_end_date)) {
    $where_clause .= $wpdb->prepare(" AND DATE(p.payment_date) BETWEEN %s AND %s", $filter_start_date, $filter_end_date);
}

// Get total records for pagination
$total_records = $wpdb->get_var("SELECT COUNT(*) FROM $table_payments p INNER JOIN $table_bookings b ON p.booking_id = b.id INNER JOIN $table_users u ON b.client_id = u.ID WHERE $where_clause");

$total_pages = ceil($total_records / $per_page);
if ($paged > $total_pages) {
    $paged = $total_pages;
}
$offset = ($paged - 1) * $per_page;

// Fetch payments with pagination
$payments = $wpdb->get_results($wpdb->prepare(
    "SELECT p.*, b.client_id, u.display_name AS client_name 
    FROM $table_payments p
    INNER JOIN $table_bookings b ON p.booking_id = b.id
    INNER JOIN $table_users u ON b.client_id = u.ID
    WHERE $where_clause
    ORDER BY p.payment_date DESC 
    LIMIT %d OFFSET %d",
    $per_page,
    $offset
));

echo '<div class="wrap">';
echo '<h1>Manage Payments</h1>';

// Search & Filters Form
echo '<br><form method="get" style="display:flex; align-items:center;column-gap: 0.5rem">
        <input type="hidden" name="page" value="wp_rental_payments">
        <input type="text" name="search" placeholder="Search by Booking ID, Client Name, or Status" value="' . esc_attr($search_query) . '">
        <select name="status">
            <option value="">All Statuses</option>
            <option value="pending" ' . selected($filter_status, 'pending', false) . '>Pending</option>
            <option value="paid" ' . selected($filter_status, 'paid', false) . '>Paid</option>
            <option value="failed" ' . selected($filter_status, 'failed', false) . '>Failed</option>
        </select>
        <select name="method">
            <option value="">All Payment Methods</option>
            <option value="credit_card" ' . selected($filter_method, 'credit_card', false) . '>Credit Card</option>
            <option value="bank_transfer" ' . selected($filter_method, 'bank_transfer', false) . '>Bank Transfer</option>
            <option value="paypal" ' . selected($filter_method, 'paypal', false) . '>PayPal</option>
        </select>
        <input type="date" name="start_date" value="' . esc_attr($filter_start_date) . '">
        <input type="date" name="end_date" value="' . esc_attr($filter_end_date) . '">
        <input type="submit" class="button-primary" value="Filter">
      </form><br>';

// Table
echo '<table class="widefat fixed" cellspacing="0">
        <thead>
            <tr>
                <th>Payment ID</th>
                <th>Client</th>
                <th>Booking ID</th>
                <th>Amount</th>
                <th>Payment Method</th>
                <th>Payment Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>';

if (!empty($payments)) {
    foreach ($payments as $payment) {
        $actions = '<a href="#" class="button view-payment-details primary" data-payment-id="' . esc_attr($payment->id) . '">View Details</a>';

        if ($payment->payment_status == "Paid") {
            $download_url = add_query_arg([
                'action' => 'download_receipt',
                'booking_id' => $payment->booking_id,
                'payment_id' => $payment->id,
                'client_id' => get_client_id_from_payment_id($payment->id)
            ]);
            $actions .= '<br><br><a href="' . esc_url($download_url) . '" class="button" target="_blank">Download Receipt</a>';
        }

        echo '<tr>
                <td>' . esc_html($payment->id) . '</td>
                <td>' . esc_html($payment->client_name) . '</td>
                <td>' . esc_html($payment->booking_id) . '</td>
                <td>' . esc_html($payment->amount) . '</td>
                <td>' . esc_html($payment->payment_method) . '</td>
                <td>' . esc_html(format_time_to_readable($payment->payment_date)) . '</td>
                <td>' . esc_html($payment->payment_status) . '</td>
                <td>' . $actions . '</td>
            </tr>';
    }
} else {
    echo '<tr><td colspan="8">No payments found.</td></tr>';
}

echo '</tbody></table>';

// Pagination
if ($total_pages > 1) {
    echo '<div class="pagination-container">';

    if ($paged > 1) {
        echo '<a class="page-numbers" href="?page=wp_rental_payments&paged=' . ($paged - 1) . '">&laquo; Previous</a>';
    }

    for ($i = 1; $i <= $total_pages; $i++) {
        $current_page_class = ($i == $paged) ? 'class="page-numbers current"' : 'class="page-numbers"';
        echo '<a ' . $current_page_class . ' href="?page=wp_rental_payments&paged=' . $i . '">' . $i . '</a>';
    }

    if ($paged < $total_pages) {
        echo '<a class="page-numbers" href="?page=wp_rental_payments&paged=' . ($paged + 1) . '">Next &raquo;</a>';
    }

    echo '</div>';
}

echo '</div>';

// Payment Details Modal
echo '<div id="payment-details-modal" style="display:none;">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <div id="payment-details"></div>
        </div>
      </div>';

?>
<!-- <script>
jQuery(document).ready(function($) {
    $('.view-details').click(function(e) {
        e.preventDefault();
        var paymentId = $(this).data('payment-id');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: { action: 'get_payment_details', payment_id: paymentId },
            success: function(response) {
                $('#payment-details').html(response);
                $('#payment-details-modal').show();
            }
        });
    });

    $('.close-modal').click(function() {
        $('#payment-details-modal').hide();
    });
});
</script> -->