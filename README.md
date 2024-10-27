# custom-user-management plugin for wordpress

### Shotcodes
[cum_registration_form] - Displays a registrtion form. <br/>
[cum_login_form] - Displays log in form, with remember me checkbox / Alerts if user is already logged in. <br/>
[cum_logout_button] - Displays a logout button. <br/>

## Current state
- Shortcode to display user registration form. 
- Able to create a user through registration form, user gets user ID (which can also be manually changed by admin).
- Admin can also manually create users. 
- Shortcode to dislay log in form. Alerts if user is already logged in.
- Shortcode to display logout button if user is logged in.
- Ability to reset password through email. (Currently uses default wordpress password reset url)
- Wordpress admin bar is not displayed for basic "read only" users. 
- Admin can select which pages are visible only to logged in users. If user is not logged in and attempts to view the page they are sent to a log in page.
- Pages that are visible to only logged in users are not visible as menu items for non logged in users.
- Export User ID, Username, Email, "Custom User ID", "First Name", "Last Name", Role as CSV.
