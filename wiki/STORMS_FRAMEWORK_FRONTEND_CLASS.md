- FrontEnd Class
	- Load Storms language file
	- Add excerpt on pages support
	- Add support for title-tag, so WP can manage <title> tag for us - no need to put it on head ourselfs
	- Enable post thumbnails on post and pages
		- Define post thumb width and height (825x510) an thumb crop as true
	- Enable html5 support for search-form, comment-form, comment-list, gallery, caption
	- Enable post-formats for aside, image, video, quote, link, gallery, status, audio, chat [REMOVE! this has no need]
	- Add defalut posts and commentes RSS feed links to head [CHECK THIS! Not sure what is this for]
	- Clean up wp_head() to remove unnecessary and unsafe wp meta tags
	- Remove WP version meta tag
	- Add Custom Header support
	- Add Custom Background support
	- Add custom post images sizes
		- storms-post-main: 650x420, crop: true
		- storms-post-thumb: 240x240, crop: true  [CONFLITO]
	- Add custom gallery image sizes
		- storms-gallery-main: 400x250
		- storms-gallery-thumb: 200x125
	- Define title separator between title tag parts as '|'
	- Remove self-closing tag and change ''s to "'s on rel_canonical() [CHECK THIS! Not sure what is this for]
	- Add rel="nofollow" and remove rel="category" [CHECK THIS! Not sure what is this for]
	- Add rel="nofollow" and remove rel="tag" [CHECK THIS! Not sure what is this for]
	- Clean up <stylesheet> tags, removing media when is not needed
	- Clena up <script> tag, removing type="text/javascript"
	- Remove unnecessary self-closing tags ('/>') on get_avatar, comment_id_fields and post_thumbail_html [CHECK THIS! Seems useless]
	- Remove version from all styles and scripts - if option 'timestamp_assets is true, add and timestamp from the last modified date of the {{current_theme}}/functions.php file
	- Clean up language_attributes() used in <html> tag, removing dir="ltr"
	- Clean up body classes, removing 'page-id-*', 'postid-*', etc and adding page/post slug as class
	- If 'theme_layout' is defined, add layout classes to tinymce body class [CHECK THIS! Not sure what is this for]
	- Wrap embedded media as suggested by Readability [CHECK THIS! Not sure what is this for]
	- Disable automatic paragraph tags (wpautop) on the_content and the_excerpt
	- Remove emojis from WP
	- Remove CSS for recent comments widget [CHECK THIS! Need to know what this affect - There is possible duplicated code here]
	- Remove CSS for galleries [CHECK THIS! Need to know what this affect]
	- Add meta_description and meta_keywords meta tags [CHECK THIS! This is done better by 3rd-party plugins]
	- Add category slug as class in the_category() function
	- Allow using '#BR#' as <BR> on menu itens - use this if you want to break menu item in lines
	- Making custom media sizes choosable from the Media Gallery
		- storms-post-main
		- storms-post-thumb
		- storms-gallery-main
		- storms-gallery-thumb
	- Custom Post Gallery: override the default 'gallery' shortcode, using a custom style written with Cycle2 jquery plugin [CHECK THIS! It's loading the jquery lib everywhere unnecessarily; Add some configuration to disable this feature]
	- Add a 'track post' feature, counting every time a user read a post and displaying the count number on post list
	- Add a shortcode to show the most popular posts, based on 'track post' feature
	

	- Storms options defined on this class:
		- post_thumb_width: 825
		- post_thumb_height: 510
		- post_thumb_crop: true
		
		- post_main_width: 650
		- post_main_height: 420
		- post_main_crop: true

		- post_thumb_width: 240 [CONFLITO]
		- post_thumb_height: 240 [CONFLITO]
		- post_thumb_crop: true [CONFLITO]

		- gallery_main_width: 400
		- gallery_main_height: 250
		- gallery_main_crop: true

		- gallery_thumb_width: 200
		- gallery_thumb_height: 125
		- gallery_thumb_crop: true

		- title_separator: '|'
		- meta_description: ''
		- meta_keywords: ''

		- load_cycle2: true
