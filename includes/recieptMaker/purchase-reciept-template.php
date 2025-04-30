<?php

// use Phppot\Order;

// require_once __DIR__ . '/../Model/Order.php';
function getHTMLPurchaseDataToPDF($client_id, $booking_id, $payment_id)
{
    ob_start();
    global $wpdb;

    // Retrieve booking, payment, and customer details
    $table_bookings = $wpdb->prefix . 'wp_rental_bookings';
    $table_payments = $wpdb->prefix . 'wp_rental_payments';

    $booking = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_bookings WHERE id = %d", $booking_id));
    $payment = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_payments WHERE id = %d", $payment_id));
    $customer = get_userdata($client_id);

    if (!$booking || !$payment || !$customer) {
        return false; // Handle missing data
    }

    // Retrieve vehicle details
    $vehicle = get_post($booking->vehicle_id);
    $thumbnail = has_post_thumbnail($vehicle->ID) ? get_the_post_thumbnail_url($vehicle->ID, 'large') : get_option('wp_rental_business_logo');
    $logo = get_option('wp_rental_business_logo');
?>
    <html>

    <head>Receipt of Purchase - <?php echo $payment->transaction_id; ?>
    </head>

    <body>
        <style>
            span {
                font-family: opensansb;
            }
        </style>
        <div style="text-align:right;">
            <span>Sender:</span> DSmart Cruise
        </div>
        <div style="border-top:1px solid #000;">

            <img src="<?php echo $logo ?>" alt="thumbnail" width="100">

            <div style="font-size: 24px;color: #003399;font-family: prata; font-weight: bold;">PAYMENT RECIEPT</div>

        </div>
        <table style="line-height: 1.5;">
            <tr>
                <td><b>Booking ID:</b> #<?php echo $booking_id; ?>
                </td>
                <td style="text-align:right;"><b>Receiver:</b></td>
            </tr>
            <tr>
                <td><b>Date:</b> <?php echo $booking->booking_date; ?></td>
                <td style="text-align:right;"><?php echo $customer->display_name; ?></td>
            </tr>
            <tr>
                <td> </td>
                <td></td>
            </tr>
            <tr>
                <td><b>Our Address:</b><br>209 S Shady Shores Dr Suite 300-2024, Lake Dallas, TX 75065</td>
                <td></td>
            </tr>
        </table>

        <div></div>

        <div style="border-bottom:1px solid #000;">
            <table style="line-height: 2;">
                <tr style="font-weight: bold;border:1px solid #cccccc;background-color:#f2f2f2;">
                    <td style="border:1px solid #cccccc;width:200px;">Item Description</td>
                    <td style="text-align:right;border:1px solid #cccccc;width:120px;">Package</td>
                    <td style="text-align:right;border:1px solid #cccccc;">Subtotal ($)</td>
                </tr>

                <tr>
                    <td style="border:1px solid #cccccc;"><?php echo $vehicle->post_title; ?></td>
                    <td style="text-align:right; border:1px solid #cccccc;"><?php echo $booking->package; ?></td>
                    <td style="text-align:right; border:1px solid #cccccc;">$<?php echo number_format($payment->amount); ?></td>
                </tr>

                <tr style="font-weight: bold;">
                    <td></td>
                    <td style="text-align:right;">Total ($)</td>
                    <td style="text-align:right;"><?php echo number_format($payment->amount); ?></td>
                </tr>
            </table>
        </div>
        <p><b>Transaction ID:</b> #<?php echo $payment->transaction_id; ?><br><b>Payment Method:</b> <?php echo $payment->payment_method; ?><br><b>Payment Date:</b> <?php echo format_time_to_readable($payment->payment_date); ?><br><b>Transaction Status:</b> <?php echo $payment->payment_status; ?><br></p>
        <p><i>Note: Check Your Email For Payment Confirmation</i></p>
    </body>

    </html>

<?php
    return ob_get_clean();
}
?>