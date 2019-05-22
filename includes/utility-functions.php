<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_action("init", function (){
	flush_rewrite_rules();
});


function deplite_scheduled_to_publish(){

	$tickets = get_posts(array(
		"post_type" => "plugun-ticket",
		"post_status" => "future"
	));

    $date = date("Y-m-d"); 
	for($i=0, $count = count($tickets); $i < $count; $i++){
		wp_update_post(array(
			'ID'    =>  $tickets[$i]->ID,
			'post_status'   =>  'publish',
			'post_date_gmt' => $date,
			'post_date' => $date
		));

		$ticket = get_post($tickets[$i]->ID);


	}
}


add_action("wp", "deplite_scheduled_to_publish");
add_action("admin_head", "deplite_scheduled_to_publish");

if(@$_GET["time"]){
	echo date("F d, Y H:i:s");	exit;
}

add_action("event_plugun_booking_status_changed", function ($booking_id, $booking_status){      

              global $wpdb;
              if(in_array($booking_status, array("paid", "approved"))){

				$booking = get_post($booking_id);  
				$event_id = get_post_meta($booking_id, "event_id", true);
				$event = get_post($event_id);
				
				require_once(DEP_LITE_PLUGIN_PATH . "/includes/fpdf/orientation.php");
                                
                                
                $ticket_path = DEP_LITE_PLUGIN_PATH . "/tickets/";
                $query = "SELECT * FROM {$wpdb->prefix}plugun_checkin WHERE booking_id = '{$booking_id}'";
                $qr_images = $wpdb->get_results($query);
				$images_path = array();                
                for($i=0, $count = count($qr_images); $i < $count; $i++){
                    
					$ticket = get_post($qr_images[$i]->ticket_id);
                    $images_path[] = $image_path = $file_path = $ticket_path . "/" . $qr_images[$i]->hash_key . ".png" ;
                    
                    $event_id = get_post_meta($qr_images[$i]->ticket_id, "ticket_event", true);
                    file_put_contents($file_path, $qr_images[$i]->qr_code);
                    $event = get_post($event_id);
                   
					 
                    $file_path = $ticket_path . "/" . $qr_images[$i]->hash_key . ".pdf" ;
                            $event_name =  $event->post_title; //  event name
                            $ticket_price =  get_post_meta($qr_images[$i]->ticket_id, "ticket_price", true) ." /Rs";
                            $date = get_post_meta($event_id, "event_date_start", true);
                            $start_time = get_post_meta($event_id, "event_time_start", true);
                            $end_time = get_post_meta($event_id, "event_time_end", true);
                            
                            $ticket_id = $qr_images[$i]->ticket_id;
                            $booking_id = $qr_images[$i]->booking_id;
                            $attendee = get_post_meta($booking_id, "name", true); // booking person name
                            $website = site_url();
                            $venue = substr(get_post_meta($event_id, "event_location", true) . ", "
                                    . get_post_meta($event_id, "event_city", true) . ", "
                                    . get_post_meta($event_id, "event_state", true) . ", "
                                    . get_post_meta($event_id, "event_country", true) . ". ",0,30);
							$ticket_name = $ticket->post_title;
                                  
                           
                            
$html = '
							
                            
							
                            
                           
                            '.$date.'  '.$start_time.' - '.$end_time.'
                            '.$venue.'
                            Ticket # :'.$ticket_id.'  -  Booking #: '.$booking_id.'
							                       '.$ticket_name.'
                            '.$website
							; 
							
							$image = $image_path;
                            $pdf=new PDF();
                            $pdf->AddPage('L', array(200, 100));
                            //$pdf->SetTextColor(100, 20, 100);
                            $pdf->Image($image,157,20,30,0,'','http://www.fpdf.org');
                            $pdf->SetFont('Arial','',20);
                            $pdf->RotatedText(40,60,'TICKET',90);
                            $pdf->SetFillColor('RED');
                            $pdf->SetXY(20,16);
                            $pdf->SetFont('Arial','',12);
                            $pdf->drawTextBox($html, 172, 60, 'L', 'T');
                            $pdf->SetFont('Arial','',20);
                            $pdf->RotatedText(54,30,$event_name,0);
                            $pdf->SetFont('Arial','B',12	);
                            $pdf->RotatedText(54,38,$attendee,0);
                     
                            $pdf->Output($file_path, "F");
                            $attachments[] = $file_path; 
                }
              }  
                

                if(!$booking_status) $booking_status = get_post_meta($booking_id, "booking_status", true);
                
                $email = new Events_Management_by_Dawsun_Email();

                $email->receiver =  get_post_meta($booking_id, "user_id", true);

                if(in_array($booking_status, array("paid", "approved"))) $email->attachments = $attachments;

				$template = get_post(get_option("event_plugun_template_booking_status_changed"));

				$subject = get_post_meta($template->ID, "event_plugun_template_subject", true);

				$subject = str_replace("{{event_name}}", $event->post_title, $subject);

                $email->subject = $subject ;				

                $email->template = array(
                    "booking_id"=> $booking->ID,
                    "template"=> "booking-status.phtml",                    
                  
                );   

                add_filter("event_plugun_email_sender", function($sender){
                    return array(
                        "user_email" => "event_plugun@dawsun.xyz",
                        "display_name" => " Event Plugun "
                    );
                });

                $email->send(); 

				for($i=0, $count = count($images_path); $i < $count; $i++){
					@unlink($images_path[$i]);
				}	
				
               
        }, 10, 2);






