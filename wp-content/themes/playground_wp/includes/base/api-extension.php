<?php

// Add custom meta into REST API

function filter_json( $data, $post, $context ) {

	$prefix = get_the_prefix();
	$prefix_length = strlen($prefix);
	$id = $post->ID;

	$post_meta = get_post_meta($id);

	// Build array of CMB2 custom meta based on presence of $prefix
	$post_type_keys_array = [];
	foreach($post_meta as $key => $value) :
		// Check if $prefix or _yoast_ is within key, to strip out stock meta
		if (strrpos($key, $prefix) === 0 || strrpos($key, '_yoast_') === 0) :
			$post_type_keys_array[] = $key;
		endif;
	endforeach;

	if (!empty($post_type_keys_array)) :
		foreach($post_type_keys_array as $meta_key) :
			// Remove $prefx for less clutter
			$key = substr($meta_key, strpos($meta_key, '_', strpos($meta_key, '_') + 1) + 1);
			// Get meta_value
			$meta_value = get_post_meta($id, $meta_key);
			// Should never be empty, however why not?
			if (!empty($meta_value)) :
				$data->data[$key] = $meta_value;
			endif;
		endforeach;

	endif;

	return $data;
}

// Add filter for each post needed to register meta in the API
// add_filter( 'rest_prepare_type-sample', 'filter_json', 10, 3 );
// add_filter( 'rest_prepare_page', 'filter_json', 10, 3 );

// Add menus into REST API

function wp_api_v2_menus_get_all_menus () {
    $menus = [];
    foreach (get_registered_nav_menus() as $slug => $description) {
        $obj = new stdClass;
        $obj->slug = $slug;
        $obj->description = $description;
        $menus[] = $obj;
    }

    return $menus;
}

function wp_api_v2_menus_get_menu_data ( $data ) {
    $menu = new stdClass;
    $menu = wp_get_nav_menu_object( $data['id'] );
    $menu->items = wp_get_nav_menu_items($menu->term_id);
    return $menu;
}

add_action( 'rest_api_init', function () {
    register_rest_route( 'menus/v1', '/menus', array(
        'methods' => 'GET',
        'callback' => 'wp_api_v2_menus_get_all_menus',
    ) );

    register_rest_route( 'menus/v1', '/menus/(?P<id>[a-zA-Z(-]+)', array(
        'methods' => 'GET',
        'callback' => 'wp_api_v2_menus_get_menu_data',
    ) );
} );

// Add site information into REST API_URL

function get_site_info() {
	$id = get_id('home');
	$key_prefix = 'site_information';

	$post_meta = get_post_meta($id);

	// Build array of CMB2 custom meta
	$post_type_keys_array = [];
	foreach($post_meta as $key => $value) :
		// Check if $key_prefix or is within key, to strip out stock meta
		if (strrpos($key, $key_prefix)) :
			$post_type_keys_array[] = $key;
		endif;
	endforeach;

	if (!empty($post_type_keys_array)) :
		foreach($post_type_keys_array as $meta_key) :
			// Remove $prefx for less clutter
			$key = substr($meta_key, strpos($meta_key, '_', strpos($meta_key, '_') + 1) + 1);
			// Get meta_value
			$meta_value = get_post_meta($id, $meta_key);
			// Should never be empty, however why not?
			if (!empty($meta_value)) :
				$data[$key] = $meta_value;
			endif;
		endforeach;
	endif;

	return [$data];
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'siteinfo/v1', '/content', array(
		'methods' => 'GET',
		'callback' => 'get_site_info',
	) );
} );
