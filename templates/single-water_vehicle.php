<?php
/*
Template Name: Single Water Vehicle
Template Post Type: water_vehicle
*/

get_header(); // Include WordPress header
?>

<body>
    <div class="vehicles-archive">
        <div class="hero-area">
            <h2>Vehicle Details</h2>
            <p><a href="<?php echo home_url(); ?>"><span>Home</span></a> > <a href="<?php echo get_post_type_archive_link('water_vehicle'); ?>"><span>Charter</span></a> > <span>Details</span></p>
        </div>
    </div>
    <div class="single-content-container" id="single-vehicle">
        <div class="inner">
            <div class="main-head">
                <div class="image-part">
                    <!-- Thumbnail -->
                    <?php
                    $thumbnail = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'large') : get_option('wp_rental_business_logo');
                    ?>
                    <img src="<?php echo esc_url($thumbnail); ?>" alt="vehicle-image" class="main-img">

                    <!-- Gallery Images -->
                    <div class="gallery-images">
                        <?php
                        // Display the thumbnail as the first gallery image
                        echo '<img src="' . esc_url($thumbnail) . '" alt="vehicle-image" class="gallery-img">';

                        // Display the gallery images from the post meta
                        $gallery_images = get_post_meta(get_the_ID(), 'gallery_images', true);
                        if ($gallery_images) {
                            $image_ids = explode(',', $gallery_images);
                            foreach ($image_ids as $image_id) {
                                $image_url = wp_get_attachment_image_url($image_id, 'large');
                                if ($image_url) {
                                    echo '<img src="' . esc_url($image_url) . '" alt="vehicle-image" class="gallery-img">';
                                }
                            }
                        }
                        ?>
                    </div>
                </div>
                <div class="excerpt-area">
                    <!-- Vehicle Title -->
                    <h3><?php the_title(); ?></h3>

                    <!-- Excerpt -->
                    <p><?php echo get_the_excerpt(); ?></p>

                    <!-- Vehicle Type -->
                    <p class="vehicle-type"><strong>Vehicle Type:</strong> <?php echo esc_html(get_post_meta(get_the_ID(), 'vehicle_type', true)); ?></p>

                    <!-- Availability -->
                    <?php
                    $availability_status = get_post_meta(get_the_ID(), 'availability_status', true);
                    $availability_class = strtolower($availability_status);
                    ?>
                    <p class="<?php echo esc_attr($availability_class); ?>"><?php echo esc_html($availability_status); ?></p>

                    <!-- Rental Pricing Form -->
                    <form action="booking-form">
                        <div class="radios">
                            <?php
                            $rental_pricing = get_post_meta(get_the_ID(), 'rental_pricing', true);
                            if ($rental_pricing && is_array($rental_pricing)) {
                                foreach ($rental_pricing as $package) {
                                    echo '<label class="radio-container">';
                                    echo '<input type="radio" name="package" value="' . esc_attr($package['duration']) . '">';
                                    echo '<span class="radio-custom"></span>';
                                    echo '<span class="radio-label"><strong>$' . esc_html($package['cost']) . '</strong> - ' . esc_html($package['duration']) . '</span>';
                                    echo '</label>';
                                }
                            }
                            ?>
                        </div>
                        <div class="action">
                            <!-- Call Us Button -->
                            <a href="tel:<?php echo esc_attr(get_option('wp_rental_admin_telephone')); ?>" class="call-us">Call Us</a>

                            <!-- Book Now Button (Only shown if available) -->
                            <?php if ($availability_status === 'Available') : ?>
                                <button id="book-now" type="submit">Book Now</button>
                            <?php endif; ?>
                        </div>
                    </form>
                    <!-- Error Notification -->
                    <div id="error-notification" class="error-notification">
                        Please select a rental package before booking.
                    </div>

                    <!-- Login Form -->
                    <div id="login-form" class="login-form" style="display: none;">
                        <form id="login-form-inner">
                            <input type="email" name="email" placeholder="Email" required>
                            <input type="password" name="password" placeholder="Password" required>
                            <div class="actions-alternatives">
                                <button type="submit" id="quick-login">Login</button>
                                <a href="<?php echo esc_url(get_permalink(get_option("wp_rental_register_page"))) ?>">Register</a>
                                <a class="recover-account" href="<?php echo esc_url(get_permalink(get_option('wp_rental_recover_page', ''))) ?>">Recover Account</a>
                            </div>

                        </form>
                    </div>



                </div>
            </div>
            <div class="details-area">
                <div class="tab-container">
                    <!-- Tab Buttons -->
                    <div class="tab-buttons">
                        <button class="tab-button active" data-tab="tab1">Description</button>
                        <button class="tab-button" data-tab="tab2">Vehicle Features</button>
                        <button class="tab-button" data-tab="tab3">Rental Pricing</button>
                        <button class="tab-button" data-tab="tab4">Booking Policy</button>
                    </div>

                    <!-- Tab Contents -->
                    <div class="tab-contents">
                        <!-- Description -->
                        <div id="tab1" class="tab-content active">
                            <?php the_content(); ?>
                        </div>

                        <!-- Vehicle Features -->
                        <div id="tab2" class="tab-content">
                            <?php
                            $vehicle_features = get_post_meta(get_the_ID(), 'vehicle_features', true);
                            if ($vehicle_features && is_array($vehicle_features)) {
                                echo '<table class="vehicle-features">';
                                foreach ($vehicle_features as $feature => $description) {
                                    echo '<tr>';
                                    echo '<td><strong>' . esc_html($feature) . '</strong></td>';
                                    echo '<td>' . esc_html($description) . '</td>';
                                    echo '</tr>';
                                }
                                echo '</table>';
                            }
                            ?>
                        </div>

                        <!-- Rental Pricing -->
                        <div id="tab3" class="tab-content">
                            <?php
                            if ($rental_pricing && is_array($rental_pricing)) {
                                echo '<table class="rental-pricing">';
                                echo '<tr><th>Duration</th><th>Cost</th></tr>';
                                foreach ($rental_pricing as $package) {
                                    echo '<tr>';
                                    echo '<td>' . esc_html($package['duration']) . '</td>';
                                    echo '<td>$' . esc_html($package['cost']) . '</td>';
                                    echo '</tr>';
                                }
                                echo '</table>';
                            }
                            ?>
                        </div>

                        <!-- Booking Policy -->
                        <div id="tab4" class="tab-content">
                            <?php echo wpautop(get_option('wp_rental_booking_policy')); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php get_footer(); // Include WordPress footer 
    ?>