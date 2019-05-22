<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

?>

<div id="wpbody" role="main">
<h1 class="wp-heading">Event Plugun Setting</h1>
<div id="wpbody-content" aria-label="Main content" tabindex="0">

<?php

$pages = array(
	"deplite-setting-general" => "General",
	"deplite-setting-template" => "Email Template",
	'deplite-support' => "Support"
);

$pages = apply_filters("event_plugun_setting_pages_tabs", $pages);

$sub_pages = apply_filters("event_plugun_setting_sub_pages_tabs", array());

?>
		

<div class="wrap wp_event_plugun">
	<form method="post" id="frm-plugun-settings" action="" enctype="multipart/form-data">
		<nav class="nav-tab-wrapper woo-nav-tab-wrapper">
		<?php foreach($pages as $slug => $title){ ?>
			<a href="admin.php?page=<?php echo $slug ?>" class="nav-tab 
            <?php if($_GET["page"]==$slug) echo ' nav-tab-active'  ?>"><?php echo ucwords($title) ?></a>
		<?php } ?>
			
        </nav>
		<?php
				
		?>
        <?php if(is_array($sub_pages) && count($sub_pages) > 0 ){ ?>
			<ul class="subsubsub">
			<?php foreach($sub_pages as $slug => $title){ ?>
				<li><a href="admin.php?page=<?php $slug ?>" class="<?php if($_GET["page"] == $slug) echo "current" ?>"><?php echo ucwords($title) ?></a>  | </li>
			<?php } ?>
			</ul>
			<p>&nbsp;</p>
		
        <?php } ?>
        
		
		
				

