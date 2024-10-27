<?php
/*
Plugin Name: Custom User Management
Description: A plugin to manage user registration, login, roles, and user management.
Version: 1.0
Author: Waltteri Heino
*/

// Ensure direct access is not allowed
if (!defined('ABSPATH')) {
    exit;
}

require_once('includes/custom-user-management-shortcodes.php');
require_once('includes/custom-user-management-export-user-data.php');

// Register a custom role
function cum_register_custom_roles() {
    add_role(
        'basic_user',
        __('Basic User'),
        array(
            'read' => true,
            'edit_posts' => false,
            'delete_posts' => false,
        )
    );
}
add_action('init', 'cum_register_custom_roles');

// User management for admins
function cum_add_admin_page() {
    add_menu_page(
        'User Management',
        'User Management',
        'manage_options',
        'cum-user-management',
        'cum_user_management_page',
        'dashicons-admin-users',
        100
    );
}
add_action('admin_menu', 'cum_add_admin_page');

// Register the Setting for Restricted Pages
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

$restricted_pages = get_option('cum_restricted_pages', array());

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

// Admin page content
function cum_user_management_page() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }

    $users = get_users();
    $pages = get_pages();
    $restricted_pages = get_option('cum_restricted_pages', array());

    ?>
    <div class="wrap">
        <h1>User Management</h1>

        <!-- User Table -->
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>User ID</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo esc_html($user->user_login); ?></td>
                    <td><?php echo esc_html($user->user_email); ?></td>
                    <td><?php echo esc_html(implode(', ', $user->roles)); ?></td>
                    <td><?php echo esc_html(get_user_meta($user->ID, 'custom_user_id', true)); ?></td>
                    <td><a href="<?php echo admin_url('user-edit.php?user_id=' . $user->ID); ?>">Edit</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Restricted Pages Selection -->
        <h2>Pages Accessible Only to Logged-In Users</h2>
<form method="post" action="options.php">
    <?php
    // Register settings and sections
    settings_fields('cum_user_management_options');
    do_settings_sections('cum_user_management_options');
    ?>

    <table class="form-table">
        <tr valign="top">
            <th scope="row">Select Pages</th>
            <td>
                <?php foreach ($pages as $page) : ?>
                    <label style="display: block; margin-bottom: 8px;">
                        <input type="checkbox" name="cum_restricted_pages[]" value="<?php echo esc_attr($page->ID); ?>"
                            <?php echo in_array($page->ID, (array) $restricted_pages) ? 'checked' : ''; ?>>
                        <?php echo esc_html($page->post_title); ?>
                    </label>
                <?php endforeach; ?>
                <p class="description">Check pages that should be restricted to logged-in users only.</p>
            </td>
        </tr>
    </table>

    <?php submit_button(); ?>
</form>
    </div>
    <?php
}

// User role management
function cum_set_default_user_role($user_id) {
    $user = new WP_User($user_id);
    $user->set_role('basic_user');
}
add_action('user_register', 'cum_set_default_user_role');



// Add custom_user_id field to the user profile in the WordPress admin
// Allows admin to set a custom user ID for each user, ex. if you have an external user database.
function cum_add_custom_user_id_profile_field($user) {
    // Get the current value of custom_user_id
    $custom_user_id = get_user_meta($user->ID, 'custom_user_id', true);
    ?>
    <h3>Custom User ID</h3>
    <table class="form-table">
        <tr>
            <th><label for="custom_user_id">Custom User ID</label></th>
            <td>
                <input type="text" name="custom_user_id" id="custom_user_id" value="<?php echo esc_attr($custom_user_id); ?>" class="regular-text" />
                <br><span class="description">This is the user's unique ID.</span>
            </td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'cum_add_custom_user_id_profile_field');
add_action('edit_user_profile', 'cum_add_custom_user_id_profile_field');

// Save custom_user_id when the profile is updated in the admin panel
function cum_save_custom_user_id_profile_field($user_id) {
    // Check if the user has the capability to edit users
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    // Update the custom_user_id field
    if (isset($_POST['custom_user_id'])) {
        update_user_meta($user_id, 'custom_user_id', sanitize_text_field($_POST['custom_user_id']));
    }
}
add_action('personal_options_update', 'cum_save_custom_user_id_profile_field');
add_action('edit_user_profile_update', 'cum_save_custom_user_id_profile_field');


// Automatically add user_id when a user registers
function cum_add_user_id_on_registration($user_id) {
    // Generate a custom user_id or use $user_id (which is the WordPress ID)
    $custom_user_id = 'UID-' . $user_id . '-' . wp_generate_password(5, false); // e.g., UID-1-abcde

    // Save it as user meta
    update_user_meta($user_id, 'custom_user_id', $custom_user_id);
}
add_action('user_register', 'cum_add_user_id_on_registration');

// Hide the wordpress admin bar for basic users
function cum_hide_admin_bar() {
    if (is_user_logged_in() && current_user_can('basic_user')) { // change 'basic_user' to the role you want to hide the admin bar for
        show_admin_bar(false); // Hide the admin bar
    }
}
add_action('after_setup_theme', 'cum_hide_admin_bar');

