<?php

// Get current user data
$current_user = wp_get_current_user();
$user_id = $current_user->ID;
$name = $current_user->display_name;
$email = $current_user->user_email;
$phone = get_user_meta($user_id, 'phone_number', true);
$address = get_user_meta($user_id, 'address', true);
?>

<div class="account-container" id="account-tab-container">
    <h1>Account</h1>
    <div class="profile-info">
        <div class="info-item">
            <label>Name:</label>
            <span><?php echo esc_html($name); ?></span>
        </div>
        <div class="info-item">
            <label>Email:</label>
            <span><?php echo esc_html($email); ?></span>
        </div>
        <div class="info-item">
            <label>Phone:</label>
            <span><?php echo esc_html($phone); ?></span>
        </div>
        <div class="info-item">
            <label>Address:</label>
            <span><?php echo esc_html($address ? $address : 'No Address Given'); ?></span>
        </div>
    </div>
    <div class="actions">
        <button id="edit-profile-btn" class="btn-primary">Edit Profile</button>
        <button id="change-password-btn" class="btn-secondary">Change Password</button>
        <button id="delete-account-btn" class="btn-danger">Delete Account</button>
    </div>
</div>

<!-- Edit Profile Popup -->
<div id="edit-profile-popup" class="popup">
    <div class="popup-content">
        <h2>Edit Profile</h2>
        <form id="edit-profile-form">
            <div class="form-group">
                <label for="edit-name">Name:</label>
                <input type="text" id="edit-name" name="name" value="<?php echo esc_attr($name); ?>" required>
            </div>
            <br>
            <div class="form-group">
                <label for="edit-email">Email:</label>
                <input type="email" id="edit-email" name="email" value="<?php echo esc_attr($email); ?>" required>
            </div>
            <br>
            <div class="form-group">
                <label for="edit-phone">Phone:</label>
                <input type="text" id="edit-phone" name="phone" value="<?php echo esc_attr($phone); ?>">
            </div>
            <br>
            <div class="form-group">
                <label for="edit-address">Address:</label>
                <input type="text" id="edit-address" name="address" value="<?php echo esc_attr($address); ?>">
            </div>
            <br>
            <div class="form-actions">
                <button type="submit" class="btn-primary">Save Changes</button>
                <button type="button" id="cancel-edit-profile" class="btn-secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Change Password Popup -->
<div id="change-password-popup" class="popup">
    <div class="popup-content">
        <h2>Change Password</h2>
        <form id="change-password-form">
            <div class="form-group">
                <label for="current-password">Current Password:</label>
                <input type="password" id="current-password" name="current_password" required>
            </div>
            <br>
            <div class="form-group">
                <label for="new-password">New Password:</label>
                <input type="password" id="new-password" name="new_password" required>
            </div>
            <br>
            <div class="form-group">
                <label for="confirm-password">Confirm New Password:</label>
                <input type="password" id="confirm-password" name="confirm_password" required>
            </div>
            <br>
            <div class="form-actions">
                <button type="submit" class="btn-primary">Change Password</button>
                <button type="button" id="cancel-change-password" class="btn-secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Account Popup -->
<div id="delete-account-popup" class="popup">
    <div class="popup-content">
        <h2>Delete Account</h2>
        <p>Are you sure you want to delete your account? This action cannot be undone.</p>
        <div class="form-actions">
            <button id="confirm-delete-account" class="btn-danger">Confirm</button>
            <button id="cancel-delete-account" class="btn-secondary">Cancel</button>
        </div>
    </div>
</div>