<?php

// Shortcode for registration form -- [cum_registration_form]
function cum_registration_form() {
    ob_start(); ?>
    <form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
        <p>
            <label for="username">Username</label>
            <input type="text" name="username" value="<?php echo (isset($_POST['username']) ? esc_attr($_POST['username']) : ''); ?>">
        </p>
        <p>
            <label for="email">Email</label>
            <input type="email" name="email" value="<?php echo (isset($_POST['email']) ? esc_attr($_POST['email']) : ''); ?>">
        </p>
        <p>
            <label for="password">Password</label>
            <input type="password" name="password">
        </p>
        <p><input type="submit" name="submit_registration" value="Register"/></p>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('cum_registration_form', 'cum_registration_form');

// Get the current page URL to use as the redirect URL
$redirect_to = esc_url($_SERVER['REQUEST_URI']);



// -- Processing registration form.
function cum_handle_registration() {
    if (isset($_POST['submit_registration'])) {
        $username = sanitize_user($_POST['username']);
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];

        $errors = [];

        if (username_exists($username)) {
            $errors[] = "Username already exists.";
        }

        if (email_exists($email)) {
            $errors[] = "Email is already in use.";
        }

        if (empty($password)) {
            $errors[] = "Password cannot be empty.";
        }

        if (empty($errors)) {
            $user_id = wp_create_user($username, $password, $email);

            if (!is_wp_error($user_id)) {
                wp_new_user_notification($user_id, null, 'both');
                wp_redirect(home_url($redirect_to));
                exit;
            } else {
                echo "Error: " . $user_id->get_error_message();
            }
        } else {
            foreach ($errors as $error) {
                echo "<p>$error</p>";
            }
        }
    }
}
add_action('wp', 'cum_handle_registration');

// Set user role on registration - default is 'basic_user'
function cum_set_default_user_role($user_id) {
    $user = new WP_User($user_id);
    $user->set_role('basic_user');
}
add_action('user_register', 'cum_set_default_user_role');





// Shortcode for login form -- [cum_login_form]
function cum_login_form() {
    if (is_user_logged_in()) {
        echo '<p>' . __('You are already logged in.', 'custom-user-management') . '</p>';
        return;
    }

    // Start output buffering
    ob_start();

    // Get the current page URL to use as the redirect URL
    $redirect_to = esc_url($_SERVER['REQUEST_URI']);

    ?>
    <form action="<?php echo esc_url(site_url('wp-login.php', 'login_post')); ?>" method="post">
        <p>
            <label for="username"><?php _e('Username', 'custom-user-management'); ?></label>
            <input type="text" name="log" id="username" required>
        </p>
        <p>
            <label for="password"><?php _e('Password', 'custom-user-management'); ?></label>
            <input type="password" name="pwd" id="password" required>
        </p>
        <p>
            <button type="submit"><?php _e('Login', 'custom-user-management'); ?></button>
        </p>

        <?php wp_nonce_field('cum_login_action', 'cum_login_nonce'); ?>

        <!-- Redirect to the same page after login -->
        <input type="hidden" name="redirect_to" value="<?php echo $redirect_to; ?>" />

        <!-- Add "Forgot Password" Link -->
        <p>
            <a href="<?php echo esc_url(wp_lostpassword_url()); ?>">
                <?php _e('Forgot your password?', 'custom-user-management'); ?>
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
                        Logout
                    </button>
                </form>';
    }
}
add_shortcode('cum_logout_button', 'cum_logout_button');