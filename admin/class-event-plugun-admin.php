<?php

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Events_Management_by_Dawsun
 * @subpackage Events_Management_by_Dawsun/admin
 * @author     Dev Team <support@eventplugun.com>
 */
class Events_Management_by_Dawsun_Admin
{
    
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;
    
    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;
    
    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
        
        add_action('parent_file', array(
            $this,
            'menu_highlight'
        ));
        add_action('admin_menu', array(
            $this,
            'adjust_the_wp_menu'
        ), 999);
        
        
        add_action('admin_print_scripts', function()
        {
            wp_enqueue_script('media-upload');
            wp_enqueue_script('thickbox');
            
        });
        
        add_action('admin_print_styles', function()
        {
            wp_enqueue_style('thickbox');
        });
        
        add_action("admin_init", function()
        {
            
            
            if (isset($_POST["setting_template_wp_nonce"])) {
                
                if (!wp_verify_nonce($_POST['setting_template_wp_nonce'], 'ep_lite_setting_general')) {
                    //add_action("admin_notice", ) 
                    $this->warnings[] = "invalid request token";
                    add_action('admin_notices', array(
                        $this,
                        "show_admin_notices"
                    ));
                    return false;
                }
                
                if (isset($_POST["event_plugun_template_successfully_checkedin"])) {
                    update_option("event_plugun_template_successfully_checkedin", sanitize_text_field($_POST["event_plugun_template_successfully_checkedin"]));
                }
                
                if (isset($_POST["event_plugun_template_booking_status_changed"])) {
                    update_option("event_plugun_template_booking_status_changed", sanitize_text_field($_POST["event_plugun_template_booking_status_changed"]));
                }
                
                if (isset($_POST["event_plugun_template_booking_done"])) {
                    update_option("event_plugun_template_booking_done", sanitize_text_field($_POST["event_plugun_template_booking_done"]));
                }
                
                if (isset($_POST["event_plugun_template_reminder"])) {
                    update_option("event_plugun_template_reminder", sanitize_text_field($_POST["event_plugun_template_reminder"]));
                }
                
                if (isset($_POST["event_plugun_template_logo"])) {
                    update_option("event_plugun_template_logo", sanitize_text_field($_POST["event_plugun_template_logo"]));
                }
                
                if (isset($_POST["event_plugun_template_footer"])) {
                    update_option("event_plugun_template_footer", sanitize_text_field($_POST["event_plugun_template_footer"]));
                }
                
                
            }
            
            if (isset($_POST["event_plugun_google_map_api"])) {
                if (!wp_verify_nonce($_POST['setting_general_wp_nonce'], 'ep_lite_setting_general')) {
                    return false;
                }
                update_option("event_plugun_google_map_api", sanitize_text_field($_POST["event_plugun_google_map_api"]));
            }
            
        });
        
        
    }
    
    
    /**
     * Register the settings page for the admin area.
     *
     * @since    1.0.0
     */
    public function register_settings_page()
    {
        
        
        
        add_menu_page('Events Management', 'Events Management', 'manage_options', 'event-plugun', null, 'dashicons-calendar', 75);
        
        
        
        $plugin_admin_menu_items = array(
            'events' => array(
                __('Events', 'ep'),
                'edit.php?post_type=plugun-event',
                null
            ),
            'event_categories' => array(
                __('Event Categories', 'ep'),
                'edit-tags.php?taxonomy=plugun-event-category',
                null
            ),
            'event_tags' => array(
                __('Event Tags', 'ep'),
                'edit-tags.php?taxonomy=plugun-event-tags',
                null
            ),
            'tickets' => array(
                __('Tickets', 'ep'),
                'edit.php?post_type=plugun-ticket',
                null
            ),
            'bookings' => array(
                __('Bookings', 'ep'),
                'edit.php?post_type=plugun-booking',
                null
            ),
            'templates' => array(
                __('Email Templates', 'ep'),
                'edit.php?post_type=plugun-template',
                null
            ),
            'settings' => array(
                __('Settings', 'ep'),
                'deplite-setting-general',
                array(
                    $this,
                    'other_setting'
                )
            ),
            
            'template_setting' => array(
                __('', 'ep'),
                'deplite-setting-template',
                array(
                    $this,
                    'template_setting'
                )
            ),

            'support' => array(
                __('', 'ep'),
                'deplite-support',
                function (){
                    require_once(DEP_LITE_PLUGIN_PATH . "/admin/partials/setting-support.php");
                }
            )
            
            
        );
        
        
        
        $plugin_admin_menu_items = apply_filters("event_plugun_admin_pages", $plugin_admin_menu_items, $this);
        
        foreach ($plugin_admin_menu_items as $handler => $value) {
            
            
            add_submenu_page('event-plugun', $value[0], $value[0], 'manage_options', $value[1], $value[2]);
        }
        
        
        
        
        //remove_submenu_page( 'event-plugun', 'admin.php?page=event-plugun');
        
        
        
    }
    
    
    public function adjust_the_wp_menu()
    {
        $page = remove_submenu_page('event-plugun', 'event-plugun');
        // $page[0] is the menu title
        // $page[1] is the minimum level or capability required
        // $page[2] is the URL to the item's file
    }
    
    
    
    
    
    
    public function menu_highlight($parent_file)
    {
        global $current_screen;
        
        // print_r($current_screen); exit;
        
        
        $taxonomy  = $current_screen->taxonomy;
        $post_type = $current_screen->post_type;
        if (isset($taxonomy) && in_array($taxonomy, array(
            'plugun-event-category',
            'plugun-event-tags'
        ))) {
            $parent_file = 'event-plugun';
        }
        
        if (isset($_GET["page"]) && in_array($_GET["page"], array(
            "checkin-log",
            "event-plugun-other-setting",
            "plugun-payment-paypal",
            "plugun-payment-authorize",
            "event-plugun-template-setting"
        ))) {
            $parent_file = 'event-plugun';
        }
        
        
        if (isset($post_type) && in_array($post_type, array(
            'plugun-ticket',
            'plugun-event',
            'plugun-booking',
            "plugun-api",
            'plugun-template'
        ))) {
            $parent_file = 'event-plugun';
        }
        
        
        return $parent_file;
    }
    
    
    /**
     * Register the settings for our settings page.
     *
     * @since    1.0.0
     */
    public function register_settings()
    {
        
        
        
        // Here we are going to register our setting.
        register_setting($this->plugin_name . '-settings', $this->plugin_name . '-settings', array(
            $this,
            'sandbox_register_setting'
        ));
        
        // Here we are going to add a section for our setting.
        add_settings_section($this->plugin_name . '-settings-section', __('Settings', 'event-plugun'), array(
            $this,
            'sandbox_add_settings_section'
        ), $this->plugin_name . '-settings');
        
        // Here we are going to add fields to our section.
        add_settings_field('post-types', __('Post Types', 'event-plugun'), array(
            $this,
            'sandbox_add_settings_field_multiple_checkbox'
        ), $this->plugin_name . '-settings', $this->plugin_name . '-settings-section', array(
            'label_for' => 'post-types',
            'description' => __('Save button will be added only to the checked post types.', 'toptal-save')
        ));
        
        
    }
    
    
    /**
     * sandbox register settings
     *
     * @since    1.0.0
     */
    
    public function sandbox_register_setting()
    {
        
        
    }
    /**
     * sandbox register settings
     *
     * @since    1.0.0
     */
    
    public function sandbox_add_settings_field_multiple_checkbox()
    {
        
        
    }
    /**
     * sandbox add  settings sections
     *
     * @since    1.0.0
     */
    
    public function sandbox_add_settings_section()
    {
        echo 'test';
        
    }
    
    
    /**
     * Display the settings page content for the page we have created.
     *
     * @since    1.0.0
     */
    
    public function plugun_settings_admin()
    {
        
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/event-plugun-admin-display.php';
        
    }
    
    
    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Events_Management_by_Dawsun_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Events_Management_by_Dawsun_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        
        // if ( 'event_plugun_settings' != $hook ) {
        //     return;
        // }
        
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/event-plugun-admin.css', array(), $this->version, 'all');
        
        //    wp_enqueue_style( $this->plugin_name.'-datepicker', plugin_dir_url( __FILE__ ) . 'css/jquery.simple-dtpicker.css', array(), $this->version, 'all' );
        
        wp_register_style('jquery-ui-datepicker-theme', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/themes/smoothness/jquery-ui.min.css');
        wp_register_style('jquery-ui-timepicker', plugin_dir_url(__FILE__) . 'css/jquery.timepicker.min.css', array(), $this->version, 'all');
        wp_enqueue_style('jquery-ui-datepicker-theme');
        wp_enqueue_style('jquery-ui-timepicker');
        
    }
    
    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Events_Management_by_Dawsun_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Events_Management_by_Dawsun_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        // if ( 'event_plugun_settings' != $hook ) {
        //     return;
        // }
        
        
        wp_enqueue_script('jquery');
        
        wp_enqueue_script('jquery-ui-datepicker');
        //wp_enqueue_script( $this->plugin_name. '-timepicker', plugin_dir_url( __FILE__ ) . 'js/jquery-ui-timepicker-addon.js' );
        wp_enqueue_script($this->plugin_name . 'time-admin', plugin_dir_url(__FILE__) . 'js/jquery.timepicker.min.js');
        wp_enqueue_script($this->plugin_name . '-admin', plugin_dir_url(__FILE__) . 'js/event-plugun-admin.js');
        
    }
    
    /*
    function checkin_logs(){
    require_once(DEP_LITE_PLUGIN_PATH . "/admin/partials/checkin-log.php");
    }
    */
    function template_setting()
    {
        require_once(DEP_LITE_PLUGIN_PATH . "/admin/partials/setting-template.php");
    }
    
    function other_setting()
    {
        require_once(DEP_LITE_PLUGIN_PATH . "/admin/partials/setting-other.php");
    }
    
    function show_admin_notices()
    {
        
        $class = 'notice notice-error';
        
        $message = __('<div>' . implode('</div><div>', $this->warnings) . '</div>', 'sample-text-domain');
        
        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
    }
    
    
    
}