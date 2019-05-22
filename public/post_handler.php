<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}
        add_action("event_plugun_booking_done", function ($booking_id){
              global $wpdb;
             
                $booking = get_post($booking_id);

                $event_id = get_post_meta($booking_id, "event_id", true);

                $event = get_post($event_id);

           
                
                $email = new Events_Management_by_Dawsun_Email();

                $email->receiver =  get_post_meta($booking_id, "user_id", true);


                $template = get_post(get_option("event_plugun_template_booking_done"));

				$subject = get_post_meta($template->ID, "event_plugun_template_subject", true);

				$subject = str_replace("{{event_name}}", $event->post_title, $subject);

                $email->subject = $subject ;	

                $email->template = array(
                    "booking_id"=> $booking->ID,
                    "template"=> "booking.phtml",                    
                  
                );   

                add_filter("event_plugun_email_sender", function($sender){
                    return array(
                        "user_email" => "event_plugun@dawsun.xyz",
                        "display_name" => " Event Plugun "
                    );
                });

                $email->send(); 

               
        });




add_action("wp", function (){
    global $wpdb;

    if(!class_exists("qrstr")){
        include(DEP_LITE_PLUGIN_PATH .'/includes/phpqrcode/qrlib.php'); 
    }

    if(isset($_REQUEST["action"])){

    switch($_REQUEST["action"]){

        case "buy_event_tickets":

            if(!wp_verify_nonce( $_POST['user_booking_wpnonce'], 'eplite_user_booking' ) ){
                    
                    
            }

            $name = sanitize_text_field($_POST["event_booking_name"]);
            $email = sanitize_email($_POST["event_booking_email"]);
            $phone = sanitize_text_field($_POST["event_booking_phone"]);
            $comment = sanitize_text_field($_POST["event_booking_comment"]);
            $event_id = (int) $_POST["event_id"];
			// ticket_qty is array, in following code values are forced to integer like (int) $qty and (int) ticket id
			// for more details review the code below.	
            if(is_array($_POST["ticket_qty"])){
                $ticket_qty = $_POST["ticket_qty"];
            }
            else $ticket_qty = array();
            

            $seats = $total_price = 0; 


            if(empty($name)){
                EpLite_Custom_Error::add("buy_event_tickets", "event_booking_name", "Please enter your name");
            }

            if(empty($email)){
                EpLite_Custom_Error::add("buy_event_tickets", "event_booking_email", "Please enter your email");
            }
            else if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                EpLite_Custom_Error::add("buy_event_tickets", "event_booking_email", "Please check your email, it doesn't seem to be valid ");
            }

            if(empty($phone)){
                EpLite_Custom_Error::add("buy_event_tickets", "event_booking_phone", "Please enter your phone number");
            }
            else if(preg_match_all("#[^0-9\s\-]+#", $phone, $matches)){
                $message = "Please valid phone number";
                if(count($matches)>0){
                    $message .= "Invalid characters found in your phone number (" . implode(", ", $matches[0]) . ")";
                }                
                EpLite_Custom_Error::add("buy_event_tickets", "event_booking_phone", $message);
            }

            if(empty($comment)){
                EpLite_Custom_Error::add("buy_event_tickets", "event_booking_comment", "Please enter your comments");
            }

            $hash_keys = $ticket_names = $tickets = array();

            $file = '';

            foreach($ticket_qty as $ticket_id => $qty){                
                if($qty > 0){
                    $ticket = get_post($ticket_id);
                    $available_qty = get_dep_lite_ticket_available_quantity((int) $ticket_id);

                    if($available_qty > 0 && $qty > $available_qty){                        
                        EpLite_Custom_Error::add("buy_event_tickets", "ticket_qty", "Your quantity for ticket ({$ticket->post_title}) is exceeding the available limit"); 
                        return ;       
                    }

                    if(sanitize_text_field($_POST["ownership"]) == "multiple_owner"){

                        for($i=1; $i <= $qty; $i++ ){
                            
                            $hash_key = get_deplite_new_hash_key();
                            $file = __DIR__ . "/". date("YmdHis") . $i . ".png";
                            QRcode::png($hash_key, $file);
                            $qr_code = @file_get_contents($file);
                            @unlink($file);
                            $wpdb->insert("{$wpdb->prefix}plugun_checkin", array(
                                "hash_key" => $hash_key,
                                "ticket_id" => (int) $ticket_id,
                                "checkins" => serialize(array()),
                                "qr_code" => $qr_code
                            ));

                            $hash_keys[] = $hash_key;
                        }

                    }
                    else {
                            $hash_key = get_deplite_new_hash_key();
                            $file = __DIR__ . "/". $hash_key . ".png";
                            QRcode::png($hash_key, $file);
                            $qr_code = @file_get_contents($file);
                            @unlink($file);
                            $wpdb->insert("{$wpdb->prefix}plugun_checkin", array(
                                "hash_key" => $hash_key,
                                "ticket_id" => (int) $ticket_id,
                                "quantity" => (int) $qty,
                                "checkins" => serialize(array()),
                                "qr_code" => $qr_code
                            ));

                            $hash_keys[] = $hash_key;

                    }


                    $tickets[$ticket_id] = $qty;
                    $ticket_names[] = $ticket->post_title;
                    
                    //$ticket_price = get_post_meta($ticket_id, "ticket_price", true) + (int) get_post_meta($ticket_id, "ticket_fee", true);
                    $ticket_price = get_deplite_ticket_price($ticket_id);
                    $seats += $qty;
                    $total_price += $ticket_price * $qty;
                }
            }

            if(count($tickets) == 0 ){
                EpLite_Custom_Error::add("buy_event_tickets", "ticket_qty", "Please select ticket quantity!");
            }

            $errors = EpLite_Custom_Error::get_by_entity("buy_event_tickets");

            if(count($errors) > 0) return false;

            $user = get_user_by("email", $email);

            if(!is_object($user)){
                $first_space = strpos($name, " ");
                if($first_space > 0 ){
                    $first_name = substr($name, 0, $first_space);
                    $last_name = substr($name, $first_space);
                }
                else {
                    $first_name = $name;
                    $last_name = $name;
                }
                

                $user_id = wp_insert_user( array(
                    "user_email" => $email,
                    "user_login" => substr($email, 0, strpos($email, "@")),
                    "user_pass" => NULL,
                    "first_name" => $first_name,
                    "last_name" =>	$last_name,
                    "description" => $comment
                ) );

                if(is_object($user_id)){
                    $register_errors = $user_id->errors;
                    foreach($register_errors as $source =>$messages) {
                        for($i=0, $count = count($messages); $i < $count; $i++){
                            EpLite_Custom_Error::add("buy_event_tickets", $source, $messages[$i]);
                        }
                    }
                    return false;
                }

                wp_new_user_notification( $user_id );


            }
            else {
                $user_id = $user->ID;
                update_user_meta($user_id, "phone", true);
            }

            $booking_id = $post_id = wp_insert_post(array(
                "post_author" => $user_id,
                "post_title" => $name,
                "post_status" => "pending",
                "post_type" => "plugun-booking",
                "post_parent" => $event_id
            ));

            if(is_object($post_id)){
                $register_errors = $post_id->errors;
                foreach($register_errors as $source =>$messages) {
                    for($i=0, $count = count($messages); $i < $count; $i++){
                        EpLite_Custom_Error::add("buy_event_tickets", $source, $messages[$i]);
                    }
                }
                return false;
            }
//$hash_keys[$i];

            $update_query = "UPDATE {$wpdb->prefix}plugun_checkin SET booking_id = '{$post_id}' WHERE booking_id = 0 ";
            $wpdb->query( $update_query );

          //  echo $update_query; 
           
            $event = get_post($event_id);

            update_post_meta($post_id, "event_id", $event_id);
            update_post_meta($post_id, "event_name", $event->post_title);

            update_post_meta($post_id, "name", $name);
            update_post_meta($post_id, "tickets", $tickets);
            update_post_meta($post_id, "ticket_names", $ticket_names);
            update_post_meta($post_id, "seats", $seats);
            update_post_meta($post_id, "amount", $total_price);
            update_post_meta($post_id, "user_comments", $comment);
            update_post_meta($post_id, "user_phone", $phone);
            update_post_meta($post_id, "user_id", $user_id);
            update_post_meta($post_id, "booking_status", "pending");

            DepLiteRegistry::add("user_booking_saved", "Your booking information is saved successfully!");
            DepLiteRegistry::add("booking_id", $post_id);

            do_action("event_plugun_booking_done", $post_id);

            
        break;

        case "qr_code_image":
        $hash = sanitize_text_field($_POST["hash"]); 
        $query = "SELECT qr_code FROM {$wpdb->prefix}plugun_checkin WHERE hash_key = '{$hash}'";
        $qr_code = $wpdb->get_var($query);
        if(!$qr_code){
            return ;
        }
        header("Content-Type: image/png");
        echo $qr_code;
        exit;
        break;
    }

   } 

});