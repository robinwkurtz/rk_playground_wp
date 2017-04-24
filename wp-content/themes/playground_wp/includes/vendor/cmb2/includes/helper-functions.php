<?php
/**
 * CMB2 Helper Functions
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    WebDevStudios
 * @license   GPL-2.0+
 * @link      http://webdevstudios.com
 */

/**
 * Helper function to provide directory path to CMB2
 * @since  2.0.0
 * @param  string  $path Path to append
 * @return string        Directory with optional path appended
 */
function cmb2_dir( $path = '' ) {
	return CMB2_DIR . $path;
}

/**
 * Autoloads files with CMB2 classes when needed
 * @since  1.0.0
 * @param  string $class_name Name of the class being requested
 */
function cmb2_autoload_classes( $class_name ) {
	if ( 0 !== strpos( $class_name, 'CMB2' ) ) {
		return;
	}

	include_once( cmb2_dir( "includes/{$class_name}.php" ) );
}

/**
 * Get instance of the CMB2_Utils class
 * @since  2.0.0
 * @return CMB2_Utils object CMB2 utilities class
 */
function cmb2_utils() {
	static $cmb2_utils;
	$cmb2_utils = $cmb2_utils ? $cmb2_utils : new CMB2_Utils();
	return $cmb2_utils;
}

/**
 * Get instance of the CMB2_Ajax class
 * @since  2.0.0
 * @return CMB2_Ajax object CMB2 utilities class
 */
function cmb2_ajax() {
	static $cmb2_ajax;
	$cmb2_ajax = $cmb2_ajax ? $cmb2_ajax : new CMB2_Ajax();
	return $cmb2_ajax;
}

/**
 * Get instance of the CMB2_Option class for the passed metabox ID
 * @since  2.0.0
 * @return CMB2_Option object Options class for setting/getting options for metabox
 */
function cmb2_options( $key ) {
	return CMB2_Options::get( $key );
}

/**
 * Get a cmb oEmbed. Handles oEmbed getting for non-post objects
 * @since  2.0.0
 * @param  array   $args Arguments. Accepts:
 *
 *         'url'         - URL to retrieve the oEmbed from,
 *         'object_id'   - $post_id,
 *         'object_type' - 'post',
 *         'oembed_args' - $embed_args, // array containing 'width', etc
 *         'field_id'    - false,
 *         'cache_key'   - false,
 *
 * @return string        oEmbed string
 */
function cmb2_get_oembed( $args = array() ) {
	return cmb2_ajax()->get_oembed( $args );
}

/**
 * A helper function to get an option from a CMB2 options array
 * @since  1.0.1
 * @param  string  $option_key Option key
 * @param  string  $field_id   Option array field key
 * @return array               Options array or specific field
 */
function cmb2_get_option( $option_key, $field_id = '' ) {
	return cmb2_options( $option_key )->get( $field_id );
}

/**
 * A helper function to update an option in a CMB2 options array
 * @since  2.0.0
 * @param  string  $option_key Option key
 * @param  string  $field_id   Option array field key
 * @param  mixed   $value      Value to update data with
 * @param  boolean $single     Whether data should not be an array
 * @return boolean             Success/Failure
 */
function cmb2_update_option( $option_key, $field_id, $value, $single = true ) {
	if ( cmb2_options( $option_key )->update( $field_id, $value, false, $single ) ) {
		return cmb2_options( $option_key )->set();
	}

	return false;
}

/**
 * Get a CMB2 field object.
 * @since  1.1.0
 * @param  array  $meta_box    Metabox ID or Metabox config array
 * @param  array  $field_id    Field ID or all field arguments
 * @param  int    $object_id   Object ID
 * @param  string $object_type Type of object being saved. (e.g., post, user, comment, or options-page).
 *                             Defaults to metabox object type.
 * @return CMB2_Field|null     CMB2_Field object unless metabox config cannot be found
 */
function cmb2_get_field( $meta_box, $field_id, $object_id = 0, $object_type = '' ) {

	$object_id = $object_id ? $object_id : get_the_ID();
	$cmb = $meta_box instanceof CMB2 ? $meta_box : cmb2_get_metabox( $meta_box, $object_id );

	if ( ! $cmb ) {
		return;
	}

	$cmb->object_type( $object_type ? $object_type : $cmb->mb_object_type() );

	return $cmb->get_field( $field_id );
}

