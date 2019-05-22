<?php 

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once(__DIR__ . "/settings.php" );

 ?>

<h2 class="wp-heading">General Settings</h2>

<form method="post">
<input type="hidden" name="setting_general_wp_nonce" value="<?php echo wp_create_nonce( "ep_lite_setting_general" ); ?>" />

<table width="100%" class="wp-list-table widefat fixed striped posts">
    <tr>
       <td width="20%">Google Map API Key: <br><em>To activate google map, you need to enter valid API key</em></td> 
       <td><input name="event_plugun_google_map_api" value="<?php echo get_option("event_plugun_google_map_api") ?>" type="text" class="input-x-large"  />          
       </td>
    </tr>
    <tr>
       <td width="20%">Currency: </td> 
       <td><select name="event_plugun_currency">
           <?php foreach(EpLiteCurrency::$list as $index => $name){ ?> 
           <option value="<?php echo $index ?>" <?php if(get_option("event_plugun_currency") == $index) echo ' selected="selected" ' ?>
           ><?php echo $name ?> (<?php echo (@EpLiteCurrency::$symbols[$index]) ? EpLiteCurrency::$symbols[$index] : $index  ?>)</option> 
           <?php } ?>
           
       </select></td>       
    </tr>
    <tr>
        <td>Currency Placement:  </td>
        <td><select name="event_plugun_currency_side">
            <option value="left" <?php if( get_option("event_plugun_currency_side") == "left" ) echo ' selected="selected" ' ?> >Left ($100)</option>
            <option value="right" <?php if( get_option("event_plugun_currency_side") == "right" ) echo ' selected="selected" ' ?> >Right (100$)</option>
                </select>
                
                </td>
    </tr> 
    
    <tr>
       <td width="30%"></td> 
       <td><button type="submit"  class="button button-primary"  >Save Settings</button></td>
    </tr>
    
</table>

</form>
<div class="clear"></div></div><!-- wpbody-content -->
<div class="clear"></div></div>
