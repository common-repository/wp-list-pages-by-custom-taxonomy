=== WP LIST PAGES BY CUSTOM TAXONOMY ===
Contributors: piccart
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=VUF3KP7YFAVZG
Tags: listing, recent posts, taxonomy, post type, widget
Requires at least: 3.2
Tested up to: 4.9.5
Stable tag: 1.4.10
License: GPL2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Widget to lists posts of any active post-type, filtering by any term of any active custom taxonomy. display title, or thumb, date and excerpt too.

== Description ==

**This plugin will provide a new widget in the Widgets Section, so you can easily add it in any widgetized area of your theme.**

With this widget you will be able to *list pages or posts or any other post-type*, filtering them by *terms of categories, tags and any other active custom taxonomy*. You can add filtering by *Custom Meta Field* value, and decide how to order the list.
You can also choose to display only *Title*, or to add *Thumbnails*, *Excerpts* and *Date*. Or you could display only the image with the details on rollover!

Its scope is very similar to the default **Recent Posts widget**, but you'll have tons of options and you can use it for any post-type and taxonomy.

Now you can also set the max width of the thumbnail, and the excerpt length, so the listing can display nicely even on larger widgetized areas.

**"This is the last listing widget plugin you'll ever need to download"**

the main features of the widget are:

*   Decide how many entries to display
*   Choose which Post-Type you want to list
*   Choose which Taxonomy will be used as filter
*   Choose which Terms to narrow your list with (or choose "any" term of the selected taxonomy)
*   Select multiple terms at once 
*   Include or exclude children terms of the selected ones
*   Filter by Custom Meta Fields values (full list of comparison operators)
*   Automatically lists in dropdown selectors, all available post-types, taxonomies, terms, and meta fields! no need to find their slugs or ids!
*   Order by ID, Date, Last Modified Date, Title, Comments, Page Order, Meta Field, or Random
*   Order Ascendant or Descendant
*   Choose if display Thumbnails, and if link them to the post url
*   Choose which Thumbnail Size to pull from the database
*   Set Thumbnail max width	
*   Choose the thumb alignment (left, right or centered above)
*   Choose if display Excerpts
*   Set Excerpt length (in characters)
*   Choose if to display Date (or "Last Modified" Date) and which alignment
*   Decide if text content can flow below the image or rather stay on its side
*   Eventually force the titles to stay on one line and hide the overflowing
*   Truncate the title after a specific amount of characters
*   Exclude specific posts/pages from the listing
*   Exclude current post
*   image placeholder if no-thumb is found
*   Choose to display results into a simple dropdown
*   Choose to display only image
*   If only-image, choose to display post data on mouse hover
*   Act as SubPages Nav Menu! (Display only the Children of the same current Parent)
*   Display the current Parent/Page as Widget Title
*   Add text or html above the list
*   Several filter hooks available for manipulating the plugin logic (for developers) 

Also, the plugin is structured to allow *easy overriding of the output template*. 
Just duplicate the file pbytax-template.php that you'll find within the plugin subfolder "templates", and put it in your theme folder.

You can also take advantage of a few useful filter hooks (see FAQ)!

the widget is built with a very little css styling, in order to adapt perfectly in any theme. though, if you want to style it up, all the html elements have been named with specific classes, to allow easy targetting in your css file.


if you'd like me to add a new feature to the widget, please let me know by writing a post in the [support forum](http://wordpress.org/support/plugin/wp-list-pages-by-custom-taxonomy) , and hopefully I will add it to the next version of *WP List Pages by Custom Taxonomy*.


If this plugin has been helpful for you, please don't forget to *review* it and consider to [make a donation](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=VUF3KP7YFAVZG). Don't be shy, a couple of dollars would still make me happy! :)

== Installation ==

Manual Installation:

1.   Upload the entire "wp-list-pages-by-custom-taxonomy" folder to the /wp-content/plugins/ directory.
2.   Activate the plugin through the 'Plugins' section in WordPress.

Easy wp admin installation:

1.   go to 'Plugins" section in wordpress and "add new plugin"
2.   choose "upload plugin" and upload the zip file
3.   click on "Activate" and you're done.

Plugin use:

1.   go to "Widgets" Section under "Appearance" in Wordpress admin.
2.   Drag and drop the widget "Wp List Pages by Custom Taxonomy" into the desired sidebar
3.   Set the options as you wish
4.   Enjoy!


== Frequently Asked Questions ==

