<?php

if ( ! defined( 'WPINC' ) ) {
	die;
}



/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Events_Management_By_Dawsun
 * @subpackage Events_Management_By_Dawsun/includes
 * @author     Dev Team <support@eventplugun.com>
 */
class Events_Management_by_Dawsun_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */

	
	public static function deactivate() {

		// Get Saved page id.
		$saved_page_id = get_option( 'event_plugun_saved_page_id' );


		$checkin_template_id = get_option( 'event_plugun_template_successfully_checkedin' );
		$booking_template_id = get_option( 'event_plugun_template_booking_done' );
		$booking_status_template_id = get_option( 'event_plugun_template_booking_status_changed' );


		// Check if the saved page id exists.
		if ( $saved_page_id ) {

			// Delete saved page.
			wp_delete_post( $saved_page_id, true );

			// Delete saved page id record in the database.
			delete_option( 'event_plugun_saved_page_id' );

		}

		if($checkin_template_id){
			wp_delete_post( $checkin_template_id, true );
			delete_option( 'event_plugun_template_successfully_checkedin' );
		}

		if($booking_template_id){
			wp_delete_post( $booking_template_id, true );
			delete_option( 'event_plugun_template_successfully_checkedin' );
		}

		if($booking_status_template_id){
			wp_delete_post( $booking_status_template_id, true );
			delete_option( 'event_plugun_template_booking_status_changed' );
		}

		$message_page_id = get_option( 'event_plugun_message_page_id' );

		// Check if the saved page id exists.
		if ( $message_page_id ) {

			// Delete saved page.
			wp_delete_post( $message_page_id, true );

			// Delete saved page id record in the database.
			delete_option( 'event_plugun_message_page_id' );

		}

	}

}
