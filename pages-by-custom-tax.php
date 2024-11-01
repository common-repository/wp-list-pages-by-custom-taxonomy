<?php
/*
Plugin Name: WP List Pages by Custom Taxonomy
Description: Widget that allow to list XX posts of any active post-type, filtering by any term of any active custom taxonomy, and display only title or thumbnail, date and excerpt too. you can also exclude specific posts by id, and filter/order by meta fields!
Author: Andrea Piccart
Version: 1.4.10
Author URI: http://bespokewebdeveloper.com
Text Domain: wp-list-pages-by-custom-taxonomy
Domain Path: /languages
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
 
	WP List Pages by Custom Taxonomy is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 2 of the License, or
	any later version.
	 
	WP List Pages by Custom Taxonomy is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.
	 
	You should have received a copy of the GNU General Public License
	along with WP List Pages by Custom Taxonomy. If not, see https://www.gnu.org/licenses/gpl-2.0.html

*/

// Block direct access to this file
if ( !defined('ABSPATH') ) {
	die('-1');
}

/** 
 * DEFINE CONSTANTS
 *
 * define plugin version
 * define the required template version
 *
 * @since v 1.3.0
 		  v 1.3.1 added PBYTAX_MIN_TEMPLATE_VERSION
 */	

define( "PBYTAX_VERSION", "1.4.10" );
define( "PBYTAX_TEMPLATE_VERSION", "1.4.7" );
define( "PBYTAX_MIN_TEMPLATE_VERSION", "1.4.2" ); 


/**
 * register the widget
 *
 *@since v. 1.0
 */
add_action( 'widgets_init', 'register_pbytax_widget');	
function register_pbytax_widget() {
	register_widget( 'Pages_by_Tax' );
}

/** 
 * localize the language files
 *
 * @since v. 1.3.0
 */
add_action('init', 'pbytax_action_init');
function pbytax_action_init(){
	load_plugin_textdomain('wp-list-pages-by-custom-taxonomy', false, basename( dirname( __FILE__ ) ) . '/languages/' );
}


/**
 * load scripts used in all admin pages
 *
 * used to load the scripts which manage the dismissible alerts
 *
 * @since v 1.3.1
 */
add_action('admin_init', 'pbytax_admin_init_scripts');
function pbytax_admin_init_scripts(){
	wp_register_script('pbytax-admin-notices', plugins_url('js/pbytax_admin_notices.js', __FILE__), array( 'jquery' ), PBYTAX_VERSION );
	wp_enqueue_script('pbytax-admin-notices');	
}


/**
 * ACTIVATION AND DEACTIVATION ROUTINES
 */
register_activation_hook( __FILE__, 'pbytax_activation_routine' );
register_deactivation_hook( __FILE__, 'pbytax_deactivation_routine' );

function pbytax_activation_routine(){

	pbytax_set_template_version();
}

function pbytax_deactivation_routine (){
    // clear cookies
	pbytax_clear_notices_cookie();
	// clear the template version
    delete_option( 'pbytax_template_version' );
}

/**
 * clear the cookie for closing the warning notices
 * fired on plugin activation
 *
 * @hooked to register_deactivation_hook
 *
 * @since v 1.3.1
 */
function pbytax_clear_notices_cookie(){
	if ( isset($_COOKIE['pbytax_notice_closed']) ){
		unset($_COOKIE['pbytax_notice_closed']);
		// empty value and expiration one hour before, to trigger browser's cookie deletion
		setcookie('pbytax_notice_closed', '', time() - 3600);
	}
	
}




/**
 * CHECK CUSTOM TEMPLATE STATUS
 *
 * @uses pbytax_set_template_version() to search for a customized template file within the them folder and store the version
 * @uses pbytax_get_template_version() to retrieve the current template version
 *
 * if the file exists and it doesn't have the proper @version in the header, it will trigger a hard warning admin notice
 * if the version is prior than the minum required, it will trigger a hard warning
 * if the file doesn't exists (therefore it is using the plugin default which is up to date) will do nothing
 * if the version is up to date, will do nothing
 * 
 * @since v 1.3.0
 		  v 1.3.1 	moved some logic into pbytax_get_template_version()
					added soft notice if template is not too much outdated, and hard warning if template is corrupted or older than the minimum required
 */
 
add_action('admin_init', 'pbytax_check_custom_template_status');	
function pbytax_check_custom_template_status(){

    // check for current template version
	pbytax_set_template_version();
	// get the current version
	$template_version = pbytax_get_template_version();

	// quit if the version is current (or if there isn't a custom template)
	if ( PBYTAX_TEMPLATE_VERSION == $template_version ){
		return;	
	}

	// if the template version is missing, trigger the hard warning notice
	if ( false === $template_version ){
		add_action( 'admin_notices', 'pbytax_template_update_hard_alert' );
		return;
	}
	
	// check how much outdated is the current version
	// first convert the version strings into full numbers
	$version_num = str_replace('.',  '', $template_version );
	$min_version = str_replace('.',  '', PBYTAX_MIN_TEMPLATE_VERSION );
	// if version is prior the minimum required, trigger an hard warning
	if ( intval($version_num) < intval($min_version) ){
		add_action( 'admin_notices', 'pbytax_template_update_hard_alert' );
		return;	
	}
	else { // display a dismissable soft warning notice
		// if we find the cookie which should hide this notice, don't display it
		if ( isset($_COOKIE['pbytax_notice_closed']) && 'pbytax-update-notice' == $_COOKIE['pbytax_notice_closed'] ){
			return;	
		}
		add_action( 'admin_notices', 'pbytax_template_update_soft_alert' );
					
	}
			
}

	
/**
 * GET TEMPLATE FILE VERSION
 * 
 * will grab the value stored in the option "pbytax_template_version"
 *
 * @return {string} the number of the current version
 		@return false if version is not found
		@return latest version number if there is no custom template file
 *
 * @since v. 1.3.1
 *        v. 1.4.6  changed this function to just retrieve the number stored in wp-options
 *                  the logic that was in this function is now in pbytax_set_template_version()
 */
 
function pbytax_get_template_version(){

    $template_version = get_option( 'pbytax_template_version' );

    if ( $template_version ) {
	    return trim( $template_version );
    }
    else {
        return false;
    }
}

/**
 * SET THE TEMPLATE VERSION
 * will search for a customized template file within the theme folder
 *
 * this function is fired by pbytax_check_custom_template_status()
 * which is attached to admin_init hook
 *
 * update the wp option with the version number
 *
 * @since v. 1.4.6
 */