function get_dep_lite_ticket_available_quantity($ticket_id, $booking_id = NULL){

	$total_quantity = get_post_meta($ticket_id, "quantity", true);


	if(!($total_quantity >= 0)){
		return -1;
	}

	$used_tickets = get_deplite_ticket_used_quantity($ticket_id, $booking_id);


	return ($total_quantity - $used_tickets);


}

function get_deplite_ticket_used_quantity($ticket_id, $booking_id = NULL){

	global $wpdb;	

	$query = "SELECT meta_value, posts.ID FROM {$wpdb->prefix}postmeta post_meta
			LEFT JOIN {$wpdb->prefix}posts posts ON posts.ID = post_meta.post_id
			WHERE post_meta.meta_key = 'tickets' AND posts.post_status != 'trash' ";

	if($booking_id){
		$query .=  " AND posts.ID != '{$booking_id}' ";
	}	

	$result = $wpdb->get_results($query); 

	$used_tickets = 0;

	for($i=0, $count = count($result); $i < $count; $i++){

		if(isset($event_id)){
			if($event_id == get_post_meta($result[$i]->ID, "event_id", $event_id)){
				continue;
			} 
		}

		$tickets = unserialize($result[$i]->meta_value);
		//echo '<pre>'; print_r($tickets); echo '</pre>';
		if(isset($tickets[$ticket_id])){
			$used_tickets += (int) $tickets[$ticket_id];
		}
		
	}

	return $used_tickets;
}

