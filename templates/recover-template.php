<?php

/**
 * Template Name: Password Recovery Page
 * Description: A password recovery page template.
 */

$login_page_id = get_option('wp_rental_login_page', '');
$dashboard_page_id = esc_url(get_option('wp_rental_dashboard_page', ''));

// Load custom header from the templates/core folder
include plugin_dir_path(__FILE__) . 'core/custom-header.php';

// Check if user has a valid recovery link
$reset_token = isset($_GET['token']) ? sanitize_text_field($_GET['token']) : '';
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$valid_link = false;

if ($reset_token && $user_id) {
    $saved_token = get_user_meta($user_id, 'wp_rental_recovery_token', true);
    if ($saved_token === $reset_token) {
        write_log("Check 3");
        $valid_link = true;
    }
}

// Redirect logged-in users to the dashboard page
if (is_user_logged_in()) {
    echo "Redirecting to dashboard...";
    safe_redirect(get_permalink($dashboard_page_id));
    exit;
}

?>

<!-- <div class="recover-container">
    <h2>Recover Your Account</h2>
    <form method="post">
        <label for="email">Enter Your Email:</label>
        <input type="email" name="email" required>

        <button type="submit">Send Recovery Email</button>
    </form>
</div> -->

<div class="auth-outer-container" id="account-recovery">
    <div class="auth-inner">
        <h1>Account Recovery</h1>
        <div id="response-message"></div>

        <?php if (!$valid_link) { ?>
            <p>Enter your email to find your account.</p>
            <form id="recover-form" method="POST">
                <?php wp_nonce_field('account_recovery_action', 'account_recovery_nonce'); ?>
                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" placeholder="Enter your email" required>
                </div>
                <div class="action">
                    <button type="submit">Find My Account</button>
                </div>
            </form>
        <?php } else { ?>
            <p>Enter your new password.</p>
            <form id="reset-form" method="POST">
                <?php wp_nonce_field('reset_password_action', 'reset_password_nonce'); ?>
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                <input type="hidden" name="token" value="<?php echo esc_attr($reset_token); ?>">
                <div class="field">
                    <label for="password">New Password</label>
                    <input type="password" name="password" id="password" placeholder="New Password" required>
                </div>
                <div class="field">
                    <label for="confirm-password">Confirm Password</label>
                    <input type="password" name="confirm-password" id="confirm-password" placeholder="Confirm Password" required>
                </div>
                <div class="action">
                    <button type="submit">Reset Password</button>
                </div>
            </form>

        <?php } ?>
        <br>
        <a href="<?php echo esc_url(get_permalink($login_page_id)); ?>">Back To Login</a>
    </div>
</div>

<?php

// Load custom footer from the templates/core folder
include plugin_dir_path(__FILE__) . 'core/custom-footer.php';


?>