function pbytax_set_template_version() {

    // check for template in active theme
	$template = locate_template( array( 'pbytax_template.php' ) );

	// if there isn't a customized version, store the current original version
	if ( ! $template ) {
		update_option( 'pbytax_template_version', PBYTAX_TEMPLATE_VERSION );
		return PBYTAX_TEMPLATE_VERSION;
	}

	// scan the content and search for the @version prefix
	$template_content = fopen( $template, 'r' );
	$version_string   = false;
	// read file line by line
	while ( ( $buffer = fgets( $template_content ) ) !== false ) {
		if ( strpos( $buffer, '@version' ) !== false ) {
			$version_txt_pos = strpos( $buffer, '@version' );
			$version_string  = substr( $buffer, ( $version_txt_pos + 9 ), 6 );
			break;
		}
	}
	fclose( $template_content );

	// if it's not found, means the template is very outdated or corrupted. so we return FALSE
	if ( false === $version_string || ! $template_content ) {
		// store it in the db
		update_option( 'pbytax_template_version', false );
		return false;
	}

	// strip eventual spaces
	$version_string = trim( $version_string );

	// store it in the db
	update_option( 'pbytax_template_version', $version_string );

	return true;
}


/**
 * DISPLAY WARNING NOTICE IF TEMPLATE IS VERY OUTDATED
 *
 * @since v. 1.3.0
 */
	
function pbytax_template_update_hard_alert(){
	?>
    <div id="pbytax-update-hard-notice" class="error notice">
    	<p><strong><?php _e( 'LIST PAGES BY CUSTOM TAXONOMY', 'wp-list-pages-by-custom-taxonomy' ); ?>:</strong></p>
      	<p><?php _e( 'Your Theme contains a customized template file ( pbytax_template.php ) which is either too old or corrupted, and should be updated!', 'wp-list-pages-by-custom-taxonomy' ); ?></p>
        <p><?php _e( 'Please check the plugin folder and make a new custom file starting from the latest version.', 'wp-list-pages-by-custom-taxonomy' ); ?></p>
        <p><small><?php _e( 'In order to dismiss this notice, your custom template file must have at least this version (or later) in the header:', 'wp-list-pages-by-custom-taxonomy' ); ?></small><strong><?php echo ' @version '.PBYTAX_MIN_TEMPLATE_VERSION; ?></strong><br/><?php _e( 'latest version is', 'wp-list-pages-by-custom-taxonomy' );  echo "<strong> ".PBYTAX_TEMPLATE_VERSION."</strong>"; ?>
	</div>
    <?php
}

/**
 * DISPLAY NOTICE IF TEMPLATE IS NOT UP TO DATE
 *
 * this is a dismissible notice. see /js/pbytax_admin_notices.js for the logic behind the hide/display/close 
 *
 * @since v. 1.3.1
 */
	
function pbytax_template_update_soft_alert(){
	?>
    <div id="pbytax-update-notice" class="update-nag notice is-dismissible">
    	<p><strong><?php _e( 'LIST PAGES BY CUSTOM TAXONOMY', 'wp-list-pages-by-custom-taxonomy' ); ?>:</strong></p>
      	<p><?php _e( 'Your Theme contains a customized template file ( pbytax_template.php ) which is not the latest one!', 'wp-list-pages-by-custom-taxonomy' ); ?></p>
        <p><?php _e( 'The new version contains only minor changes, therefore the update is not mandatory, though it is recommended.', 'wp-list-pages-by-custom-taxonomy' ); ?>
		<?php _e( 'Please check the plugin folder and make a new custom file starting from the latest version.', 'wp-list-pages-by-custom-taxonomy' ); ?></p>
        <p><small><?php _e( 'You can dismiss this notice, but in order to stop it from appearing again, your custom template file must have this version in the header:', 'wp-list-pages-by-custom-taxonomy' ); ?></small><strong><?php echo ' @version '.PBYTAX_TEMPLATE_VERSION; ?></strong></p>
	</div>
    <?php
}

/**
 * Display the post title
 * Will eventually truncate it if longer than the title_max_length
 * @filter  'pbytax_post_title' allow to modify the title before it's printed out
 *
 * @param WP_Post   $post       the currently looped post object
 * @param array     $instance   the widget instance with all the options
 */
function pbytax_the_title( $post, $instance ){

    if ( !$post || !$post->post_title ){
        return;
    }

	$title = $full_title = $post->post_title;

    // eventually trim it
    if ( $instance['title_max_length'] ){
        $max_length = intval( $instance['title_max_length'] );

        if ( strlen($title) > $max_length ) {
	        $title = substr( $post->post_title, 0, $max_length );
	        $title .= '...';
        }
    }

    echo apply_filters('pbytax_post_title', $title, $full_title, $instance );

}

/**
 * Used to display the title/first-option of the dropdown
 * for now it's basically just a wrapper to filter the title
 * @filter 'pbytax_dropdown_title'
 *
 * @param string    $dropdown_text      text string as per widget options
 * @param array     $instance           full list of current widget options
 * @param array     $data               ( 'count' => count($pbytax_posts) )
 *
 * @return string
 */
function pbytax_the_dropdown_title( $dropdown_text, $instance, $data = array() ){

    $dropdown_text = apply_filters( 'pbytax_dropdown_title', $dropdown_text, $instance, $data );

    echo $dropdown_text;
}


/**
 * WIDGET MAIN CLASS
 *
 * Adds Pages_by_Tax widget.
 * Widget Main Class which extends WP_Widget
 *
 * @since v. 1.0
 */
class Pages_by_Tax extends WP_Widget {	

	/**
	 * Register widget with WordPress.
	 *
	 * @since v. 1.0
	 */
	function __construct() {
		parent::__construct(
			'Pages_by_Tax', // Base ID
			__('WP Pages by Custom Taxonomy', 'wp-list-pages-by-custom-taxonomy'), // Name
			array( 'description' => __( 'list posts of any post-type, filtering by any term of any active custom taxonomy', 'pages-by-custom-tax' ), ) // Args
		);
		// load the scripts and styles for the admin
		add_action( 'sidebar_admin_setup', array( $this, 'pbytax_admin_setup' ) );
		// load the styles for the frontend
		add_action( 'wp_enqueue_scripts',array( $this, 'pbytax_styles_setup') );
	}
	
	/**
	 * add scripts and styles to the setting page
	 *
	 * @since v. 1.0
	 */
	function pbytax_admin_setup() {
		wp_register_script('pbytax-admin-js', plugins_url('js/pbytax_admin.js', __FILE__), array( 'jquery' ), PBYTAX_VERSION );
		wp_enqueue_script('pbytax-admin-js');
		wp_register_style('pbytax-admin-styles', plugins_url('css/pbytax-admin-style.css',__FILE__ ), false, PBYTAX_VERSION, false);
		wp_enqueue_style('pbytax-admin-styles'); 
		
	}
	/** 
	 * load styles in frontend
	 *
	 * @since v. 1.0
	 */
	function pbytax_styles_setup() {
		wp_register_style('pbytax-styles', plugins_url('css/pbytax-style.css',__FILE__ ), false, PBYTAX_VERSION, false);
		wp_enqueue_style('pbytax-styles');
	}
	