/**
 * Get a field's value.
 * @since  1.1.0
 * @param  array  $meta_box    Metabox ID or Metabox config array
 * @param  array  $field_id    Field ID or all field arguments
 * @param  int    $object_id   Object ID
 * @param  string $object_type Type of object being saved. (e.g., post, user, comment, or options-page).
 *                             Defaults to metabox object type.
 * @return mixed               Maybe escaped value
 */
function cmb2_get_field_value( $meta_box, $field_id, $object_id = 0, $object_type = '' ) {
	$field = cmb2_get_field( $meta_box, $field_id, $object_id, $object_type );
	return $field->escaped_value();
}

/**
 * Because OOP can be scary
 * @since  2.0.2
 * @param  array $meta_box_config Metabox Config array
 * @return CMB2 object            Instantiated CMB2 object
 */
function new_cmb2_box( array $meta_box_config ) {
	return cmb2_get_metabox( $meta_box_config );
}

/**
 * Retrieve a CMB2 instance by the metabox ID
 * @since  2.0.0
 * @param  mixed  $meta_box    Metabox ID or Metabox config array
 * @param  int    $object_id   Object ID
 * @param  string $object_type Type of object being saved. (e.g., post, user, comment, or options-page).
 *                             Defaults to metabox object type.
 * @return CMB2 object
 */
function cmb2_get_metabox( $meta_box, $object_id = 0, $object_type = '' ) {

	if ( $meta_box instanceof CMB2 ) {
		return $meta_box;
	}

	if ( is_string( $meta_box ) ) {
		$cmb = CMB2_Boxes::get( $meta_box );
	} else {
		// See if we already have an instance of this metabox
		$cmb = CMB2_Boxes::get( $meta_box['id'] );
		// If not, we'll initate a new metabox
		$cmb = $cmb ? $cmb : new CMB2( $meta_box, $object_id );
	}

	if ( $cmb && $object_id ) {
		$cmb->object_id( $object_id );
	}

	if ( $cmb && $object_type ) {
		$cmb->object_type( $object_type );
	}

	return $cmb;
}

/**
 * Returns array of sanitized field values from a metabox (without saving them)
 * @since  2.0.3
 * @param  mixed $meta_box         Metabox ID or Metabox config array
 * @param  array $data_to_sanitize Array of field_id => value data for sanitizing (likely $_POST data).
 * @return mixed                   Array of sanitized values or false if no CMB2 object found
 */
function cmb2_get_metabox_sanitized_values( $meta_box, array $data_to_sanitize ) {
	$cmb = cmb2_get_metabox( $meta_box );
	return $cmb ? $cmb->get_sanitized_values( $data_to_sanitize ) : false;
}

/**
 * Retrieve a metabox form
 * @since  2.0.0
 * @param  mixed   $meta_box  Metabox config array or Metabox ID
 * @param  int     $object_id Object ID
 * @param  array   $args      Optional arguments array
 * @return string             CMB2 html form markup
 */
function cmb2_get_metabox_form( $meta_box, $object_id = 0, $args = array() ) {

	$object_id = $object_id ? $object_id : get_the_ID();
	$cmb       = cmb2_get_metabox( $meta_box, $object_id );

	ob_start();
	// Get cmb form
	cmb2_print_metabox_form( $cmb, $object_id, $args );
	$form = ob_get_contents();
	ob_end_clean();

	return apply_filters( 'cmb2_get_metabox_form', $form, $object_id, $cmb );
}

/**
 * Display a metabox form & save it on submission
 * @since  1.0.0
 * @param  mixed   $meta_box  Metabox config array or Metabox ID
 * @param  int     $object_id Object ID
 * @param  array   $args      Optional arguments array
 */
