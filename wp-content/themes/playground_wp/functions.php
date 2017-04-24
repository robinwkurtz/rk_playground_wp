<?php

function get_id($name, $lang = false)
{
	$pageID = '';
	switch ($name) {
		case 'home':
			$pageID = get_translated_id(2);
			break;
	}
	$searchLang = ($lang ? $lang : get_lang_active());
	return get_default_id($pageID, 'page', $searchLang);
};

function social_list($simple = false) {
	$social_list = array(
		'behance' => array(
			'title' => 'Behance',
			'icon' => 'fa-behance'
		),
		'dribbble' => array(
			'title' => 'Dribbble',
			'icon' => 'fa-dribbble'
		),
		'facebook' => array(
			'title' => 'Facebook',
			'icon' => 'fa-facebook-square'
		),
		'github' => array(
			'title' => 'Github',
			'icon' => 'fa-github'
		),
		'linkedin' => array(
			'title' => 'LinkedIn',
			'icon' => 'fa-linkedin'
		),
		'instagram' => array(
			'title' => 'Instagram',
			'icon' => 'fa-instagram'
		),
		'medium' => array(
			'title' => 'Medium',
			'icon' => 'fa-medium'
		),
		'twitter' => array(
			'title' => 'Twitter',
			'icon' => 'fa-twitter-square'
		),
		'youtube' => array(
			'title' => 'YouTube',
			'icon' => 'fa-youtube-play'
		),
		'personal' => array(
			'title' => 'Personal',
			'icon' => 'fa-user-circle-o'
		)
	);

	if ($simple) {
		foreach($social_list as $key => $social) :
			$social_list[$key] = $social_list[$key]['title'];
		endforeach;
	}

	return $social_list;
}

require_once 'includes/base-theme.php';

function theme_domain()
{
	return 'Robin Kurtz';
}

function get_the_prefix()
{
	$return = "_";
	$return .= 'rk';
	$return .= "_";
	return $return;
}

function wp_head_action()
{
	echo "<script>window.jQuery || document.write('<script src=\"" . get_template_directory_uri() . "/scripts/vendor/jquery-3.1.1.min.js\"><\\/script>')</script>";
}
add_action('wp_head', 'wp_head_action');

function load_my_scripts()
{
	$cssPath = get_template_directory_uri() . '/css';
	$jsPath = get_template_directory_uri() . '/scripts';

	if (!is_admin()) { //If the page is admin page, don't load//
		wp_enqueue_script('modernizr', "$jsPath/vendor/modernizr.min.js", false, '1.0', false);
		wp_register_script('jquery', "//ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js", false, '1.11.0');
		wp_enqueue_script('jquery');

		$lang = get_lang_active();
		wp_enqueue_script( 'recaptchaAPI', 'https://www.google.com/recaptcha/api.js?hl='.$lang);
		wp_enqueue_script('validate', $jsPath . '/vendor/validate/jquery.validation.js', '1.0', false);

		// Register the script
		wp_register_script( 'scripts', $jsPath . '/scripts.min.js' );

		// Enqueued script with localized data.
		wp_enqueue_script( 'scripts' );

		// wp_enqueue_script('placeHolders', "$jsPath/vendor/jquery.placeholders.min.js", array('jquery'), '1.0', false);

		wp_enqueue_style('g-fonts', '//fonts.googleapis.com/css?family=Montserrat:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i|Varela+Round', false, '1', 'all');

		// wp_enqueue_style('style', "$cssPath/styles.min.css", false, '1');

	}
}
add_action('wp_enqueue_scripts', 'load_my_scripts');

// function my_cdn_upload_url() {
//     return 'http://mk124.yourcdn.com/yoursite/wp-content/uploads';
// }
// add_filter('pre_option_upload_url_path', 'my_cdn_upload_url', 10);
// add_filter('template_directory_uri', 'my_cdn_upload_url', 10);
