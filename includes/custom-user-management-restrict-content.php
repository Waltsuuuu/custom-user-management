<?php

// Restrict pages from logged-out users
function cum_register_settings() {
    register_setting('cum_user_management_options', 'cum_restricted_pages', array(
        'type' => 'array',   // Save as array
        'sanitize_callback' => 'cum_sanitize_restricted_pages',
        array(
            'type' => 'array', // Save as array
            'sanitize_callback' => 'cum_sanitize_restricted_pages',
            'default' => array(),
        )
    ));
}
add_action('admin_init', 'cum_register_settings');

// Sanitization callback to ensure values are integers
function cum_sanitize_restricted_pages($input) {
    return array_map('intval', $input);
}

function cum_restrict_pages() {
    if (is_page()) {
        $restricted_pages = get_option('cum_restricted_pages', array());
        
        if (in_array(get_queried_object_id(), $restricted_pages) && !is_user_logged_in()) {
            // Redirect to login page or display a message
            wp_redirect(wp_login_url(get_permalink()));
            exit;
        }
    }
}
add_action('template_redirect', 'cum_restrict_pages');


// Filter menu items to hide restricted pages from non-logged-in users
function cum_filter_menu_items($items, $args) {
    // Get the restricted pages
    $restricted_pages = get_option('cum_restricted_pages', array());

    // Check if the user is not logged in
    if (!is_user_logged_in() && !empty($restricted_pages)) {
        // Create a new array to store filtered items
        $filtered_items = array();

        // Loop through each item
        foreach ($items as $item) {
            // Check if the menu item is restricted
            if (!in_array($item->object_id, $restricted_pages)) {
                // If not restricted, add to the filtered items array
                $filtered_items[] = $item;
            }
        }

        return $filtered_items;
    }

    return $items;
}
add_filter('wp_nav_menu_objects', 'cum_filter_menu_items', 10, 2);


// Hide the wordpress admin bar for basic users
function cum_hide_admin_bar() {
    if (is_user_logged_in() && current_user_can('basic_user')) { // change 'basic_user' to the role you want to hide the admin bar for
        show_admin_bar(false); // Hide the admin bar
    }
}
add_action('after_setup_theme', 'cum_hide_admin_bar');