for any other specific question and for bug reports, please use the [support forum](http://wordpress.org/support/plugin/wp-list-pages-by-custom-taxonomy)

= I have a specific need for my site, could you help me? =

Yes, I am a freelance web developer and for living I develop bespoke plugins and functionalities of any sort.
though:
if your request could be considered just an additional feature for this plugin, I will add it to my to-do list and see if it's possible to implement this feature in the future versions of "WP List Pages by Custom Taxonomy".
if it's something very specific to your site, I would probably suggest to hire me for a few hours, and I'll develop exactly what you need.


= Are there hooks and filters that I can use to manipulate the plugin's output? =

Yes, we have a few of them:

**pbytax_query_args**
-  *$pbytax_args = apply_filters( 'pbytax_query_args', $pbytax_args, $instance, $widget_num );*
-  this filter allows you to edit the args just before they are passed to the query.
-  it provides the current query args array, the array containing all the details of the current widget instance, and the number of the PbT widget.

**pbytax_default_options**
-  *$defaults = apply_filters( 'pbytax_default_options', $defaults );*
-  this filter allows to modify the defaults array, just before it is parsed with the instance's values

**pbytax_instance_update**
-  *$instance = apply_filters( 'pbytax_instance_update', $instance, $new_instance, $old_instance);*
-  this filter allows you to manipulate the widget options array just before they are updated.
-  it provides the value of the current final options instance, the array of the options as they have been submitted, and the array of the options as they were before this update is finalized.

**pbytax_prepare_instance**
-  *$new_instance = apply_filters( 'pbytax_prepare_instance', $new_instance, $instance, $widget_num );*
-  this filter allows to manipulate the to manipulate the options array before it is passed to the query args builder
-  it provides the value of the current final options array, the options array as they were passed by the widget settings, and the number of the widget instance

**pbytax_post_title**
- *echo apply_filters('pbytax_post_title', $title, $full_title, $instance );*
- filter the post title before it's printed out. $title might be trimmed, hence $full_title is the string before trimming.

**pbytax_dropdown_title**
- *echo apply_filters( 'pbytax_dropdown_title', $dropdown_text, $instance, $data );*
- this filter allows to edit the dropdown's first option
- it's already possible to set a text string for it within the options, but with this filter you can do more complex customisations
- by instance you can add the number of posts, using $data['count']

== Screenshots ==

1. Options available in the widget
2. sample of frontend display using a default theme

== Changelog ==

= 1.4.10 =
* FIX: Ensure that the excerpt is reset after each item. Fallback to default excerpt if we couldn't build a custom one
* WP COMPATIBILITY: tested compatibility with wp 4.9.5

= 1.4.9 =
* IMPROVEMENT: added filter to customise the title of the dropdown view
* IMPROVEMENT: extra checks for the template version

= 1.4.8 =
* IMPROVEMENT: added option to order by menu_order
* IMPROVEMENT: added option to select the thumbnail size to be used in the widget
* WP COMPATIBILITY: tested compatibility with wp 4.9.1


= 1.4.7 =
* FIX: Better way to clean the template version number and avoid problems with additional spaces
* IMPROVEMENT: added option to set a maximum length for the post titles
* WP COMPATIBILITY: tested compatibility with wp 4.8.2

= 1.4.6 =
* IMPROVEMENT: added option to display the "last modified" instead of normal date
* IMPROVEMENT: added option to order by "last modified"
* IMPROVEMENT: added option to specify a date format
* IMPROVEMENT: store the current template version as wp option so we don't have to check it every time from frontend.
* IMPROVEMENT: tidied up activation and deactivation routines.

= 1.4.5 = 
* FIX: Removed the use of get_plugin_data() as it is actually less efficient

= 1.4.4 = 
* IMPORTANT FIX: My sincere apologies! there was an error in the uploading of the last version! the entire repo ended up in the tag of version 1.4.3

= 1.4.3 =
* WP COMPATIBILITY: tested compatibility with wp 4.6.1
* COMPATIBILITY: tweaked the get_terms() function to be ready for newly introduced format (kept compatibility for old wp version till wp won't drop it completely)
* IMPROVEMENT: use get_plugin_data() to retrieve current plugin version
* IMPROVEMENT: more efficient way to get the widget custom template version
* IMPROVEMENT: use transient to store the meta_keys query for the settings selectors

= 1.4.2 =

* COMPATIBILITY: fixed the plugin and template versions, and minimum required versions for admin notices
* IMPORTANT: core edits made on the file /templates/pbytax_template.php. make sure to update your custom template file within your theme, if you have made one 

= 1.4.1 =

* IMPROVEMENT: added option to exclude the current post from the list
* IMPROVEMENT: display widget instance number on settings
* IMPROVEMENT: option to hide the title text overflowing one line 
* IMPROVEMENT: option to allow/avoid the text to flow below the image
* IMPROVEMENT: option to decide the Date alignment
* COMPATIBILITY: moved the date to the bottom, to avoid issues with longer titles
* COMPATIBILITY: improved the transition effect on meta-info overlay on image hovering
* COMPATIBILITY: vertically centered the meta-info overlay on image hovering
* WP COMPATIBILITY: tested compatibility with wp 4.5


= 1.4.0 =
* IMPORTANT: core edits made on the file /templates/pbytax_template.php. make sure to update your custom template file within your theme, if you have made one
* IMPROVEMENT: query_args logic included from main functions rather than into template file (added support for older versions)
* IMPROVEMENT: get_posts() query is now called from main functions rather than into template file (added support for older versions)
* IMPROVEMENT: if set to Act as SubPages Navigation, the widget is completely hidden when no posts are found
* IMPROVEMENT: added filter hook 'pbytax_prepare_instance' to manipulate the options array before it is passed to the query args builder
* COMPATIBILITY: safer conditionals to ensure that SubPages Navigation displays always correctly and only on single pages
* COMPATIBILITY: deprecated the need to reset posts query after each widget. now uses get_posts() and don't overwrite the main $post variables


= 1.3.1 =
* COMPATIBILITY: added plugin version to enqueued styles and scripts
* COMPATIBILITY: improved admin notices about custom template file status (soft and hard notice based on current version)
* FIX: corrected minor typo in a php conditional which checks for an empty term array

= 1.3.0 =
* IMPORTANT: core edits made on the file /templates/pbytax_template.php. make sure to update your custom template file within your theme, if you have made one
* IMPROVEMENT: moved the logic to build the query args into a separate file
* IMPROVEMENT: added filter hook 'pbytax_query_args' to filter the args before passing them to the query
* IMPROVEMENT: added filter hook 'pbytax_default_options' to filter the options-defaults array
* IMPROVEMENT: added filter hook 'pbytax_instance_update' to manipulate the widget options before they are updated
* IMPROVEMENT: general tidy up of the code and comments
* IMPROVEMENT: added support for different Languages
* IMPROVEMENT: option to use the widget as Sub-Pages navigation Menu
* IMPROVEMENT: option to use the current Parent/Page as Widget Title
* IMPROVEMENT: added headings to separate the settings
* COMPATIBILITY: added admin warning if custom template file needs to be updated
* FIX: avoid conflict array/string when term is set to "any"
* FIX: fixed bug which was not displaying more than one PbT widgets per sidebar

= 1.2.5 =
* IMPROVEMENT: Option to set image to display block above the rest
* IMPROVEMENT: Option to display post data on mouse over when only-thumb
* IMPROVEMENT: better html/css markup

= 1.2.4 =
* FIX: avoid empty div when intro is not provided
* FIX: no-thumb image now adapt size to container
* IMPROVEMENT: Option to display only image

= 1.2.3 =
* fix to avoid empty terms array when adding the widget on the theme installer
* added the option to link the thumbnail to the post
* added option to choose thumb alignment
* tested compatibility with wp 4.3.1

= 1.2.2 =
* FIXED BUG on Chrome that was blocking the update of the terms when selecting the taxonomy in the settings
* option to select any as taxonomy
* wrapped the widget settings in a div
* meta fields starting with underscore are now listed

= 1.2.15 =
* Added option to insert first word for the frontend titles selector dropdown

= 1.2.1 =
* fixed php conflict when activating in some type of servers
* New field to add text/html above the list

= 1.2.0 =
* Tested with Wp 4.1
* Added Option to Display Date in the frontend
* Added Ordering by Comments
* Added Ordering by Meta Field (numeric or text)
* Added Option to Filter by Custom Meta Field Value 
* Added Option to set the Thumb width
* Added Option to set the Excerpt length
* Added Option to display posts in a dropdown in frontend

= 1.0.1 =
* Updated readme.txt

= 1.0 =
* First version available to public

== Upgrade Notice ==

= 1.4.10 =
FIX: Ensure that the excerpt is reset after each item. Fallback to default excerpt if we couldn't build a custom one
WP COMPATIBILITY: tested compatibility with wp 4.9.5

= 1.4.9 =
IMPROVEMENT: added filter to customise the title of the dropdown view
IMPROVEMENT: extra checks for the template version

= 1.4.8 =
IMPROVEMENT: added option to order by menu_order
IMPROVEMENT: added option to select the thumbnail size to be used in the widget
WP COMPATIBILITY: tested compatibility with wp 4.9.1

= 1.4.7 =
FIX: Better way to clean the template version number and avoid problems with additional spaces
IMPROVEMENT: added option to set a maximum length for the post titles
WP COMPATIBILITY: tested compatibility with wp 4.8.2

= 1.4.6 =
IMPROVEMENT: added option to display the "last modified" instead of normal date
IMPROVEMENT: added option to order by "last modified"
IMPROVEMENT: added option to specify a date format
IMPROVEMENT: store the current template version as wp option so we don't have to check it every time from frontend.
IMPROVEMENT: tidied up activation and deactivation routines.

= 1.4.5 = 
IMPORTANT FIX: Removed the use of get_plugin_data() as it is actually less efficient

= 1.4.4 = 
IMPORTANT: My sincere apologies! there was an error in the uploading of the last version! the entire repo ended up in the tag of version 1.4.3

= 1.4.3 =
COMPATIBILITY: tweaked the get_terms() function to be ready for newly introduced format (kept compatibility for old version)
IMPROVEMENT: use get_plugin_data() to retrieve current version
IMPROVEMENT: more efficient way to get the widget custom template version
IMPROVEMENT: use transient to store the meta_keys query for the settings selectors

= 1.4.2 =

COMPATIBILITY: fixed the plugin and template versions, and minimum required versions for admin notices
IMPORTANT: core edits made on the file /templates/pbytax_template.php. make sure to update your custom template file within your theme, if you have made one 

= 1.4.1 =

IMPROVEMENT: added option to exclude the current post from the list
IMPROVEMENT: display widget instance number on settings
IMPROVEMENT: option to hide the title text overflowing one line 
IMPROVEMENT: option to allow/avoid the text to flow below the image
IMPROVEMENT: option to decide the Date alignment
COMPATIBILITY: moved the date to the bottom, to avoid issues with longer titles
COMPATIBILITY: improved the transition effect on meta-info overlay on image hovering
COMPATIBILITY: vertically centered the meta-info overlay on image hovering
WP COMPATIBILITY: tested compatibility with wp 4.5

= 1.4.0 =
IMPORTANT: core edits made on the file /templates/pbytax_template.php. make sure to update your custom template file within your theme, if you have made one
IMPROVEMENT: query_args logic included from main functions rather than into template file (added support for older versions)
IMPROVEMENT: get_posts() query is now called from main functions rather than into template file (added support for older versions)
IMPROVEMENT: if set to Act as SubPages Navigation, the widget is completely hidden when no posts are found
IMPROVEMENT: added filter hook 'pbytax_prepare_instance' to manipulate the options array before it is passed to the query args builder
COMPATIBILITY: deprecated the need to reset posts query after each widget. now uses get_posts() and don't overwrite the main $post variables
COMPATIBILITY: safer conditionals to ensure that SubPages Navigation displays always correctly and only on single pages

= 1.3.1 =
COMPATIBILITY: added plugin version to enqueued styles and scripts
COMPATIBILITY: improved admin notices about custom template file status (soft and hard notice based on current version)
FIX: corrected minor typo in a php conditional which checks for an empty term array

= 1.3.0 =
IMPORTANT: core edits made on the file /templates/pbytax_template.php. make sure to update your custom template file within your theme, if you have made one
IMPROVEMENT: moved the logic to build the query args into a separate file 
IMPROVEMENT: added hook 'pbytax_query_args' to filter the args before passing them to the query
IMPROVEMENT: added hook 'pbytax_default_options' to filter the options-defaults array
IMPROVEMENT: added hook 'pbytax_instance_update' to manipulate the widget options before they are updated
IMPROVEMENT: general tidy up of the code and comments
IMPROVEMENT: added support for different Languages (no languages packs yet)
IMPROVEMENT: option to use the widget as Sub-Pages navigation Menu
IMPROVEMENT: option to use the current Parent/Page as Widget Title
IMPROVEMENT: added headings to separate the settings
COMPATIBILITY: added admin warning if custom template file needs to be updated
FIX: avoid conflict array/string when term is set to "any"
FIX: fixed bug which was not displaying more than one PbT widgets per sidebar

= 1.2.5 =
IMPROVEMENT: Option to set image to display block above the rest
IMPROVEMENT: Option to display post data on mouse over when only-thumb
IMPROVEMENT: better html/css markup

= 1.2.4 =
FIX: avoid empty div when intro is not provided
FIX: no-thumb image now adapt size to container
IMPROVEMENT: Option to display only image

= 1.2.3 =
fix to avoid empty terms array when adding the widget on the theme installer
option to link the thumbnail to the post
option to choose thumb alignment
tested compatibility with wp 4.3.1

= 1.2.2 =
FIXED BUG on Chrome that was blocking the update of the terms when selecting the taxonomy in the settings
option to select any as taxonomy
wrapped the widget settings in a div, to better target the js and css for the admin
meta fields starting with underscore are now listed

= 1.2.15 =
Added option to insert first word for the frontend titles selector dropdown

= 1.2.1 =
fixed php conflict when activating in some type of servers
New field to add text/html above the list

= 1.2.0 =
Tested with Wp 4.1
Added Option to Display Date in frontend
Added Ordering by Comments,
Added Ordering by Meta Field (numeric or text),
Added Option to Filter by Custom Meta Field Value 
Added Option to set the Thumb width
Added Option to set the Excerpt length
Added Option to display posts in a dropdown in frontend

= 1.0.1 =
Updated readme.txt

= 1.0 =
First version available to public


