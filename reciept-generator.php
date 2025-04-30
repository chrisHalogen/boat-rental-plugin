<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering
ob_start();

// The Ticket Download Function
require_once WVR_PLUGIN_PATH . '/tcpdf/tcpdf.php'; // Adjust the path to TCPDF

function generate_receipt_pdf($booking_id, $payment_id, $client_id)
{
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

    // Convert image URL to absolute path
    $thumbnail_path = str_replace(home_url(), ABSPATH, $thumbnail);

    // Debug the image path
    error_log('Thumbnail Path: ' . $thumbnail_path);

    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('DSmart Cruise');
    $pdf->SetTitle('Booking Receipt');
    $pdf->SetSubject('Booking Receipt');
    $pdf->SetKeywords('Receipt, Booking, PDF');

    // Set default header data
    $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, 'Booking Receipt', 'Thank you for choosing us!');

    // Set header and footer fonts
    $pdf->setHeaderFont(array('prata', '', PDF_FONT_SIZE_MAIN)); // Use Prata for header
    $pdf->setFooterFont(array('opensans', '', PDF_FONT_SIZE_DATA)); // Use Open Sans for footer

    // Set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // Set auto page breaks
    $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

    // Add a page
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('opensans', '', 12); // Use Open Sans for body text

    $img_html = "";

    // Add image to HTML
    if (file_exists($thumbnail_path)) {
        $thumbnail_data = base64_encode(file_get_contents($thumbnail_path));
        $thumbnail_src = 'data:image/jpeg;base64,' . $thumbnail_data;
        $img_html = '<div class="vehicle-image"><img src="' . $thumbnail_src . '" alt="Vehicle Thumbnail"></div>';
    } else {
        error_log('Thumbnail file not found: ' . $thumbnail_path);
    }

    // Add content
    $html = '
    <style>
        h1 { font-family: prata; color: #ffffff; font-weight: bold; }
        h2 { font-family: prata; color: #242424;  font-weight: bold; }
        p { font-family: opensans; color: #7a7a7a; }
        .header { background-color: #001540; color: #ffffff; padding: 10px; text-align: center; }
        .section-title { font-family: prata; color: #003399; font-size: 18px; font-weight: bold; margin-bottom: 0; }
        .details { font-family: opensans; color: #7a7a7a; }
    </style>

    <div class="header">
        <h1>Booking Receipt</h1>
    </div>
    ' . $img_html . '
    <div class="section">
        <div class="section-title">Customer Details</div>
        <div class="details">
            <p><strong>Name:</strong> ' . esc_html($customer->display_name) . '</p>
            <p><strong>Email:</strong> ' . esc_html($customer->user_email) . '</p>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Booking Details</div>
        <div class="details">
            <p><strong>Vehicle:</strong> ' . esc_html($vehicle->post_title) . '</p>
            <p><strong>Booking Date:</strong> ' . esc_html($booking->booking_date) . '</p>
            <p><strong>Package:</strong> ' . esc_html($booking->package) . '</p>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Payment Details</div>
        <div class="details">
            <p><strong>Transaction ID:</strong> ' . esc_html($payment->transaction_id) . '</p>
            <p><strong>Amount:</strong> $' . esc_html($payment->amount) . '</p>
            <p><strong>Payment Method:</strong> ' . esc_html($payment->payment_method) . '</p>
            <p><strong>Payment Date:</strong> ' . esc_html($payment->payment_date) . '</p>
        </div>
    </div>';

    // Output the HTML content
    $pdf->writeHTML($html, true, false, true, false, '');

    ob_end_clean();
    // $pdf->Output($pdf_name, 'I');

    // Close and output PDF document
    $pdf->Output('dsmart_booking_receipt_' . $booking_id . '_' . time() . '.pdf', 'D'); // 'D' forces download
}

// End output buffering and clean the buffer
// ob_end_clean();

// Call the function
// generate_receipt_pdf($booking_id, $payment_id, $client_id);
