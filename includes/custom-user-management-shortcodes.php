<?php
// Set user role on registration - default is 'basic_user'
function cum_set_default_user_role($user_id) {
    $user = new WP_User($user_id);
    $user->set_role('basic_user');
}
add_action('user_register', 'cum_set_default_user_role');


// Shortcode for login form -- [cum_login_form]
function cum_login_form() {
    if (is_user_logged_in()) {
        echo '<p>' . __('Olet kirjautunut sisään.', 'custom-user-management') . '</p>';
        return;
    }

    // Start output buffering
    ob_start();

    // Get the current page URL to use as the redirect URL
    $redirect_to = esc_url($_SERVER['REQUEST_URI']);

    ?>
    <form action="<?php echo esc_url(site_url('wp-login.php', 'login_post')); ?>" method="post">
        <p>
            <label for="username"><?php _e('Käyttäjätunnus', 'custom-user-management'); ?></label>
            <input type="text" name="log" id="username" required>
        </p>
        <p>
            <label for="password"><?php _e('Salasana', 'custom-user-management'); ?></label>
            <input type="password" name="pwd" id="password" required>
        </p>
        <p>
            <button type="submit"><?php _e('Kirjaudu', 'custom-user-management'); ?></button>
        </p>

        <?php wp_nonce_field('cum_login_action', 'cum_login_nonce'); ?>

        <!-- Redirect to the same page after login -->
        <input type="hidden" name="redirect_to" value="<?php echo $redirect_to; ?>" />

        <!-- Add "Forgot Password" Link -->
        <p>
            <a href="<?php echo esc_url(wp_lostpassword_url()); ?>">
                <?php _e('Salasana hukassa?', 'custom-user-management'); ?>
            </a>
        </p>

    </form>
    <?php

    // End output buffering and return content
    return ob_get_clean();
}
add_shortcode('cum_login_form', 'cum_login_form');


// Shortcode for logout button -- [cum_logout_button] 
function cum_logout_button() {
    // Get the current page URL to use as the redirect URL after logout
    $redirect_to = esc_url($_SERVER['REQUEST_URI']);

    if (is_user_logged_in()) {
        // Display the logout button with the redirect URL set to the current page
        return '<form action="' . wp_logout_url($redirect_to) . '" method="post">
                    <button type="submit" style="padding: 10px 20px; background-color: #f00; color: #fff; border: none; cursor: pointer;">
                        Kirjaudu ulos
                    </button>
                </form>';
    }
}
add_shortcode('cum_logout_button', 'cum_logout_button');