function get_deplite_available_tickets($event_id){

	global $wpdb;

	$query = "SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_value = '{$event_id}' AND meta_key = 'ticket_event'";
	
	$tickets = $wpdb->get_results($query);

	$tickets_available = array();

	$ticket_info = array();

	for($i=0, $count = count($tickets); $i < $count; $i++){

		$ticket_availability = get_post_meta($tickets[$i]->post_id, "ticket_availability", true);

		if($ticket_availability == 0 && $ticket_availability != ""){
			    
				$event_id = get_post_meta($tickets[$i]->post_id, "ticket_event", true);

				$event_checkin_date_from = get_post_meta($event_id, "event_date_start", true);

				if($event_checkin_date_from){
					$event_checkin_time_from = get_post_meta($event_id, "event_time_start", true);
					if($event_checkin_time_from){
						preg_match("#(\d+):(\d+)#",$event_checkin_time_from,  $matches);
						$temp_time = $matches;
						if(strstr($event_checkin_time_from, "PM")) $temp_time[1] += 12; 
						$event_checkin_time_from = $temp_time[1] . ':' . $temp_time[2] . ":00";
						$event_checkin_date_from .= " " . $event_checkin_time_from;
					} 
					$checkin_time_start = strtotime($event_checkin_date_from) ;
					//echo date("Y-m-d H:i:s", $time) . '>' . $event_checkin_date_from . '<br>'; exit;
					if(time() <  $checkin_time_start ) continue;
				} 

				$event_checkin_date_to = get_post_meta($event_id, "event_date_end", true);

				if($event_checkin_date_to){
					$event_checkin_time_to = get_post_meta($event_id, "event_time_end", true);
					//if($event_checkin_time_to) $event_checkin_date_to .= " " . $event_checkin_time_to;
					if($event_checkin_time_to){
						preg_match("#(\d+):(\d+)#",$event_checkin_time_to,  $matches);
						$temp_time = $matches;
						if(strstr($event_checkin_time_to, "PM")) $temp_time[1] += 12; 
						$event_checkin_time_to = $temp_time[1] . ':' . $temp_time[2] . ":00";
						$event_checkin_date_to .= " " . $event_checkin_time_to;
					}
					$checkin_time_end = strtotime($event_checkin_date_to) ;
					if(time() >  $checkin_time_end ) continue;
				} 

				$tickets_available[] = $tickets[$i]->post_id;
				continue;

		}

		$sell_start_from = get_post_meta($tickets[$i]->post_id, "dates_for_selling_from", true);

		$ticket_info[ $tickets[$i]->post_id ]["sell_start_from"] = $sell_start_from;			

		//echo "sell_Starts: " . $sell_start_from;

		if($sell_start_from){
			$sell_start_from_time = get_post_meta($tickets[$i]->post_id, "dates_for_selling_from_time", true);
			if($sell_start_from_time){
				preg_match("#(\d+):(\d+)#",$sell_start_from_time,  $matches);
				$temp_time = $matches;
				if(strstr($sell_start_from_time, "PM")) $temp_time[1] += 12; 
				$sell_start_from_time = $temp_time[1] . ':' . $temp_time[2] . ":00";
				$sell_start_from .= " " . $sell_start_from_time;
			}
			else $sell_start_from .= " 00:00:00";

			if( (int) time() < (int) strtotime($sell_start_from)){

				continue;
				
			}
		}

		$sell_start_to = get_post_meta($tickets[$i]->post_id, "dates_for_selling_to", true);

		$ticket_info[ $tickets[$i]->post_id ]["sell_start_to"] = $sell_start_to;

		if($sell_start_to){
			$sell_start_to_time = get_post_meta($tickets[$i]->post_id, "dates_for_selling_to_time", true);
			if($sell_start_to_time){
				preg_match("#(\d+):(\d+)#",$sell_start_to_time,  $matches);
				$temp_time = $matches;
				if(strstr($sell_start_to_time, "PM")) $temp_time[1] += 12; 
				$sell_start_to_time = $temp_time[1] . ':' . $temp_time[2] . ":00";
				$sell_start_to .= " " . $sell_start_to_time;
			}
			else $sell_start_to .= " 23:59:59" ;
			if( (int) time() > (int) strtotime($sell_start_to)){
				continue;
			}
		}
		else {
			$sell_start_to = get_post_meta($event_id, "event_date_end", true); 
			$sell_start_to .= " 23:59:59" ;
			if(time() > strtotime($sell_start_to)){
				continue;
			}
		}

		$tickets_available[] = $tickets[$i]->post_id;		

	}


	return $tickets_available;


}