	/**
	 * Front-end display of widget.
	 * @see WP_Widget::widget()
	 * 
	 * searches for a template file in the theme dir first, then eventually loads it from the plugin folder
	 *
	 * @param {array} $args     Widget arguments.
	 * @param {array} $instance Saved values from database.
	 *
	 * @since v. 1.0.0
	 		v. 1.4.0 query args included from here, and get_posts query made from here (added support for prior versions)
	 */
	public function widget( $args, $instance ) {
		
		// store  the widget number
		$widget_num = $this->number;
		
		// get template version and convert it to a number
		$template_version = pbytax_get_template_version();
		$template_version = $template_version ? str_replace('.',  '', $template_version ) : 0;
		
		// include the file with the logic which builds the query args and query the posts
		// we have to consider that versions prior 1.3.1 will include that file (and make the query) directly from the template file
		// @since 1.4.0
		$hide_widget = $already_queried = false;
		if ( $template_version && 131 <= intval($template_version) ){
			
			// elaborate the $instance options and prepare its variables values for the query
			$instance = $this->pbytax_prepare_instance_vars( $instance );
		
			// setup the query args
			$pbytax_args = $this->pbytax_setup_query_args( $instance );
			
			// query it!
			$pbytax_posts = get_posts( $pbytax_args );
			$already_queried = true;
			
			// check if the widget should be completely hidden (i.e. acts as sub-pages navigation and there are no subpages)
			if ( $instance['display_only_children'] == "yes" && ( empty($pbytax_posts) || !is_singular() || !is_post_type_hierarchical(get_post_type()) ) ){
				$hide_widget = true;
			}
			
			// extract all variables (see get_defaults() for a list of parameters)
			// in versions prior to 1.4.0 this will be done within the pbytax_template.php or pbytax_args.php
			extract( $instance, EXTR_OVERWRITE );
		}
		
		// if we haven't got anything, and the widget is set to act as sub-pages navigator, display nothing at all 
		// this works only from v 1.3.1. other prior versions would display an empty div in this circumstance ("no posts found" notice will be hidden though)
		if ( isset($hide_widget) && true === $hide_widget ){
			return;
		}
		else {		
			// print before widget options and title
			echo $args['before_widget'];
			if ( !empty($instance['title']) && $instance['current_page_as_title'] != "yes" ) {
				echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
			}
			
			// use a template for the output so that it can easily be overridden by theme
			// check for template in active theme
			$template = locate_template(array('pbytax_template.php'));
	
			if ( $template == "" || !$template ){
			// if none found use the default template
				$template = plugin_dir_path( __FILE__ ) . 'templates/pbytax_template.php';
			}
			include ( $template ); 
			
			// print after widget
			echo $args['after_widget'];			
		}
		
	}


	
	/**
	 * Set defaults for the options
	 *
	 * @return array of defaults
	 *
	 * @filter 'pbytax_default_options' filter the defaults array before it's returned
	 		@param $defaults array of defaults. must be returned by the hook
	 *
	 * @since v 1.4.0
	 */
	 
	 public function get_defaults() {
		// set defaults
		$defaults = array (
			'title' => 'Widget title',
			'intro' => '',
			'max_entries' => 10,
			'filter_post_type' => 'post',
			'filter_taxonomy' => 'category',
			'filter_term' => array('any'),
			'meta_key_name' => 'none',
			'meta_key_value' => '',
			'meta_is_number' => 'no',
			'meta_compare' => '=',
			'order_by' => 'date',
			'order_style' => 'DESC',
			'include_children' => 'true',
			'display_thumb' => 'no',
			'link_thumb' => 'no',
			'thumb_size' => 'thumbnail',
			'thumb_align' => 'left',
			'thumb_max_width' => 60,
			'display_excerpt' => 'no',
			'excerpt_length' => 50,
			'display_date' => 'no',
			'display_mod_date' => 'no',
			'date_align' => 'left',
			'date_format' => 'd-m-Y',
			'display_in_dropdow' => 'no',
			'dropdown_text' => 'Browse&#8230;',
			'text_float' => 'flow-below',
			'title_no_wrap' => 'false',
			'title_max_length' => 0,
			'display_only_thumb' => 'no',
			'display_thumb_overlay' => 'no',
			'display_only_children' => 'no',
			'current_page_as_title' => 'no',
			'exclude_posts' => '',
			'exclude_current_post' => 'no'
			);
		
		// add a filter to allow modify of the defaults array
		// this filter will apply to all the widget instances
		$defaults = apply_filters( 'pbytax_default_options', $defaults );	
			
		return $defaults;

	 }
	 
	 
	 
	 /**
	 * Set up instance options variables
	 *
	 * get the instance and elaborate the options to be ready for query
	 *
	 * @uses get_defaults() to ensure that $instance contains all the correct info
	 * @see get_defaults() for a list of the $instance parameters
	 *
	 * @param {array}	$instance	the instance array containing the options passed by the settings form
	 *
	 * @return {array}	$new_instance	an updated version of the options array, with the addition of custom variables
	 *
	 * @filter 'pbytax_prepare_instance'	filter the new options array before it is returned
	 			@param {array}	$new_instance	the prepared options array. MUST be returned by the filter
				@param {array}	$instance	the options array as it came from the widget settings
				@param {int}	$widget_num	the number of the widget instance
	 *
	 * @since v 1.4.0
	 */
	
