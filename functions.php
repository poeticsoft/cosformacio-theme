<?php

//Error reporting
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);

//Define constants
define('SITE_URL', 			home_url().'/');
define('AJAX_URL', 			admin_url('admin-ajax.php'));
define('THEME_PATH', 		get_template_directory().'/');
define('CHILD_PATH', 		get_stylesheet_directory().'/');
define('THEME_URI', 		get_template_directory_uri().'/');
define('CHILD_URI', 		get_stylesheet_directory_uri().'/');
define('THEMEX_PATH', 	THEME_PATH.'framework/');
define('THEMEX_URI', 		THEME_URI.'framework/');
define('THEMEX_PREFIX', 'themex_');

//Set content width
$content_width=1140;

//Load language files
load_theme_textdomain('academy', THEME_PATH.'languages');

//Include theme functions
include(THEMEX_PATH.'functions.php');

//Include configuration
include(THEMEX_PATH.'config.php');

//Include core class
include(THEMEX_PATH.'classes/themex.core.php');

//Create theme instance
$themex=new ThemexCore($config);

/* Login custom styles */  

function poeticsoft_cos_login_stylesheet() {

	wp_enqueue_style(
		'poeticsoft_cos_login_style',
		THEME_URI . '/style-login.css',
		array(),
		filemtime(THEME_PATH .'/style-login.css'),
		'all'
	);
}
add_action('login_enqueue_scripts', 'poeticsoft_cos_login_stylesheet');

/* Admin custom styles */

function poeticsoft_cos_admin_scripts($hook) {

	wp_enqueue_style(
		'poeticsoft_cos_admin_style', 
		THEME_URI . '/style-admin.css', 
		array(),
		filemtime(THEME_PATH .'/style-admin.css'),
		'all'
	);
}

add_action('admin_enqueue_scripts', 'poeticsoft_cos_admin_scripts');

/**
 * Shortcodes
 */

/* For slider */

function poeticsoft_cos_slider_image_handler($atts, $content, $tag) {

	$ImageTag = trim($content);
	$SrcIndexStart = strpos($ImageTag, 'src="');
	$SrcIndexEnd = strpos($ImageTag, '"', $SrcIndexStart + 5);
	$ImgSrc = substr($ImageTag, $SrcIndexStart + 5, $SrcIndexEnd - $SrcIndexStart - 4);

	return '<div class="CosSliderImage" ' .
						'style="background-image: url(' . $ImgSrc . ')">' .
					'</div>';
} 

add_shortcode('cos_slider_image', 'poeticsoft_cos_slider_image_handler');

function poeticsoft_cos_slider_textos_handler($atts, $content, $tag) {

	return '<div class="CosSliderText">' .
						do_shortcode(trim($content)) .
				'</div>';
}

add_shortcode('cos_slider_text', 'poeticsoft_cos_slider_textos_handler');

/* Role Names */

// Change default role names
function poeticsoft_cos_change_role_name() {

	global $wp_roles;

	if (!isset($wp_roles)) {
		
			$wp_roles = new WP_Roles();
	}

	$wp_roles->roles['customer']['name'] = 'No Soci';
	$wp_roles->role_names['customer'] = 'No Soci';  

	$wp_roles->roles['subscriber']['name'] = 'Soci';
	$wp_roles->role_names['subscriber'] = 'Soci';           
}
add_action('init', 'poeticsoft_cos_change_role_name');

 
function poeticsoft_cos_update_order_status( $order_status, $order_id ) {
 
  $order = new WC_Order( $order_id );

  if (
    'processing' == $order_status && 
    (
      'on-hold' == $order->status || 
      'pending' == $order->status
    ) 
  ) {

    return 'completed';
  }

  return $order_status;
}

add_filter(
  'woocommerce_payment_complete_order_status',
  'poeticsoft_cos_update_order_status',
  10,
  2
);