function deplite_booking_opening_closing_dates($event_id){

	global $wpdb;

	$query = "SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_value = '{$event_id}' AND meta_key = 'ticket_event'";
	
	$opening_dates = $closing_dates = array();

	$tickets = $wpdb->get_results($query);

	$event = get_post($event_id);

	for($i=0, $count = count($tickets); $i < $count; $i++){
		$start_date = get_post_meta($tickets[$i]->post_id, "dates_for_selling_from", true) . ' ' . get_post_meta($tickets[$i]->post_id, "dates_for_selling_from_time", true) ;
		if(!$start_date){
			$start_date = $event->post_date;//date("Y-m-d");
		}

		$opening_dates[] = strtotime($start_date);

		$end_date = get_post_meta($tickets[$i]->post_id, "dates_for_selling_to", true) . ' ' . get_post_meta($tickets[$i]->post_id, "dates_for_selling_to_time", true) ;
		if(!$end_date){
			$end_date = get_post_meta($event_id, "event_date_end", true);
		}
		$closing_dates[] = strtotime($end_date);
	}

	sort($opening_dates);
	rsort($closing_dates);

	$opening_date = null;
	$closing_date = null;

	if(isset($opening_dates[0])){
		$opening_date = $opening_dates[0];
	}

	if(isset($closing_dates[0])){
		$closing_date = $closing_dates[0];
	}

	return array(
		"booking_open" => $opening_date,
		"booking_close" => $closing_date,
	);

}



function deplite_get_random_text($char_limit = 6){

	$chars = array(0,1,2,3,4,5,6,7,8,9, "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", 
						"r", "s", "t", "u", "v", "w", "x", "y", "z");

	$text = "";

	for($i=0; $i < $char_limit; $i++){
		$index = rand(0, count($chars) - 1);
		$text .= $chars[$index];
	}

	return $text;
}

function get_deplite_new_hash_key( $max_attempts = 40){

	global $wpdb;

	$attempts = 0;

	 do{
		$hash_key = deplite_get_random_text(16);  	
		$query = "SELECT COUNT(*) FROM {$wpdb->prefix}plugun_checkin WHERE hash_key = '{$hash_key}'";
		$exists = $wpdb->get_var($query);		
		$attempts++;
	}while( $exists || $attempts <= $max_attempts );

	return $hash_key;
}


function display_deplite_column_api_status( $column, $post_id ) {
	//echo $column;
    if ($column == 'api_status'){
        $status = array("Inactive", "Active");
		echo $status[ get_post_meta($post_id, "api_status", true) ];
    }
}
add_action( 'manage_posts_custom_column' , 'display_deplite_column_api_status', 10, 2 );

/* Add custom column to post list */
function deplite_add_apistatus_column( $columns, $post_type  ) {
	if($post_type == "plugun_api"){
		$columns["api_status"] =  __( 'API Status', 'your_text_domain' );
	}
    
	return $columns;
}
add_filter( 'manage_posts_columns' , 'deplite_add_apistatus_column', 10, 2 );


function deplite_footer_script(){
	echo '<script>jQuery(document).ready(function() { jQuery("a:empty").remove(); });
	//		wp_deactivate_menus();

        
      //  wp_activate_menu("#menu-pages", "edit.php?post_type=page");
	</script>';
}

add_action('in_admin_footer', 'deplite_footer_script');

function get_deplite_ticket_price($ticket_id){

	$price = (int) get_post_meta($ticket_id, "ticket_price", true);
	$fee_amount = (int) get_post_meta($ticket_id, "ticket_fee", true);	

	if(get_post_meta($ticket_id, "ticket_fee_type", true)){
		$fee_amount = round($fee_amount * $price / 100, 2);
	}

	return $price + $fee_amount;

}

add_action('edit_form_after_title', function() {
    global $post, $wp_meta_boxes;
    do_meta_boxes(get_current_screen(), 'advanced', $post);
    unset($wp_meta_boxes[get_post_type($post)]['advanced']);
});

/*
function remove_publish_box() {
	remove_meta_box( 'submitdiv', 'plugun-booking', 'side' );
}
*/
add_action('admin_footer-edit.php', 'deplite_custom_bulk_admin_footer');
 