	public function pbytax_prepare_instance_vars( $instance ){		
		// quit if no $instance is passed
		if ( !$instance ){
			return;
		}
	
		// get and parse the dafaults with the instance options
		$defaults = $this->get_defaults();
		// if vars are set, override defaults
		$instance = wp_parse_args( $instance, $defaults );
	
		// store the vars from the widget instance into the new $instance and eventually elaborate them
		$new_instance = $instance;
	 
	 	// get the current_page_id and current_parent_id and add them to the instance vars
		// ensure we're on a singular page/post, otherwise we'd retrieve the wrong data
		if ( is_singular() ){
			global $wp_query;
			$new_instance['current_page_id'] = $wp_query->post->ID;
			$new_instance['current_parent_id'] = $wp_query->post->post_parent;
			// if this page has a parent and we want to display only children of the same parent, then we want the page_id to be the parent rather than the current page
			if ( $new_instance['current_parent_id'] && $new_instance['display_only_children'] == "yes" ){
				$new_instance['current_page_id'] = $new_instance['current_parent_id'];
			}	
		}
		else {
			$new_instance['current_page_id'] = "";
			$new_instance['current_parent_id'] = "";
		}	 
	 	
		// if we want all the entries, set the correct value
		if ( $new_instance['max_entries'] == "0" ){
			$new_instance['max_entries']= -1;
		}
		// if filter_term is empty or if it is set to display any term, set the value to "any"
		if ( $new_instance['filter_term'] != "" ){
			if ( is_array($new_instance['filter_term']) ){
				if ( in_array('any', $new_instance['filter_term']) ){
					$new_instance['filter_term'] = 'any';
				}
			}
		}
		else {
			$new_instance['filter_term'] = 'any';
		}	
		
		// if we should display only children of the same parent, ensure that include_children is also set to "yes"
		if ( $new_instance['display_only_children'] == "yes" ){
			$new_instance['include_children'] = "yes";
		}
		
		// convert the exclude post list to an array
		if ( $new_instance['exclude_posts'] != "" ){
			$new_instance['exclude_posts'] = explode("-", $new_instance['exclude_posts']);	
		}
		else {
			$new_instance['exclude_posts'] = array();
		}
		// if orderby is set to meta value, but no meta key name and value are passed, default to order by date
		// if meta field is numeric and orderby is set to meta value, tweak it to numeric order
		if ( $new_instance['order_by'] == "meta_value" ){
			if ( $new_instance['meta_key_name'] == "none" || $new_instance['meta_key_value'] == "" ){
				$new_instance['order_by'] = "date";
			}
			if ( $new_instance['meta_is_number'] == "yes" ){
				$new_instance['order_by'] = "meta_value_num";
			}
		}
		
		// eventually load filters for the $instance
		$widget_num = $this->number;
		$new_instance = apply_filters( 'pbytax_prepare_instance', $new_instance, $instance, $widget_num );
		// return the prepared instance
		return $new_instance;	 
	 
	}
	
	
	/**
	 * Setup the query args
	 *
	 * @param {array}	$instance	the values of the options for this query. must be prepared by pbt_prepare_instance_vars()
	 *
	 * @return {array}	$pbytax_args	an array of formatted wp query args
	 *
	 * @filter 'pbytax_query_args'	filter the query args array before it is returned
 			@param $pbytax_args {array} the current args. must be returned from the hook.
			@param $instance {array} the array with the current instance options
			@param $widget_num {int} the number of the current widget instance
	 *
	 * @since v 1.4.0
	 */
	
	public function pbytax_setup_query_args( $instance ){
		// quit if no $instance is passed
		if ( !$instance ){
			return;
		}
		
		// extract all variables (see get_defaults() for a list of parameters)
		extract( $instance, EXTR_OVERWRITE );
		
		$pbytax_args =  array(
			'numberposts' => $max_entries ,
			'post_type' => $filter_post_type,
			'orderby' => $order_by,
			'order' => $order_style,
			'post_status' => 'publish',
			); 
		// if there are posts to be excluded (which can be just the current one), add the parameter
		if ( !empty($exclude_posts) || $exclude_current_post == "yes" ){
			if ( $exclude_current_post == "yes" ){
				$exclude_posts[] = $current_page_id;	
			}
			$pbytax_args['post__not_in'] = $exclude_posts;
		}
		// if a meta key has been passed, add it to the query
		if ( $meta_key_name != "none" ) {
			$pbytax_args['meta_key'] = $meta_key_name;
			if ( $meta_key_value != "" ) {
				//  for order purposes
				if ( $meta_is_number == "yes" && $order_by == "meta_value_num" ){
					$pbytax_args['meta_value_num'] = $meta_key_value;	
				}
				elseif ( $order_by == "meta_value" ) {
					$pbytax_args['meta_value'] = $meta_key_value;
				}
				// for fetching purposes
				if ( $meta_is_number == "yes" ){
					$meta_type= "NUMERIC";
				}
				else {
					$meta_type= "CHAR";
				}
				$pbytax_args['meta_query'][0] = array('key'=>$meta_key_name, 'value'=>$meta_key_value, 'compare'=>"$meta_compare", 'type'=>$meta_type);
	
			}
		}
		// if taxonomy is set to any, skip all the below
		if ( $filter_taxonomy != "any_tax"){
			// if "any" term is selected, we have to retrieve a list of terms ids for the selected taxonomy
			if ( $filter_term == "any" ){
				$filter_term = get_terms( $filter_taxonomy, array( 'taxonomy' => $filter_taxonomy, 'fields' => 'ids'	) );
			}
			// add the taxonomy query to the args
			$pbytax_args['tax_query'] = array( 
				 array(
					'taxonomy' => $filter_taxonomy, 
					'field' => 'id', 
					'terms' => $filter_term, 				
					'operator' => 'IN' 
					)
				);
			// add children inclusion to the tax query
			if ($include_children=="true"){
				$pbytax_args['tax_query'][0]['include_children'] = true;
			}
			else {
				$pbytax_args['tax_query'][0]['include_children'] = false;
			}
		}
		// eventually query only children of the current page
		if ( $display_only_children == "yes" && $current_page_id && is_singular() ){
			$pbytax_args['post_parent'] = $current_page_id;	
		}
		
		
		// load eventual hooks and output the query args
		$widget_num = $this->number;
		$pbytax_args = apply_filters( 'pbytax_query_args', $pbytax_args, $instance, $widget_num );
		
		// return the args
		return $pbytax_args;
		
		
	}
	 
	 
	 

