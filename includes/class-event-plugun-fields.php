<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly.

/**
 * Starter Plugin Post Type Class
 *
 * All functionality pertaining to post types in Starter Plugin.
 *
 * @package WordPress
 * @subpackage Events_Management_By_Dawsun
 * @category Plugin
 * @author Matty
 * @since 1.0.0
 */
class Events_Management_by_Dawsun_Fields

{
	/**
	 * The post type token.
	 * @access public
	 * @since  1.0.0
	 * @var    string
	 */
	public $post_type;

	/**
	 * The post type singular label.
	 * @access public
	 * @since  1.0.0
	 * @var    string
	 */
	public $singular;

	/**
	 * The post type singular label.
	 * @access public
	 * @since  1.0.0
	 * @var    string
	 */
	public $event_plugun;

	/**
	 * The post type plural label.
	 * @access public
	 * @since  1.0.0
	 * @var    string
	 */
	public $plural;

	/**
	 * The post type args.
	 * @access public
	 * @since  1.0.0
	 * @var    array
	 */
	public $args;

	/**
	 * The taxonomies for this post type.
	 * @access public
	 * @since  1.0.0
	 * @var    array
	 */
	public $taxonomies;

	/**
	 * Constructor function.
	 * @access public
	 * @since 1.0.0
	 */
	public

	function __construct($post_type = 'event-plugun', $singular = '', $plural = '', $args = array() , $taxonomies = array())
	{
		$this->post_type = $post_type;
		$this->singular = $singular;
		$this->plural = $plural;
		$this->args = $args;
		$this->taxonomies = $taxonomies;
		add_action('init', array(
			$this,
			'register_post_type'
		));
		add_action('init', array(
			$this,
			'register_taxonomy'
		));
		if (is_admin()) {
			global $pagenow;
			add_action('admin_menu', array(
				$this,
				'meta_box_setup'
			) , 20);
			add_action('save_post', array(
				$this,
				'meta_box_save'
			));
			add_filter('enter_title_here', array(
				$this,
				'enter_title_here'
			));
			add_filter('post_updated_messages', array(
				$this,
				'updated_messages'
			));
			if ($pagenow == 'edit.php' && isset($_GET['post_type']) && esc_attr($_GET['post_type']) == $this->post_type) {
				add_filter('manage_edit-' . $this->post_type . '_columns', array(
					$this,
					'register_custom_column_headings'
				) , 10, 1);
				add_action('manage_posts_custom_column', array(
					$this,
					'register_custom_columns'
				) , 10, 2);
			}
		}

		add_action('after_setup_theme', array(
			$this,
			'ensure_post_thumbnails_support'
		));
		add_action('after_theme_setup', array(
			$this,
			'register_image_sizes'
		));
		add_action('admin_menu', array(
			$this,
			'my_remove_meta_boxes'
		));
	} // End __construct()
	public

	function my_remove_meta_boxes()
	{
		remove_meta_box('postexcerpt', 'plugun-event', 'normal');
		remove_meta_box('trackbacksdiv', 'plugun-event', 'normal');
		remove_meta_box('postcustom', 'plugun-event', 'normal');
		remove_meta_box('commentstatusdiv', 'plugun-event', 'normal');
		remove_meta_box('commentsdiv', 'plugun-event', 'normal');
		remove_meta_box('revisionsdiv', 'plugun-event', 'normal');
		remove_meta_box('authordiv', 'plugun-event', 'normal');
		remove_meta_box('sqpt-meta-tags', 'plugun-event', 'normal');
		remove_meta_box('pageparentdiv', 'plugun-event', 'normal');
	}

	public

	function event_date_get_meta($value)
	{
		global $post;
		$field = get_post_meta($post->ID, $value, true);
		if (!empty($field)) {
			return is_array($field) ? stripslashes_deep($field) : stripslashes(wp_kses_decode_entities($field));
		}
		else {
			return false;
		}
	}

	/**
	 * Register the post type.
	 * @access public
	 * @return void
	 */
	public

