<?php

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Events_Management_by_Dawsun
 * @subpackage Events_Management_by_Dawsun/includes
 * @author     Dev Team <support@eventplugun.com>
 */
 
class DepLite_Plugin_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'event-plugun',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