function cmb2_print_metabox_form( $meta_box, $object_id = 0, $args = array() ) {

	$object_id = $object_id ? $object_id : get_the_ID();
	$cmb = cmb2_get_metabox( $meta_box, $object_id );

	// if passing a metabox ID, and that ID was not found
	if ( ! $cmb ) {
		return;
	}

	$args = wp_parse_args( $args, array(
		'form_format' => '<form class="cmb-form" method="post" id="%1$s" enctype="multipart/form-data" encoding="multipart/form-data"><input type="hidden" name="object_id" value="%2$s">%3$s<input type="submit" name="submit-cmb" value="%4$s" class="button-primary"></form>',
		'save_button' => __( 'Save', 'cmb2' ),
		'object_type' => $cmb->mb_object_type(),
		'cmb_styles'  => $cmb->prop( 'cmb_styles' ),
		'enqueue_js'  => $cmb->prop( 'enqueue_js' ),
	) );

	// Set object type explicitly (rather than trying to guess from context)
	$cmb->object_type( $args['object_type'] );

	// Save the metabox if it's been submitted
	// check permissions
	// @todo more hardening?
	if (
		$cmb->prop( 'save_fields' )
		// check nonce
		&& isset( $_POST['submit-cmb'], $_POST['object_id'], $_POST[ $cmb->nonce() ] )
		&& wp_verify_nonce( $_POST[ $cmb->nonce() ], $cmb->nonce() )
		&& $object_id && $_POST['object_id'] == $object_id
	) {
		$cmb->save_fields( $object_id, $cmb->object_type(), $_POST );
	}

	// Enqueue JS/CSS
	if ( $args['cmb_styles'] ) {
		CMB2_hookup::enqueue_cmb_css();
	}

	if ( $args['enqueue_js'] ) {
		CMB2_hookup::enqueue_cmb_js();
	}

	$form_format = apply_filters( 'cmb2_get_metabox_form_format', $args['form_format'], $object_id, $cmb );

	$format_parts = explode( '%3$s', $form_format );

	// Show cmb form
	printf( $format_parts[0], $cmb->cmb_id, $object_id );
	$cmb->show_form();

	if ( isset( $format_parts[1] ) && $format_parts[1] ) {
		printf( str_ireplace( '%4$s', '%1$s', $format_parts[1] ), $args['save_button'] );
	}

}

/**
 * Display a metabox form (or optionally return it) & save it on submission
 * @since  1.0.0
 * @param  mixed   $meta_box  Metabox config array or Metabox ID
 * @param  int     $object_id Object ID
 * @param  array   $args      Optional arguments array
 */
function cmb2_metabox_form( $meta_box, $object_id = 0, $args = array() ) {
	if ( ! isset( $args['echo'] ) || $args['echo'] ) {
		cmb2_print_metabox_form( $meta_box, $object_id, $args );
	} else {
		return cmb2_get_metabox_form( $meta_box, $object_id, $args );
	}
}

/**
 * Returns options markup for a state select field.
 * @param  mixed $value Selected/saved state
 * @return string       html string containing all state options
 * github.com/WebDevStudios/CMB2/wiki/Adding-your-own-field-types#example-4-multiple-inputs-one-field-lets-create-an-address-field
 */
function cmb2_get_state_options( $value = false ) {
    $state_list = array( 'AL'=>'Alabama','AK'=>'Alaska','AZ'=>'Arizona','AR'=>'Arkansas','CA'=>'California','CO'=>'Colorado','CT'=>'Connecticut','DE'=>'Delaware','DC'=>'District Of Columbia','FL'=>'Florida','GA'=>'Georgia','HI'=>'Hawaii','ID'=>'Idaho','IL'=>'Illinois','IN'=>'Indiana','IA'=>'Iowa','KS'=>'Kansas','KY'=>'Kentucky','LA'=>'Louisiana','ME'=>'Maine','MD'=>'Maryland','MA'=>'Massachusetts','MI'=>'Michigan','MN'=>'Minnesota','MS'=>'Mississippi','MO'=>'Missouri','MT'=>'Montana','NE'=>'Nebraska','NV'=>'Nevada','NH'=>'New Hampshire','NJ'=>'New Jersey','NM'=>'New Mexico','NY'=>'New York','NC'=>'North Carolina','ND'=>'North Dakota','OH'=>'Ohio','OK'=>'Oklahoma','OR'=>'Oregon','PA'=>'Pennsylvania','RI'=>'Rhode Island','SC'=>'South Carolina','SD'=>'South Dakota','TN'=>'Tennessee','TX'=>'Texas','UT'=>'Utah','VT'=>'Vermont','VA'=>'Virginia','WA'=>'Washington','WV'=>'West Virginia','WI'=>'Wisconsin','WY'=>'Wyoming' );

    $state_options = '';
    foreach ( $state_list as $abrev => $state ) {
        $state_options .= '<option value="'. $abrev .'" '. selected( $value, $abrev, false ) .'>'. $state .'</option>';
    }

    return $state_options;
}

/**
 * Render Address Field
 */
