<?php

/**
 * Template Name: Custom Register Page
 * Description: A secure custom register page template with email verification.
 */

$business_logo = esc_url(get_option('wp_rental_business_logo', ''));
$login_page_id = get_option('wp_rental_login_page', '');
$dashboard_page_id = get_option('wp_rental_dashboard_page', '');

write_log(get_permalink($login_page_id));

// Load custom header from the templates/core folder
include plugin_dir_path(__FILE__) . 'core/custom-header.php';

// Redirect logged-in users to the dashboard page
if (is_user_logged_in()) {
    echo "Redirecting to dashboard";
    safe_redirect(get_permalink($dashboard_page_id));
    exit;
}

// // Check if email verification message should be displayed
// $registration_message = isset($_GET['registration_success']) ? "Registration successful! Please check your email for the activation link." : "";

?>


<div class="auth-outer-container">
    <div class="auth-inner">
        <h1>Client Registration</h1>


        <div id="response-message"></div>

        <p id="welcome-message">Welcome to the registration page. Please fill out the form below to create your account.</p>

        <form method="POST" id="register-form">

            <!-- WordPress Nonce for CSRF Protection -->
            <?php wp_nonce_field('custom_register_action', 'custom_register_nonce'); ?>

            <input type="hidden" name="action" value="custom_register">

            <div class="field">
                <label for="name">Name</label>
                <input type="text" name="name" id="name" placeholder="Name" required>
            </div>

            <div class="field">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" placeholder="Email" required>
            </div>

            <div class="field">
                <label for="phone">Phone</label>
                <input type="tel" name="phone" id="phone" placeholder="Phone" required>
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="Password" required>
            </div>

            <div class="field">
                <label for="re-password">Confirm Password</label>
                <input type="password" name="re-password" id="re-password" placeholder="Retype Password" required>
            </div>

            <div class="action">
                <button type="submit">Create My Account</button>
            </div>
        </form>

        <p class="after-form">Already have an account? <a href="<?php echo esc_url(get_permalink($login_page_id)); ?>">Login</a></p>
        <a href="<?php echo esc_url(home_url()); ?>">Back Home</a>
    </div>
</div>

<?php

// Load custom footer from the templates/core folder
include plugin_dir_path(__FILE__) . 'core/custom-footer.php';


?>