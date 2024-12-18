# custom-user-management plugin for wordpress

### Shotcodes
[cum_registration_form] - Displays registration form.
[cum_login_form] - Displays log in form, with remember me checkbox / Alerts if user is already logged in. <br/>
[cum_logout_button] - Displays a logout button, if user is logged in. <br/>

## Current state
- Admin can manually create users. 
- Custom user ID, to represent membership number (Executor#).
- Shortcode to display registration form. Create user with username, email, password. 
- Shortcode to dislay log in form. Alerts if user is already logged in.
- Shortcode to display logout button if user is logged in.
- Ability to reset password through email. (Currently uses default wordpress password reset url and UI)
- Wordpress admin bar is not displayed for basic "read only" users. 
- Ability to restrict access to pages for non logged in users. Pages that are restricted do not show up on non logged in users menu bar.
- Export User ID, Username, Email, "Custom User ID", "First Name", "Last Name", Role as CSV.
