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
        'custom_manager',
        __('Custom Manager'),
        array(
            'read' => true,
            'edit_posts' => true,
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

// Admin page content
function cum_user_management_page() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }

    $users = get_users();
    ?>
    <div class="wrap">
        <h1>User Management</h1>
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
    </div>
    <?php
}

// User role management
function cum_set_default_user_role($user_id) {
    $user = new WP_User($user_id);
    $user->set_role('custom_manager');
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
