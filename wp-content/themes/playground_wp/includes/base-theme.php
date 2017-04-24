<?php

require_once 'wiki-page.php';
require_once 'base/post-types.php';
require_once 'base/shortcodes.php';
require_once 'base/meta-fields.php';
require_once 'base/api-extension.php';
require_once 'vendor/wp-api-menus/wp-api-menus.php';
require_once 'base/form-builder.php';


function is_dev() {
	if (isset($_REQUEST['reversetest'])):
		return false;
	elseif (isset($_REQUEST['prev'])):
		return true;
	elseif ($_SERVER['REMOTE_ADDR'] == '76.69.235.208'):
		return true;
	else:
		return false;
	endif;
}

// Wordoress Patch(s)

add_filter('auto_update_core', '__return_false');

function remove_generators() {
	remove_action('wp_head', 'wp_generator');
	remove_action('wp_head', 'wlwmanifest_link');
	remove_action('wp_head', 'rsd_link');
	add_filter('the_generator', 'remove_info');
	if (class_exists('SitePress')):
		remove_action('wp_head', array($GLOBALS['sitepress'], 'meta_generator_tag'));
	endif;
}
add_action('init', 'remove_generators');

function wphidenag() {
	if (!is_dev()):
		remove_action('admin_notices', 'update_nag', 3);
	endif;
}
add_action('admin_menu', 'wphidenag');

function remove_menus() {
    remove_menu_page( 'theme-editor.php' ); // Theme Editor
    remove_menu_page( 'edit-comments.php' ); // Comments
}
add_action('admin_menu', 'remove_menus');

add_theme_support('post-thumbnails');
remove_theme_support('post-formats');

add_action( 'wp_enqueue_scripts', 'mytheme_scripts' );
function mytheme_scripts() {
	wp_enqueue_style( 'dashicons' );
}

function set_html_content_type() {
	return 'text/html';
}

// CMB2

if (!class_exists('CMB2_Bootstrap_208')) {
	require_once 'vendor/cmb2/init.php';
	require_once 'vendor/cmb2/conditional/cmb2-conditionals.php';
	require_once 'vendor/cmb2/cmb2-attached-posts/cmb2-attached-posts-field.php';
}
if (!class_exists('taxonomy_metadata')) {
	require_once 'vendor/cmb2/taxonomy_metadata.php';
	require_once 'vendor/cmb2/taxonomy_metadata_cmb2.php';
}

// Backend Customization

function custom_login_logo() {
	echo '<style type="text/css">.login #login h1 a { width: 90% !important; background-image: url(' . get_bloginfo('template_directory') . '/images/logo.png) !important; background-size: contain !important; height: 100px !important }</style>';
}
add_action('login_head', 'custom_login_logo');

// Menu Helper(s)

function load_admin_functions() {
	if (is_admin()):
		add_editor_style('css/editor-style.css');
	endif;
	register_nav_menus(
		array(
			'menu-header' => __('Header Menu'),
			'menu-footer' => __('Footer Menu'),
		)
	);
}
add_action('init', 'load_admin_functions');


// Content Fetchers

function get_lang_active($validateAgainst = false) {
	$activeLang = 'en';
	if (class_exists('SitePress')):
		global $sitepress;
		$activeLang = substr($sitepress->get_current_language(), 0, 2);
	endif;
	if (!$validateAgainst):
		return $activeLang;
	else:
		return ($activeLang == $validateAgainst);
	endif;
}

function get_languages_short($includeActive = false) {
	if (function_exists('icl_get_languages')):
		$languages = icl_get_languages('skip_missing=0');
		$langReturn = array();
		if (1 < count($languages)):
			foreach ($languages as $l):
				if ($includeActive):
					$return = "<a href='{$l['url']}'" . ($l['active'] ? " class='active'" : '') . ">";
					$return .= substr($l['language_code'], 0, 2);
					$return .= "</a>";
					$langReturn[] = $return;
				else:
					if (!$l['active']) $langReturn[] = '<a href="' . $l['url'] . '">' . $l['language_code'] . '</a>';
				endif;

			endforeach;
		endif;
		return $langReturn;
	endif;
	return '';
}

