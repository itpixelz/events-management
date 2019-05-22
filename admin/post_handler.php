<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_action("post_updated", "dep_lite_update_booking");

function dep_lite_update_booking($post_id){
    $post = get_post($post_id);

    switch($post->post_type){
        case "plugun-booking":
            dep_lite_save_booking_info($post_id);
        break;
    }    
}


function dep_lite_save_booking_info($post_id){
    
            $name = sanitize_text_field(@$_POST["name"]);            
            $phone = sanitize_text_field(@$_POST["user_phone"]);
            $user_comments = sanitize_text_field(@$_POST["user_comments"]);            
         
            $admin_private_notes = sanitize_text_field(@$_POST["admin_private_notes"]);


            if(empty($name)){
                EpLite_Custom_Error::add("buy_event_tickets", "name", "Please enter customer name");
            }


            if(empty($phone)){
                EpLite_Custom_Error::add("buy_event_tickets", "user_phone", "Please enter user phone number");
            }
            else if(preg_match_all("#[^0-9\s\-]+#", $phone, $matches)){
                $message = "Please valid phone number";
                if(count($matches)>0){
                    $message .= "Invalid characters found in user phone number (" . implode(", ", $matches[0]) . ")";
                }                
                EpLite_Custom_Error::add("buy_event_tickets", "event_booking_phone", $message);
            }

            if(empty($user_comments)){
                EpLite_Custom_Error::add("buy_event_tickets", "user_comments", "Please enter your comments");
            }

            $errors = EpLite_Custom_Error::get_by_entity("buy_event_tickets");

            if(count($errors) > 0) return false;           
            
            $seats = $total_price = 0; 
            $total_price = get_post_meta($post_id, "amount", true);


            update_post_meta($post_id, "name", $name);
            
            update_post_meta($post_id, "seats", $seats);

            if($_POST["discount"]){
                update_post_meta($post_id, "discount", (float) sanitize_text_field($_POST["discount"]));
                $total_price -= (float) sanitize_text_field($_POST["discount"]);
            }

            if($_POST["fee"]){
                update_post_meta($post_id, "fee", (float) sanitize_text_field($_POST["fee"]));
                $total_price += (float) sanitize_text_field($_POST["fee"]);
            }
            
            update_post_meta($post_id, "amount", $total_price);
            update_post_meta($post_id, "user_comments", $user_comments);
            update_post_meta($post_id, "user_phone", $phone);

            update_post_meta($post_id, "admin_private_notes", $admin_private_notes);
            
            
 
}