function cmb2_render_address_field_callback( $field, $value, $object_id, $object_type, $field_type ) {

    // make sure we specify each part of the value we need.
    $value = wp_parse_args( $value, array(
        'address-1' => '',
        'address-2' => '',
        'city'      => '',
		'province' => '',
		'postal' => '',
		'country' => '',
		'google' => '',
		'lat' => '',
		'long' => '',
		'pin' => ''
    ) );

    ?>
    <div>
		<p>
			<label for="<?php echo $field_type->_id( '_address_1' ); ?>">Address 1</label>
		</p>
        <?php echo $field_type->input( array(
            'name'  => $field_type->_name( '[address-1]' ),
            'id'    => $field_type->_id( '_address_1' ),
            'value' => $value['address-1'],
            'desc'  => '',
        ) ); ?>
    </div>

    <div>
		<p>
			<label for="<?php echo $field_type->_id( '_address_2' ); ?>'">Address 2</label>
		</p>
        <?php echo $field_type->input( array(
            'name'  => $field_type->_name( '[address-2]' ),
            'id'    => $field_type->_id( '_address_2' ),
            'value' => $value['address-2'],
            'desc'  => '',
        ) ); ?>
    </div>

    <div class="alignleft">
		<p>
			<label for="<?php echo $field_type->_id( '_city' ); ?>'">City</label>
		</p>
        <?php echo $field_type->input( array(
            'class' => 'cmb_text_small',
            'name'  => $field_type->_name( '[city]' ),
            'id'    => $field_type->_id( '_city' ),
            'value' => $value['city'],
            'desc'  => '',
        ) ); ?>
    </div>

	<div class="alignleft">
		<p>
			<label for="<?php echo $field_type->_id( '_province' ); ?>'">Province</label>
		</p>
        <?php echo $field_type->input( array(
            'class' => 'cmb_text_small',
			'name'    => $field_type->_name( '[province]' ),
            'id'      => $field_type->_id( '_province' ),
            // 'options' => cmb2_get_state_options( $value['state'] ),
			'value' => $value['province'],
            'desc'    => '',
        ) ); ?>
    </div>

	<div class="alignleft">
		<p>
			<label for="<?php echo $field_type->_id( '_country' ); ?>'">Country</label>
		</p>
        <?php echo $field_type->input( array(
            'class' => 'cmb_text_small',
            'name'  => $field_type->_name( '[country]' ),
            'id'    => $field_type->_id( '_country' ),
            'value' => $value['country'],
            'desc'  => '',
        ) ); ?>
    </div>

	<br class="clear">

	<div>
		<p>
			<label for="<?php echo $field_type->_id( '_postal' ); ?>'">Postal Code</label>
		</p>
        <?php echo $field_type->input( array(
            'name'  => $field_type->_name( '[postal]' ),
            'id'    => $field_type->_id( '_postal' ),
            'value' => $value['postal'],
            'desc'  => '',
        ) ); ?>
    </div>

    <br class="clear">

	<div>
		<p>
			<label for="<?php echo $field_type->_id( '_google' ); ?>'">Google Map URL</label>
		</p>
        <?php echo $field_type->input( array(
            'name'  => $field_type->_name( '[google]' ),
            'id'    => $field_type->_id( '_google' ),
            'value' => $value['google'],
            'desc'  => '',
        ) ); ?>
    </div>

    <br class="clear">

	<div class="alignleft">
		<p>
			<label for="<?php echo $field_type->_id( '_lat' ); ?>'">Latitude</label>
		</p>
        <?php echo $field_type->input( array(
            'class' => 'cmb_text_small',
			'name'    => $field_type->_name( '[lat]' ),
            'id'      => $field_type->_id( '_lat' ),
			'value' => $value['lat'],
            'desc'    => '',
        ) ); ?>
    </div>

	<div class="alignleft">
		<p>
			<label for="<?php echo $field_type->_id( '_long' ); ?>'">Longitude</label>
		</p>
        <?php echo $field_type->input( array(
            'class' => 'cmb_text_small',
			'name'    => $field_type->_name( '[long]' ),
            'id'      => $field_type->_id( '_long' ),
			'value' => $value['long'],
            'desc'    => '',
        ) ); ?>
    </div>

	<br class="clear">

	<div>
		<p>
			<label for="<?php echo $field_type->_id( '_pin' ); ?>'">Pin</label>
		</p>
	    <?php echo $field_type->input( array(
	        'class' => 'cmb_text_small',
			'name'    => $field_type->_name( '[pin]' ),
	        'id'      => $field_type->_id( '_pin' ),
			'value' => $value['pin'],
	    ) ); ?>
	</div>

    <?php
    echo $field_type->_desc( true );

}
add_filter( 'cmb2_render_address', 'cmb2_render_address_field_callback', 10, 5 );
