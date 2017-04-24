<?php

/**
* Gets a number of terms and displays them as options
* @param  string $taxonomy Taxonomy terms to retrieve. Default is category.
* @param  string|array $args Optional. get_terms optional arguments
* @return array                  An array of options that matches the CMB2 options array
*/

//function cmb2_get_term_options_posts($taxonomy = 'category', $args = array())
//{
//	$args['taxonomy'] = $taxonomy;
//	// $defaults = array( 'taxonomy' => 'category' );
//	$args = wp_parse_args($args, array('taxonomy' => 'category'));
//	$taxonomy = $args['taxonomy'];
//	$terms = (array)get_terms($taxonomy, $args);
//	// Initate an empty array
//	$term_options = array();
//	if (!empty($terms)) {
//		foreach ($terms as $term) {
//			$term_options[$term->term_id] = $term->name;
//		}
//	}
//	return $term_options;
//}

add_action('cmb2_init', 'cust_meta_fields');
function cust_meta_fields()
{
	$prefix = get_the_prefix();
	$wysiwygOptions = array(
		'wpautop' => true, // use wpautop?
		'media_buttons' => true, // show insert/upload button(s)
		//'textarea_name' => $editor_id, // set the textarea name to something different, square brackets [] can be used here
		'textarea_rows' => get_option('default_post_edit_rows', 10), // rows="..."
		'tabindex' => '',
		'editor_css' => '', // intended for extra styles for both visual and HTML editors buttons, needs to include the `<style>` tags, can use "scoped".
		'editor_class' => '', // add extra class(es) to the editor textarea
		'teeny' => false, // output the minimal editor config used in Press This
		'dfw' => false, // replace the default fullscreen with DFW (needs specific css)
		'tinymce' => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
		'quicktags' => true // load Quicktags, can be used to pass settings directly to Quicktags using an array()
	);
	$wysiwygSmallNoMediaOptions = array(
		'wpautop' => true, // use wpautop?
		'media_buttons' => false, // show insert/upload button(s)
		//'textarea_name' => $editor_id, // set the textarea name to something different, square brackets [] can be used here
		'textarea_rows' => get_option('default_post_edit_rows', 5), // rows="..."
		'tabindex' => '',
		'editor_css' => '', // intended for extra styles for both visual and HTML editors buttons, needs to include the `<style>` tags, can use "scoped".
		'editor_class' => '', // add extra class(es) to the editor textarea
		'teeny' => false, // output the minimal editor config used in Press This
		'dfw' => false, // replace the default fullscreen with DFW (needs specific css)
		'tinymce' => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
		'quicktags' => true // load Quicktags, can be used to pass settings directly to Quicktags using an array()
	);
	$homeWhitelist = array(get_id('home'));

	/**
	* Site Information
	* @var $site_informaton
	*/

	$site_informaton = new_cmb2_box(array(
		'id' => 'site_information',
		'title' => __('Site\'s Information', be_domain()),
		'object_types' => array('page'), // Post type
		'show_on'      => array( 'key' => 'page-template', 'value' => array('tpl-page-home.php') ),
		'context' => 'normal',
		'priority' => 'high',
		'show_names' => true
	));
	$site_informaton->add_field(array(
		'name' => __('Legal', be_domain()),
		'id' => $prefix . 'site_information_legal',
		'type' => 'wysiwyg',
		'opions' => $wysiwygOptions
	));
	$social = $site_informaton->add_field( array(
		'name' => _x( 'Social Media', 'Global Information', be_domain() ),
	    'id'          => $prefix . 'site_information_social',
	    'type'        => 'group',
	    'options'     => array(
	        'group_title'   => __( 'Link {#}', be_domain() ), // since version 1.1.4, {#} gets replaced by row number
	        'add_button'    => __( 'Add Another Link', be_domain() ),
	        'remove_button' => __( 'Remove Link', be_domain() ),
	        'sortable'      => true, // beta
	    )
	));
	$site_informaton->add_group_field( $social, array(
	    'name' => 'Title',
	    'id'   => 'title',
	    'type' => 'text'
	));
	$site_informaton->add_group_field( $social, array(
	    'name' => 'URL',
	    'id'   => 'url',
	    'type' => 'text'
	));
	$site_informaton->add_group_field( $social, array(
	    'name' => 'Icon',
	    'id'   => 'icon',
	    'type' => 'text'
	));


	/**
	* Home Content
	* @var $home_content
	*/

	$home_content = new_cmb2_box(array(
		'id' => 'home_content',
		'title' => __('Home\'s Content', be_domain()),
		'object_types' => array('page'), // Post type
		'show_on'      => array( 'key' => 'page-template', 'value' => array('tpl-page-home.php') ),
		'context' => 'normal',
		'priority' => 'high',
		'show_names' => true
	));
	$home_content->add_field(array(
		'name' => __('Test', be_domain()),
		'id' => $prefix . 'home_content_test',
		'type' => 'wysiwyg',
		'opions' => $wysiwygOptions
	));

}


/**
* Excludes a PostID array
*    'show_on'    => array('key' => 'exclude_id', 'value' => array('id'),
* @param $display
* @param $meta_box
* @return bool
*/
function be_metabox_exclude_for_id( $display, $meta_box ) {
	if ( ! isset( $meta_box['show_on']['key'], $meta_box['show_on']['value'] ) ) {
		return $display;
	}

	if ( 'exclude_id' !== $meta_box['show_on']['key'] ) {
		return $display;
	}

	$post_id = 0;

	// If we're showing it based on ID, get the current ID
	if ( isset( $_GET['post'] ) ) {
		$post_id = $_GET['post'];
	} elseif ( isset( $_POST['post_ID'] ) ) {
		$post_id = $_POST['post_ID'];
	}

	if ( ! $post_id ) {
		return $display;
	}

	// If current page id is in the included array, do not display the metabox
	$icust_to_exclude = ! is_array( $meta_box['show_on']['value'] )
	? array( $meta_box['show_on']['value'] )
	: $meta_box['show_on']['value'];

	return ! in_array( $post_id, $icust_to_exclude );
}
add_filter( 'cmb2_show_on', 'be_metabox_exclude_for_id', 10, 2 );

/**
* Usage: 'show_on' => array( 'key' => 'page-template', 'value' => @array || @string ),
* @param $display
* @param $meta_box
* @return bool
*/
function metabox_hide_on_template($display, $meta_box)
{

	if ('hide_on' !== $meta_box['show_on']['key'])
	return $display;

	// Get the current ID
	if (isset($_GET['post'])) $post_id = $_GET['post'];
	elseif (isset($_POST['post_ID'])) $post_id = $_POST['post_ID'];
	if (!isset($post_id)) return false;

	$template_name = get_page_template_slug($post_id);

	$return = true;
	if (is_array($meta_box['show_on']['value'])):
		$return = (in_array($template_name, $meta_box['show_on']['value']) ? false : true);
	else:
		$return = ($template_name == $meta_box['show_on']['value'] ? false : true);
	endif;
	return $return;
}

add_filter('cmb_show_on', 'metabox_hide_on_template', 10, 2);

function templateFilter()
{
	if (isset($_GET['post'])) {
		$id = $_GET['post'];
		$template = get_post_meta($id, '_wp_page_template', true);

		$dontShowEditor = array(
			// List template file names with ext, i.e tpl-page-home.php
		);

		if (in_array($template, $dontShowEditor) || in_array($id, $dontShowEditor)) {
			remove_post_type_support('page', 'editor');
		}

	}
}

add_action('init', 'templateFilter');
