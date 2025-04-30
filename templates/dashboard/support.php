<?php
// Define contact details
$support_email = get_option('wp_rental_admin_email');
$support_phone = get_option('wp_rental_admin_telephone');
$support_address = get_option('wp_rental_admin_address');
$address_link = 'https://maps.app.goo.gl/gqUemXZPeAiQmShT7'; // Replace with the actual link you provide
?>

<div class="support-container" id="support-container">
    <!-- <h1>Support</h1> -->

    <!-- Contact Details -->
    <div class="contact-details">
        <!-- <h2>Contact Us</h2> -->
        <h1>Contact Us</h1>
        <div class="contact-item">
            <label>Email:</label>
            <a href="mailto:<?php echo esc_attr($support_email); ?>" class="contact-link">
                <?php echo esc_html($support_email); ?>
            </a>
        </div>
        <div class="contact-item">
            <label>Phone:</label>
            <a href="tel:<?php echo esc_attr($support_phone); ?>" class="contact-link">
                <?php echo esc_html($support_phone); ?>
            </a>
        </div>
        <div class="contact-item">
            <label>Address:</label>
            <a href="<?php echo esc_url($address_link); ?>" target="_blank" class="contact-link">
                <?php echo esc_html($support_address); ?>
            </a>
        </div>
    </div>

    <!-- Complaint Form -->
    <div class="complaint-form">
        <h1>Lodge a Complaint</h1>
        <form id="complaint-form">
            <div class="form-group">
                <label for="message-title">Message Title:</label>
                <input type="text" id="message-title" name="message_title" required>
            </div>
            <div class="form-group">
                <label for="message-content">Message:</label>
                <textarea id="message-content" name="message_content" rows="5" required></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-primary">Send Message</button>
            </div>
        </form>
    </div>
</div>