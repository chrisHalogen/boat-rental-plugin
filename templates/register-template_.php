<?php
/**
 * Template Name: Custom Register Page
 * Description: A secure custom register page template with email verification.
 */

$business_logo = esc_url(get_option('wp_rental_business_logo', ''));
$login_page_id = esc_url(get_option('wp_rental_login_page', ''));
$dashboard_page_id = esc_url(get_option('wp_rental_dashboard_page', ''));
wp_head();

// Redirect logged-in users to the dashboard page
if (is_user_logged_in()) {
    wp_redirect(get_permalink($dashboard_page_id));
    exit;
}

// Check if email verification message should be displayed
$registration_message = isset($_GET['registration_success']) ? "Registration successful! Please check your email for the activation link." : "";

?>

<div class="auth-background-container" id="auth-background-container">
    <div class="auth-container">
        <div class="inner-box">
            <?php if (!empty($business_logo)) { ?>
                <img src="<?php echo $business_logo; ?>" alt="dsmartcruise-logo">
            <?php } ?>
            
            <div class="auth-content">
                <h1>Client Registration</h1>
                
                <?php if (!empty($registration_message)) { ?>
                    <p style="color: green; font-weight: bold;"><?php echo esc_html($registration_message); ?></p>
                <?php } ?>

                <p>Welcome to the registration page. Please fill out the form below to create your account.</p>

                <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">

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
                <a href="<?php echo esc_url(home_url()); ?>">Back To Home</a>
            </div>
        </div>
    </div>
</div>

<?php wp_footer(); ?>
