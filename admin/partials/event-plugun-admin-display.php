<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://www.eventplugun.com
 * @since      1.0.0
 *
 * @package    Event_Plugun
 * @subpackage Event_Plugun/admin/partials
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once(__DIR__ . "/settings.php");

?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

 <div id="wrap">
	<form method="post" action="options.php">

<select name="page-dropdown"
 onchange='document.location.href=this.options[this.selectedIndex].value;'> 
 <option value="">
<?php echo esc_attr( __( 'Select page' ) ); ?></option> 
 <?php 
  $pages = get_pages(); 
  foreach ( $pages as $page ) {
  	$option = '<option value="' . get_page_link( $page->ID ) . '">';
	$option .= $page->post_title;
	$option .= '</option>';
	echo $option;
  }
 ?>
</select>
	


		<?php
			settings_fields( 'event-plugun-settings' );
			do_settings_sections( 'event-plugun-settings' );
			submit_button();
		?>




	</form>

  
</div>


<div class="clear"></div></div><!-- wpbody-content -->
<div class="clear"></div></div>