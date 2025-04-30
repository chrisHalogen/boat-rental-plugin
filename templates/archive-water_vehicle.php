<?php
/*
Template Name: Water Vehicles Archive
Template Post Type: water_vehicle
*/

get_header(); // Include WordPress header
?>

<body>
    <div class="vehicles-archive">
        <div class="hero-area">
            <h2>Charter</h2>
            <p><a href="<?php echo home_url(); ?>"><span>Home</span></a> > <span>Charter</span></p>
        </div>
    </div>
    <div class="archive-content-container">
        <div class="archive-filters">
            <!-- Archive Filters -->
            <form action="<?php echo esc_url(get_post_type_archive_link('water_vehicle')); ?>" method="get">
                <select name="vehicle_type">
                    <option value="">All Types</option>
                    <option value="Jet Ski" <?php echo isset($_GET['vehicle_type']) && $_GET['vehicle_type'] === 'Jet Ski' ? 'selected' : ''; ?>>Jet Ski</option>
                    <option value="Boat" <?php echo isset($_GET['vehicle_type']) && $_GET['vehicle_type'] === 'Boat' ? 'selected' : ''; ?>>Boat</option>
                    <option value="Water Sports Car" <?php echo isset($_GET['vehicle_type']) && $_GET['vehicle_type'] === 'Water Sports Car' ? 'selected' : ''; ?>>Water Sports Car</option>
                </select>
                <select name="availability_status">
                    <option value="">All Availabilities</option>
                    <option value="Available" <?php echo isset($_GET['availability_status']) && $_GET['availability_status'] === 'Available' ? 'selected' : ''; ?>>Available</option>
                    <option value="Rented" <?php echo isset($_GET['availability_status']) && $_GET['availability_status'] === 'Rented' ? 'selected' : ''; ?>>Rented</option>
                    <option value="Under Maintenance" <?php echo isset($_GET['availability_status']) && $_GET['availability_status'] === 'Under Maintenance' ? 'selected' : ''; ?>>Under Maintenance</option>
                </select>
                <input type="text" name="s" placeholder="Search by name" value="<?php echo isset($_GET['s']) ? esc_attr($_GET['s']) : ''; ?>">
                <input type="submit" value="Filter">
            </form>
        </div>
        <div class="archive-section">
            <?php
            $paged = (isset($_GET['pagenumber']) && !empty($_GET['pagenumber'])) ? intval($_GET['pagenumber']) : 1;
            $args = array(
                'post_type' => 'water_vehicle',
                'posts_per_page' => 6,
                'paged' => $paged,
                'meta_query' => array(),
            );


            // Filter by vehicle type (stored as post meta)
            if (isset($_GET['vehicle_type']) && !empty($_GET['vehicle_type'])) {
                $args['meta_query'][] = array(
                    'key' => 'vehicle_type',
                    'value' => sanitize_text_field($_GET['vehicle_type']),
                    'compare' => '=',
                );
            }

            // Filter by availability status (stored as post meta)
            if (isset($_GET['availability_status']) && !empty($_GET['availability_status'])) {
                $args['meta_query'][] = array(
                    'key' => 'availability_status',
                    'value' => sanitize_text_field($_GET['availability_status']),
                    'compare' => '=',
                );
            }

            // Ensure meta_query uses AND relationship if both filters are applied
            if (count($args['meta_query']) > 1) {
                $args['meta_query']['relation'] = 'AND';
            }

            // Search by vehicle name
            if (isset($_GET['s']) && !empty($_GET['s'])) {
                $args['s'] = sanitize_text_field($_GET['s']);
            }

            $query = new WP_Query($args);

            if ($query->have_posts()) :
                while ($query->have_posts()) : $query->the_post();
                    $availability_status = get_post_meta(get_the_ID(), 'availability_status', true);
                    $vehicle_type_name = get_post_meta(get_the_ID(), 'vehicle_type', true);
                    $thumbnail = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'large') : get_option('wp_rental_business_logo');
                    $excerpt = get_the_excerpt() ? wp_trim_words(get_the_excerpt(), 60) : 'Click to Learn More';

                    $vehicle_type_class = "";

                    if (isset($vehicle_type_name) && $vehicle_type_name == "Jet Ski") {
                        $vehicle_type_class = "jet-ski";
                    } else if (isset($vehicle_type_name) && $vehicle_type_name == "Boat") {
                        $vehicle_type_class = "boat";
                    } else if (isset($vehicle_type_name) && $vehicle_type_name == "Water Sports Car") {
                        $vehicle_type_class = "water-sports-car";
                    }
            ?>
                    <div class="vehicle-card">
                        <span class="vehicle-type <?php echo esc_attr($vehicle_type_class); ?>"><?php echo esc_html(ucfirst(str_replace('-', ' ', $vehicle_type_name))); ?></span>
                        <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php the_title(); ?>" class="thumbnail">
                        <div class="text-content">
                            <h3 class="vehicle-name"><?php the_title(); ?></h3>
                            <p class="clamped-text"><?php echo esc_html($excerpt); ?></p>
                            <div class="divider"></div>
                            <div class="action">
                                <p class="<?php echo esc_attr(strtolower($availability_status == "Under Maintenance" ? "under-maintenance" : $availability_status)); ?>"><?php echo esc_html($availability_status); ?></p>
                                <a href="<?php the_permalink(); ?>">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else : ?>
                <p>No vehicles found.</p>
            <?php endif; ?>
        </div>

        <div class="archive-pagination">
            <?php

            echo paginate_links(array(
                'base' => add_query_arg('pagenumber', '%#%'),
                'format' => '',
                'total' => $query->max_num_pages,
                'prev_text' => __('« Previous'),
                'next_text' => __('Next »'),
                'current' => max(1, $paged),
            ));
            ?>
        </div>
    </div>

    <?php wp_reset_postdata(); ?>
</body>

<?php get_footer(); // Include WordPress footer 
?>