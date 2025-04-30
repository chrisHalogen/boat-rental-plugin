<?php

/**
 * Template Name: Custom Login Page
 * Description: A secure custom login page template.
 */

$business_logo = esc_url(get_option('wp_rental_business_logo', ''));
$register_page_id = get_option('wp_rental_register_page', '');
$login_page_id = get_option('wp_rental_login_page', '');
$dashboard_page_id = get_option('wp_rental_dashboard_page', '');
$recover_page = get_option('wp_rental_recover_page', '');

// write_log(get_permalink($register_page_id));

// Load custom header
include plugin_dir_path(__FILE__) . 'core/custom-header.php';

// Redirect logged-in users to the dashboard page
if (is_user_logged_in()) {
    echo "Redirecting to dashboard...";
    safe_redirect(get_permalink($dashboard_page_id));
    exit;
}
?>

<div class="auth-outer-container" id="client-login-form">
    <div class="auth-inner">
        <h1>Client Login</h1>

        <!-- Response message container -->
        <div id="response-message"></div>

        <p id="welcome-message">Welcome back! Please log in to access your account.</p>

        <!-- Login Form -->
        <form method="POST" id="login-form">
            <!-- WordPress Nonce for CSRF Protection -->
            <?php wp_nonce_field('custom_login_action', 'custom_login_nonce'); ?>

            <input type="hidden" name="action" value="custom_login">

            <div class="field">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" placeholder="Email" required>
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="Password" required>
            </div>

            <div class="action">
                <button type="submit">Log In</button>
            </div>
        </form>

        <p class="after-form">Don't have an account? <a href="<?php echo esc_url(get_permalink($register_page_id)); ?>">Register</a></p>
        <p class="after-form">Forgot Your Password? <a href="<?php echo esc_url(get_permalink($recover_page)); ?>">Recover Your Account</a></p>
        <a href="<?php echo esc_url(home_url()); ?>">Back Home</a>
    </div>
</div>

<?php
// Load custom footer
include plugin_dir_path(__FILE__) . 'core/custom-footer.php';
?>