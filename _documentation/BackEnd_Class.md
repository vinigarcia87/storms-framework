# BackEnd Class

Last updated on 2019-11-20 02:00:00 PM

### set_editor_style
Tell the TinyMCE editor to use a custom stylesheet
This theme styles the visual editor to resemble the theme style
The framework does not include a editor-style! The theme must create his own
- option( 'set_editor_style', 'yes' )

### change_admin_title
Change the <title> tag in admin area
The title will look like: 'Title of the page | My website'
* TODO Add a filter for this!
	
### set_admin_page_title
Change 'admin pages' title text to the website name and website description ### if defined
For example, "Dashboard" becomes "My Site | Another Wordpress blog"	
* TODO Add a filter for this!
	
### remove_appearance_editor
Remove editor menu from appearance panel
For safety reasons, we don't need that
	
### remove_links_from_menu
Remove sensitive menu itens from Worpress admin menu ### for any admin user that is not the "super user"
* TODO Does not remove anything right now, we should select some items to remove
- option( 'restricted_users_email', '/@storms.com.br$/' )
	
### remove_admin_color_scheme_picker
Stop users from switching Admin Color Schemes
Why anyone whould need this?
	
### disable_wp_update_for_non_admin
Disable the "please update now" message in WP dashboard for non admin users

### toolbar_system_environment_alert
Add an alert to admin bar to make clear what environment the user is conected to
It uses SF_ENV constant to check the current environment

### remove_adminbar_itens
Remove menu items from admin bar
They are unnecessary for most users, and you may need to add your own links
TODO Add some options or filters to allow the theme to exclude some links from the list

### remove_dashboard_widgets
Remove default dashboard widgets
Those are not useful for the average user
	
### add_dashboard_widgets
Add custom Storms dashboard widgets
Only visible to admin "super user"
SystemErrors Dashboard Widget: List errors shown on debug.log file
- option( 'restricted_users_email', '/@storms.com.br$/' )

### login_redirect
Redirect users to home on login, when they trying to access admin pages
but let admin and editor users go to wherever they want to

### login_error_msg
Generic login error message
For security reasons, it's not wise to tell hackers if they guess wrong the username or the password

	
	