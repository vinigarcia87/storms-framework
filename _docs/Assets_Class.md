# Assets Class

Last updated on 2019-11-20 02:00:00 PM

### stylesheet_uri
Custom stylesheet URI
get_stylesheet_uri() return assets/css/style.min.css

### enqueue_main_style
Register and load main theme stylesheet

### remove_unused_styles
We remove some well-know plugin's styles, so you can add them manually only on the pages you need
Styles that we remove are: contact-form-7, newsletter-subscription, newsletter_enqueue_style

### jquery_scripts
Enqueue jQuery scripts
- option( 'load_jquery', 'yes' )
- option( 'load_external_jquery', 'no' )

### jquery_local_fallback
Output the local fallback immediately after jQuery's <script>
Only if external jquery is been used
- option( 'load_external_jquery', 'no' )

### remove_unused_scripts
We remove some well-know plugin's scripts, so you can add them manually only on the pages you need
Scripts that we remove are: jquery-form, contact-form-7, newsletter-subscription, wp-embed

### frontend_scripts
Register main theme script
Register cycle2 and cycle2-carousel script
Adjust Thread comments WordPress script to load only on specific pages
* TODO Check if cycle2 is necessary