<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}


function wp_rental_register_water_vehicles()
{
    $labels = array(
        'name'          => 'Water Vehicles',
        'singular_name' => 'Water Vehicle',
        'menu_name'     => 'Water Vehicles',
        'add_new'       => 'Add New Vehicle',
        'add_new_item'  => 'Add New Water Vehicle',
        'edit_item'     => 'Edit Water Vehicle',
        'new_item'      => 'New Water Vehicle',
        'view_item'     => 'View Vehicle',
        'search_items'  => 'Search Water Vehicles',
        'not_found'     => 'No vehicles found',
        'not_found_in_trash' => 'No vehicles found in Trash',
    );

    $args = array(
        'labels'        => $labels,
        'public'        => true,
        'menu_icon'     => 'dashicons-car', // Jet ski/car icon in WP admin
        'supports'      => array('title', 'editor', 'excerpt', 'thumbnail'),
        'has_archive'   => true,
        'show_in_rest'  => true, // Enables Gutenberg support
        'rewrite' => array('slug' => 'water-vehicles'), // Custom slug for the archive
    );

    register_post_type('water_vehicle', $args);
}

add_action('init', 'wp_rental_register_water_vehicles');

// Adding Vehicle Type and Availability to the archive table
// function wp_rental_add_vehicle_columns($columns) {
//     $columns['vehicle_type'] = 'Vehicle Type';
//     $columns['availability_status'] = 'Availability';
//     return $columns;
// }
// add_filter('manage_edit-water_vehicle_columns', 'wp_rental_add_vehicle_columns');

// Now, populate the custom columns with data.
// function wp_rental_display_vehicle_columns($column, $post_id) {
//     if ($column == 'vehicle_type') {
//         echo esc_html(get_post_meta($post_id, 'vehicle_type', true));
//     }

//     if ($column == 'availability_status') {
//         echo esc_html(get_post_meta($post_id, 'availability_status', true));
//     }
// }
// add_action('manage_water_vehicle_posts_custom_column', 'wp_rental_display_vehicle_columns', 10, 2);


// Make Columns Sortable
function wp_rental_make_vehicle_columns_sortable($sortable_columns)
{
    $sortable_columns['vehicle_type'] = 'vehicle_type';
    $sortable_columns['availability_status'] = 'availability_status';
    return $sortable_columns;
}
add_filter('manage_edit-water_vehicle_sortable_columns', 'wp_rental_make_vehicle_columns_sortable');


function wp_rental_add_vehicle_columns($columns)
{
    $columns['vehicle_type'] = 'Vehicle Type';
    $columns['availability_status'] = 'Availability';
    $columns['rental_pricing'] = 'Rental Pricing';
    return $columns;
}
add_filter('manage_edit-water_vehicle_columns', 'wp_rental_add_vehicle_columns');

function wp_rental_display_vehicle_columns($column, $post_id)
{
    if ($column == 'vehicle_type') {
        echo esc_html(get_post_meta($post_id, 'vehicle_type', true));
    }
    if ($column == 'availability_status') {
        echo esc_html(get_post_meta($post_id, 'availability_status', true));
    }
    if ($column == 'rental_pricing') {
        $pricing = get_post_meta($post_id, 'rental_pricing', true);
        if (!empty($pricing)) {
            foreach ($pricing as $p) {
                echo esc_html($p['duration'] . ': $' . $p['cost']) . '<br>';
            }
        } else {
            echo 'N/A';
        }
    }
}
add_action('manage_water_vehicle_posts_custom_column', 'wp_rental_display_vehicle_columns', 10, 2);
