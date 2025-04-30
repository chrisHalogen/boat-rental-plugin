<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

function wp_rental_add_vehicle_meta_boxes($post)
{
    // Ensure we're on the edit/add screen for the 'water_vehicle' post type
    add_meta_box(
        'wp_rental_vehicle_details',
        'Vehicle Details',
        'wp_rental_vehicle_details_callback',
        'water_vehicle', // Only for 'water_vehicle'
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'wp_rental_add_vehicle_meta_boxes');

function wp_rental_save_vehicle_meta($post_id)
{
    if (isset($_POST['vehicle_type'])) {
        update_post_meta($post_id, 'vehicle_type', sanitize_text_field($_POST['vehicle_type']));
    }

    if (isset($_POST['availability_status'])) {
        update_post_meta($post_id, 'availability_status', sanitize_text_field($_POST['availability_status']));
    }

    if (isset($_POST['gallery_images'])) {
        $image_ids = array_filter(array_map('sanitize_text_field', explode(',', $_POST['gallery_images'])));
        update_post_meta($post_id, 'gallery_images', implode(',', $image_ids));
    }

    if (isset($_POST['is_featured'])) {
        update_post_meta($post_id, 'is_featured', sanitize_text_field($_POST['is_featured']));
    }

    // Save rental pricing properly as an array
    if (isset($_POST['rental_pricing']) && is_array($_POST['rental_pricing'])) {
        $pricing_data = array();

        foreach ($_POST['rental_pricing'] as $pricing) {


            if (!empty($pricing['duration']) && !empty($pricing['cost'])) {
                $pricing_data[] = array(
                    'duration' => sanitize_text_field($pricing['duration']),
                    'cost' => floatval($pricing['cost'])
                );
            }
        }

        update_post_meta($post_id, 'rental_pricing', $pricing_data);
    }

    // Save features as an associative array
    if (isset($_POST['vehicle_features_keys']) && isset($_POST['vehicle_features_values'])) {
        $features = [];
        $keys = $_POST['vehicle_features_keys'];
        $values = $_POST['vehicle_features_values'];

        foreach ($keys as $index => $key) {
            $key = sanitize_text_field($key);
            $value = sanitize_text_field($values[$index] ?? '');
            if (!empty($key)) {
                $features[$key] = $value;
            }
        }
        update_post_meta($post_id, 'vehicle_features', $features);
    }
}
add_action('save_post', 'wp_rental_save_vehicle_meta');



function wp_rental_vehicle_details_callback($post)
{
    $vehicle_type = get_post_meta($post->ID, 'vehicle_type', true);
    $availability_status = get_post_meta($post->ID, 'availability_status', true);
    $rental_pricing = get_post_meta($post->ID, 'rental_pricing', true) ?: [];
    $gallery_images = get_post_meta($post->ID, 'gallery_images', true);



    // Ensure it's an array
    $gallery_images = explode(',', $gallery_images);

    // Trim any spaces to avoid issues
    $gallery_images = array_map('trim', $gallery_images);

    $features = get_post_meta($post->ID, 'vehicle_features', true) ?: []; // Retrieve stored features



?>
    <p>
        <label for="vehicle_type">Vehicle Type:</label>
        <select name="vehicle_type" id="vehicle_type">
            <option value="Jet Ski" <?php selected($vehicle_type, 'Jet Ski'); ?>>Jet Ski</option>
            <option value="Boat" <?php selected($vehicle_type, 'Boat'); ?>>Boat</option>
            <option value="Water Sports Car" <?php selected($vehicle_type, 'Water Sports Car'); ?>>Water Sports Car</option>
        </select>
    </p>

    <p>
        <label for="is_featured">Featured Vehicle:</label>
        <select name="is_featured" id="is_featured">
            <option value="no" <?php selected(get_post_meta($post->ID, 'is_featured', true), 'no'); ?>>No</option>
            <option value="yes" <?php selected(get_post_meta($post->ID, 'is_featured', true), 'yes'); ?>>Yes</option>
        </select>
    </p>

    <!-- Rental Pricing Section -->


    <h4>Rental Pricing</h4>
    <div id="rental_pricing_wrapper">
        <?php foreach ($rental_pricing as $index => $pricing) { ?>
            <div class="pricing-item">
                <select name="rental_pricing[<?php echo $index; ?>][duration]" required>
                    <option value="1 hour" <?php selected($pricing['duration'], '1 hour'); ?>>1 Hour</option>
                    <option value="3 hours" <?php selected($pricing['duration'], '3 hours'); ?>>3 Hours</option>
                    <option value="6 hours" <?php selected($pricing['duration'], '6 hours'); ?>>6 Hours</option>
                    <option value="1 day" <?php selected($pricing['duration'], '1 day'); ?>>1 Day</option>
                    <option value="3 days" <?php selected($pricing['duration'], '3 days'); ?>>3 Days</option>
                    <option value="1 week" <?php selected($pricing['duration'], '1 week'); ?>>1 Week</option>
                </select>
                <input type="number" step="0.01" name="rental_pricing[<?php echo $index; ?>][cost]" value="<?php echo esc_attr($pricing['cost']); ?>" placeholder="Cost" required />
                <button type="button" class="remove-pricing remove_rental_pricing">❌</button>
            </div>
        <?php } ?>
    </div>
    <button type="button" id="add_rental_pricing" class="button">Add Pricing</button>
    <!-- <button type="button" id="add-pricing">➕ Add Pricing</button> -->



    <p>
        <label for="availability_status">Availability Status:</label>
        <select name="availability_status" id="availability_status">
            <option value="Available" <?php selected($availability_status, 'Available'); ?>>Available</option>
            <option value="Rented" <?php selected($availability_status, 'Rented'); ?>>Rented</option>
            <option value="Under Maintenance" <?php selected($availability_status, 'Under Maintenance'); ?>>Under Maintenance</option>
        </select>
    </p>

    <p>
        <label>Gallery Images:</label>
        <button type="button" class="button" id="upload_gallery_button">Add Gallery Images</button>
        <input type="hidden" name="gallery_images" id="gallery_images" value="<?php echo esc_attr(implode(',', $gallery_images)); ?>" />
    </p>

    <div id="gallery_preview">
        <?php
        if (!empty($gallery_images)) {
            foreach ($gallery_images as $image_id) {
                if (is_numeric($image_id)) {
                    $image_url = wp_get_attachment_url($image_id);
                    if ($image_url) {
                        echo '<div class="gallery-item" data-id="' . esc_attr($image_id) . '">
                                <img src="' . esc_url($image_url) . '" style="width:100px; margin:5px;" />
                                <button type="button" class="remove-image button">Remove</button>
                            </div>';
                    }
                }
            }
        }
        ?>
    </div>


    <h3>Vehicle Features</h3>
    <div id="vehicle_features_container">
        <?php if (!empty($features)) : ?>
            <?php foreach ($features as $key => $value) : ?>
                <div class="feature-row">
                    <input type="text" name="vehicle_features_keys[]" value="<?php echo esc_attr($key); ?>" placeholder="Feature Name">
                    <input type="text" name="vehicle_features_values[]" value="<?php echo esc_attr($value); ?>" placeholder="Feature Description">
                    <button type="button" class="remove-feature button">Remove</button>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="feature-row">
                <input type="text" name="vehicle_features_keys[]" placeholder="Feature Name">
                <input type="text" name="vehicle_features_values[]" placeholder="Feature Description">
                <button type="button" class="remove-feature button">Remove</button>
            </div>
        <?php endif; ?>
    </div>
    <button type="button" id="add_feature" class="button">Add Feature</button>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const container = document.getElementById("vehicle_features_container");
            document.getElementById("add_feature").addEventListener("click", function() {
                const div = document.createElement("div");
                div.classList.add("feature-row");
                div.innerHTML = '<input type="text" name="vehicle_features_keys[]" placeholder="Feature Name"> ' +
                    '<input type="text" name="vehicle_features_values[]" placeholder="Feature Description"> ' +
                    '<button type="button" class="remove-feature button">Remove</button>';
                container.appendChild(div);
            });

            container.addEventListener("click", function(event) {
                if (event.target.classList.contains("remove-feature")) {
                    event.target.parentElement.remove();
                }
            });

        });
    </script>
    <style>
        .feature-row {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
            max-width: 50rem;
        }

        .feature-row input {
            flex: 1;
        }

        .rental_pricing_entry {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }

        .rental_pricing_entry select,
        .rental_pricing_entry input {
            margin-right: 10px;
        }

        .remove_rental_pricing {
            /* background: red; */
            color: white;
            border: none;
            cursor: pointer;
        }

        .pricing-item {
            margin-bottom: 10px;
        }
    </style>

<?php
}