function deplite_custom_bulk_admin_footer() {
 
  global $post_type;
 
  if($post_type == 'plugun-booking') {

    $events = get_posts(array(
        "post_type" => "plugun-event" 
    ));
    $dropdown = '<select name="export_event_id" id="export_event_id">';
    for($i=0, $count = count($events); $i < $count; $i++){
        $dropdown .= '<option value="' . $events[$i]->ID . '">Export '.$events[$i]->post_title.'</option>';
    }
    $dropdown .= '</select>';//bulkactions
    ?>
    <script type="text/javascript">
      jQuery(document).ready(function() {
        jQuery('<?php echo $dropdown ?>').appendTo('.bulkactions');
        
        jQuery("select#export_event_id").change(function (){
           window.location.replace("?<?php echo $_SERVER["QUERY_STRING"] ?>&export_event_id=" + jQuery(this).val());     
        });
      });
    </script>
    <?php
  }
}

add_action("admin_init", "deplite_admin_processes");

function deplite_admin_processes(){


    if(@$_REQUEST["export_event_id"]){
        global $wpdb;


        // create a file pointer connected to the output stream

        $column_data = $data = $columns = array();

		$event_id = sanitize_text_field($_REQUEST["export_event_id"]);

        $output = fopen('php://output', 'w');

        $query = "SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = 'event_id' AND meta_value = '{$event_id}'";

        $bookings = $wpdb->get_results($query);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=dep_lite_booking_data.csv');
        

        if(count($bookings) == 0) exit; 


      //  echo '<pre>'; print_r($bookings); echo '</pre>' . $query;

        $booking = get_post($bookings[0]->post_id);

        $columns = array(
            "booking_name" => "Booking Name",
            "booking_phone" => "Booking Phone",
            "booking_amount" => "Booking Total",
            "booking_tickets" => "Tickets",
            "discount" => "Discount",
            "fee" => "Fee",
            "event_name" => "Event Name",
            "event_location" => "Event Location",
            "event_timining" => "Event Timing"  
        );
       
        fputcsv($output, $columns);

        for($i=0, $count = count($bookings); $i < $count; $i++){

            $data = array();

            $data["booking_name"] = get_post_meta($bookings[$i]->post_id, "name", true);
            $data["booking_phone"] = get_post_meta($bookings[$i]->post_id, "user_phone", true);
            $data["booking_amount"] = get_post_meta($bookings[$i]->post_id, "amount", true);

            $tickets = get_post_meta($bookings[$i]->post_id, "tickets", true);

            $ticket_ids = array();

            foreach($tickets as $ticket_id => $qty){
                //$ticket_ids[] = $ticket_id;
            

                $query = "SELECT tickets.ID, tickets.post_title FROM {$wpdb->prefix}posts tickets" ;
        
                $query .= "  WHERE tickets.post_type = 'plugun-ticket' AND ID = '{$ticket_id}' ";
                $tickets = $wpdb->get_results($query);             
            
                $ticket_info = null;
                for($j=0, $counter = count($tickets); $j < $counter; $j++){
                    $ticket_info .= $tickets[$j]->post_title;// . " (" . get_post_meta($tickets[$j]->ID, "price", true) . "), \n\r";
                    $ticket_info .= " (" ;
                    $ticket_info .= get_post_meta($tickets[$j]->ID, "ticket_price", true) ;                
                    $ticket_info .= " x " ;                
                    $ticket_info .=  $qty;                
                    $ticket_info .= ") " ; 
                }

            }

            $data["booking_tickets"] = $ticket_info; //implode(", ", $ticket_info);

            $data["discount"] = get_post_meta($bookings[$i]->post_id, "discount", true);
            $data["fee"] = get_post_meta($bookings[$i]->post_id, "fee", true);

            $event_id = get_post_meta($bookings[$i]->post_id, "event_id", true);

            $event = get_post($event_id);

            $data["event_name"] = $event->post_title;

            $location = get_post_meta($event_id, "event_location", true) ;
            $location .= ", " . get_post_meta($event_id, "event_city", true);
            $location .= ", " . get_post_meta($event_id, "event_postal", true); 
            $location .= ", " . get_post_meta($event_id, "event_state", true); 
            $location .= ", " . get_post_meta($event_id, "event_country", true);

            $data["event_location"] = $location;

            $start_time  = date("F, d, Y H:i", strtotime(get_post_meta($event_id, "event_date_start", true) .  " " . get_post_meta($event_id, "event_time_start", true))) ; 
            $end_time  = date("F, d, Y H:i", strtotime(get_post_meta($event_id, "event_date_end", true) .  " " . get_post_meta($event_id, "event_time_end", true))) ; 
            
            $timing = $start_time . " to " . $end_time;
            $data["event_timining"] = $timing;

          

            fputcsv($output, $data);

        }

		exit;
      
      
        
        }
}

