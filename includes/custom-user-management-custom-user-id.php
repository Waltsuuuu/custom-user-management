<?php

// Add custom_user_id field to the user profile in the WordPress admin
// Allows admin to set a custom user ID for each user, ex. if you have an external user database.
function cum_add_custom_user_id_profile_field($user) {
    // Get the current value of custom_user_id
    $custom_user_id = get_user_meta($user->ID, 'custom_user_id', true);
    ?>
    <h3>Executor#</h3>
    <table class="form-table">
        <tr>
            <th><label for="custom_user_id">Executor#</label></th>
            <td>
                <input type="text" name="custom_user_id" id="custom_user_id" value="<?php echo esc_attr($custom_user_id); ?>" class="regular-text" />
                <br><span class="description">Jäsennumero</span>
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
