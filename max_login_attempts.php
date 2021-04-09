<?php
/*
Plugin Name: Max Login Attempts
Plugin URI: http://azim.pw/
Description: A plugin to block ip after specific time of failed login attempts
Version: 1.0.0
Author: Azim
Author URI: http://azim.pw/
*/

require('max_login_attempts_db_work.php');

register_activation_hook( __FILE__, 'mla_install' );
register_deactivation_hook( __FILE__, 'mla_uninstall' );



add_filter('login_errors', 'filter_login_errors');
add_filter('wp_authenticate_user', 'on_authenticate_user', 10, 2);

function filter_login_errors( $error ) {
    if (strpos($error, 'field is empty.') !== false) 
        return $error;
    
    $max_try = 4;
    $ip = get_the_user_ip();

    mla_insert_data($ip);

    $fail_count = mla_get_fail_count($ip);
    
    if($fail_count >= $max_try)
        return 'Your ip is blocked';

    return $error.'<br> You have '.($max_try - $fail_count).' remaining attempts';	
}
 
function on_authenticate_user ($user, $password) {
    $max_try = 4;
    $ip = get_the_user_ip();
    $fail_count = mla_get_fail_count($ip);

    if($fail_count >= $max_try) 
        return new WP_Error('ip_blocked', 'Your ip is blocked');
    
     return $user;
}


add_action('wp_login', 'on_user_login', 10, 2);

function on_user_login( $user_login, $user ) {
    $ip = get_the_user_ip();
    mla_reset_fail_count($ip);    
}



// add_action( 'admin_menu', 'register_login_attempts_menu_page' );
// add_action( 'admin_enqueue_scripts', 'load_login_attempts_css_js' );

// function register_login_attempts_menu_page() {
//     add_menu_page(
//         __( 'Login Attempts', 'textdomain' ),
//         'Login attempts',
//         'manage_options',
//         'login_attempts/login_attempts_admin.php',
//         '',
//         plugins_url( 'login_attempts/images/menu.png' )
//     );
// }

// function load_login_attempts_css_js( $hook ) {

//     if ( 'login_attempts_admin.php' != $hook ) {
//         return;
//     }

//     wp_enqueue_style('login_attempts_css', plugins_url('login_attempts/css/la_custom.css',__FILE__));
// }






function get_the_user_ip() {

    if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
    
    //check ip from share internet
    
    $ip = $_SERVER['HTTP_CLIENT_IP'];
    
    } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
    
    //to check ip is pass from proxy
    
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    
    } else {
    
    $ip = $_SERVER['REMOTE_ADDR'];
    
    }
    
    return apply_filters( 'wpb_get_ip', $ip );
    
    }

?>