function get_languages_long($includeActive = false) {
	if (function_exists('icl_get_languages')):
		$languages = icl_get_languages('skip_missing=0');
		$langReturn = array();
		if (1 < count($languages)):
			foreach ($languages as $l):
				if ($includeActive):
					$return = "<a href='{$l['url']}'" . ($l['active'] ? " class='active'" : '') . ">";
					$return .= $l['native_name'];
					$return .= "</a>";
					$langReturn[] = $return;
				else:
					if (!$l['active']) $langReturn[] = '<a href="' . $l['url'] . '">' . $l['native_name'] . '</a>';
				endif;
			endforeach;
		endif;
		return $langReturn;
	endif;
	return '';
}

function get_lang_code($activeLang = false) {
	if (class_exists('SitePress')):
		if (!$activeLang):
			$activeLang = get_lang_active();
		endif;
		$langs = icl_get_languages('skip_missing=0');
		if (isset($langs[$activeLang]['default_locale'])):
			return $langs[$activeLang]['default_locale'];
		endif;
	endif;
	return false;
}

function get_translated_id($id, $type = 'page') {
	$returnID = intval($id);
	if (class_exists('SitePress')) {
		if (function_exists('icl_object_id')) {
			$returnID = icl_object_id($id, $type, true);
		}
	}
	return intval($returnID);
}

function get_default_id($id, $type = 'page', $lang = 'en') {
	$returnID = intval($id);
	if (class_exists('SitePress')) {
		if (function_exists('icl_object_id')) {
			$returnID = icl_object_id($id, $type, true, $lang);
		}
	}
	return intval($returnID);
}

function get_theme_path($withSlash = false, $extraPath = '') {
	$morePath = '';
	if ($withSlash == true) $morePath .= '/';
	if ($extraPath !== '') $morePath .= $extraPath;
	return get_template_directory_uri() . $morePath;
}

function get_image_path($name) {
	return get_theme_path() . '/images/' . $name;
}

function get_meta($id, $field) {
	$prefix = get_the_prefix();
	return get_post_meta(intval($id), $prefix . $field, true);
}

function get_tax_meta($category, $id, $field) {
	return taxonomy_metadata::get($category, $id, $field);
}

function be_domain() {
	$front_domain = 'Theme';
	if (!function_exists('theme_domain')):
		$front_domain = theme_domain();
	endif;
	return $front_domain . ' Backend';
}

// $value is id or object
function can_edit($value, $right = false) {
	$current_user = wp_get_current_user();
	if (user_can( $current_user, 'administrator' )) :
		if (is_object($value) && $value->user_login) :
			$link = get_edit_user_link($value->ID);
		else :
			$link = get_edit_post_link($value);
		endif;

		return '<a href="' . $link . '" target="_blank" class="editor dashicons-before dashicons-edit"' . ($right ? ' style="float:right"' : '') . '><span>Edit</span></a>';
	else :
		return null;
	endif;
}

function custom_excerpt_length($length) {
	return 20;
}
add_filter( 'excerpt_length', 'custom_excerpt_length', 999 );

function wpdocs_excerpt_more( $more ) {
    return sprintf( ' ... <a class="read-more" href="%1$s">%2$s</a>',
        get_permalink( get_the_ID() ),
        __('[Read More]', theme_domain())
    );
}
add_filter( 'excerpt_more', 'wpdocs_excerpt_more' );

function slugify($text) {
  // replace non letter or digits by -
  $text = preg_replace('~[^\pL\d]+~u', '-', $text);

  // transliterate
  $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

  // remove unwanted characters
  $text = preg_replace('~[^-\w]+~', '', $text);

  // trim
  $text = trim($text, '-');

  // remove duplicate -
  $text = preg_replace('~-+~', '-', $text);

  // lowercase
  $text = strtolower($text);

  if (empty($text)) {
    return 'n-a';
  }
  return $text;
}


// Navigation Walker

class navWalker_button extends Walker_Nav_Menu {

	function end_el(&$output, $item, $depth = 0, $args = array()) {
		$output .= "</li>";
	}

	function start_lvl(&$output, $depth = 0, $args = array()) {
		$output .= "<ul class=\"sub-menu\">";
	}

	function end_lvl(&$output, $depth = 0, $args = array()) {
		$output .= "</ul>";
	}

