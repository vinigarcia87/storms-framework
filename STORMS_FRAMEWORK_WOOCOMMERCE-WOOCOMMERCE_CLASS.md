- Bootstrap/Bootstrap Class
	- Enable WooCommerce support
	- If use_wc_product_gallery is true, enable WC gallery zoom, swipe and slider
	- Remove WC meta tags from <head>
	- If use_wc_style is false, we remove all WC default styles
	- If manage_woocommerce_scripts is true, we dequeue all WC scripts from non WC pages (is_woocommerce(), is_cart(), is_checkout())  [CHECK THIS! This is confusing... not sure if it is right]
	  if it is an WC page, we check if use_wc_product_gallery is true; if it's not, we dequeue all WC product gallery's scripts
	- Customize the shop page title
	- If redirect_to_checkout_on_click_buy is true, redirect the user to checkout when adding product to cart
	- If remove_tab_description is true, we remove the product description tab from product page
	- If remove_tab_reviews is true, we remove the product review tab from product page
	- If remove_tab_additional_information is true, we remove the product additional information tab from product page
	- Customize all form fields from WC with Bootstrap style [CHECK THIS! This should be on Bootstrap Class, checking first if WC support is active]
	- Define WC deafult image dimensions (no options here, but it can be changed on WC configuration page): [CHECK THIS! Is always updating WC options... If the user change those values, this function will override everything]
		- catalog images: 236x236, crop true
		- single images: 527x527, crop true
		- thumbnail images: 153x153, crop true
	- Changes the WC pages accordingly with the user defined layouts for product_layout (product page) and shop_layout (shop/category/tag page) [CHEK THIS! First, maybe this shouldn't be here, but in Layout Class (It even depends on Layout Class); Second, the fallback for wrong layout don't consider possible user included layouts]
	- Defines product sidebar (sidebar on product page) and shop sidebar (sidebar on shop/category/tag page) - We remove the sidebar action from woocommerce_sidebar because we have our own way of dealing with sidebars
	- Customize WC breadcrumb with Boostrap layout [CHECK THIS! If should be on Boostrap Class]
	- Define default number of thumbnail columns for product's images
	- Define the deafult number of products per page and number of columns on shop page
	- Override the deafult WC feature_products shortcode, by a customized one
	- Change number of related products on product page [CHECK THIS! Need options to allow changing]
	- Change number of products to be shown on cross-sells loop [CHECK THIS! Need options to allow changing]
	- Change number of columns to be shown on cross-sells loop [CHECK THIS! Need options to allow changing]
	- Customize the loop, adding Bootstrap grid classes to products accordingly to the number of columns [CHECK THIS! This should be here or in Boostrap Class?]
	- Adding clearfixes after loop items to allow better responsive layout [CHECK THIS! This may not be working - is marked as not finished]

	- Storms options defined on this class:
		- use_wc_product_gallery: true
		- use_wc_style: false
		- manage_woocommerce_scripts: false
		- use_wc_product_gallery: true
		- shop_page_title: {$page_title}
		- redirect_to_checkout_on_click_buy: false
		- remove_tab_description: false
		- remove_tab_reviews: false
		- remove_tab_additional_information: false
		- product_layout: '2c-r'
		- shop_layout: '2c-r'
		- add_wc_breadcrumb_before_main_content: true
		- customize_woo_breadcrumb: true
		- storms_product_thumbnail_columns: 4
		- products_per_page: depends on the number of columns defined: if number of columns is 3, then products_per_page is 9; if number of columns is greater than 2, then products_per_page is 12
		- shop_loop_number_of_columns: 4
		- widget title tag: 'h3'

- Bootstrap/Functions Class [CHECK THIS! Check if this functions are necessary or useful at all]
	- Check if WC is active on this website
	- Display product search, using WC_Widget_Product_Search
	- Get the product thumbnail or the placeholder if not set
	- Retrieve a post's terms as a list with specified format - improve get_the_term_list() allowing custom css classes to the term link
	- Override woocommerce_subcategory_thumbnail, removing fixed width and height on thumbnails
	- Redefine woocommerce_output_related_products
	- woocommerce_single_variation_add_to_cart_button()
	- storms_header_cart()
	- storms_cart_link()






