<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once(__DIR__ . "/settings.php" );

?>
<h2 class="wp-heading-inline">Email Template Setting</h2>

<input type="hidden" name="setting_template_wp_nonce" value="<?php echo wp_create_nonce( "ep_lite_setting_general" ); ?>" />

<form method="post">
<table width="100%" class="wp-list-table widefat fixed striped posts">
    
    <tr>
       <td width="30%">Succcessful Checkin Email Template: </td> 
       <td><?php echo get_template_dropdown("event_plugun_template_successfully_checkedin", get_option("event_plugun_template_successfully_checkedin"))  ?></td>
    </tr>

    <tr>    
       <td>Booking Status Changed Email Template: </td>
       <td><?php echo get_template_dropdown("event_plugun_template_booking_status_changed", get_option("event_plugun_template_booking_status_changed"))  ?></td> 
    </tr>

    <tr>    
       <td>Booking Complete Email Template: </td>
       <td><?php echo get_template_dropdown("event_plugun_template_booking_done", get_option("event_plugun_template_booking_done"))  ?></td> 
    </tr>

    <tr>    
       <td>Reminder Email Template: </td>
       <td><?php echo get_template_dropdown("event_plugun_template_reminder", get_option("event_plugun_template_reminder"))  ?></td> 
    </tr>

    <tr>    
       <td>Template Header Image: </td>
       <td><button class="btn btn-default button button-secondary" id="template-logo">Choose Image</button>
       <input type="hidden" name="event_plugun_template_logo" value="<?php echo get_option("event_plugun_template_logo") ?>">
       <br>
       <p>&nbsp;</p> 
       <img id="template-logo-preview" src="<?php echo get_option("event_plugun_template_logo") ?>" height="150" />
       </td> 
    </tr>

    <tr>    
       <td>Template Footer: </td>
       <td><!--<textarea class="input-x-large" cols="6" name="event_plugun_template_footer"><?php echo get_option("event_plugun_template_footer") ?></textarea>-->
       <?php wp_editor( stripslashes(get_option("event_plugun_template_footer")), "event_plugun_template_footer"); ?>
       </td> 
    </tr>
    
    <tr>
       <td width="30%"></td> 
       <td><button type="submit"  class="button button-primary" >Save Settings</button></td>
    </tr>
    
</table>

</form>

<div class="clear"></div></div><!-- wpbody-content -->
<div class="clear"></div></div>

<?php


function get_template_dropdown($name, $default = null, $params = array()){

    $templates = get_posts(array(
        "post_type" => "plugun-template",
        "posts_per_page" => -1
    ));

    $params["name"] = $name; 

    $dropdown = "<select ";

    foreach($params as $index => $value){
        $dropdown .= " {$index}=\"{$value}\" ";    
    }

    $dropdown .= ' >';

    for($i=0, $count = count($templates); $i < $count; $i++){
        $selected = ($templates[$i]->ID == $default) ? ' selected="selected" ' : '';
        $dropdown .= "<option value=\"{$templates[$i]->ID}\" {$selected}>{$templates[$i]->post_title}</option> ";
    }

    $dropdown .= "</select>";

    return $dropdown;

}

?>