add_theme_support( 'post-thumbnails' );


add_shortcode("deplite_get_license", function (){

});

add_shortcode("dep_is_premium", function(){
    return file_exists(DEP_LITE_PLUGIN_PATH . "/api.php") && file_exists(DEP_LITE_PLUGIN_PATH . "/includes/Payment.php");
});

add_action ("admin_init", function (){
if(!session_id()) session_start();
if(isset($_SESSION["warnings"])){

	add_action( 'admin_notices', function (){

			$class = 'notice notice-error';

			$message = __(   implode("<br>\n", $_SESSION["warnings"]) , 'sample-text-domain' );

			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ),  $message  ); 

			unset($_SESSION["warnings"]);
	} );

}

});

function general_admin_notice(){	
    global $pagenow;
	if(defined("EVENT_PLUGUN_PRO_PATH")) return ;
    if ( $pagenow == 'admin.php' && strstr($_GET["page"], "deplite") ) {
         echo '<div class="notice notice-success is-dismissible">
		 	 <h4>Upgrade Events Management Plugin to Pro Version</h4>
             <p><strong>Good news!</strong> you can get API access feature to login in highly interactive and user friendly mobile app 
			 where you can get benefit of our <a href="http://www.eventplugun.com/pricing/" target="_blank">pro version</a> 
			 <a href="http://www.eventplugun.com/features/" target="_blank">features</a>. You need this feature because it helps you 
			 to get API access through plugin and use it in mobile application for check-ins management.</p>
         </div>';
    }
	if ( $pagenow == 'edit.php' && $_GET["post_type"] == "plugun-template" ) {
         echo '<div class="notice notice-success is-dismissible">
		 	<h4>Upgrade Events Management Plugin to Pro Version</h4>
             <p>You can use QR code to login in mobile app, for this you need to purchase our <a href="http://www.eventplugun.com/pricing/" target="_blank">pro version</a>. 
			 Every ticket has specific QR code which can be read through check-in mobile app</p>
         </div>';
    }

}
add_action('admin_notices', 'general_admin_notice');


