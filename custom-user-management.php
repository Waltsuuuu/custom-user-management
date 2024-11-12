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
require_once('includes/custom-user-management-restrict-content.php');
require_once('includes/custom-user-management-custom-user-id.php');

// Register a custom role
function cum_register_custom_roles() {
    add_role(
        'kokelas',
        __('Kokelas'),
        array(
            'read' => true,
            'edit_posts' => false,
            'delete_posts' => false,
        )
    );

    add_role(
        'jäsen',
        __('Jäsen'),
        array(
            'read' => true,
            'edit_posts' => false,
            'delete_posts' => false,
        )
    );

    add_role(
        'kunniajäsen',
        __('Kunniajäsen'),
        array(
            'read' => true,
            'edit_posts' => false,
            'delete_posts' => false,
        )
    );

}
add_action('init', 'cum_register_custom_roles');


// Create admin page menu item 
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


// Remove default wordpress roles
function cum_remove_default_roles() {
    // Remove the 'Subscriber' role
    remove_role('subscriber');

    // Remove the 'Contributor' role
    remove_role('contributor');

    // Remove the 'Author' role
    remove_role('author');

    // Remove the 'Editor' role
    remove_role('editor');

}
add_action('init', 'cum_remove_default_roles');


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
