<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

register_activation_hook(__FILE__, 'dep_lite_set_reminder');

function dep_lite_set_reminder() {
    if (! wp_next_scheduled ( 'dep_lite_send_reminder' )) {
	    wp_schedule_event(time(), 'hourly', 'dep_lite_send_reminder');
    }
}

add_action('dep_lite_send_reminder', 'remind_users');

function dep_lite_remind_users() {
    global $wpdb;

    $notifications = array();
    $oEmail = new Events_Management_by_Dawsun_Email();

    $query = "SELECT bookings.ID as booking_id, upcomming_events.meta_value, events.ID, events.post_title, user.meta_value as user_id 
                        FROM {$wpdb->prefix}posts bookings 
                INNER JOIN {$wpdb->prefix}postmeta valid_booking ON bookings.ID = valid_booking.post_id AND valid_booking.meta_key = 'booking_status'
                INNER JOIN {$wpdb->prefix}postmeta upcomming_events ON bookings.ID = upcomming_events.post_id AND upcomming_events.meta_key = 'event_id'
                INNER JOIN {$wpdb->prefix}posts events ON upcomming_events.meta_value = events.ID 
                INNER JOIN {$wpdb->prefix}postmeta user ON bookings.ID = user.post_id AND user.meta_key = 'user_id'
                
            WHERE bookings.post_type = 'plugun-booking' AND valid_booking.meta_value IN('paid', 'approved') ";

            //INNER JOIN {$wpdb->prefix}users user_detail ON user.meta_value = user_detail.ID

    $upcomming_events = $wpdb->get_results($query);        

    for($i=0, $count = count($upcomming_events); $i < $count; $i++){

        $event_date_start = get_post_meta($upcomming_events[$i]->ID, "event_date_start", true);
        $q = "SELECT DISTINCT(ticket_id) FROM `{$wpdb->prefix}plugun_checkin`";
        $tickets = $wpdb->get_col($q);
        $datediff = $wpdb->get_var("SELECT DATEDIFF('{$event_date_start}', NOW())");

        if(in_array($datediff, array(1,3))){

            $qu = "SELECT ID, post_title as ticket_name, checkin_date.meta_value as checkin_date, checkin_time.meta_value as checkin_time 
                    FROM {$wpdb->prefix}posts tickets 
                    LEFT JOIN {$wpdb->prefix}postmeta checkin_date ON tickets.ID = checkin_date.post_id AND checkin_date.meta_key = 'dates_for_checkin_from' 
                    LEFT JOIN {$wpdb->prefix}postmeta checkin_time ON tickets.ID = checkin_time.post_id AND checkin_time.meta_key = 'times_for_checkin_from' 
                    WHERE tickets.post_type = 'plugun-ticket' AND tickets.ID IN('".implode("', '", $tickets)."')
                    ";
            $tickets_info = $wpdb->get_results($qu); 

            $notifications[] = array(
                "event_name" => ucwords($upcomming_events[$i]->post_title),
                "tickets_info" => $tickets_info,
                "time_left" => $datediff . " Day(s)"               
            );

            $reminder_template_id = get_option("event_plugun_template_reminder");            

            $template = get_post($reminder_template_id);

          //  echo '<pre>'. print_r($template, true); echo '</pre>'; exit;

            $email = new Events_Management_by_Dawsun_Email();

            $email->receiver = $upcomming_events[$i]->user_id;             
            
            $email->subject = get_post_meta($template->ID, "event_plugun_template_subject", true);  //$subject ;	

            $email->template = array(
                "notification_data"=> $notifications,
                "template"=> "reminder.phtml",                                      
            ); 

            $email->send();

        }
       
    }

}



