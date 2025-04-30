<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Save Settings
if (isset($_POST['wp_rental_save_settings'])) {
    update_option('wp_rental_currency', sanitize_text_field($_POST['wp_rental_currency']));
    update_option("wp_rental_admin_email", sanitize_email($_POST["wp_rental_admin_email"]));
    update_option('wp_rental_admin_telephone', sanitize_text_field($_POST['wp_rental_admin_telephone']));
    update_option('wp_rental_admin_address', sanitize_text_field($_POST['wp_rental_admin_address']));
    update_option('wp_rental_stripe_key', sanitize_text_field($_POST['wp_rental_stripe_key']));
    update_option('wp_rental_stripe_test_key', sanitize_text_field($_POST['wp_rental_stripe_test_key']));
    update_option('wp_rental_stripe_mode', sanitize_text_field($_POST['wp_rental_stripe_mode'])); // Save Stripe mode

    update_option('wp_rental_booking_policy', sanitize_textarea_field($_POST['wp_rental_booking_policy']));
    update_option('wp_rental_business_logo', esc_url_raw($_POST['wp_rental_business_logo']));

    // Save selected pages
    update_option('wp_rental_login_page', absint($_POST['wp_rental_login_page']));
    update_option('wp_rental_register_page', absint($_POST['wp_rental_register_page']));
    update_option('wp_rental_recover_page', absint($_POST['wp_rental_recover_page']));
    update_option('wp_rental_dashboard_page', absint($_POST['wp_rental_dashboard_page']));

    echo '<div class="updated"><p>Settings Saved!</p></div>';
}

// Get Current Settings
$currency = get_option('wp_rental_currency', 'USD');
$stripe_key = get_option('wp_rental_stripe_key', '');
$stripe_test_key = get_option('wp_rental_stripe_test_key', '');
$stripe_mode = get_option('wp_rental_stripe_mode', 'test'); // Default to Test Mode

$admin_email = get_option('wp_rental_admin_email', '');
$admin_address = get_option('wp_rental_admin_address', '');
$admin_telephone = get_option('wp_rental_admin_telephone', '');
$booking_policy = get_option('wp_rental_booking_policy', '');
$business_logo = get_option('wp_rental_business_logo', '');

$login_page = get_option('wp_rental_login_page', '');
$register_page = get_option('wp_rental_register_page', '');
$recover_page = get_option('wp_rental_recover_page', '');
$dashboard_page = get_option('wp_rental_dashboard_page', '');

// Get all pages for the dropdown
$pages = get_pages();

// Currency options
$currencies = [
    'USD' => 'US Dollar ($)',
    'EUR' => 'Euro (€)',
    'GBP' => 'British Pound (£)',
    'NGN' => 'Nigerian Naira (₦)',
    'CAD' => 'Canadian Dollar (C$)',
    'AUD' => 'Australian Dollar (A$)',
    'INR' => 'Indian Rupee (₹)',
    'JPY' => 'Japanese Yen (¥)',
    'CNY' => 'Chinese Yuan (¥)',
    'ZAR' => 'South African Rand (R)'
];
?>

