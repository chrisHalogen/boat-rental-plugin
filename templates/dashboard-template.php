<?php
/**
 * Template Name: User Dashboard
 * Description: A simple user dashboard template.
 */

get_header();
?>

<div class="dashboard-container">
    <h2>Welcome to Your Dashboard</h2>
    
    <p>Hello, <?php echo wp_get_current_user()->display_name; ?>!</p>
    
    <ul>
        <li><a href="<?php echo esc_url(home_url('/profile')); ?>">Edit Profile</a></li>
        <li><a href="<?php echo esc_url(wp_logout_url(home_url())); ?>">Logout</a></li>
    </ul>
</div>

<?php get_footer(); ?>
