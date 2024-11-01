<?php
/*
 * Template for the output of the Widget
 * Override by placing a file called pbytax_template.php in your active theme
 *
 * @version 1.4.7
 		v. 1.3.0	query args logic is moved to file /includes/pbytax_query_args.php and included from here
		v. 1.4.0	query args included from main function, and query get_posts() made from there too
 *
 */
	
	//// THE LOOP STARTS HERE,
	// HERE YOU COULD EDIT THE HTML STRUCTURE OF THE OUTPUT

	// posts object is passed from the main function
	// ensure we've got something and print the list
	if (!empty($pbytax_posts)){
		?>
        
        <?php // eventually display the current page title as widget title 
		// the main function has already skipped the actual widget title if this option is selected
		// $current_page_id was set up previously in the included file pbytax_query_args.php
		if ( $current_page_as_title == "yes" ){
			?>
		<h3 class="widget-title pbytax-widget-title">
        	<?php echo get_the_title($current_page_id); ?>
        </h3>
        <?php }	?>
        
		<?php // if any, display the introduction text/html
		if ( $intro ){ ?>
		<div class="pbytax-intro">
        	<?php echo $intro; ?>
		</div>
        <?php } ?>
        
        <?php
		/**
		 * if the output should be in a DROPDOWN SELECTOR
		 */
		if ($display_in_dropdown=="yes"){
			?>
            
            <select class="pbytax-dropdown" id="pbytax-selector" onchange="location = this.options[this.selectedIndex].value;">
            	<option value="#"><?php pbytax_the_dropdown_title( $dropdown_text, $instance, array('count'=>count($pbytax_posts)) ); ?> </option>
            	<?php // loop the posts
				foreach( $pbytax_posts as $pbytax_post ) {	
					?>
            		<option value="<?php echo esc_url( get_permalink( $pbytax_post->ID ) ); ?>">
                    	<?php pbytax_the_title( $pbytax_post, $instance ); ?>
                    </option>
            	<?php // close the foreach loop of posts
				} ?>
            </select>
            
        <?php 
		} 
		else {
			/**
		 	 * if this was NORMAL WIDGET rather than dropdown
		 	 */
			?>
            
            <ul class="pbytax-list">                           
                <?php // loop the posts
                foreach( $pbytax_posts as $pbytax_post ) {	
                    ?>
                    
                    <li class="pbytax-item">
                    	<div class="pbytax-item-content">
							<?php // display thumb if required
                            if ($display_thumb=="yes"){
                                // set the max-width of the image container
                                if( $thumb_max_width != 60 || $display_only_thumb == 'yes' ){ 
                                    if ( $thumb_max_width != 0 && $display_only_thumb != 'yes' ){ // if display only thumb, width must be 100%
                                        $img_div_style = 'style="max-width:'.$thumb_max_width.'px"'; 
                                    }
                                    else { // go full width if is set to 0
                                        $img_div_style = 'style="width:100%;max-width:100%;"';
										// tweak the thumb align class as well
										$thumb_align = "block";
                                    }
                                }
                                ?>
                                <div class="pbytax-thumb <?php echo 'pbytax-thumb-'.$thumb_align; ?>" <?php echo $img_div_style; ?>>
                                    <?php // store the image html if any is set
                                    if (get_the_post_thumbnail($pbytax_post->ID)){ 
                                        $thumb_html = get_the_post_thumbnail( $pbytax_post->ID, $thumb_size, array('class' => 'img-full-width') );
                                    } else { // use the no-thumb image instead
                                        $thumb_html = '<img src="'.plugins_url().'/wp-list-pages-by-custom-taxonomy/images/no-thumb.jpg" class="img-full-width wp-post-image" />'; 
                                    } ?> 
                                
                                    <?php // print the link for the image,
                                    if ( $link_thumb == "yes" ){
                                        ?>                                
                                        <a class="pbytax-thumb-link" href="<?php echo esc_url( get_permalink( $pbytax_post->ID ) ); ?>" title="<?php echo esc_attr( $pbytax_post->post_title ); ?>"><?php echo $thumb_html; ?></a>                                
                                    <?php 
                                    } 
                                    else {  // otherwise print only the image without link
                                        echo $thumb_html;
                                    }
                                    ?>                                                         
                                </div>	
                            <?php } ?>
                            
                            <?php // if set to display only thumb, ignore the following part
                            // but go on and print it into an overlay div, if display only thumb AND display overlay are both yes
                            if ( $display_only_thumb != "yes" || ($display_only_thumb == "yes" && $display_thumb_overlay == "yes") ){
                                // print out an overlay div if necessary
                                if ( $display_only_thumb == "yes" && $display_thumb_overlay == "yes" ){
                                    ?>
                                    <div class="pbytax-overlay-content">
                                    	<div class="pbytax-overlay-inner">
                                <?php } ?>   
                                <?php // set up class and styles for the text div
								$text_wrap_class = 'pbytax-flow-below';
								$text_wrap_style = "";
								if ( $text_float != "flow-below" && $display_thumb == "yes" && $thumb_align != "block" ){
									// if we don't want the text to float below the image
									$text_wrap_class = "pbytax-flow-fixed";
									$text_wrap_style = 'style="padding-'.$thumb_align.':'.($thumb_max_width+6).'px;margin-'.$thumb_align.':-'.($thumb_max_width+6).';"';
								}
								?>
                                <div class="pbytax-text-wrap <?php echo $text_wrap_class; ?>" <?php echo $text_wrap_style; ?>>
                                    <div class="postbytax-title-wrap <?php if( $title_no_wrap=="true" ){ echo "pbytax-no-wrap"; } ?>">
                                    	<a class="pbytax-post-title" href="<?php echo esc_url( get_permalink( $pbytax_post->ID ) ); ?>" title="<?php echo esc_attr( $pbytax_post->post_title ); ?>">
                                            <?php pbytax_the_title( $pbytax_post, $instance ); ?>
                                        </a>
                                    </div>       

                                    <?php // display the excerpt if required
                                    if ($display_excerpt=="yes"){
                                        ?>
                                        <div class="pbytax-excerpt">
                                            <?php // get the excerpt but display only the number of characters set in the options
                                            unset( $excerpt );
                                            $excerpt = strip_tags( strip_shortcodes($pbytax_post->post_content) );
                                            $excerpt = substr ( $excerpt, 0, $excerpt_length );
                                            // if we couldn't build the excerpt, try the wp excerpt
                                            if ( !$excerpt ){
                                                $excerpt = isset($pbytax_post->post_excerpt) ? $pbytax_post->post_excerpt : '';
                                            }
											$more = ( strlen($excerpt) >= $excerpt_length ) ? ' [...]' : '';
											echo esc_html($excerpt) . $more;
											?>
                                        </div>	
                                    <?php 
                                    } ?>
                                    <?php // date
                                    if ( $display_date == "yes" ) {
		                                $date = get_the_date( $date_format, $pbytax_post->ID );
	                                } elseif( $display_mod_date == "yes" ) {
		                                $date = get_the_modified_date( $date_format, $pbytax_post->ID );
	                                }
	                                if ( $date ){
                                        ?>
                                        <div class="pbytax-meta">
                                       		<span class="pbytax-date pbytax-date-<?php echo $date_align; ?>"><?php echo $date; ?></span>
                                        </div>
                                    <?php } ?>
                                </div>
                            <?php 
                            } ?>
                            <?php // close the overlay container if necessary
                            if ( $display_only_thumb == "yes" && $display_thumb_overlay == "yes" ){
                                ?>
                                	</div>
                                </div>
                            <?php } ?>
                            <div class="clear"></div>
                    	</div>
                    </li>                	
                    
                <?php // close the foreach loop of posts
                } ?>            
            </ul>
       	<?php // end the loop
		} ?>
        
    <?php
	} // if no posts
	else {
		// display the notice only if we haven't selected to display only the children, because in that case we might want to output nothing when a page doesn't have children
		if ( $display_only_children != "yes" ){
			echo '<span class="pbytax-not-found">no matches</span>';
		}
	}

		
?>     