function adding_custom_meta_boxes( $post_type ) {
	if(defined("EVENT_PLUGUN_PRO_PATH")) return ;
	if(!in_array($post_type, array("plugun-event", "plugun-template", "plugun-ticket", "plugun-booking"))){
		return ;
	} 
    add_meta_box( 
        'my-meta-box',
        __( 'Get Events Management Plugin Pro Version' ),
        'get_our_premium_version',
        $post_type,
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'adding_custom_meta_boxes' );

if(!defined("EVENT_PLUGUN_PRO_PATH")){

	add_action( 'wp_dashboard_setup', function () {
		wp_add_dashboard_widget(
			'deplite_dashboard_widget',
			'Get Events Management Plugin Pro Version',
			'get_our_premium_version'
		);
	});

}



function get_our_premium_version(){
	$message = '<p>Upgrade to <a href="http://www.eventplugun.com/pricing/" target="_blank">pro version</a> 
	now and start scanning all tickets through mobile. <a href="http://www.eventplugun.com/pricing/" target="_blank">Pro Version</a> will allow you to
	<ol>
	<li>Scan all tickets through mobile app</li>
	<li>Maintain records of all check-ins</li>
	<li>To keep record of available bookings</li>
	<li>To keep record of tickets booked</li>
	</ul>
	You can check pro version <a href="http://www.eventplugun.com/features/" target="_blank">features</a> in detail
	</p>';
	echo $message;
}

function dep_get_gmap_route($center){
	?>
<script 
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDDlFUhiBJO741v__PHQHxIlReBAdsLdjI&callback=initMap">
    </script>	
<style>	
	#map {
        height: 400px;
        width: 300px;
      }
</style>	  
<!--
<div id="floating-panel">
    <b>Start: </b>
    <select id="start">
      <option value="Blue Area, Islamabad">Blue Area</option>
      <option value="Shifa International Hospital, Islamabad">Shifa International Hospital</option>
      <option value="Jinnah Super, F7 Markaz, Islamabad">Jinnah Super</option>
      <option value="Super, F6 Markaz, Islamabad">Super</option>      
    </select>
    <b>End: </b>
    <select id="end">
      <option value="Blue Area, Islamabad">Blue Area</option>
      <option value="Shifa International Hospital, Islamabad">Shifa International Hospital</option>
      <option value="Jinnah Super, F7 Markaz, Islamabad">Jinnah Super</option>
      <option value="Super, F6 Markaz, Islamabad">Super</option>      
    </select>    </div>
    <div id="map"></div>
-->		
<select id="start" style="display: none;">
      <option value="Blue Area, Islamabad">Blue Area</option>
      <option value="Shifa International Hospital, Islamabad">Shifa International Hospital</option>
      <option value="Jinnah Super, F7 Markaz, Islamabad">Jinnah Super</option>
      <option value="Super, F6 Markaz, Islamabad">Super</option>      
    </select>
    <script>
        
      function initMap() {
		var directionsService = new google.maps.DirectionsService;
        var directionsDisplay = new google.maps.DirectionsRenderer;
        var currentPos = {lat: 33.6952308, lng: 73.0128546 };
		var map = new google.maps.Map(document.getElementById('map'), {
          zoom: 12,
          center: {lat: currentPos.lat, lng: currentPos.lng}
        });
        directionsDisplay.setMap(map);

        var onChangeHandler = function() {
            currentPos = get_current_location();
          calculateAndDisplayRoute(directionsService, directionsDisplay);
        };
        document.getElementById('start').addEventListener('change', onChangeHandler);
        //document.getElementById('end').addEventListener('change', onChangeHandler);
		 	
		  
      }

      function calculateAndDisplayRoute(directionsService, directionsDisplay) {
		var origan = "<?php echo $center ?>";
		var userLocation = "F10 Markaz, Islamabad";   
        directionsService.route({
          origin: origan,
          destination: userLocation,
          travelMode: 'DRIVING'
        }, function(response, status) {
          if (status === 'OK') {
            directionsDisplay.setDirections(response);
          } else {
            window.alert('Directions request failed due to ' + status);
          }
        });
      }

function get_current_location(){ 
      if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(function(position) {
            var pos = {
              lat: position.coords.latitude,
              lng: position.coords.longitude
            };
            return pos;
          });
      } 
} 

function get_center(address){
	$.ajax({
	url:"http://maps.googleapis.com/maps/api/geocode/json?address="+address+"&sensor=false",
	type: "POST",
	success:function(res){
		return {
		"lat": res.results[0].geometry.location.lat,
		"lng": res.results[0].geometry.location.lng
		}
	}
	});
}    

     jQuery(document).ready(function (){
				jQuery("#start").trigger("change");
		 });    
    </script>
    

	<?php
}