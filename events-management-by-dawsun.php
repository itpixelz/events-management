<?php

/**
 * Events Management by Dawsun
 *
 * Events Management by Dawsun - Event management wordpress plugin
 *
 * @link              http://www.eventplugun.com
 * @since             1.0.0
 * @package           Events_Management_by_Dawsun
 *
 * @wordpress-plugin
 * Plugin Name:       Events Management by Dawsun
 * Plugin URI:        https://www.eventplugun.com
 * Description:       Event Plugin for wordpress for tickets and calendar
 * Version:           1.0.2
 * Author:            Dawsun
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       event-plugun
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}


header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: on, 01 Jan 1970 00:00:00 GMT");



define("DEP_LITE_PLUGIN_PATH", __DIR__ . "/");

define("DEP_LITE_PLUGIN_URL", plugins_url(basename(__DIR__)) . "/");





/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-event-plugun-activator.php
 */

function dep_lite_activate_event_plugun()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-event-plugun-activator.php';
    Events_Management_by_Dawsun_Activator::activate();
}

require_once(__DIR__ . "/includes/Events_Management_by_Dawsun_Email.php");
require_once(__DIR__ . "/includes/Custom_Error.php");
require_once(__DIR__ . "/includes/Registry.php");
require_once(__DIR__ . "/includes/Calendar.php");
require_once(__DIR__ . "/settings/Currency.php");
require_once(__DIR__ . "/includes/utility-functions.php");

$oCurrency = new EpLiteCurrency();

new DepLiteCalendar();

require_once(__DIR__ . "/reminder.php");


nocache_headers();

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-event-plugun-deactivator.php
 */
function dep_lite_deactivate_event_plugun()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-event-plugun-deactivator.php';
    Events_Management_by_Dawsun_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'dep_lite_activate_event_plugun');
register_deactivation_hook(__FILE__, 'dep_lite_deactivate_event_plugun');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-event-plugun.php';


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function deplite_run_event_plugun()
{

    $plugin = new Events_Management_by_Dawsun();
    $plugin->run();

}
deplite_run_event_plugun();

function deplite_theme_name_scripts()
{
    wp_enqueue_style('plugun_Style', DEP_LITE_PLUGIN_URL . '/style.css');
}
add_action('wp_enqueue_scripts', 'deplite_theme_name_scripts');

@include_once(__DIR__ . "/api.php");

require_once(__DIR__ . "/public/post_handler.php");
require_once(__DIR__ . "/admin/post_handler.php");
