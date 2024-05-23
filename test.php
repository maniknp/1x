<?php
/**
 * This file contains the definition of the sample_function.
 *
 * @package root 
 * @version 1.0
 */

/**
 * Sample function demonstrating deprecated and new PHP functions.
 *
 * @return mixed The result of the example operations.
 */
function sample_function() {
	// Replacing the deprecated create_function with an anonymous function.
	$sum_function = function ( $a, $b ) {
		return $a + $b;
	};

	// Using the anonymous function.
	$result = $sum_function( 1, 2 );

	// Example of using a function available in PHP 7.3 and later.
	$array  = array( 1, 2, 3, 4 );
	$result = array_key_first( $array );

	return $result;
}
