<?php

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Events_Management_by_Dawsun
 * @subpackage Events_Management_by_Dawsun/includes
 * @author     Dev Team <support@eventplugun.com>
 */
class Events_Management_by_Dawsun_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		// Saved Page Arguments

		ob_start();

		global $wpdb, $wp_rewrite;

		//$wp_rewrite->flush_rules( true );

		flush_rewrite_rules();

		$saved_page_args = array(
			'post_title'   => __( 'Upcoming Events', 'event-plugun' ),
			'post_content' => '[plugun_events]',
			'post_status'  => 'publish',
			'post_type'    => 'page'
		);
		// Insert the page and get its id.
		$saved_page_id = wp_insert_post( $saved_page_args );
              
                
                $checkin_args = array(
			'post_title'   => __( 'Successful checkin', 'event-plugun' ),
			'post_content' => '<h3 class="text-center">Event Details</h3>
<table width="100%" >
	<tr>
	    <th style="border: 1px solid #ddd; padding: 5px;">Booking Name</th>
	    <td style="border: 1px solid #ddd; padding: 5px;">{{booking_name}}</td>
	</tr>
	<tr>
	    <th style="border: 1px solid #ddd; padding: 5px;">Ticket Name</th>
	    <td style="border: 1px solid #ddd; padding: 5px;">{{ticket_name}}</td>
	</tr>
        <tr>
 	    <th style="border: 1px solid #ddd; padding: 5px;">Ticket ID</th>
            <td style="border: 1px solid #ddd; padding: 5px;">{{ticket_id}}</td>
	</tr>
	<tr>
	    <th style="border: 1px solid #ddd; padding: 5px;">Checkin Log</th>
	    <td>{{checkin_log}}</td>
        </tr>
</table>',
			'post_status'  => 'publish',
			'post_type'    => 'plugun-template'
		);
		// Insert the page and get its id.
		$checkin_template_id = wp_insert_post( $checkin_args );
             update_post_meta($checkin_template_id, "event_plugun_template_subject", 'You have successfully checkedin at {{event_name}}');   
             update_option("event_plugun_template_successfully_checkedin", $checkin_template_id);
                
                                $booking_args = array(
			'post_title'   => __( 'Successful booking', 'event-plugun' ),
			'post_content' => 'You have successfully booked tickets for {{event_name}}
						<table width="100%">
						<tr>
						<td style="border: 1px solid #ddd; padding: 5px;" width="30%">Event Name: </td>
						<td style="border: 1px solid #ddd; padding: 5px;">{{event_name}}</td>   
						</tr>
						<tr>
						<td style="border: 1px solid #ddd; padding: 5px;" width="30%">Event Address: </td>
						<td style="border: 1px solid #ddd; padding: 5px;">{{event_address}}</td>   
						</tr>
						<tr>
						<td style="border: 1px solid #ddd; padding: 5px;" width="30%">Event Dates: </td>
						<td style="border: 1px solid #ddd; padding: 5px;">Start Date: {{event_date_start}} - End Date: {{event_date_end}}</td>   
						</tr>
						<tr>
						<td style="border: 1px solid #ddd; padding: 5px;" width="30%">Ticket List: </td>
						<td style="border: 1px solid #ddd; padding: 5px;">{{ticket_list_info}}</td>   
						</tr>
						<tr>
						<td style="border: 1px solid #ddd; padding: 5px;" width="30%">Booking Amount: </td>
						<td style="border: 1px solid #ddd; padding: 5px;">{{booking_amount}}</td>   
						</tr>
						</table>',
			'post_status'  => 'publish',
			'post_type'    => 'plugun-template'
		);
		// Insert the page and get its id.
		$booking_template_id = wp_insert_post( $booking_args );
		update_post_meta($booking_template_id, "event_plugun_template_subject", 'You have successfully booked tickets for {{event_name}}');
                
                update_option("event_plugun_template_booking_done", $booking_template_id);
                
                $booking_status_args = array(
			'post_title'   => __( 'Booking status changed', 'event-plugun' ),
			'post_content' => 'Your booking for {{event_name}} is {{booking_status}} 
					<table width="100%">
						<tr>
						<td style="border: 1px solid #ddd; padding: 5px;" width="30%">Event Name: </td>
						<td style="border: 1px solid #ddd; padding: 5px;">{{event_name}}</td>   
						</tr>
						<tr>
						<td style="border: 1px solid #ddd; padding: 5px;" width="30%">Event Address: </td>
						<td style="border: 1px solid #ddd; padding: 5px;">{{event_address}}</td>   
						</tr>
						<tr>
						<td style="border: 1px solid #ddd; padding: 5px;" width="30%">Event Dates: </td>
						<td style="border: 1px solid #ddd; padding: 5px;">Start Date: {{event_date_start}} - End Date: {{event_date_end}}</td>   
						</tr>
						<tr>
						<td style="border: 1px solid #ddd; padding: 5px;" width="30%">Ticket List: </td>
						<td style="border: 1px solid #ddd; padding: 5px;">{{ticket_list_info}}</td>   
						</tr>
						<tr>
						<td style="border: 1px solid #ddd; padding: 5px;" width="30%">Booking Amount: </td>
						<td style="border: 1px solid #ddd; padding: 5px;">{{booking_amount}}</td>   
						</tr>
						</table>',
			'post_status'  => 'publish',
			'post_type'    => 'plugun-template'
		);
		// Insert the page and get its id.
		$booking_status_template_id = wp_insert_post( $booking_status_args );
update_post_meta($booking_status_template_id, "event_plugun_template_subject", 'Your booking status is changed for {{event_name}}');
		update_option('event_plugun_template_booking_status_changed', $booking_status_template_id);
		

		update_option("event_plugun_template_logo", DEP_LITE_PLUGIN_URL . "/public/images/event-pluign.png");

		update_option("EVENT_PLUGUN_LICENSE_FREE", 1);
		update_option("EVENT_PLUGUN_LICENSE_PRO", 0);
		update_option("EVENT_PLUGUN_LICENSE", "FREE");
                
		$message_page_args = array(
			'post_title'   => __( 'Message', 'event-plugun' ),
			'post_name' => 'event-plugun-message',
			'post_status'  => 'publish',
			'post_type'    => 'page'
		);
		// Insert the page and get its id.
		$message_page_id = wp_insert_post( $message_page_args );
		// Save page id to the database.
		add_option( 'event_plugun_saved_page_id', $saved_page_id );
		add_option( 'event_plugun_message_page_id', $message_page_id );		

		$charset_collate = $wpdb->get_charset_collate();


		$query = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}plugun_checkin(
					hash_key varchar(128) NOT NULL, 
					booking_id int(11) NOT NULL, 
					ticket_id int(11) NOT NULL, 
					quantity int(11) NOT NULL DEFAULT 1,
					checkins text NOT NULL, 
					qr_code blob NOT NULL,
					PRIMARY KEY  (hash_key)
				) {$charset_collate};";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $query );

		if($wpdb->last_error !== ''){
    		//$wpdb->print_error();
			file_put_contents(__DIR__ . "/activation_db_error.log", $wpdb->last_error);
		}

		do_action("event_plugun_activated", "lite");

		$error_log = ob_get_contents();

		ob_clean();

		file_put_contents(__DIR__ . '/error.log', $error_log);

	}

}
