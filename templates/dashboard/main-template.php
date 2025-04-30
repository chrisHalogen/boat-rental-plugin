<?php
/*
Template Name: Dashboard Area
*/

// Redirect non-logged-in users to the login page
if (!is_user_logged_in()) {
    safe_redirect(get_permalink(get_option('wp_rental_login_page')));
    exit;
}

// Get the dashboard page ID from the database
$dashboard_page_id = get_option('wp_rental_dashboard_page');

// Set the page title dynamically
$page_title = 'Dashboard'; // Default title
$subpage = get_query_var('subpage'); // Get the subpage from the query variable

if ($subpage === 'bookings') {
    $page_title = 'Bookings';
} elseif ($subpage === 'payments') {
    $page_title = 'Payments';
} elseif ($subpage === 'support') {
    $page_title = 'Support';
} elseif ($subpage === 'account') {
    $page_title = 'Account';
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
    <title><?php echo esc_html($page_title); ?> - DSmart Cruise</title>
</head>

<body <?php body_class(); ?>>
    <div class="outer-container" id="dashboard-outter-container">
        <div class="inner-container">
            <div class="mobile-header-container">
                <div class="logo-container">
                    <img src="<?php echo plugin_dir_url(__FILE__); ?>../../assets/images/dsclogo.png" alt="logo-img-mobile">
                </div>
                <div class="icon-container" id="toggle1">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </div>
            </div>
            <div class="navigation" id="dashboard-mobile-navigation">
                <div class="icon-container close-button" id="toggle2">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </div>
                <div class="logo-container">
                    <img src="<?php echo plugin_dir_url(__FILE__); ?>../../assets/images/dsclogo.png" alt="logo-img">
                </div>
                <nav>
                    <ul>
                        <li><a href="<?php echo get_permalink($dashboard_page_id); ?>" class="<?php echo (is_page($dashboard_page_id) && !$subpage) ? 'active' : ''; ?>">Dashboard</a></li>
                        <li><a href="<?php echo get_permalink($dashboard_page_id); ?>/bookings" class="<?php echo ($subpage === 'bookings') ? 'active' : ''; ?>">Bookings</a></li>
                        <li><a href="<?php echo get_permalink($dashboard_page_id); ?>/payments" class="<?php echo ($subpage === 'payments') ? 'active' : ''; ?>">Payments</a></li>
                        <li><a href="<?php echo get_permalink($dashboard_page_id); ?>/support" class="<?php echo ($subpage === 'support') ? 'active' : ''; ?>">Support</a></li>
                        <li><a href="<?php echo get_permalink($dashboard_page_id); ?>/account" class="<?php echo ($subpage === 'account') ? 'active' : ''; ?>">Account</a></li>
                        <li><a href="#" id="home-link">Home</a></li>
                        <li><a href="#" id="logout-link">Logout</a></li>
                    </ul>
                </nav>
            </div>
            <div class="vertical-divider"></div>
            <div class="content">
                <?php
                // Load the appropriate content based on the subpage
                if ($subpage === 'bookings') {
                    include plugin_dir_path(__FILE__) . 'bookings.php';
                } elseif ($subpage === 'payments') {
                    include plugin_dir_path(__FILE__) . 'payments.php';
                } elseif ($subpage === 'support') {
                    include plugin_dir_path(__FILE__) . 'support.php';
                } elseif ($subpage === 'account') {
                    include plugin_dir_path(__FILE__) . 'account.php';
                } elseif ($subpage === 'booking-details') {
                    include plugin_dir_path(__FILE__) . 'booking-details.php';
                } else {
                    include plugin_dir_path(__FILE__) . 'dashboard.php';
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Popup for Home -->
    <div id="home-popup" class="popup">
        <div class="popup-content">
            <p>Are you sure you want to go back to the homepage?</p>
            <button id="confirm-home">Confirm</button>
            <button id="cancel-home">Cancel</button>
        </div>
    </div>

    <!-- Popup for Logout -->
    <div id="logout-popup" class="popup">
        <div class="popup-content">
            <p>Are you sure you want to logout?</p>
            <button id="confirm-logout">Confirm</button>
            <button id="cancel-logout">Cancel</button>
        </div>
    </div>

    <?php wp_footer(); ?>
</body>

</html>