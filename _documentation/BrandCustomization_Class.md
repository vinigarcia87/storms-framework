# BrandCustomization Class

Last updated on 2019-11-20 02:00:00 PM

### set_admin_scripts
Add custom admin scripts

### add_brand_meta_tags
Add meta tags with brand information to the website header
Meta tags added: author meta tag, copyright meta tag
- option( 'meta_autor', 'Storms Websolutions' )
- option( 'meta_copyright', '&copy; 2012 - ' . date('Y') . ' ' . __( 'by', 'storms' ) . ' <strong>' . $brand_name . '</strong> - ' . __( 'All rights reserved', 'storms' ) . '.' )

### set_default_favicon
Add favicon to the website
- option( 'website_favicon', '/img/storms/icons/storms_favicon.png' )

### add_menu_user_card_developed_by
Add "user card" and "developed by" card on admin menu
* TODO Add options for the user define what to show here

### change_footer_text
Change dashboard and login footer text
- option( 'meta_autor', 'Storms Websolutions' )
- option( 'meta_copyright', '&copy; 2012 - ' . date('Y') . ' ' . __( 'by', 'storms' ) . ' <strong>' . $brand_name . '</strong> - ' . __( 'All rights reserved', 'storms' ) . '.' )

### adminbar_color_scheme
Color schemes do not apply to admin bar at front end, even if user is logged in - This is the way to force it

### add_adminbar_brand_link
Add brand icon to admin bar
* TODO Add some options for customization

### add_dashboard_widgets
Add custom dashboard widgets
BrandInfo Dashboard Widget: Shows a box with some brand information, like contact email, version, etc

### login_scripts
Add scripts and styles to customize the login page

### login_page_script
Scripts that change some aspects of the login page
Document title, always check "remember me", "back to blog" text and "password recovery" text

### change_login_logo_url
Change the url of the logo, to be the website URL

### change_login_logo_url_title
Change the title of the logo, to be the website name


