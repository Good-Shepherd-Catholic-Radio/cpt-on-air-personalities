<?php
/**
 * Provides helper functions.
 *
 * @since	  1.0.0
 *
 * @package	GSCR_CPT_On_Air_Personalities
 * @subpackage GSCR_CPT_On_Air_Personalities/core
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Returns the main plugin object
 *
 * @since		1.0.0
 *
 * @return		GSCR_CPT_On_Air_Personalities
 */
function GSCRCPTONAIRPERSONALITIES() {
	return GSCR_CPT_On_Air_Personalities::instance();
}