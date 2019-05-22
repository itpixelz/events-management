<?php

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Events_Management_by_Dawsun
 * @subpackage Events_Management_by_Dawsun/includes
 * @author     Dev Team <support@eventplugun.com>
 */

class Events_Management_by_Dawsun {

	// Post Types - Start
	/**
	 * The post types we're registering.
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $post_types = array();


	public static $postTypes = array();

	// shortcodes - Start
	/**
	 * The post types we're registering.
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $short_codes;

	// Post Types - End

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Events_Management_by_Dawsun_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->token 			= 'event-plugun';
		$this->plugin_url 		= plugin_dir_url( __FILE__ );
		$this->plugin_path 		= plugin_dir_path( __FILE__ );

		$this->plugin_name = 'event-plugun';
		$this->version = '1.0.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();



	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Events_Management_by_Dawsun_Loader. Orchestrates the hooks of the plugin.
	 * - DepLite_Plugin_i18n. Defines internationalization functionality.
	 * - Events_Management_by_Dawsun_Admin. Defines all hooks for the admin area.
	 * - Events_Management_by_Dawsun_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-event-plugun-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-event-plugun-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-event-plugun-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-event-plugun-public.php';

		$this->loader = new Events_Management_by_Dawsun_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the DepLite_Plugin_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new DepLite_Plugin_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Events_Management_by_Dawsun_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'register_settings_page' );

		// Hook our settings
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}




	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Events_Management_by_Dawsun_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		require_once( 'class-event-plugun-post-type.php' );
		require_once( 'class-event-plugun-taxonomy.php' );
		require_once( 'class-event-plugun-shortcodes.php' );
		$this->short_codes = new Events_Management_by_Dawsun_Shotcodes();

		add_action("plugins_loaded", function (){

		$post_types = array(
			"plugun_event" => array(
				"name" => "plugun-event",
				"singular_title" => "Event",
				"plural_title" => "Events",
				"menu_icon" => 'dashicons-calendar-alt' 
			),

			"plugun_ticket" => array(
				"name" => "plugun-ticket",
				"singular_title" => "Event Ticket",
				"plural_title" => "Event Tickets",
				"menu_icon" => 'dashicons-calendar-alt' 
			),

			"plugun_booking" => array(
				"name" => "plugun-booking",
				"singular_title" => "Booking",
				"plural_title" => "Bookings",
				"menu_icon" => 'dashicons-calendar-alt' 
			),

			
			"plugun_template" => array(
				"name" => "plugun-template",
				"singular_title" => "Email Template",
				"plural_title" => "Email Templates",
				"menu_icon" => 'dashicons-calendar-alt' 
			),
		);

		$post_types = apply_filters("event_plugun_register_post_types", $post_types);

		//self::$postTypes = $this->post_types

		foreach($post_types as $name => $post_type){
			self::$postTypes[$name] = $this->post_types[$name] = new Events_Management_by_Dawsun_Post_Type( $post_type["name"], 
						__( $post_type["singular_title"], 'event-plugun' ), __( $post_type["plural_title"], 'event-plugun' ), 
						array( 'menu_icon' => $post_type["menu_icon"] ) );
		} 

		});
		
		

		
	}


	function get_post_type_names(){
		foreach(self::$postTypes as $name => $post_type){
			$names[] = $name;
		}

		return $names;
	}

	function get_post_type($name){
		return self::$postTypes[$name];
	}


	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */

	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Events_Management_by_Dawsun_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