	/**
	 * Back-end widget form.
	 * @see WP_Widget::form()
	 *
	 * @param {array} $instance Previously saved values from database.
	 *
	 * @since v. 1.0
	 		v. 1.3.0 added filter 'pbytax_default_options'
			v. 1.4.0 moved the defaults logic into get_defaults()
	 */
	public function form( $instance ) {
		
		// get the dafaults 
		$defaults = $this->get_defaults();			
			
		// if vars are set, override defaults
		$instance = wp_parse_args( $instance, $defaults );
		// convert the array to separated variables
		extract($instance, EXTR_OVERWRITE);

		// ensure that term field is an array
		if ( !is_array($filter_term) || empty($filter_term) || $filter_term == "any" ){
			$filter_term = array("any");
		}


		// print the field for the title and the field for the number of posts to display
		?>
        <div class="pbytax-settings">
        	<p><?php _e('Widget Instance Number:', 'wp-list-pages-by-custom-taxonomy' ); echo ' '.$this->number; ?></p>
        	<h3><?php _e( 'General Settings', 'wp-list-pages-by-custom-taxonomy' ); ?></h3>
            <p>
                <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'wp-list-pages-by-custom-taxonomy' ); ?></label> 
                <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'intro' ); ?>"><?php _e( 'Introduction text/html:', 'wp-list-pages-by-custom-taxonomy' ); ?></label> 
                <textarea rows="3" class="widefat" id="<?php echo $this->get_field_id( 'intro' ); ?>" name="<?php echo $this->get_field_name( 'intro' ); ?>"><?php echo esc_html( $intro ); ?></textarea>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'max_entries' ); ?>"><?php _e( 'Max Entries:', 'wp-list-pages-by-custom-taxonomy' ); ?> </label> 
                <input class="widefat" id="<?php echo $this->get_field_id( 'max_entries' ); ?>" name="<?php echo $this->get_field_name( 'max_entries' ); ?>" type="number" value="<?php echo esc_attr( $max_entries ); ?>" step="1" min="0"> <?php _e( '(set 0 to list all)', 'wp-list-pages-by-custom-taxonomy' ); ?>
            </p>
            <h3><?php _e( 'Filters Options', 'wp-list-pages-by-custom-taxonomy' ); ?></h3>
            <p>
                <label for="<?php echo $this->get_field_id( 'filter_post_type' ); ?>"><?php _e( 'Post Type:', 'wp-list-pages-by-custom-taxonomy' ); ?> </label>
                <select class="widefat" id="<?php echo $this->get_field_id( 'filter_post_type' ); ?>" name="<?php echo $this->get_field_name( 'filter_post_type' ); ?>" onchange="displayMetaKeysSelector(this.value, '<?php echo $this->number; ?>')">
                    <option value="any" <?php if($filter_post_type=="any"){ echo "selected"; } ?> ><?php _e( 'any', 'wp-list-pages-by-custom-taxonomy' ); ?></option>
                    <?php // get all registered post types and print them excluding the useless default ones
                    $post_types_list =  get_post_types( '', 'names' ); 
                    foreach ($post_types_list as $post_type_name){	
                        if ($post_type_name!='attachment' && $post_type_name!='revision' && $post_type_name!='nav_menu_item'){
                            ?>
                            <option value="<?php echo $post_type_name; ?>" <?php if($post_type_name==$filter_post_type){ echo "selected"; } ?> ><?php echo $post_type_name; ?></option>
                        <?php
                        }
                    } ?>
                </select>       
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'filter_taxonomy' ); ?>"><?php _e( 'Taxonomy:', 'wp-list-pages-by-custom-taxonomy' ); ?> </label>
                <select class="widefat tax-selector" id="<?php echo $this->get_field_id( 'filter_taxonomy' ); ?>" name="<?php echo $this->get_field_name( 'filter_taxonomy' ); ?>" onchange="displayTermsSelector(this.value, '<?php echo $this->number; ?>')">
                <option value="any_tax" <?php if($filter_taxonomy=="any_tax"){ echo "selected"; } ?> ><?php _e( 'any', 'wp-list-pages-by-custom-taxonomy' ); ?></option>
                
                <?php // get all registered taxonomies and print them
                $taxonomies_list = get_taxonomies();
                foreach ($taxonomies_list as $tax_name){			
                    ?>
                    <option value="<?php echo $tax_name; ?>" <?php if($tax_name==$filter_taxonomy){ echo "selected"; } ?> ><?php echo $tax_name; ?></option>
                <?php 
                } ?>
                </select>
                
                <label for="<?php echo $this->get_field_id( 'filter_term' ); ?>"><?php _e( 'Pull from this Term', 'wp-list-pages-by-custom-taxonomy' ); ?> <span style="font-size:80%">(<?php _e( 'hold CTRL + click to select multiple terms', 'wp-list-pages-by-custom-taxonomy' ); ?>)</span>:</label>
                
                <select multiple class="widefat terms-selector-<?php echo $this->number; ?>" <?php if($filter_taxonomy!="any_tax"){ echo 'style="display:none" disabled'; } ?> id="<?php echo 'any_tax-'.$this->number; ?>" name="<?php echo $this->get_field_name( 'filter_term' ); ?>[]" >
                        <option value="any" <?php if(in_array('any', $filter_term) && $filter_taxonomy=="any_tax"){ echo "selected"; } ?> ><?php _e( 'any','wp-list-pages-by-custom-taxonomy' ); ?></option>
                </select>
                
                <?php // build a selector for each taxonomy, listing the terms. jquery will then display the correct one based on selected taxonomy
                foreach ($taxonomies_list as $tax_name){
                   ?>
                    <select multiple class="widefat terms-selector-<?php echo $this->number; ?>" <?php if($filter_taxonomy!=$tax_name){ echo 'style="display:none" disabled'; } ?> id="<?php echo $tax_name.'-'.$this->number; ?>" name="<?php echo $this->get_field_name( 'filter_term' ); ?>[]" >
                        <option value="any" <?php if(in_array('any', $filter_term) && $filter_taxonomy==$tax_name){ echo "selected"; } ?> ><?php _e( 'any','wp-list-pages-by-custom-taxonomy' ); ?></option>
                        <?php
                        // get the terms of the taxonomy and print them
                        $terms = get_terms( $tax_name, array( 'taxonomy' => $tax_name ) );
                        foreach ($terms as $term) {            
                            ?>
                            <option value="<?php echo $term->term_id; ?>" <?php if(in_array($term->term_id, $filter_term) && $filter_taxonomy==$tax_name){ echo "selected"; } ?> ><?php echo $term->name; ?></option>      
                        <?php } ?>
                    </select>
                <?php } ?>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'order_by' ); ?>"><?php _e( 'Order By:', 'wp-list-pages-by-custom-taxonomy' ); ?> </label>
                <select class="widefat" id="<?php echo $this->get_field_id( 'order_by' ); ?>" name="<?php echo $this->get_field_name( 'order_by' ); ?>">
                    <option value="date" <?php if($order_by=="date"){ echo "selected"; } ?> ><?php _e( 'Date', 'wp-list-pages-by-custom-taxonomy' ); ?></option>
                    <option value="ID" <?php if($order_by=="ID"){ echo "selected"; } ?> ><?php _e( 'ID', 'wp-list-pages-by-custom-taxonomy' ); ?></option>
                    <option value="menu_order" <?php if($order_by=="menu_order"){ echo "selected"; } ?> ><?php _e( 'Page Order', 'wp-list-pages-by-custom-taxonomy' ); ?></option>
                    <option value="modified" <?php if($order_by=="modified"){ echo "selected"; } ?> ><?php _e( 'Modified Date', 'wp-list-pages-by-custom-taxonomy' ); ?></option>
                    <option value="title" <?php if($order_by=="title"){ echo "selected"; } ?> ><?php _e( 'Title', 'wp-list-pages-by-custom-taxonomy' ); ?></option>
                    <option value="comment_count" <?php if($order_by=="comment_count"){ echo "selected"; } ?> ><?php _e( 'Comments', 'wp-list-pages-by-custom-taxonomy' ); ?></option>
                    <option value="rand" <?php if($order_by=="rand"){ echo "selected"; } ?> ><?php _e( 'Random', 'wp-list-pages-by-custom-taxonomy' ); ?></option>
                    <option value="meta_value" <?php if($order_by=="meta_value"){ echo "selected"; } ?> ><?php _e( 'Meta Field (need to set Meta Key Name)', 'wp-list-pages-by-custom-taxonomy' ); ?></option>
                </select>
    
                <label for="<?php echo $this->get_field_id( 'order_style' ); ?>"><?php _e( 'Order:', 'wp-list-pages-by-custom-taxonomy' ); ?> </label>
                <select class="widefat" id="<?php echo $this->get_field_id( 'order_style' ); ?>" name="<?php echo $this->get_field_name( 'order_style' ); ?>">
                    <option value="ASC" <?php if($order_style=="ASC"){ echo "selected"; } ?> ><?php _e( 'Ascendant', 'wp-list-pages-by-custom-taxonomy' ); ?></option>
                    <option value="DESC" <?php if($order_style=="DESC"){ echo "selected"; } ?> ><?php _e( 'Descendant', 'wp-list-pages-by-custom-taxonomy' ); ?></option>
                </select>
            </p>
            <p>
                <input type="checkbox" class="checkbox-margin" value="true" name="<?php echo $this->get_field_name( 'include_children' ); ?>" <?php if($include_children=="true"){ echo "checked"; } ?> > <?php _e( 'Include Children', 'wp-list-pages-by-custom-taxonomy' ); ?>
                <br/>
                <input type="checkbox" class="checkbox-margin" value="yes" name="<?php echo $this->get_field_name( 'exclude_current_post' ); ?>" <?php if($exclude_current_post=="yes"){ echo "checked"; } ?> > <?php _e( 'Exclude Current Post', 'wp-list-pages-by-custom-taxonomy' ); ?>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'exclude_posts' ); ?>"><?php _e( 'Exclude posts:', 'wp-list-pages-by-custom-taxonomy' ); ?></label> 
                <input class="widefat" id="<?php echo $this->get_field_id( 'exclude_posts' ); ?>" name="<?php echo $this->get_field_name( 'exclude_posts' ); ?>" type="text" value="<?php echo $exclude_posts; ?>"><br/> <small><?php _e('(insert ids separated by a medium dash - or a space)', 'wp-list-pages-by-custom-taxonomy'); ?></small>
            </p>
            <div class="meta_fields_options" <?php if ($filter_post_type=="any"){ echo 'style="display:none"'; } ?> >
                <p>
                <strong><?php _e('Custom Meta Fields Options', 'wp-list-pages-by-custom-taxonomy'); ?></strong><br/>
                    <label for="<?php echo $this->get_field_id( 'meta_key_name' ); ?>"><?php _e( 'Meta Field Name:', 'wp-list-pages-by-custom-taxonomy' ); ?> </label>
                    <?php // foreach post_type (retrieved previously) print a selector with the meta-keys
                    foreach ($post_types_list as $post_type_name){	
                        if ($post_type_name!='attachment' && $post_type_name!='revision' && $post_type_name!='nav_menu_item'){
                            // get all custom meta keys for this post type
                            $meta_keys_list = $this->pbytax_get_post_type_meta_keys($post_type_name);
                            ?>
                            <select class="widefat meta-keys-selector-<?php echo $this->number; ?>" id="<?php echo $post_type_name.'-keys-'.$this->number; ?>" name="<?php echo $this->get_field_name( 'meta_key_name' ); ?>" <?php if($filter_post_type!=$post_type_name){ echo 'style="display:none" disabled'; } ?>>
                                <option value="none" <?php if($meta_key_name=="none"){ echo "selected"; } ?> ><?php _e('none', 'wp-list-pages-by-custom-taxonomy'); ?></option>
                                <?php // foreach meta key, print an option
                                foreach ($meta_keys_list as $meta_key){
                                    ?>
                                    <option value="<?php echo $meta_key; ?>" <?php if($post_type_name==$filter_post_type && $meta_key_name==$meta_key){ echo "selected"; } ?> ><?php echo $meta_key; ?></option>
                                <?php 
                                } ?>
                            </select>       
                        <?php
                        }
                    } ?>
                    <br/>
                    <label for="<?php echo $this->get_field_id( 'meta_key_value' ); ?>"><?php _e( 'Meta field Value (leave blank to not filter by meta field):', 'wp-list-pages-by-custom-taxonomy' ); ?></label> 
                    <input class="widefat" id="<?php echo $this->get_field_id( 'meta_key_value' ); ?>" name="<?php echo $this->get_field_name( 'meta_key_value' ); ?>" type="text" value="<?php echo $meta_key_value; ?>">
                    <br/>
                    <label for="<?php echo $this->get_field_id( 'meta_compare' ); ?>"><?php _e( 'Meta Compare:', 'wp-list-pages-by-custom-taxonomy' ); ?> </label>
                    <select class="widefat" id="<?php echo $this->get_field_id( 'meta_compare' ); ?>" name="<?php echo $this->get_field_name( 'meta_compare' ); ?>">
                        <option value="=" <?php if($meta_compare=="="){ echo "selected"; } ?> >=</option>
                        <option value="!=" <?php if($meta_compare=="!="){ echo "selected"; } ?> > &ne;</option>
                        <option value=">=" <?php if($meta_compare==">="){ echo "selected"; } ?> > &gt;=</option>
                        <option value="<=" <?php if($meta_compare=="<="){ echo "selected"; } ?> > &lt;=</option>
                        <option value=">" <?php if($meta_compare==">"){ echo "selected"; } ?> > &gt;</option>
                        <option value="<" <?php if($meta_compare=="<"){ echo "selected"; } ?> > &lt;</option>
                        <option value="LIKE" <?php if($meta_compare=="LIKE"){ echo "selected"; } ?> > LIKE</option>
                        <option value="NOT LIKE" <?php if($meta_compare=="NOT LIKE"){ echo "selected"; } ?> > NOT LIKE</option>
                        <option value="IN" <?php if($meta_compare=="IN"){ echo "selected"; } ?> > IN</option>
                        <option value="NOT IN" <?php if($meta_compare=="NOT IN"){ echo "selected"; } ?> > NOT IN</option>
                    </select>
                    <br/>
                    <input style="padding-top:4px;" type="checkbox" value="yes" name="<?php echo $this->get_field_name( 'meta_is_number' ); ?>" <?php if($meta_is_number=="yes"){ echo "checked"; } ?> > <?php _e('Numeric Meta Field', 'wp-list-pages-by-custom-taxonomy'); ?>
                </p>
            </div>            
            
            <h3><?php _e( 'Display Options', 'wp-list-pages-by-custom-taxonomy' ); ?></h3>
            <p>
                <input type="checkbox" class="checkbox-margin" value="yes" name="<?php echo $this->get_field_name( 'display_date' ); ?>" <?php if($display_date=="yes"){ echo "checked"; } ?> > <?php _e( 'Display Date', 'wp-list-pages-by-custom-taxonomy' ); ?> 
            <br/>
                <input type="checkbox" class="checkbox-margin" value="yes" name="<?php echo $this->get_field_name( 'display_mod_date' ); ?>" <?php if($display_mod_date=="yes"){ echo "checked"; } ?> > <?php _e( 'Display Last Modified Date', 'wp-list-pages-by-custom-taxonomy' ); ?>
            <br/>
            	 <input type="checkbox" class="checkbox-margin" value="yes" name="<?php echo $this->get_field_name( 'display_excerpt' ); ?>" <?php if($display_excerpt=="yes"){ echo "checked"; } ?> > <?php _e( 'Display Excerpt', 'wp-list-pages-by-custom-taxonomy' ); ?> <span class="float-right-field"><?php _e( 'Length:', 'wp-list-pages-by-custom-taxonomy' ); ?><input class="small-number" id="<?php echo $this->get_field_id( 'excerpt_length' ); ?>" name="<?php echo $this->get_field_name( 'excerpt_length' ); ?>" type="number" value="<?php echo esc_attr( $excerpt_length ); ?>" step="1" min="10" /> </span>
            <br/>
                <input type="checkbox" class="checkbox-margin" value="yes" name="<?php echo $this->get_field_name( 'display_thumb' ); ?>" <?php if($display_thumb=="yes"){ echo "checked"; } ?> > <?php _e( 'Display Thumbnail', 'wp-list-pages-by-custom-taxonomy' ); ?>  <span class="float-right-field"><?php _e('Max Width:', 'wp-list-pages-by-custom-taxonomy'); ?><input class="small-number" id="<?php echo $this->get_field_id( 'thumb_max_width' ); ?>" name="<?php echo $this->get_field_name( 'thumb_max_width' ); ?>" type="number" value="<?php echo esc_attr( $thumb_max_width ); ?>" step="1" min="0" /> </span>
            <br/>
                <input type="checkbox" class="checkbox-margin" value="yes" name="<?php echo $this->get_field_name( 'link_thumb' ); ?>" <?php if($link_thumb=="yes"){ echo "checked"; } ?> > <?php _e('Link Thumbnail to Post', 'wp-list-pages-by-custom-taxonomy'); ?>
            <br/>
            	<label for="<?php echo $this->get_field_id( 'thumb_align' ); ?>"><?php _e( 'Thumbnail align:', 'wp-list-pages-by-custom-taxonomy' ); ?> </label>
                <select class="widefat" id="<?php echo $this->get_field_id( 'thumb_align' ); ?>" name="<?php echo $this->get_field_name( 'thumb_align' ); ?>">
                    <option value="left" <?php if(!$thumb_align || $thumb_align=="left"){ echo "selected"; } ?> ><?php _e('Left', 'wp-list-pages-by-custom-taxonomy'); ?></option>
                    <option value="right" <?php if($thumb_align=="right"){ echo "selected"; } ?> ><?php _e('Right', 'wp-list-pages-by-custom-taxonomy'); ?></option>
                    <option value="block" <?php if($thumb_align=="block"){ echo "selected"; } ?> ><?php _e('Centered Above', 'wp-list-pages-by-custom-taxonomy'); ?></option>
                </select>
            <br/>
	            <?php _e( 'Image format', 'wp-list-posts-by-custom-taxonomy' ); ?> <?php _e( '(will be resized to the max width set above)', 'wp-list-pages-by-custom-taxonomy' ); ?>
                <select class="widefat" id="<?php echo $this->get_field_id( 'thumb_size' ); ?>" name="<?php echo $this->get_field_name( 'thumb_size' ); ?>">
		            <?php // get a list of available thumb sizes
		            $thumb_sizes = get_intermediate_image_sizes();
		            // foreach of them print an option
		            if ( $thumb_sizes && is_array($thumb_sizes) ){
			            foreach ( $thumb_sizes as $t_size ){
				            ?>
                            <option value="<?php echo $t_size; ?>" <?php if($thumb_size==$t_size || (!$thumb_size && $t_size == "thumbnail") ){ echo "selected"; } ?> ><?php echo $t_size; ?></option>
				            <?php
			            }
		            } ?>
                </select>
            <br/>
            <?php _e('Date Align:', 'wp-list-pages-by-custom-taxonomy'); ?><select class="widefat" id="<?php echo $this->get_field_id( 'date_align' ); ?>" name="<?php echo $this->get_field_name( 'date_align' ); ?>"> 
                	<option value="left" <?php if(!$date_align || $date_align=="left"){ echo "selected"; } ?> ><?php _e('Left', 'wp-list-pages-by-custom-taxonomy'); ?></option>
                    <option value="right" <?php if($date_align=="right"){ echo "selected"; } ?> ><?php _e('Right', 'wp-list-pages-by-custom-taxonomy'); ?></option>
                </select>
            <br/>
                <label for="<?php echo $this->get_field_id( 'date_format' ); ?>"><?php _e( 'Date Format:', 'wp-list-pages-by-custom-taxonomy' ); ?>
                    <small>(<a href="//php.net/manual/en/function.date.php" target="_blank"><?php _e( 'more info', 'wp-list-pages-by-custom-taxonomy' ); ?></a>)</small>
                </label>
                <input class="widefat" id="<?php echo $this->get_field_id( 'date_format' ); ?>" name="<?php echo $this->get_field_name( 'date_format' ); ?>" type="text" value="<?php echo esc_attr( $date_format ); ?>">
            </p>
            <p>   
                <input type="checkbox" class="checkbox-margin" value="yes" name="<?php echo $this->get_field_name( 'display_in_dropdown' ); ?>" <?php if($display_in_dropdown=="yes"){ echo "checked"; } ?> > <?php _e('Display only Titles in a Dropdown Selector', 'wp-list-pages-by-custom-taxonomy'); ?>   
            <br/>  	
                <label for="<?php echo $this->get_field_id( 'dropdown_text' ); ?>"><?php _e( 'Dropdown Text:', 'wp-list-pages-by-custom-taxonomy' ); ?></label> 
                <input class="widefat" id="<?php echo $this->get_field_id( 'dropdown_text' ); ?>" name="<?php echo $this->get_field_name( 'dropdown_text' ); ?>" type="text" value="<?php echo esc_attr( $dropdown_text ); ?>">
            </p>
            <p>
            	<input type="checkbox" class="checkbox-margin" value="yes" name="<?php echo $this->get_field_name( 'display_only_thumb' ); ?>" <?php if($display_only_thumb=="yes"){ echo "checked"; } ?> > <?php _e( 'Display only Image', 'wp-list-pages-by-custom-taxonomy' ); ?> 
                <br/>
                 <input type="checkbox" class="checkbox-margin" value="yes" name="<?php echo $this->get_field_name( 'display_thumb_overlay' ); ?>" <?php if($display_thumb_overlay=="yes"){ echo "checked"; } ?> > <?php _e( 'if only image, display the other details on mouse over', 'wp-list-pages-by-custom-taxonomy' ); ?>   
            </p>
            <p>
            	<input type="checkbox" class="checkbox-margin" value="yes" name="<?php echo $this->get_field_name( 'display_only_children' ); ?>" <?php if($display_only_children=="yes"){ echo "checked"; } ?> > <?php _e( 'Display only Children of the current Parent', 'wp-list-pages-by-custom-taxonomy' ); ?> 
                <br/> <small><?php _e('(works only if you have selected a hierarchical post_type)', 'wp-list-pages-by-custom-taxonomy'); ?></small>
                <br/>
                 <input type="checkbox" class="checkbox-margin" value="yes" name="<?php echo $this->get_field_name( 'current_page_as_title' ); ?>" <?php if($current_page_as_title=="yes"){ echo "checked"; } ?> > <?php _e( 'display current parent as Widget Title', 'wp-list-pages-by-custom-taxonomy' ); ?>   
            </p>
            <p>
               	<input type="checkbox" class="checkbox-margin" value="flow-below" name="<?php echo $this->get_field_name( 'text_float' ); ?>" <?php if($text_float=="flow-below"){ echo "checked"; } ?> > <?php _e( 'Allow Text to flow below picture', 'wp-list-pages-by-custom-taxonomy' ); ?>
                <br/>
                <input type="checkbox" class="checkbox-margin" value="true" name="<?php echo $this->get_field_name( 'title_no_wrap' ); ?>" <?php if($title_no_wrap=="true"){ echo "checked"; } ?> > <?php _e( 'Hide Title words that overflow one line', 'wp-list-pages-by-custom-taxonomy' ); ?>
                <br/>
                <?php _e('Title Max Length:', 'wp-list-pages-by-custom-taxonomy'); ?> <input class="small-number" id="<?php echo $this->get_field_id( 'title_max_length' ); ?>" name="<?php echo $this->get_field_name( 'title_max_length' ); ?>" type="number" value="<?php echo esc_attr( $title_max_length ); ?>" step="1" min="0" /> </span>
            </p>
            
		</div>
        
		<?php 
	}


	/**
	 * Sanitize widget form values as they are saved.
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 *
	 * @filter 'pbytax_instance_update' to manipulate the instance array before it's updated
	 		@params $instance (must be returned by the hook), $new_instance, $old_instance
	 *
	 * @since v. 1.0
	 		v. 1.3.0 added the filter 'pbytax_instance_update' 
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['intro'] = $new_instance['intro']; // we don't sanitize or validate this, so it can contain any html or script
		$instance['exclude_posts'] = sanitize_title( $new_instance['exclude_posts']); // although we specify to put dashes, we'll sanitize the input to reduce risks
		// other fields are selectors and number field, so data will always be in the correct format
		$instance['max_entries'] = $new_instance['max_entries'];
		$instance['filter_post_type'] = $new_instance['filter_post_type'];
		$instance['filter_taxonomy'] = $new_instance['filter_taxonomy'];
		$instance['filter_term'] = $new_instance['filter_term'];
		$instance['meta_key_name'] = $new_instance['meta_key_name'];
		$instance['meta_key_value'] = $new_instance['meta_key_value']; // this could be anything, so we can't sanitize and user must be careful to fill it
		$instance['meta_is_number'] = $new_instance['meta_is_number'];
		$instance['meta_compare'] = $new_instance['meta_compare'];
		$instance['order_by'] = $new_instance['order_by'];
		$instance['order_style'] = $new_instance['order_style'];	
		$instance['include_children'] = $new_instance['include_children'];
		$instance['display_thumb'] = $new_instance['display_thumb'];
		$instance['link_thumb'] = $new_instance['link_thumb'];
		$instance['thumb_align'] = $new_instance['thumb_align'];
		$instance['thumb_size'] = $new_instance['thumb_size'];
		$instance['thumb_max_width'] = $new_instance['thumb_max_width'];	
		$instance['display_excerpt'] = $new_instance['display_excerpt'];
		$instance['excerpt_length'] = $new_instance['excerpt_length'];
		$instance['display_date'] = $new_instance['display_date'];
		$instance['display_mod_date'] = $new_instance['display_mod_date'];
		$instance['date_align'] = $new_instance['date_align'];
		$instance['date_format'] = sanitize_text_field( $new_instance['date_format'] ); // ToDo: ensure this is valid as date string
		$instance['display_in_dropdown'] = $new_instance['display_in_dropdown'];
		$instance['text_float'] = $new_instance['text_float'];
		$instance['title_no_wrap'] = $new_instance['title_no_wrap'];
		$instance['title_max_length'] = $new_instance['title_max_length'];
		$instance['display_only_thumb'] = $new_instance['display_only_thumb'];
		$instance['display_thumb_overlay'] = $new_instance['display_thumb_overlay'];
		$instance['display_only_children'] = $new_instance['display_only_children'];
		$instance['current_page_as_title'] = $new_instance['current_page_as_title'];
		$instance['dropdown_text'] = strip_tags($new_instance['dropdown_text']); // strip tags to avoid breaking of the dropdown
		$instance['exclude_current_post'] = $new_instance['exclude_current_post'];

		// add a filter to allow modify of the updated instance array
		// this filter will apply to all the widget instances
		$instance = apply_filters( 'pbytax_instance_update', $instance, $new_instance, $old_instance);	
		
		return $instance;
	}
	
	
	/**
	 * query all custom meta keys names for the required post-type
	 * used to build up selectors in the widget admin
	 *
	 * @since v. 1.0
	 		v. 1.2.2 include meta fields starting with underscore
	 */
	public function pbytax_get_post_type_meta_keys($post_type){
		global $wpdb;
		// quit if no post type is passed
		if (!$post_type) return;
		
		// check if we have already stored this query
		$meta_keys = get_transient( 'pbytax_meta_keys_query_'.$post_type );
		
		// if we haven't found it, make a new query
		if ( false === $meta_keys || empty($meta_keys) ){
		
			$query = "
				SELECT DISTINCT($wpdb->postmeta.meta_key) 
				FROM $wpdb->posts 
				LEFT JOIN $wpdb->postmeta 
				ON $wpdb->posts.ID = $wpdb->postmeta.post_id 
				WHERE $wpdb->posts.post_type = '%s' 
				AND $wpdb->postmeta.meta_key != '' 
				AND $wpdb->postmeta.meta_key NOT RegExp '(^[_0-9].+$)' 
				AND $wpdb->postmeta.meta_key NOT RegExp '(^[0-9]+$)'
			";
			// EDITED in v 1.2.2 the query was cutting off all fields starting with an underscore, but we're now keeping them
			// this was the query part which has been deprecated
			// AND $wpdb->postmeta.meta_key NOT RegExp '(^[_0-9].+$)'
			
			$meta_keys = $wpdb->get_col($wpdb->prepare($query, $post_type));
		
			if ( is_array($meta_keys) ){
				// as there are often many duplicated metas starting with an underscore, we'll filter them out.
				foreach ($meta_keys as $index => $meta_key){
					if ( substr($meta_key,0,1)=="_" ){
						//check if there is an entry with the same value but without the underscore
						if ( in_array( substr($meta_key, 1), $meta_keys) ) {
							unset($meta_keys[$index]);	
						}
					}
				}
			}
			
			// save the query
			set_transient( 'pbytax_meta_keys_query_'.$post_type, $meta_keys, 60*60*12 );
		
		}

		return $meta_keys;
	
	}
	
	

} // class Pages_by_Tax
