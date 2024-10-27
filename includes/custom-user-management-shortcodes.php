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
                wp_redirect(home_url('/login'));
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


// Shortcode for login form -- [cum_login_form]
function cum_login_form() {
    if (is_user_logged_in()) {
        echo '<p>You are already logged in!</p>';
        return;
    }

    ob_start();
    wp_login_form();
    return ob_get_clean();
}
add_shortcode('cum_login_form', 'cum_login_form');


// Shortcode for logout button -- [cum_logout_button] 
function cum_logout_button() {
    if (is_user_logged_in()) {
        // Display the logout button
        return '<form action="' . wp_logout_url(home_url()) . '" method="post">
                    <button type="submit" style="padding: 10px 20px; background-color: #f00; color: #fff; border: none; cursor: pointer;">Logout</button>
                </form>';
    } else {
        // Optional: If the user is not logged in, display login button
        return '<a href="' . wp_login_url() . '" style="padding: 10px 20px; background-color: #0073aa; color: #fff; border: none; cursor: pointer;">Login</a>';
    }
}
add_shortcode('cum_logout_button', 'cum_logout_button');