<div class="wrap">
    <h2>Rental Plugin Settings</h2>
    <form method="post" id="wp-rental-admin-settings">
        <table class="form-table">

            <tr>
                <th>Business Logo:</th>
                <td>
                    <input type="text" id="wp_rental_business_logo" name="wp_rental_business_logo" value="<?php echo esc_url($business_logo); ?>" class="regular-text">
                    <button type="button" class="button button-secondary" id="upload_logo_button">Select Logo</button>
                    <br>
                    <img id="business_logo_preview" src="<?php echo esc_url($business_logo); ?>" style="max-width: 200px; margin-top: 10px; display: <?php echo $business_logo ? 'block' : 'none'; ?>;">
                </td>
            </tr>

            <!-- Login Page Dropdown -->
            <tr>
                <th>Login Page:</th>
                <td>
                    <select name="wp_rental_login_page">
                        <option value="">Select a page</option>
                        <?php foreach ($pages as $page) : ?>
                            <option value="<?php echo esc_attr($page->ID); ?>" <?php selected($login_page, $page->ID); ?>>
                                <?php echo esc_html($page->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>

            <!-- Register Page Dropdown -->
            <tr>
                <th>Register Page:</th>
                <td>
                    <select name="wp_rental_register_page">
                        <option value="">Select a page</option>
                        <?php foreach ($pages as $page) : ?>
                            <option value="<?php echo esc_attr($page->ID); ?>" <?php selected($register_page, $page->ID); ?>>
                                <?php echo esc_html($page->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>

            <!-- Account Recovery Page Dropdown -->
            <tr>
                <th>Account Recovery Page:</th>
                <td>
                    <select name="wp_rental_recover_page">
                        <option value="">Select a page</option>
                        <?php foreach ($pages as $page) : ?>
                            <option value="<?php echo esc_attr($page->ID); ?>" <?php selected($recover_page, $page->ID); ?>>
                                <?php echo esc_html($page->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>

            <!-- Dashboard Page Dropdown -->
            <tr>
                <th>Dashboard Page:</th>
                <td>
                    <select name="wp_rental_dashboard_page">
                        <option value="">Select a page</option>
                        <?php foreach ($pages as $page) : ?>
                            <option value="<?php echo esc_attr($page->ID); ?>" <?php selected($dashboard_page, $page->ID); ?>>
                                <?php echo esc_html($page->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>

            <!-- Currency Selection -->
            <tr>
                <th>Currency:</th>
                <td>
                    <select name="wp_rental_currency">
                        <?php foreach ($currencies as $code => $name) : ?>
                            <option value="<?php echo esc_attr($code); ?>" <?php selected($currency, $code); ?>>
                                <?php echo esc_html($name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>

            <!-- Admin Email -->
            <tr>
                <th>Admin Email:</th>
                <td><input type="text" name="wp_rental_admin_email" value="<?php echo esc_attr($admin_email); ?>" class="regular-text"></td>
            </tr>

            <!-- Telephone -->
            <tr>
                <th>Contact Telephone:</th>
                <td><input type="tel" name="wp_rental_admin_telephone" value="<?php echo esc_attr($admin_telephone); ?>" class="regular-text"></td>
            </tr>

            <!-- Admin Address -->
            <tr>
                <th>Contact Address:</th>
                <td><input type="text" name="wp_rental_admin_address" value="<?php echo esc_attr($admin_address); ?>" class="regular-text"></td>
            </tr>

            <!-- Stripe Test API Key -->
            <tr>
                <th>Stripe Test API Key:</th>
                <td><input type="text" name="wp_rental_stripe_test_key" value="<?php echo esc_attr($stripe_test_key); ?>" class="regular-text"></td>
            </tr>

            <!-- Stripe API Key -->
            <tr>
                <th>Stripe API Key:</th>
                <td><input type="text" name="wp_rental_stripe_key" value="<?php echo esc_attr($stripe_key); ?>" class="regular-text"></td>
            </tr>

            <!-- Stripe Mode (Test/Live) -->
            <tr>
                <th>Stripe Mode:</th>
                <td>
                    <select name="wp_rental_stripe_mode">
                        <option value="test" <?php selected($stripe_mode, 'test'); ?>>Test Mode</option>
                        <option value="live" <?php selected($stripe_mode, 'live'); ?>>Live Mode</option>
                    </select>
                </td>
            </tr>

            <!-- Booking Policy -->
            <tr>
                <th>Booking Policy:</th>
                <td><textarea name="wp_rental_booking_policy" class="large-text"><?php echo esc_textarea($booking_policy); ?></textarea></td>
            </tr>
        </table>

        <p><input type="submit" name="wp_rental_save_settings" value="Save Settings" class="button button-primary"></p>
    </form>
</div>