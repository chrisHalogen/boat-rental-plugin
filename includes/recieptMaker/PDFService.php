<?php

// use Phppot\Config;

ini_set('display_errors', 'On');
error_reporting(E_ALL);

// require_once __DIR__ . '/../lib/Config.php';
// $config = new Config();

class PDFService
{

    function generatePDF($client_id, $booking_id, $payment_id)
    {
        require_once WVR_PLUGIN_PATH . '/tcpdf/tcpdf.php'; // Adjust the path to TCPDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->SetHeaderData('', PDF_HEADER_LOGO_WIDTH, '', 'Thank you for cruising with us!', array(
            0,
            0,
            0
        ), array(
            255,
            255,
            255
        ));
        $pdf->SetTitle('DSmart Booking Reciept - ' . $booking_id . '_' . time());

        $pdf->SetMargins(20, 10, 20, true);
        if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
            require_once(dirname(__FILE__) . '/lang/eng.php');
            $pdf->setLanguageArray($l);
        }
        $pdf->setHeaderFont(array('prata', '', PDF_FONT_SIZE_MAIN));
        $pdf->SetFont('opensans', '', 12);
        $pdf->AddPage();
        // $orderedDate = date('d F Y', strtotime($result[0]["order_at"]));
        // $due_date = date("d F Y", strtotime('+' . Config::TERMS . 'days', strtotime($orderedDate)));

        require_once __DIR__ . '/purchase-reciept-template.php';
        $html = getHTMLPurchaseDataToPDF($client_id, $booking_id, $payment_id);
        $filename = 'dsmart_booking_receipt_' . $booking_id . '_' . time();
        $pdf->writeHTML($html, true, false, true, false, '');
        ob_end_clean();
        $pdf->Output($filename . '.pdf', 'D');
    }
}
