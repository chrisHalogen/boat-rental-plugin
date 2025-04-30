<?php


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
// $table_payments = $wpdb->prefix . 'rental_payments';
// $table_bookings = $wpdb->prefix . 'rental_bookings';

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

// Base query
$query = "SELECT p.*, b.vehicle_id 
          FROM $table_payments p
          JOIN $table_bookings b ON p.booking_id = b.id
          WHERE b.client_id = $user_id";

// Apply filters
if (!empty($search)) {
    $query .= $wpdb->prepare(
        " AND (p.transaction_id LIKE %s OR p.booking_id LIKE %s)",
        '%' . $wpdb->esc_like($search) . '%',
        '%' . $wpdb->esc_like($search) . '%'
    );
}
if (!empty($status)) {
    $query .= $wpdb->prepare(" AND p.payment_status = %s", $status);
}
if (!empty($date)) {
    $query .= $wpdb->prepare(" AND DATE(p.created_at) = %s", $date);
}

// Count total rows for pagination
$total_query = "SELECT COUNT(1) FROM ($query) AS total";
$total = $wpdb->get_var($total_query);

// Fetch payments
$query .= $wpdb->prepare(" ORDER BY p.created_at DESC LIMIT %d OFFSET %d", $per_page, $offset);
$payments = $wpdb->get_results($query);

// Get business logo
$business_logo = get_option('wp_rental_business_logo');
?>

<div class="payments-container" id="payments-container">
    <h1>Payments</h1>

    <!-- Filters -->
    <div class="filters">
        <form method="get" action="">
            <input type="text" name="search" placeholder="Transaction ID or Booking ID" value="<?php echo esc_attr($search); ?>">
            <select name="status">
                <option value="">All Statuses</option>
                <option value="Pending" <?php selected($status, 'Pending'); ?>>Pending</option>
                <option value="Paid" <?php selected($status, 'Paid'); ?>>Paid</option>
                <option value="Failed" <?php selected($status, 'Failed'); ?>>Failed</option>
                <option value="Refunded" <?php selected($status, 'Refunded'); ?>>Refunded</option>
            </select>
            <input type="date" name="date" value="<?php echo esc_attr($date); ?>">
            <button type="submit" class="btn-primary">Filter</button>
        </form>
    </div>

    <!-- Payment History -->
    <?php if (!empty($payments)) : ?>
        <div class="payments-table-container">
            <table class="payment-history">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment) : ?>
                        <tr>
                            <td><?php echo esc_html($payment->booking_id); ?></td>
                            <td><?php echo esc_html(number_format($payment->amount, 2)); ?></td>
                            <td><?php echo esc_html($payment->payment_method); ?></td>
                            <td>
                                <button class="btn-view-details" data-payment-id="<?php echo esc_attr($payment->id); ?>">View Details</button>

                                <?php

                                if ($payment->payment_status == "Paid") {
                                    $download_url = add_query_arg([
                                        'action' => 'download_receipt',
                                        'booking_id' => $payment->booking_id,
                                        'payment_id' => $payment->id,
                                        'client_id' => get_client_id_from_payment_id($payment->id)
                                    ]);

                                    $download_link = "";

                                    $actions = '<br><br><a href="' . esc_url($download_url) . '" target="_blank" class="download-reciept" style="font-weight:bold;background-color: #28a745;">Get Receipt</a>';

                                    echo $actions;
                                }

                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php else : ?>
        <div class="no-payments-found">
            <p>No payments found.</p>
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
<div id="payment-details-popup" class="payment-popup">
    <div class="popup-content">
        <h2>Payment Details</h2>
        <div id="payment-details-content"></div>
        <div class="popup-actions">
            <button id="make-payment-btn" class="btn-primary">Make Payment</button>
            <button id="download-invoice-btn" class="btn-secondary">Download Invoice</button>
            <button id="close-popup-btn" class="btn-danger">Close</button>
        </div>
    </div>
</div>

<!-- Stripe Payment Form (Hidden) -->
<div id="stripe-payment-form" style="display: none;">
    <form id="stripe-form">
        <div id="stripe-card-element"></div>
        <button type="submit" id="stripe-submit-btn">Pay Now</button>
    </form>
</div>

<script>
    // Stripe Publishable Key
    const stripePublishableKey = '<?php echo esc_js(get_option('stripe_publishable_key')); ?>';
</script>