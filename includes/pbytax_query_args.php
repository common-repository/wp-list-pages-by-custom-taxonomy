<?php
/**
 * @DEPRECATED in v. 1.4.0 	query args are now built by functions pbytax_prepare_instance_vars() and pbytax_setup_query_args()
 		this file is still included in the package for backward compatibility
 *
 * build the args for the query
 * this file is included from templates/pbytax_template.php
 *
 * @param $instance {array} contains all the options for the current widget
 *
 * @filter 'pbytax_query_args' hook to manipulate the args before they are passed to the query
 		@param $pbytax_args {array} the current args. must be returned from the hook.
		@param $instance {array} the array with the current instance options
		@param $widget_num {int} the number of the current widget instance
 *
 * @since v. 1.3.0 (previously this was printed directly into the template file)
 *
 */
 
 // Block direct access to this file
if ( !defined('ABSPATH') ) {
	die('-1');
}
 
	// store the vars
	$intro = $instance['intro'];
	$max_entries = $instance['max_entries'];
	if ($max_entries=="0"){
		$max_entries= -1;
	}
	$filter_term_id = $instance['filter_term'];
	if ($filter_term_id!=""){
		if ( is_array($filter_term_id) ){
			if ( in_array('any', $filter_term_id) ){
				$filter_term_id = 'any';
			}
		}
	}
	else {
		$filter_term_id = 'any';
	}
	$post_type = $instance['filter_post_type'];
	$taxonomy = $instance['filter_taxonomy'];
	$order_by = $instance['order_by'];
	$order_style = $instance['order_style'];
	$include_children = $instance['include_children'];
	$display_thumb = $instance['display_thumb'];
	$link_thumb = $instance['link_thumb'];
	$thumb_align = $instance['thumb_align'];
	$thumb_max_width = $instance['thumb_max_width'];
	$display_excerpt = $instance['display_excerpt'];
	$excerpt_length = $instance['excerpt_length'];
	$display_date = $instance['display_date'];
	$display_in_dropdown = $instance['display_in_dropdown'];
	$dropdown_text = $instance['dropdown_text'];
	$display_only_thumb = $instance['display_only_thumb'];
	$display_thumb_overlay = $instance['display_thumb_overlay'];
	$display_only_children = $instance['display_only_children'];
	if ( $display_only_children == "yes" ){
		$include_children = "yes";
	}
	$current_page_as_title = $instance['current_page_as_title'];	
	$exclude_posts = $instance['exclude_posts'];
	if ($exclude_posts!=""){
		$exclude_posts = explode("-", $exclude_posts);	
	}
	$meta_key_name = $instance['meta_key_name'];
	$meta_key_value = $instance['meta_key_value'];
	$meta_is_number = $instance['meta_is_number'];
	// if meta field is numeric and orderby is set to meta key, tweak it
	$meta_compare = $instance['meta_compare'];
	// if orderby is set to meta value, but no meta key name and value are passed, default to order by date
	// if meta field is numeric and orderby is set to meta value, tweak it to numeric order
	if ($order_by=="meta_value"){
		if ($meta_key_name=="none" || $meta_key_value==""){
			$order_by = "date";
		}
		if ($meta_is_number=="yes"){
			$order_by = "meta_value_num";
		}
	}
	// build up the args for the query
	$pbytax_args =  array(
		'numberposts' => $max_entries ,
		'post_type' => $post_type,
		'orderby' => $order_by,
		'order' => $order_style,
		'post_status' => 'publish',
		); 
	// if there are posts to be excluded, add the parameter
	if ( !empty($exclude_posts) ){
		$pbytax_args['post__not_in'] = $exclude_posts;
	}
	// if a meta key has been passed, add it to the query
	if ($meta_key_name!="none") {
		$pbytax_args['meta_key'] = $meta_key_name;
		if ($meta_key_value!="") {
			//  for order purposes
			if ($meta_is_number=="yes" && $order_by=="meta_value_num"){
				$pbytax_args['meta_value_num'] = $meta_key_value;	
			}
			elseif ($order_by=="meta_value") {
				$pbytax_args['meta_value'] = $meta_key_value;
			}
			// for fetching purposes
			if ($meta_is_number=="yes"){
				$meta_type= "NUMERIC";
			}
			else {
				$meta_type= "CHAR";
			}
			$pbytax_args['meta_query'][0] = array('key'=>$meta_key_name, 'value'=>$meta_key_value, 'compare'=>"$meta_compare", 'type'=>$meta_type);

		}
	}
	// if taxonomy is set to any, skip all the below
	if ($taxonomy!="any_tax"){
		// if "any" term is selected, we have to retrieve a list of terms ids for the selected taxonomy
		if ($filter_term_id=="any"){
			$filter_term_id = get_terms( $taxonomy, array( 'taxonomy' => $filter_taxonomy, 'fields' => 'ids' ) );
		}
		// add the taxonomy query to the args
		$pbytax_args['tax_query'] = array( 
			 array(
				'taxonomy' => $taxonomy, 
				'field' => 'id', 
				'terms' => $filter_term_id, 				
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
	global $wp_query;
    $current_page_id = $wp_query->post->ID;
	$current_parent_id = $wp_query->post->post_parent;
	// if this page has a parent, then we want the children of the parent, rather than the current page
	if ( $current_parent_id ){
		$current_page_id = $current_parent_id;
	}
	// go on only if we have got a valid page ID, and we are on a singular page/post
	if ( $display_only_children == "yes" && $current_page_id && is_singular() ){
		$pbytax_args['post_parent'] = $current_page_id;	
	}
	
	
	
	// load eventual hooks to filter the query args
	$widget_num = $this->number;
	$pbytax_args = apply_filters( 'pbytax_query_args', $pbytax_args, $instance, $widget_num );