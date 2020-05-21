# FrontEnd Class

Last updated on 2019-11-20 02:00:00 PM

### setup_features
Setup the theme support
Content width
WP management of document title
Excerpt on pages
Post Thumbnails on posts and pages - Add custom post thumbnail sizes
HTML5 markup for search-form, comment-form, comment-list, gallery, caption
Post Formats: aside, image, video, quote, link, gallery, status, audio, chat
RSS feed links to HTML <head>
Wide alignment class for Gutenberg blocks
Custom Header
Custom Background
- option( 'content_width', 1140 )
- option( 'post_thumb_width' , 825 )
- option( 'post_thumb_height' , 510 )
- option( 'post_thumb_crop' , true )

### head_cleanup
Cleanup wp_head(), to remove unnecessary and unsafe wp meta tags

### remove_the_generator
Remove WP version meta tag

### title_separator
Change the separator between title tag parts
- option( 'title_separator', '|' )

### rel_canonical
Remove self-closing tag and change ''s to "'s on rel_canonical()

### modify_category_rel
Add rel="nofollow" and remove rel="category"

### modify_tag_rel
Add rel="nofollow" and remove rel="tag"

### clean_style_tag
Clean up output of stylesheet <link> tags

### clean_script_tag
Clean up output of <script> tags

### remove_self_closing_tags
Remove unnecessary self-closing tags

### language_attributes
Clean up language_attributes() used in <html> tag - Remove dir="ltr"

### remove_script_version
Remove version query string from all styles and scripts
Can add a custom timestamp based on the modification time of the functions.php
That can help to force browsers to purge cache for styles and scripts anytime we upload a new theme version
- option( 'timestamp_assets', 'yes' )

### body_class
Cleanup body classes
Add post/page slug if not present
Remove classes that show page/post id

### embed_wrap
Enclose embedded media in a div.
Wrapping all flash embeds in a div allows for easier styling with CSS media queries.
- apply_filters( 'oembed_wrap_classes', array( 'embed-wrap' ) )

### remove_emojis
Remove Emoji's from Wordpress
- option( 'remove_emoji', 'yes' )

### remove_wp_widget_recent_comments_style
Remove injected CSS for recent comments widget

### remove_recent_comments_style
Remove injected CSS from recent comments widget

### remove_default_gallery_style
Remove injected CSS from photo gallery

### add_category_slug
Add the category slug to the_category()

### menu_title_markup
Allow us to use #BR# on menu items, to add line-break
WP remove all html from menu items names, so we force <br> by using #BR#

### register_menus
Register wp_nav_menu() menus
Main Menu
- option( 'add_storms_menu', 'yes' )

### register_widgets_area
Register widgets area
Header Sidebar widget area
Main Sidebar widget area
Footer Sidebar widget area
- option( 'widget_title_tag', 'h3' )
- option( 'add_header_sidebar', 'yes' )
- option( 'add_main_sidebar', 'yes' )
- option( 'add_footer_sidebar', 'yes' )
- option( 'number_of_footer_sidebars', 4 )
