<?php
/**
 * @package 1x
 * @since 1.0.0
 */


/**
 * Admin head
 *
 * @return void
 */
function jwtpbm_admin_head() {
	// echo "admin_head_test";
}
add_action( 'admin_head', 'jwtpbm_admin_head' );


/**
 * Frontend head
 * @return void
 */
function jwtpbm_wp_head() {
	// echo "wp_head_test";
}
add_action( 'wp_head', 'jwtpbm_wp_head' );