	function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0)
    {
		global $wp_query;
		$indent = '';

		$class_names = $value = '';

		$classes = empty($item->classes) ? array() : (array)$item->classes;

		$class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item));
		$class_names = ' class="' . esc_attr($class_names) . ' post-id-' . esc_attr($item->object_id) . '"';

		$output .= $indent . '<li' . $value . $class_names . '>';

		$attributes = !empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';

		$attributes .= !empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';
		$attributes .= !empty($item->url) ? ' href="' . esc_attr($item->url) . '"' : '';
		$strposlink = esc_attr($item->url);

		$items_post_type = get_meta($item->object_id, 'template_feed_type');

		$class = 'btn small';
		if ($item->current || $items_post_type === get_post_type()) {
			$class .= ' active';
		}

		$item_output = '<a' . $attributes . ' class="' . $class . '">';
		$item_output .= apply_filters('the_title', $item->title, $item->ID);
		$item_output .= '</a>';

		$output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args, $id);

	}
}

class navWalker_basic extends Walker_Nav_Menu {

	function end_el(&$output, $item, $depth = 0, $args = array()) {
		$output .= "</li>";
	}

	function start_lvl(&$output, $depth = 0, $args = array()) {
		$output .= "<ul class=\"sub-menu\">";
	}

	function end_lvl(&$output, $depth = 0, $args = array()) {
		$output .= "</ul>";
	}

	function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0)
    {
		global $wp_query;
		$indent = '';

		$class_names = $value = '';

		$classes = empty($item->classes) ? array() : (array)$item->classes;

		$class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item));
		$class_names = ' class="' . esc_attr($class_names) . ' post-id-' . esc_attr($item->object_id) . '"';

		$output .= $indent . '<li' . $value . $class_names . '>';

		$attributes = !empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';

		$attributes .= !empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';
		$attributes .= !empty($item->url) ? ' href="' . esc_attr($item->url) . '"' : '';
		$strposlink = esc_attr($item->url);

		$item_output = '<a' . $attributes . '>';
		$item_output .= apply_filters('the_title', $item->title, $item->ID);
		$item_output .= '</a>';

		$output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args, $id);

	}
}

class navWalker_temp extends Walker_Nav_Menu {

	function end_el(&$output, $item, $depth = 0, $args = array()) {
		$output .= "</li>";
	}

	function start_lvl(&$output, $depth = 0, $args = array()) {
		$output .= "<ul class=\"sub-menu\">";
	}

	function end_lvl(&$output, $depth = 0, $args = array()) {
		$output .= "</ul>";
	}

	function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0)
    {
		global $wp_query;
		$indent = '';

		$class_names = $value = '';

		$classes = empty($item->classes) ? array() : (array)$item->classes;

		$class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item));
		$class_names = ' class="' . esc_attr($class_names) . ' post-id-' . esc_attr($item->object_id) . '"';

		$output .= $indent . '<li' . $value . $class_names . '>';

		$attributes = !empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';

		$attributes .= !empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';
		$attributes .= !empty($item->url) ? ' href="' . esc_attr($item->url) . '?temp"' : '';
		$strposlink = esc_attr($item->url);

		$item_output = '<a' . $attributes . '>';
		$item_output .= apply_filters('the_title', $item->title, $item->ID);
		$item_output .= '</a>';

		$output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args, $id);

	}
}


// Query helpers

function active_query_arg($key, $value) {
	return (isset($_GET[$key]) && ($_GET[$key] === $value || is_array($value) && in_array($_GET[$key], $value)));
}

function toggle_query_arg($key, $value) {
	if (active_query_arg($key, $value)) {
		return remove_query_arg($key);
	} else {
		return add_query_arg($key, $value);
	}
}

// Body Class(es)

function browser_body_class($classes) {
	global $is_lynx, $is_gecko, $is_IE, $is_opera, $is_NS4, $is_safari, $is_chrome, $is_iphone;
	if ($is_lynx) $classes[] = 'lynx';
	elseif ($is_gecko) $classes[] = 'gecko';
	elseif ($is_opera) $classes[] = 'opera';
	elseif ($is_NS4) $classes[] = 'ns4';
	elseif ($is_safari) $classes[] = 'safari';
	elseif ($is_chrome) $classes[] = 'chrome';
	elseif ($is_IE) $classes[] = 'ie';
	else $classes[] = 'unknown';
	if ($is_iphone) $classes[] = 'iphone';
	if (wp_is_mobile()) {
		$classes[] = 'mobile';
	}
	return $classes;
}
add_filter('body_class', 'browser_body_class');
