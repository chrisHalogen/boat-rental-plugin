<?php
/*
Template Name: Water Vehicles Archive
Template Post Type: water_vehicle
*/

$fleet_page_id = get_option('wp_rental_fleet_page');

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
            <form action="<?php echo esc_url(get_permalink($fleet_page_id)); ?>" method="get">
                <select name="vehicle_type">
                    <option value="">All Types</option>
                    <option value="jet-ski">Jet Ski</option>
                    <option value="boat">Boat</option>
                    <option value="water-sports-car">Water Sports Car</option>
                </select>
                <select name="availability_status">
                    <option value="">All Availabilities</option>
                    <option value="available">Available</option>
                    <option value="rented">Rented</option>
                    <option value="under-maintenance">Under Maintenance</option>
                </select>
                <input type="text" name="s" placeholder="Search by name">
                <input type="submit" value="Filter">
            </form>
        </div>
        <div class="archive-section">
            <?php
            $paged = (get_query_var('pagenumber')) ? get_query_var('pagenumber') : 1;
            $args = array(
                'post_type' => 'water_vehicle',
                'posts_per_page' => 6,
                'paged' => $paged,
                'meta_query' => array(),
                'tax_query' => array(),
            );

            // Filter by vehicle type
            if (isset($_GET['vehicle_type']) && !empty($_GET['vehicle_type'])) {
                $args['tax_query'][] = array(
                    'taxonomy' => 'vehicle_type',
                    'field' => 'slug',
                    'terms' => sanitize_text_field($_GET['vehicle_type']),
                );
            }

            // Filter by availability status
            if (isset($_GET['availability_status']) && !empty($_GET['availability_status'])) {
                $args['meta_query'][] = array(
                    'key' => 'availability_status',
                    'value' => sanitize_text_field($_GET['availability_status']),
                    'compare' => '=',
                );
            }

            // Search by vehicle name
            if (isset($_GET['s']) && !empty($_GET['s'])) {
                $args['s'] = sanitize_text_field($_GET['s']);
            }

            $query = new WP_Query($args);

            if ($query->have_posts()) :
                while ($query->have_posts()) : $query->the_post();
                    $availability_status = get_post_meta(get_the_ID(), 'availability_status', true);
                    $thumbnail = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'medium') : get_option('wp_rental_business_logo');
                    $excerpt = get_the_excerpt() ? wp_trim_words(get_the_excerpt(), 10) : 'Click to Learn More';
                    $vehicle_type_name = get_post_meta(get_the_ID(), 'vehicle_type', true);
                    // $vehicle_type_terms = get_the_terms(get_the_ID(), 'vehicle_type');
                    // $vehicle_type_class = $vehicle_type_terms ? $vehicle_type_terms[0]->slug : '';
                    // $vehicle_type_name = $vehicle_type_terms ? $vehicle_type_terms[0]->name : '';

                    $vehicle_type_class = "";
                    $availability_class = "";

                    if (isset($vehicle_type_name) && $vehicle_type_name == "Jet Ski") {
                        $vehicle_type_class = "jet-ski";
                    } else if (isset($vehicle_type_name) && $vehicle_type_name == "Boat") {
                        $vehicle_type_class = "boat";
                    } else if (isset($vehicle_type_name) && $vehicle_type_name == "Water Sports Car") {
                        $vehicle_type_class = "water-sports-car";
                    }

                    if (isset($availability_status) && $availability_status == "Available") {
                        $availability_class = "available";
                    } else if (isset($availability_status) && $availability_status == "Under Maintenance") {
                        $availability_class = "not-available";
                    } else if (isset($availability_status) && $availability_status == "Rented") {
                        $availability_class = "rented";
                    }


            ?>
                    <div class="vehicle-card">
                        <span class="vehicle-type <?php echo esc_attr($vehicle_type_class); ?>"><?php echo esc_html($vehicle_type_name); ?></span>
                        <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php the_title(); ?>" class="thumbnail">
                        <div class="text-content">
                            <h3 class="vehicle-name"><?php the_title(); ?></h3>
                            <p><?php echo esc_html($excerpt); ?></p>
                            <div class="divider"></div>
                            <div class="action">
                                <p class="<?php echo esc_attr(strtolower($availability_class)); ?>"><?php echo esc_html($availability_status); ?></p>
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
                'total' => $query->max_num_pages,
                'prev_text' => __('« Previous'),
                'next_text' => __('Next »'),
            ));
            ?>
        </div>
    </div>

    <?php wp_reset_postdata(); ?>
</body>

<?php get_footer(); // Include WordPress footer 
?>