	function register_post_type()
	{
		$labels = array(
			'name' => sprintf(_x('%s', 'post type general name', 'event-plugun') , $this->plural) ,
			'singular_name' => sprintf(_x('%s', 'post type singular name', 'event-plugun') , $this->singular) ,
			'add_new' => _x('Add New', $this->post_type, 'event-plugun') ,
			'add_new_item' => sprintf(__('Add New %s', 'event-plugun') , $this->singular) ,
			'edit_item' => sprintf(__('Edit %s', 'event-plugun') , $this->singular) ,
			'new_item' => sprintf(__('New %s', 'event-plugun') , $this->singular) ,
			'all_items' => sprintf(__('%s', 'event-plugun') , $this->plural) ,
			'view_item' => sprintf(__('View %s', 'event-plugun') , $this->singular) ,
			'search_items' => sprintf(__('Search %a', 'event-plugun') , $this->plural) ,
			'not_found' => sprintf(__('No %s Found', 'event-plugun') , $this->plural) ,
			'not_found_in_trash' => sprintf(__('No %s Found In Trash', 'event-plugun') , $this->plural) ,
			'parent_item_colon' => '',
			'menu_name' => $this->plural
		);
		$single_slug = apply_filters('event-plugun_single_slug', _x(sanitize_title_with_dashes($this->singular) , 'single post url slug', 'event-plugun'));
		$archive_slug = apply_filters('event-plugun_archive_slug', _x(sanitize_title_with_dashes($this->plural) , 'post archive url slug', 'event-plugun'));
		$defaults = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_menu' => false,
			'query_var' => true,
			'rewrite' => array(
				'slug' => $single_slug
			) ,
			'capability_type' => 'post',
			'has_archive' => $archive_slug,
			'hierarchical' => false,
			'supports' => array(
				'title',
				'editor',
				'excerpt',
				'thumbnail'
			) ,
			'menu_position' => 5,
			'menu_icon' => 'dashicons-smiley'
		);
		$args = wp_parse_args($this->args, $defaults);
		register_post_type($this->post_type, $args);
	} // End register_post_type()
	/**
	 * Register the "thing-category" taxonomy.
	 * @access public
	 * @since  1.3.0
	 * @return void
	 */
	public

	function register_taxonomy()
	{
		$this->taxonomies['plugun-event-category'] = new Events_Management_by_Dawsun_Taxonomy($post_type = 'plugun-event', $token = 'plugun-event-category', $singular = 'Event Category', $plural = 'Event Categories', $args = array(
			'show_in_menu' => 'event-plugun'
		)); // Leave arguments empty, to use the default arguments.
		$this->taxonomies['plugun-event-category']->register();
	} // End register_taxonomy()
	/**
	 * Add custom columns for the "manage" screen of this post type.
	 * @access public
	 * @param string $column_name
	 * @param int $id
	 * @since  1.0.0
	 * @return void
	 */
	public

	function register_custom_columns($column_name, $id)
	{
		global $post;
		switch ($column_name) {
		case 'image':
			echo $this->get_image($id, 40);
			break;

		default:
			break;
		}
	} // End register_custom_columns()
	/**
	 * Add custom column headings for the "manage" screen of this post type.
	 * @access public
	 * @param array $defaults
	 * @since  1.0.0
	 * @return void
	 */
	public

	function register_custom_column_headings($defaults)
	{
		$new_columns = array(
			'image' => __('Image', 'event-plugun')
		);
		$last_item = array();
		if (isset($defaults['date'])) {
			unset($defaults['date']);
		}

		if (count($defaults) > 2) {
			$last_item = array_slice($defaults, -1);
			array_pop($defaults);
		}

		$defaults = array_merge($defaults, $new_columns);
		if (is_array($last_item) && 0 < count($last_item)) {
			foreach($last_item as $k => $v) {
				$defaults[$k] = $v;
				break;
			}
		}

		return $defaults;
	} // End register_custom_column_headings()
	/**
	 * Update messages for the post type admin.
	 * @since  1.0.0
	 * @param  array $messages Array of messages for all post types.
	 * @return array           Modified array.
	 */
	public

	function updated_messages($messages)
	{
		global $post, $post_ID;
		$messages[$this->post_type] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf(__('%3$s updated. %sView %4$s%s', 'event-plugun') , '<a href="' . esc_url(get_permalink($post_ID)) . '">', '</a>', $this->singular, strtolower($this->singular)) ,
			2 => __('Custom field updated.', 'event-plugun') ,
			3 => __('Custom field deleted.', 'event-plugun') ,
			4 => sprintf(__('%s updated.', 'event-plugun') , $this->singular) ,
			/* translators: %s: date and time of the revision */
			5 => isset($_GET['revision']) ? sprintf(__('%s restored to revision from %s', 'event-plugun') , $this->singular, wp_post_revision_title((int)$_GET['revision'], false)) : false,
			6 => sprintf(__('%1$s published. %3$sView %2$s%4$s', 'event-plugun') , $this->singular, strtolower($this->singular) , '<a href="' . esc_url(get_permalink($post_ID)) . '">', '</a>') ,
			7 => sprintf(__('%s saved.', 'event-plugun') , $this->singular) ,
			8 => sprintf(__('%s submitted. %sPreview %s%s', 'event-plugun') , $this->singular, strtolower($this->singular) , '<a target="_blank" href="' . esc_url(add_query_arg('preview', 'true', get_permalink($post_ID))) . '">', '</a>') ,
			9 => sprintf(__('%s scheduled for: %1$s. %2$sPreview %s%3$s', 'event-plugun') , $this->singular, strtolower($this->singular) ,

			// translators: Publish box date format, see http://php.net/date

			'<strong>' . date_i18n(__('M j, Y @ G:i') , strtotime($post->post_date)) . '</strong>', '<a target="_blank" href="' . esc_url(get_permalink($post_ID)) . '">', '</a>') ,
			10 => sprintf(__('%s draft updated. %sPreview %s%s', 'event-plugun') , $this->singular, strtolower($this->singular) , '<a target="_blank" href="' . esc_url(add_query_arg('preview', 'true', get_permalink($post_ID))) . '">', '</a>')
		);
		return $messages;
	} // End updated_messages()
	/**
	 * Setup the meta box.
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public

	function meta_box_setup()
	{
		$meta_boxes = array(

			// for events

			array(
				'value' => 'event_date',
				'name' => 'Event Date',
				'callback' => array(
					$this,
					'event_date_html'
				) ,
				'post_type' => 'plugun-event',
				'location' => 'side',
				'priority' => 'core'
			) ,
			array(
				'value' => 'event_location',
				'name' => 'Event Location',
				'callback' => array(
					$this,
					'event_location_html'
				) ,
				'post_type' => 'plugun-event',
				'location' => 'normal',
				'priority' => 'default'
			) ,

			// for tickets

			array(
				'value' => 'event_date',
				'name' => 'Event Date',
				'callback' => array(
					$this,
					'event_date_html'
				) ,
				'post_type' => 'plugun-ticket',
				'location' => 'side',
				'priority' => 'core'
			) ,
			array(
				'value' => 'event_date',
				'name' => 'Event Date',
				'callback' => array(
					$this,
					'event_date_html'
				) ,
				'post_type' => 'plugun-ticket',
				'location' => 'side',
				'priority' => 'core'
			)
		);
		foreach($meta_boxes as $key => $value) {
			add_meta_box($value['value'], __($value['name'], 'plugun-event') , $value['callback'], $value['post_type'], $value['location'], $value['priority']);
		}
	} // End meta_box_setup()
	public

	function event_location_html($post)
	{
		wp_nonce_field('_event_location_nonce', 'event_location_nonce');
?>

	 
		<input class="full-width" type="text" name="event_location" id="event_location" value="<?php
		echo $this->event_date_get_meta('event_location');
?>">
	 <?php
	}

	/**
	 * The contents of our meta box.
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public

	function event_date_html($post)
	{
		wp_nonce_field('_event_date_nonce', 'event_date_nonce');
?>

		<p>
			<label for="event_date_start"><span class="dashicons color-success dashicons-calendar-alt"></span> <?php
		_e('Start Date & Time', 'event_date');
?></label><br />
			<input type="text" class="datepicker-input" name="event_date_start" id="event_date_start" value="<?php
		echo $this->event_date_get_meta('event_date_start');
?>">
		</p>	<p>
			<label for="event_date_date_end"><span class="dashicons color-warn dashicons-calendar-alt"></span> <?php
		_e('End Date & Time', 'event_date');
?></label><br />
			<input type="text" class="datepicker-input" name="event_date_end" id="event_date_end" value="<?php
		echo $this->event_date_get_meta('event_date_end');
?>">
		</p><?php
	}

	/**
	 * Save meta box fields.
	 * @access public
	 * @since  1.0.0
	 * @param int $post_id
	 * @return int $post_id
	 */
	public

	function meta_box_save($post_id)
	{
		global $post, $messages;
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
		if (!isset($_POST['event_date_nonce']) || !wp_verify_nonce($_POST['event_date_nonce'], '_event_date_nonce')) return;
		if (!isset($_POST['event_location_nonce']) || !wp_verify_nonce($_POST['event_location_nonce'], '_event_location_nonce')) return;
		if (!current_user_can('edit_post', $post_id)) return;

		// save event dates

		if (isset($_POST['event_date_start'])) update_post_meta($post_id, 'event_date_start', esc_attr($_POST['event_date_start']));
		if (isset($_POST['event_date_end'])) update_post_meta($post_id, 'event_date_end', esc_attr($_POST['event_date_end']));

		// save event location

		if (isset($_POST['event_location'])) update_post_meta($post_id, 'event_location', esc_attr($_POST['event_location']));
	} // End meta_box_save()

	// public function update_meta ($meta = array()){
	// }

	/**
	 * Customise the "Enter title here" text.
	 * @access public
	 * @since  1.0.0
	 * @param string $title
	 * @return void
	 */
	public

	function enter_title_here($title)
	{
		if (get_post_type() == $this->post_type) {
			$title = __('Enter the event title here', 'event-plugun');
		}

		return $title;
	} // End enter_title_here()
	/**
	 * Get the settings for the custom fields.
	 * @access public
	 * @since  1.0.0
	 * @return array
	 */
	public

	function get_custom_fields_settings()
	{
		$fields = array();
		$fields['url'] = array(
			'name' => __('URL', 'event-plugun') ,
			'description' => __('Enter a URL that applies to this thing (for example: http://domain.com/).', 'event-plugun') ,
			'type' => 'url',
			'default' => '',
			'section' => 'info'
		);
		return apply_filters('event-plugun_custom_fields_settings', $fields);
	} // End get_custom_fields_settings()
	/**
	 * Get the image for the given ID.
	 * @param  int 				$id   Post ID.
	 * @param  mixed $size Image dimension. (default: "thing-thumbnail")
	 * @since  1.0.0
	 * @return string       	<img> tag.
	 */
	protected
	function get_image($id, $size = 'thing-thumbnail')
	{
		$response = '';
		if (has_post_thumbnail($id)) {

			// If not a string or an array, and not an integer, default to 150x9999.

			if ((is_int($size) || (0 < intval($size))) && !is_array($size)) {
				$size = array(
					intval($size) ,
					intval($size)
				);
			}
			elseif (!is_string($size) && !is_array($size)) {
				$size = array(
					150,
					9999
				);
			}

			$response = get_the_post_thumbnail(intval($id) , $size);
		}

		return $response;
	} // End get_image()
	/**
	 * Register image sizes.
	 * @access public
	 * @since  1.0.0
	 */
	public

	function register_image_sizes()
	{
		if (function_exists('add_image_size')) {
			add_image_size($this->post_type . '-thumbnail', 150, 9999); // 150 pixels wide (and unlimited height)
		}
	} // End register_image_sizes()
	/**
	 * Run on activation.
	 * @access public
	 * @since 1.0.0
	 */
	public

	function activation()
	{
		$this->flush_rewrite_rules();
	} // End activation()
	/**
	 * Flush the rewrite rules
	 * @access public
	 * @since 1.0.0
	 */
	private
	function flush_rewrite_rules()
	{
		$this->register_post_type();
		flush_rewrite_rules();
	} // End flush_rewrite_rules()
	/**
	 * Ensure that "post-thumbnails" support is available for those themes that don't register it.
	 * @access public
	 * @since  1.0.0
	 */
	public

	function ensure_post_thumbnails_support()
	{
		if (!current_theme_supports('post-thumbnails')) {
			add_theme_support('post-thumbnails');
		}
	} // End ensure_post_thumbnails_support()
} // End Class