<?php
if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly.

/**
 * Starter Plugin Post Type Class
 *
 * All functionality pertaining to post types in Starter Plugin.
 *
 * @package WordPress
 * @subpackage Events_Management_by_Dawsun
 * @category Plugin
 * @author Matty
 * @since 1.0.0
 */
class Events_Management_by_Dawsun_Post_Type
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
    
    
    protected $warnings = array();
    
    
    
    /**
     * Constructor function.
     * @access public
     * @since 1.0.0
     */
    public function __construct($post_type = 'event-plugun', $singular = '', $plural = '', $args = array(), $taxonomies = array())
    {
        $this->post_type  = $post_type;
        $this->singular   = $singular;
        $this->plural     = $plural;
        $this->args       = $args;
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
            ), 20);
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
                ), 10, 1);
                add_action('manage_posts_custom_column', array(
                    $this,
                    'register_custom_columns'
                ), 10, 2);
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
    
    
    public function my_remove_meta_boxes()
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
        remove_meta_box('postdivrich', 'plugun-ticket', 'normal');
    }
    
    
    public function get_meta($value, $post_id = null)
    {
        global $post;
        
        
        if ($post_id == null) {
            $post_id = $post->ID;
        }
        
        $field = get_post_meta($post_id, $value, true);
        if (!empty($field)) {
            return is_array($field) ? stripslashes_deep($field) : stripslashes(wp_kses_decode_entities($field));
        } else {
            return false;
        }
    }
    
    
    
    /**
     * Register the post type.
     * @access public
     * @return void
     */
    public function register_post_type()
    {
        $labels = array(
            'name' => sprintf(_x('%s', 'post type general name', 'event-plugun'), $this->plural),
            'singular_name' => sprintf(_x('%s', 'post type singular name', 'event-plugun'), $this->singular),
            'add_new' => _x('Add New', $this->post_type, 'event-plugun'),
            'add_new_item' => sprintf(__('Add New %s', 'event-plugun'), $this->singular),
            'edit_item' => sprintf(__('Edit %s', 'event-plugun'), $this->singular),
            'new_item' => sprintf(__('New %s', 'event-plugun'), $this->singular),
            'all_items' => sprintf(__('%s', 'event-plugun'), $this->plural),
            'view_item' => sprintf(__('View %s', 'event-plugun'), $this->singular),
            'search_items' => sprintf(__('Search %a', 'event-plugun'), $this->plural),
            'not_found' => sprintf(__('No %s Found', 'event-plugun'), $this->plural),
            'not_found_in_trash' => sprintf(__('No %s Found In Trash', 'event-plugun'), $this->plural),
            'parent_item_colon' => '',
            'menu_name' => $this->plural
        );
        
        $single_slug  = apply_filters('event-plugun_single_slug', _x(sanitize_title_with_dashes($this->singular), 'single post url slug', 'event-plugun'));
        $archive_slug = apply_filters('event-plugun_archive_slug', _x(sanitize_title_with_dashes($this->plural), 'post archive url slug', 'event-plugun'));
        
        $defaults = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => false,
            'query_var' => true,
            'rewrite' => array(
                'slug' => $single_slug
            ),
            'capability_type' => 'post',
            'has_archive' => $archive_slug,
            'hierarchical' => false,
            'supports' => array(
                'title',
                'editor',
                // 'excerpt',
                'thumbnail'
            ),
            'menu_position' => 5,
            'menu_icon' => 'dashicons-smiley'
        );
        
        
        if (in_array($this->post_type, array(
            'plugun-ticket',
            'plugun-booking',
            'plugun-api',
            'plugun-template'
            
        ))) {
            unset($defaults['supports'][1]);
            unset($defaults['supports'][2]);
            
            $defaults['publicly_queryable'] = false;
            $defaults['public']             = false;
            
            if ($this->post_type == 'plugun-booking') {
                //$defaults['publicly_queryable'] = false;
                //$defaults['public'] = false;
            }
        }
        
        
        
        $args = wp_parse_args($this->args, $defaults);
        
        register_post_type($this->post_type, $args);
    } // End register_post_type()
    
    
    
    /**
     * Register the "thing-category" taxonomy.
     * @access public
     * @since  1.3.0
     * @return void
     */
    public function register_taxonomy()
    {
        $this->taxonomies['plugun-event-category'] = new Events_Management_by_Dawsun_Taxonomy($post_type = 'plugun-event', $token = 'plugun-event-category', $singular = 'Event Category', $plural = 'Event Categories', $args = array(
            'show_in_menu' => 'event-plugun'
        )); // Leave arguments empty, to use the default arguments.s->taxonomies['plugun-event-category'] = new
        
        $this->taxonomies['plugun-event-tags'] = new Events_Management_by_Dawsun_Taxonomy($post_type = 'plugun-event', $token = 'plugun-event-tags', $singular = 'Event Tag', $plural = 'Event Tags', $args = array(
            'show_in_menu' => 'event-plugun',
            'show_tagcloud' => false,
            'hierarchical' => false
        )); // Leave arguments empty, to use the default arguments.
        
        
        
        $this->taxonomies['plugun-event-category']->register();
        $this->taxonomies['plugun-event-tags']->register();
    } // End register_taxonomy()
    
    /**
     * Add custom columns for the "manage" screen of this post type.
     * @access public
     * @param string $column_name
     * @param int $id
     * @since  1.0.0
     * @return void
     */
    public function register_custom_columns($column_name, $id)
    {
        global $post, $wpdb;
        
        
        // if events
        if ($this->post_type == 'plugun-event') {
            switch ($column_name) {
                case 'tickets':
                    echo $this->event_tickets_content($post->ID);
                    break;
                case 'event_date':
                    echo $this->get_meta('event_date_start');
                    echo ($this->get_meta('event_date_start') != $this->get_meta('event_date_end')) ? ' to ' . $this->get_meta('event_date_end') : "";
                    echo '<br />';
                    
                    $event_time_start = $this->get_meta('event_time_start');
                    $event_time_end   = $this->get_meta('event_time_end');
                    
                    if ($this->get_meta('all_day_event') || (!$event_time_start && !$event_time_end)) {
                        $event_time = "All day event";
                    } else {
                        $event_time = $event_time_start . " - " . $event_time_end . " Daily ";
                    }
                    
                    
                    echo $event_time;
                    break;
                
                case 'event_location':
                    if ($this->get_meta('physical_location') == 1) {
                        echo $this->get_meta('event_location');
                        echo ($this->get_meta('event_city') != '') ? ',<br />' . $this->get_meta('event_city') : "";
                        echo ($this->get_meta('event_postal') != '') ? ',<br />' . $this->get_meta('event_postal') : "";
                        echo ($this->get_meta('event_state') != '') ? ',<br />' . $this->get_meta('event_state') : "";
                        echo ($this->get_meta('event_country') != '') ? ',<br />' . $this->get_meta('event_country') : "";
                    } else {
                        $webinar_url = $this->get_meta('webinar_url');
                        if($webinar_url)
                        echo '<a href="'.$webinar_url.'" target="_blank">Webinar</a>';
                    }
                    break;
                
                case "tickets_availability":
                    $queried_data = $wpdb->get_results("
    SELECT wposts.ID, wposts.post_title
    FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta
    WHERE wposts.ID = wpostmeta.post_id
    AND wpostmeta.meta_key = 'ticket_event'
    AND wpostmeta.meta_value = $id
    AND wposts.post_type = 'plugun-ticket'
    AND wposts.post_status = 'publish'
    ");
                    
                    
                    
                    
                    for ($i = 0, $count = count($queried_data); $i < $count; $i++) {
                        
                        echo '<a href="post.php?post=' . $queried_data[$i]->ID . '&action=edit">' . $queried_data[$i]->post_title . '</a>';
                        $quantity = get_post_meta($queried_data[$i]->ID, "quantity", true);
                        if ($quantity) {
                            $available_quantity = get_dep_lite_ticket_available_quantity($queried_data[$i]->ID);
                            $availablity_text   = $available_quantity; //($available_quantity == -1) ? "unlimited" : $available_quantity;                
                            if ($availablity_text < -1)
                                $availablity_text = 0;
                        } else
                            $availablity_text = "Unlimited";
                        echo " (" . $availablity_text . ")<br> ";
                    }
                    break;
                
                default:
                    
                    break;
            }
        }
        
        // if tickets
        if ($this->post_type == 'plugun-ticket') {
            switch ($column_name) {
                case 'event':
                    echo $this->get_post_title_content($this->get_meta('ticket_event'));
                    break;
                case 'price':
                    echo ($this->get_meta('ticket_price') != '') ? EpLiteCurrency::get_format($this->get_meta('ticket_price')) : "Free";
                    break;
                
                case 'quantity':
                    echo ($this->get_meta('quantity') != '') ? $this->get_meta('quantity') : "Unlimited";
                    break;
                
                case 'date_range':
                    
                    $date_range_from = $this->get_meta('dates_for_selling_from');
                    $date_range_to   = $this->get_meta('dates_for_selling_to');
                    
                    if ($this->get_meta('ticket_availability') || (!$date_range_from && !$date_range_to)) {
                        $event_id = get_post_meta($id, "ticket_event", true);
                        
                        $date_range_from = get_post_meta($event_id, "event_date_start", true);
                        $date_range_to   = get_post_meta($event_id, "event_date_end", true);
                    }
                    
                    
                    
                    echo $date_range_from . " to " . $date_range_to;
                    
                    break;
                
                case "tickets_availability":
                    
                    echo $this->get_meta('quantity') ? get_dep_lite_ticket_available_quantity($id) : "Unlimited";
                    break;
                
                default:
                    break;
            }
        }
        
        // if booking
        if ($this->post_type == 'plugun-booking') {
            
            $booking = get_post($id);
            $event_id   = get_post_meta($id, "event_id", true);
            $event_name = get_post_meta($id, "event_name", true);
            
            switch ($column_name) {
                case 'booking_event':
                    //echo $this->get_post_title_content($this->get_meta('booking_event'));
                    
                    echo '<a href="post.php?action=edit&post=' . $event_id . '" title="Edit ' . $event_name . '">' . $event_name . '</a>';
                    break;
                
                case 'booking_ticket':
                    
                    $tickets = get_post_meta($id, "tickets", true);
                    if (is_array($tickets)) {
                        foreach ($tickets as $ticket_id => $qty) {
                            $ticket = get_post($ticket_id);
                            echo '<div><a href="post.php?action=edit&post=' . $ticket_id . '" title="Edit ' . $ticket->post_title . '">' . $ticket->post_title . '</a></div>';
                        }
                        
                    }
                    
                    break;
                case 'amount':
                    echo ($this->get_meta('amount') != '') ? EpLiteCurrency::get_format($this->get_meta('amount')) : "Free";
                    break;
                
                case 'seats':
                    // echo ($this->get_meta('seats') != '') ? $this->get_meta('seats') : "";
                    //echo get_post_meta($id, "seats", true);

                         $query = "SELECT SUM(IFNULL(quantity, 0)) FROM wp_posts booking 
                            INNER JOIN wp_postmeta booking_meta ON booking.ID = booking_meta.post_id
                            INNER JOIN wp_plugun_checkin checkins ON booking.ID = checkins.booking_id 
                            WHERE booking_meta.meta_key = 'event_id' AND booking_meta.meta_value = '{$event_id}'
                            AND booking.ID = '{$post->ID}'";
                           echo $wpdb->get_var($query);

                    break;
                
                case 'status':
                    
                    $booking_status = ($this->get_meta('booking_status')) ? $this->get_meta('booking_status') : 'Pending';
                    echo ucwords($booking_status);
                    break;
                
                
                
                
                default:
                    break;
            }
        }
    } // End register_custom_columns()
    
    /**
     * Add custom column headings for the "manage" screen of this post type.
     * @access public
     * @param array $defaults
     * @since  1.0.0
     * @return void
     */
    public function register_custom_column_headings($defaults)
    {
        $new_columns = array();
        
        
        
        switch ($this->post_type) {
            case 'plugun-event':
                $new_columns = array(
                    'event_date' => '<span class="dashicons dashicons-calendar-alt"></span> ' . __('Date Range', 'event-plugun'),
                    'event_location' => '<span class="dashicons dashicons-location-alt"></span> ' . __('Location', 'event-plugun'),
                    'tickets' => '<span class="dashicons dashicons-tickets-alt"></span> ' . __('Tickets', 'event-plugun'),
                    
                    'tickets_availability' => '<span class="dashicons dashicons-cart"></span> ' . __('Tickets Availablity', 'event-plugun')
                );
                break;
            
            case 'plugun-ticket':
                $new_columns = array(
                    'event' => '<span class="dashicons dashicons-media-text"></span> ' . __('Event', 'event-plugun'),
                    'date_range' => '<span class="dashicons dashicons-calendar-alt"></span> ' . __('Date Range', 'event-plugun'),
                    
                    
                    'price' => '<span class="dashicons dashicons-money"></span> ' . __('Price', 'event-plugun'),
                    'quantity' => '<span class="dashicons dashicons-editor-justify"></span> ' . __('Quantity', 'event-plugun'),
                    'tickets_availability' => '<span class="dashicons dashicons-cart"></span> ' . __('Tickets Availablity', 'event-plugun')
                );
                break;
            
            case 'plugun-booking':
                $new_columns = array(
                    'booking_event' => '<span class="dashicons dashicons-media-text"></span> ' . __('Event', 'event-plugun'),
                    'booking_ticket' => '<span class="dashicons dashicons-calendar-alt"></span> ' . __('Titcket', 'event-plugun'),
                    
                    
                    'amount' => '<span class="dashicons dashicons-money"></span> ' . __('Amount', 'event-plugun'),
                    'seats' => '<span class="dashicons dashicons-editor-justify"></span> ' . __('Seats', 'event-plugun'),
                    'status' => '<span class="dashicons dashicons-cart"></span> ' . __('Status', 'event-plugun')
                    
                );
                break;
            
            default:
                
                break;
        }
        
        
        
        
        
        $last_item = array();
        
        if (isset($defaults['date'])) {
            unset($defaults['date']);
        }
        
        if (count($defaults) > 2) {
            $last_item = array_slice($defaults, -1);
            
            // array_pop($defaults);
        }
        $defaults = array_merge($defaults, $new_columns);
        
        if (is_array($last_item) && 0 < count($last_item)) {
            foreach ($last_item as $k => $v) {
                $defaults[$k] = $v;
                break;
            }
        }
        
        return $defaults;
    } // End register_custom_column_headings()
    
    
    
    public function event_tickets_content($event_id)
    {
        $ticket_content = '';
        
        global $wpdb;
        
        $queried_data = $wpdb->get_results("
    SELECT wposts.ID, wposts.post_title
    FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta
    WHERE wposts.ID = wpostmeta.post_id
    AND wpostmeta.meta_key = 'ticket_event'
    AND wpostmeta.meta_value = $event_id
    AND wposts.post_type = 'plugun-ticket'
    AND wposts.post_status = 'publish'
    ");
        
        
        
        if ($queried_data) {
            foreach ($queried_data as $a) {
                $price = $this->get_meta('ticket_price', $a->ID);
                
                if ($price != '' || $price != 0) {
                    $price = EpLiteCurrency::get_format($price);
                } else {
                    $price = 'Free';
                }
                
                $ticket_content .= '<a href="post.php?post=' . $a->ID . '&action=edit">
                ' . $a->post_title . ' - ' . $price . '</a>
                <br />';
            }
        } else {
            $ticket_content .= '<p>no tickets</p>';
        }
        
        
        
        
        
        return $ticket_content;
    }
    
    
    public function get_post_title_content($post_id)
    {
        $post_content = '';
        
        global $wpdb;
        
        $queried_data = $wpdb->get_results("
            SELECT ID, post_title
            FROM $wpdb->posts
            WHERE ID = '$post_id'
            ");
        
        
        
        if ($queried_data) {
            foreach ($queried_data as $a) {
                $post_content .= '<a href="post.php?post=' . $a->ID . '&action=edit">
                ' . $a->post_title . '</a>
                <br />';
            }
        } else {
            $post_content .= '<p>no event</p>';
        }
        
        
        
        
        
        return $post_content;
    }
    
    /**
     * Update messages for the post type admin.
     * @since  1.0.0
     * @param  array $messages Array of messages for all post types.
     * @return array           Modified array.
     */
    public function updated_messages($messages)
    {
        global $post, $post_ID;
        
        $messages[$this->post_type] = array(
            0 => '', // Unused. Messages start at index 1.
            1 => sprintf(__('%3$s updated. %sView %4$s%s', 'event-plugun'), '<a href="' . esc_url(get_permalink($post_ID)) . '">', '</a>', $this->singular, strtolower($this->singular)),
            2 => __('Custom field updated.', 'event-plugun'),
            3 => __('Custom field deleted.', 'event-plugun'),
            4 => sprintf(__('%s updated.', 'event-plugun'), $this->singular),
            /* translators: %s: date and time of the revision */
            5 => isset($_GET['revision']) ? sprintf(__('%s restored to revision from %s', 'event-plugun'), $this->singular, wp_post_revision_title((int) $_GET['revision'], false)) : false,
            6 => sprintf(__('%1$s published. %3$sView %2$s%4$s', 'event-plugun'), $this->singular, strtolower($this->singular), '<a href="' . esc_url(get_permalink($post_ID)) . '">', '</a>'),
            7 => sprintf(__('%s saved.', 'event-plugun'), $this->singular),
            8 => sprintf(__('%s submitted. %sPreview %s%s', 'event-plugun'), $this->singular, strtolower($this->singular), '<a target="_blank" href="' . esc_url(add_query_arg('preview', 'true', get_permalink($post_ID))) . '">', '</a>'),
            9 => sprintf(__('%s scheduled for: %1$s. %2$sPreview %s%3$s', 'event-plugun'), $this->singular, strtolower($this->singular), 
            // translators: Publish box date format, see http://php.net/date
                '<strong>' . date_i18n(__('M j, Y @ G:i'), strtotime($post->post_date)) . '</strong>', '<a target="_blank" href="' . esc_url(get_permalink($post_ID)) . '">', '</a>'),
            10 => sprintf(__('%s draft updated. %sPreview %s%s', 'event-plugun'), $this->singular, strtolower($this->singular), '<a target="_blank" href="' . esc_url(add_query_arg('preview', 'true', get_permalink($post_ID))) . '">', '</a>')
        );
        
        return $messages;
    } // End updated_messages()
    
    /**
     * Setup the meta box.
     * @access public
     * @since  1.0.0
     * @return void
     */
    public function meta_box_setup()
    {
        $meta_boxes = array(
            
            
            // for events
            array(
                'value' => 'event_date',
                'name' => 'Event Date & Time',
                'callback' => array(
                    $this,
                    'event_date_html'
                ),
                'post_type' => 'plugun-event',
                'location' => 'side',
                'priority' => 'core'
            ),
            
            array(
                'value' => 'template_subject',
                'name' => 'Email Template Subject',
                'callback' => array(
                    $this,
                    'enter_email_subject'
                ),
                'post_type' => 'plugun-template',
                'location' => 'advanced',
                'priority' => 'high'
            ),
            
            array(
                'value' => 'template_params',
                'name' => 'Email Template Parameters',
                'callback' => array(
                    $this,
                    'template_params'
                ),
                'post_type' => 'plugun-template',
                'location' => 'normal',
                'priority' => 'core'
            ),
            
            array(
                'value' => 'event_location',
                'name' => 'Event Location',
                'callback' => array(
                    $this,
                    'event_location_html'
                ),
                'post_type' => 'plugun-event',
                'location' => 'normal',
                'priority' => 'default'
            ),
            array(
                'value' => 'event_tickets',
                'name' => 'Event Tickets/Bookings',
                'callback' => array(
                    $this,
                    'event_tickets_html'
                ),
                'post_type' => 'plugun-event',
                'location' => 'normal',
                'priority' => 'default'
            ),
            
            // for tickets
            array(
                'value' => 'ticket_limits',
                'name' => 'Ticket Limits',
                'callback' => array(
                    $this,
                    'ticket_limits_html'
                ),
                'post_type' => 'plugun-ticket',
                'location' => 'normal',
                'priority' => 'high'
            ),
            
            array(
                'value' => 'ticket_event',
                'name' => 'Event',
                'callback' => array(
                    $this,
                    'ticket_event_html'
                ),
                'post_type' => 'plugun-ticket',
                'location' => 'side',
                'priority' => 'core'
            ),
            array(
                'value' => 'ticket_pricing',
                'name' => 'Ticket Pricing',
                'callback' => array(
                    $this,
                    'ticket_pricing_html'
                ),
                'post_type' => 'plugun-ticket',
                'location' => 'side',
                'priority' => 'default'
            ),
            
            // for bookings
            array(
                'value' => 'booking_event_details',
                'name' => 'Booking Event Details',
                'callback' => array(
                    $this,
                    'booking_event_details_html'
                ),
                'post_type' => 'plugun-booking',
                'location' => 'normal',
                'priority' => 'high'
            ),
            array(
                'value' => 'booking_status',
                'name' => 'Booking Status',
                'callback' => array(
                    $this,
                    'booking_status_html'
                ),
                'post_type' => 'plugun-booking',
                'location' => 'side',
                'priority' => 'core'
            ),
            
            array(
                'value' => 'api_settings',
                'name' => 'Api Settings',
                'callback' => array(
                    $this,
                    'api_settings'
                ),
                'post_type' => 'plugun-api',
                'location' => 'normal',
                'priority' => 'high'
            )
            
            
        );
        
        foreach ($meta_boxes as $key => $value) {
            add_meta_box($value['value'], __($value['name'], 'plugun-event'), $value['callback'], $value['post_type'], $value['location'], $value['priority']);
        }
    } // End meta_box_setup()
    
    
    public function api_settings($post)
    {
        wp_nonce_field('_api_settings_nonce', 'event_tickets_nonce');
        
        
        $events = get_posts(array(
            "post_type" => "plugun-event",
            "posts_per_page" => -1
        ));
        
?>
        <style>
            table.widefat em{
                color: #aaa;
            }
        </style>    
        <table width="100%" class="wp-list-table widefat fixed striped posts">
           <tr>
        
        <td  width="25%">
        <p>
        <label for="api_status"><?php
        _e('API Status', 'event-plugun');
?></label>
        
        </td>
        <td>
        <select name="api_status">
            <option value="1" <?php
        if (in_array(get_post_meta($post->ID, "api_status", true), array(
            "1",
            "",
            false
        )))
            echo ' selected="selected" ';
?> >Active</option>
            <option value="0" <?php
        if (get_post_meta($post->ID, "api_status", true) == "0")
            echo ' selected="selected"  ';
?>>Inctive</option>
        </select>        
        </p>
        </td>
        </tr> 
        <tr>
        <td>
        <label for="event_list"><?php
        _e('Events', 'event-plugun');
?></label><br>
        <em>These Events will be attached to the API. Related data about these events can be accessed through this API.</em>
        </td>
        <td>
        <div style="border: 1px solid #ddd;height:100px; overflow-y: scroll; padding: 10px; " >
        <?php
        
        $selected_events = get_post_meta($post->ID, "events", true);
        
        if (!is_array($selected_events))
            $selected_events = array();
        
        for ($i = 0, $count = count($events); $i < $count; $i++) {
?>
                <!--<option value="<?php
            echo $events[$i]->ID;
?>" <?php
            if ($events[$i]->ID == get_post_meta($post->ID, "event_id", true))
                echo ' selected="selected" ';
?> 
                        ><?php
            echo $events[$i]->post_title;
?></option>-->
            <input type="checkbox" name="events[]" value="<?php
            echo $events[$i]->ID;
?>" <?php
            if (in_array($events[$i]->ID, $selected_events))
                echo ' checked="checked" ';
?> /><?php
            echo $events[$i]->post_title;
?><br>            
        <?php
        }
?>
        </div>
        </p>
        </td>
        </tr>
        <tr>
        <td>
        <p>
        <label for="api_url"><?php
        _e('API URL', 'event-plugun');
?></label><br>
        </td>
        <td>
        <?php
        echo site_url();
?>/
        </p>
        </td>
        </tr>
        <tr>
        <td>
        <p>
        <label for="api_key"><?php
        _e('API Key', 'event-plugun');
        $api_key = dep_pro_get_api_key($post->ID);
?></label>
         </td>
         <td>
        <input type="hidden" name="api_key" readonly="readonly" value="<?php
        echo $api_key;
?>"><?php
        echo $api_key;
?>         
        </p>
        </td>
        </tr>
        
        <tr>
        <td>
        <p>
        <label for="sms_enabled"><?php
        _e('Enalbe SMS Notification ? ', 'event-plugun');
?></label><br>
        <em>SMS will be sent through mobile App</em>

        </td>
        <td>
        <select name="sms_enabled">
            <option value="0" <?php
        if (!get_post_meta($post->ID, "sms_enabled", true))
            echo ' selected="selected"  ';
?>> No </option>
            <option value="1" <?php
        if (get_post_meta($post->ID, "sms_enabled", true) == "1")
            echo ' selected="selected" ';
?> > Yes </option>
        </select>        
        <BR>
        
        </p>
        </td>
        </tr>
        <tr>
        <td>

        <p>
        <label for="api_qr_code"><?php
        _e('API QR code', 'event-plugun');
?></label><br>
        <em>This QR image will be used to login you in your mobile App account</em>
        </td>
        <td>
        <img src="<?php
        echo site_url();
?>/?api_qr_code=<?php
        echo $post->ID;
?>">
        </p>

        </td>
        </tr>
        </table>


<?php
    }
    
    
    
    public function event_tickets_html($post)
    {
        wp_nonce_field('_event_tickets_nonce', 'event_tickets_nonce');
?>


        <input type="checkbox" name="no_tickets" id="no_tickets" value="1" <?php
        echo ($this->get_meta('no_tickets') === '1') ? 'checked' : '';
?>>
                <label for="no_tickets"><?php
        _e('No Tickets/Bookings', 'event-plugun');
?></label> </p>

<div id="if_no_tickets" style="display:nones;">

  <div class="table-responsive">
     <table class="table table-bordered">
        <thead>
           <tr>
              <th>Ticket</th>
              <th>Price</th>
              <th>Min/Max</th>
              <th>Start/End</th>
              <th>Space</th>
              <th>Booked</th>
           </tr>
        </thead>
        <tbody class="tickets_body">
          <?php
        
        global $wpdb;
        
        $queried_data = $wpdb->get_results("
          SELECT wposts.ID, wposts.post_title
          FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta
          WHERE wposts.ID = wpostmeta.post_id
          AND wpostmeta.meta_key = 'ticket_event'
          AND wpostmeta.meta_value = $post->ID
          AND wposts.post_type = 'plugun-ticket'
           AND wposts.post_status = 'publish'
          ");
        
        
        
        if ($queried_data) {
            foreach ($queried_data as $a) {
                $price = $this->get_meta('ticket_price', $a->ID);
                
                
                
                if ($price != '' || $price != 0) {
                    $price = '$' . $price;
                } else {
                    $price = 'Free';
                }
                
                
                
                $hidden_html = '<div style="display:none;">
                  <input class="it-id" name="ticket_id[]" value="' . $a->ID . '">
                  <input class="it-name" name="ticket_name[]" value="' . $a->post_title . '">
                  <input class="it-price" name="tickets_price[]" value="' . $this->get_meta('ticket_price', $a->ID) . '">
                  <input class="it-min" name="ticket_min[]" value="' . $this->get_meta('min_tickets', $a->ID) . '">
                  <input class="it-max" name="ticket_max[]" value="' . $this->get_meta('max_tickets', $a->ID) . '">
                  <input class="it-from-date" name="ticket_from_date[]" value="' . $this->get_meta('dates_for_selling_from', $a->ID) . '">
                  <input class="it-to-date" name="ticket_to_date[]" value="' . $this->get_meta('dates_for_selling_to', $a->ID) . '">
                  <input class="it-to-time" name="ticket_to_time[]" value="' . $this->get_meta('dates_for_selling_to_time', $a->ID) . '">
                  <input class="it-from-time" name="ticket_from_time[]" value="' . $this->get_meta('dates_for_selling_from_time', $a->ID) . '">
                  <input class="it-spaces" name="ticket_spaces[]" value="' . $this->get_meta('quantity', $a->ID) . '">

                  <input class="it-checkin-from-date" name="tickets_dates_for_checkin_from[]" value="' . $this->get_meta('dates_for_checkin_from', $a->ID) . '">
                  <input class="it-checkin-to-date" name="tickets_dates_for_checkin_to[]" value="' . $this->get_meta('dates_for_checkin_to', $a->ID) . '">
                  <input class="it-checkin-from-time" name="tickets_times_for_checkin_from[]" value="' . $this->get_meta('times_for_checkin_from', $a->ID) . '">
                  <input class="it-checkin-to-time" name="tickets_times_for_checkin_to[]" value="' . $this->get_meta('times_for_checkin_to', $a->ID) . '">
                  <input class="it-max-checkins" name="max_tickets_checkins[]" value="' . $this->get_meta('max_ticket_checkins', $a->ID) . '">
                  </div>';
                
                echo '
                  <tr id="ticket_' . $a->ID . '">
                  <th scope="row">
                  <div style="display:none;" >
                  ' . $hidden_html . '
                  </div>
                  ' . $a->post_title . '<br /><a class="edit_ticket" ticket_id="' . $a->ID . '"  href="#">Edit</a></th>
                  <td>' . $price . '</td>
                  <td>' . $this->get_meta('min_tickets', $a->ID) . '/' . $this->get_meta('max_tickets', $a->ID) . '</td>
                  <td>' . $this->get_meta('dates_for_selling_from', $a->ID) . ' ' . $this->get_meta('dates_for_selling_from_time', $a->ID) . '<br>' . $this->get_meta('dates_for_selling_to', $a->ID) . ' ' . $this->get_meta('dates_for_selling_to_time', $a->ID) . '</td>
                  <td>' . $this->get_meta('quantity', $a->ID) . '</td>
                  <td>' . get_deplite_ticket_used_quantity($a->ID) . '</td>
                  </tr>

                  ';
            }
        } else {
            echo '<p>no tickets</p>';
        }
?>

        </tbody>
     </table>
  </div>

  <button class="button button-secondary button-small" id="add_new_ticket">Add new ticket</button>


    <div class="new_ticket" style="display:none;">
<input type="hidden" id="ticket_id" value="0">
         <table>
            <tbody>
                <tr>
                  <td><label for="ticket_name">Ticket Name:</label></td>
                  <td><input type="text" id="ticket_name"></td>
                </tr>
                <tr>
                  <td><label for="ticket_description">Description:</label></td>
                  <td><textarea name="ticket_description" id="ticket_description"></textarea></td>
                </tr>
                <tr>
                  <td><label for="ticket_price">Price:</label></td>
                  <td><input type="text" id="tickets_price"></td>
                </tr>
                <tr>
                  <td><label for="ticket_spaces">Spaces(Seats):</label></td>
                  <td><input type="text" id="ticket_spaces"></td>
                </tr>
                <tr>
                  <td><label for="ticket_min">Minimum:</label></td>
                  <td><input type="text" id="ticket_min"> per booking</td>
                </tr>
                <tr>
                  <td><label for="ticket_max">Maximum:</label></td>
                  <td><input type="text" id="ticket_max"> per booking</td>
                </tr>
                <tr>
                  <td><label for="ticket_available_from">Availablity From:</label></td>
                  <td><input type="text" id="ticket_available_from" > at <input type="text" class="timepicker" id="ticket_available_from_time"></td>
                </tr>
                <tr>
                  <td><label for="ticket_available_to">Availablity To:</label></td>
                  <td><input type="text" id="ticket_available_to" class=""> at <input type="text" class="timepicker" id="ticket_available_to_time"></td>
                </tr>

                <tr>
                  <td><label for="dates_for_checkin_from">Checkin From:</label></td>
                  <td><input type="text" id="tickets_dates_for_checkin_from" > at <input type="text" class="timepicker" id="tickets_times_for_checkin_from"></td>
                </tr>
                <tr>
                  <td><label for="tickets_dates_for_checkin_to">Checkin To:</label></td>
                  <td><input type="text" id="tickets_dates_for_checkin_to" class=""> at <input type="text" class="timepicker" id="tickets_times_for_checkin_to"></td>
                </tr>

                <tr>
                  <td><label for="tickets_dates_for_checkin_to">Max Checkins:</label></td>
                  <td><input type="text" id="max_tickets_checkins" class=""></td>
                </tr>

            </tbody>
         </table>
         <button id="add_ticket" class="button button-primary button-small">Save Ticket</button>
    </div>

     <?php
        
    }
    public function event_location_html($post)
    {
        wp_nonce_field('_event_location_nonce', 'event_location_nonce');

        
?>


        <input type="radio" name="physical_location" value="1" <?php
        echo ($this->get_meta('physical_location') === '1' || get_post_meta($post->ID, "physical_location", true ) == false) ? 'checked' : '';
?>> <label for="physical_location"><?php _e('Physical location', 'event-plugun'); ?></label>


        <input type="radio" name="physical_location" value="2" <?php

        echo ($this->get_meta('physical_location') === '2') ? 'checked' : '';
?>> <label for="physical_location"><?php _e('Webinar Link', 'event-plugun'); ?>
               </label> </p>



<div id="webinar_data" style="display:none;">
    <label>Webinaar Url<br />
    <input class="full-width" type="text" name="webinar_url" id="webinar_url" value="<?php
        echo $this->get_meta('webinar_url');
?>"></label><br /><br />
</div>               

<div id="physical_location_data" style="display:block;">

       <label>Address<br />
        <input class="full-width" type="text" name="event_location" id="event_location" value="<?php
        echo $this->get_meta('event_location');
?>"></label><br /><br />

         <label>City/County<br />
        <input class="small-width" type="text" name="event_city" id="event_city" value="<?php
        echo $this->get_meta('event_city');
?>"></label><br /><br />

         <label>Postal Code<br />
        <input class="small-width" type="text" name="event_postal" id="event_postal" value="<?php
        echo $this->get_meta('event_postal');
?>"></label><br /><br />
         <label>State<br />
        <input class="small-width" type="text" name="event_state" id="event_state" value="<?php
        echo $this->get_meta('event_state');
?>"></label>  <br /><br />

        <label>Country<br />

        <select class="small-width" name="event_country" id="event_country">
        <option value="0">Select country</option>

        <?php
        
        
        $selected_country = $this->get_meta('event_country');
        
        $countries = $this->country_list();
        
        
        foreach ($countries as $country) {
?>
             <option value="<?php
            echo $country;
?>" <?php
            echo ($selected_country === $country) ? 'selected="selected"' : '';
?>><?php
            echo $country;
?></option>
            <?php
            
        }
?>


        </select></label>

        <?php
if(get_option("event_plugun_google_map_api") && $this->get_meta('physical_location') == 1){
                ?>
<script>
    	var address = "<?php echo $this->get_meta('event_location') ?>, <?php echo $this->get_meta('event_city') ?>";
      function initMap() {

        var map = new google.maps.Map(document.getElementById('map'), {
          zoom: 15,
        });
        var geocoder = new google.maps.Geocoder();
        geocoder.geocode({'address': address}, function(results, status) {
          if (status === 'OK') {
            map.setCenter(results[0].geometry.location);
            var marker = new google.maps.Marker({
              map: map,
              position: results[0].geometry.location
            });
          } else {
            alert('Geocode was not successful for the following reason: ' + status);
          }
        });
      }
    </script>
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=<?php echo get_option("event_plugun_google_map_api") ?>&callback=initMap">
    </script>
                <p>&nbsp;</p>
                <div id="map" style="height: 400px; width: 100%;"></div>

</div>

     <?php
        
    }

    }    
    
    
    
    public function country_list()
    {
        $countries = array(
            "AF" => "Afghanistan",
            "AX" => "�land Islands",
            "AL" => "Albania",
            "DZ" => "Algeria",
            "AS" => "American Samoa",
            "AD" => "Andorra",
            "AO" => "Angola",
            "AI" => "Anguilla",
            "AQ" => "Antarctica",
            "AG" => "Antigua and Barbuda",
            "AR" => "Argentina",
            "AM" => "Armenia",
            "AW" => "Aruba",
            "AU" => "Australia",
            "AT" => "Austria",
            "AZ" => "Azerbaijan",
            "BS" => "Bahamas",
            "BH" => "Bahrain",
            "BD" => "Bangladesh",
            "BB" => "Barbados",
            "BY" => "Belarus",
            "BE" => "Belgium",
            "BZ" => "Belize",
            "BJ" => "Benin",
            "BM" => "Bermuda",
            "BT" => "Bhutan",
            "BO" => "Bolivia",
            "BA" => "Bosnia and Herzegovina",
            "BW" => "Botswana",
            "BV" => "Bouvet Island",
            "BR" => "Brazil",
            "IO" => "British Indian Ocean Territory",
            "BN" => "Brunei Darussalam",
            "BG" => "Bulgaria",
            "BF" => "Burkina Faso",
            "BI" => "Burundi",
            "KH" => "Cambodia",
            "CM" => "Cameroon",
            "CA" => "Canada",
            "CV" => "Cape Verde",
            "KY" => "Cayman Islands",
            "CF" => "Central African Republic",
            "TD" => "Chad",
            "CL" => "Chile",
            "CN" => "China",
            "CX" => "Christmas Island",
            "CC" => "Cocos (Keeling) Islands",
            "CO" => "Colombia",
            "KM" => "Comoros",
            "CG" => "Congo",
            "CD" => "Congo, The Democratic Republic of The",
            "CK" => "Cook Islands",
            "CR" => "Costa Rica",
            "CI" => "Cote D'ivoire",
            "HR" => "Croatia",
            "CU" => "Cuba",
            "CY" => "Cyprus",
            "CZ" => "Czech Republic",
            "DK" => "Denmark",
            "DJ" => "Djibouti",
            "DM" => "Dominica",
            "DO" => "Dominican Republic",
            "EC" => "Ecuador",
            "EG" => "Egypt",
            "SV" => "El Salvador",
            "GQ" => "Equatorial Guinea",
            "ER" => "Eritrea",
            "EE" => "Estonia",
            "ET" => "Ethiopia",
            "FK" => "Falkland Islands (Malvinas)",
            "FO" => "Faroe Islands",
            "FJ" => "Fiji",
            "FI" => "Finland",
            "FR" => "France",
            "GF" => "French Guiana",
            "PF" => "French Polynesia",
            "TF" => "French Southern Territories",
            "GA" => "Gabon",
            "GM" => "Gambia",
            "GE" => "Georgia",
            "DE" => "Germany",
            "GH" => "Ghana",
            "GI" => "Gibraltar",
            "GR" => "Greece",
            "GL" => "Greenland",
            "GD" => "Grenada",
            "GP" => "Guadeloupe",
            "GU" => "Guam",
            "GT" => "Guatemala",
            "GG" => "Guernsey",
            "GN" => "Guinea",
            "GW" => "Guinea-bissau",
            "GY" => "Guyana",
            "HT" => "Haiti",
            "HM" => "Heard Island and Mcdonald Islands",
            "VA" => "Holy See (Vatican City State)",
            "HN" => "Honduras",
            "HK" => "Hong Kong",
            "HU" => "Hungary",
            "IS" => "Iceland",
            "IN" => "India",
            "ID" => "Indonesia",
            "IR" => "Iran, Islamic Republic of",
            "IQ" => "Iraq",
            "IE" => "Ireland",
            "IM" => "Isle of Man",
            "IL" => "Israel",
            "IT" => "Italy",
            "JM" => "Jamaica",
            "JP" => "Japan",
            "JE" => "Jersey",
            "JO" => "Jordan",
            "KZ" => "Kazakhstan",
            "KE" => "Kenya",
            "KI" => "Kiribati",
            "KP" => "Korea, Democratic People's Republic of",
            "KR" => "Korea, Republic of",
            "KW" => "Kuwait",
            "KG" => "Kyrgyzstan",
            "LA" => "Lao People's Democratic Republic",
            "LV" => "Latvia",
            "LB" => "Lebanon",
            "LS" => "Lesotho",
            "LR" => "Liberia",
            "LY" => "Libyan Arab Jamahiriya",
            "LI" => "Liechtenstein",
            "LT" => "Lithuania",
            "LU" => "Luxembourg",
            "MO" => "Macao",
            "MK" => "Macedonia, The Former Yugoslav Republic of",
            "MG" => "Madagascar",
            "MW" => "Malawi",
            "MY" => "Malaysia",
            "MV" => "Maldives",
            "ML" => "Mali",
            "MT" => "Malta",
            "MH" => "Marshall Islands",
            "MQ" => "Martinique",
            "MR" => "Mauritania",
            "MU" => "Mauritius",
            "YT" => "Mayotte",
            "MX" => "Mexico",
            "FM" => "Micronesia, Federated States of",
            "MD" => "Moldova, Republic of",
            "MC" => "Monaco",
            "MN" => "Mongolia",
            "ME" => "Montenegro",
            "MS" => "Montserrat",
            "MA" => "Morocco",
            "MZ" => "Mozambique",
            "MM" => "Myanmar",
            "NA" => "Namibia",
            "NR" => "Nauru",
            "NP" => "Nepal",
            "NL" => "Netherlands",
            "AN" => "Netherlands Antilles",
            "NC" => "New Caledonia",
            "NZ" => "New Zealand",
            "NI" => "Nicaragua",
            "NE" => "Niger",
            "NG" => "Nigeria",
            "NU" => "Niue",
            "NF" => "Norfolk Island",
            "MP" => "Northern Mariana Islands",
            "NO" => "Norway",
            "OM" => "Oman",
            "PK" => "Pakistan",
            "PW" => "Palau",
            "PS" => "Palestinian Territory, Occupied",
            "PA" => "Panama",
            "PG" => "Papua New Guinea",
            "PY" => "Paraguay",
            "PE" => "Peru",
            "PH" => "Philippines",
            "PN" => "Pitcairn",
            "PL" => "Poland",
            "PT" => "Portugal",
            "PR" => "Puerto Rico",
            "QA" => "Qatar",
            "RE" => "Reunion",
            "RO" => "Romania",
            "RU" => "Russian Federation",
            "RW" => "Rwanda",
            "SH" => "Saint Helena",
            "KN" => "Saint Kitts and Nevis",
            "LC" => "Saint Lucia",
            "PM" => "Saint Pierre and Miquelon",
            "VC" => "Saint Vincent and The Grenadines",
            "WS" => "Samoa",
            "SM" => "San Marino",
            "ST" => "Sao Tome and Principe",
            "SA" => "Saudi Arabia",
            "SN" => "Senegal",
            "RS" => "Serbia",
            "SC" => "Seychelles",
            "SL" => "Sierra Leone",
            "SG" => "Singapore",
            "SK" => "Slovakia",
            "SI" => "Slovenia",
            "SB" => "Solomon Islands",
            "SO" => "Somalia",
            "ZA" => "South Africa",
            "GS" => "South Georgia and The South Sandwich Islands",
            "ES" => "Spain",
            "LK" => "Sri Lanka",
            "SD" => "Sudan",
            "SR" => "Suriname",
            "SJ" => "Svalbard and Jan Mayen",
            "SZ" => "Swaziland",
            "SE" => "Sweden",
            "CH" => "Switzerland",
            "SY" => "Syrian Arab Republic",
            "TW" => "Taiwan, Province of China",
            "TJ" => "Tajikistan",
            "TZ" => "Tanzania, United Republic of",
            "TH" => "Thailand",
            "TL" => "Timor-leste",
            "TG" => "Togo",
            "TK" => "Tokelau",
            "TO" => "Tonga",
            "TT" => "Trinidad and Tobago",
            "TN" => "Tunisia",
            "TR" => "Turkey",
            "TM" => "Turkmenistan",
            "TC" => "Turks and Caicos Islands",
            "TV" => "Tuvalu",
            "UG" => "Uganda",
            "UA" => "Ukraine",
            "AE" => "United Arab Emirates",
            "GB" => "United Kingdom",
            "US" => "United States",
            "UM" => "United States Minor Outlying Islands",
            "UY" => "Uruguay",
            "UZ" => "Uzbekistan",
            "VU" => "Vanuatu",
            "VE" => "Venezuela",
            "VN" => "Viet Nam",
            "VG" => "Virgin Islands, British",
            "VI" => "Virgin Islands, U.S.",
            "WF" => "Wallis and Futuna",
            "EH" => "Western Sahara",
            "YE" => "Yemen",
            "ZM" => "Zambia",
            "ZW" => "Zimbabwe"
        );
        
        
        return $countries;
    }
    
    
    
    /**
     * The contents of our meta box.
     * @access public
     * @since  1.0.0
     * @return void
     */
    
    public function event_date_html($post)
    {
        wp_nonce_field('_event_date_nonce', 'event_date_nonce');
?>

        <p>
            <label for="event_date_start"><span class="dashicons color-success dashicons-calendar-alt"></span> <?php
        _e('Start date', 'event-plugun');
?></label><br>
            <input type="text"   name="event_date_start" id="event_date_start" value="<?php
        echo $this->get_meta('event_date_start');
?>">
        </p>


        <p>
            <label for="event_date_end"><span class="dashicons color-warn dashicons-calendar-alt"></span> <?php
        _e('End date', 'event-plugun');
?></label><br>
            <input type="text"   name="event_date_end" id="event_date_end" value="<?php
        echo $this->get_meta('event_date_end');
?>">
        </p>



        <p><strong><span class="dashicons dashicons-clock"></span> Daily Timings</strong><br />

<input type="checkbox" name="all_day_event" id="all_day_event" value="1" <?php
        echo ($this->get_meta('all_day_event') === '1') ? 'checked' : '';
?>>
        <label for="all_day_event"><?php
        _e('All Day Event', 'event-plugun');
?></label> </p>

        <div id="if_no_all_day" style="display:none;">
            <label for="event_time_start"> <?php
        _e('Start: ', 'event-plugun');
?>
            <input type="text" class="timepicker" name="event_time_start" id="event_time_start" value="<?php
        echo $this->get_meta('event_time_start');
?>"></label><br />

            <label for="event_time_end"> <?php
        _e('End: ', 'event-plugun');
?>
            <input type="text" class="timepicker" name="event_time_end" id="event_time_end" value="<?php
        echo $this->get_meta('event_time_end');
?>"></label>
        </p>
      </div>



        <?php
        
    }
    
    
    public function ticket_event_html($post)
    {
        wp_nonce_field('_ticket_event_nonce', 'ticket_event_nonce');
?>

<p>
        <label for="ticket_event"><?php
        _e('Select Event', 'ticket_event');
?></label><br>
        <select class="full-width" name="ticket_event" id="ticket_event">
        <option value="0">Select event</option>

        <?php
        
        
        $selected_event = $this->get_meta('ticket_event');
        
        global $wpdb;
        
        $events_data = $wpdb->get_results("
    SELECT ID, post_title
    FROM $wpdb->posts
    WHERE post_type = 'plugun-event'
    AND post_status = 'publish'
    ");
        
        
        
        if ($events_data) {
            foreach ($events_data as $e) {
?>
         <option value="<?php
                echo $e->ID;
?>" <?php
                echo ($selected_event === $e->ID) ? 'selected="selected"' : '';
?>><?php
                echo $e->post_title;
?></option>
        <?php
                
            }
        }
?>


        </select>
    </p>

        <?php
        
    }
    
    
    public function booking_status_html($post)
    {
        wp_nonce_field('_ticket_event_nonce', 'ticket_event_nonce');
?>

<p>
        <select class="full-width" name="booking_status" id="booking_status">
        <option value="">Select status</option>

        <?php
        
        
        $selected_event = $this->get_meta('booking_status');
        
        global $wpdb;
        $booking_status = array(
            'pending' => 'Pending',
            'paid' => "Paid",
            'approved' => 'Approved',
            'disapproved' => "Disapproved"
            
        );
        
        
        
        
        foreach ($booking_status as $key => $value) {
?>
         <option value="<?php
            echo $key;
?>" <?php
            echo ($selected_event == $key) ? 'selected="selected"' : '';
?>><?php
            echo $value;
?></option>
        <?php
            
        }
?>


        </select>
    </p>

        <?php
        
    }
    
    
    public function ticket_limits_html($post)
    {
        wp_nonce_field('_ticket_limits_nonce', 'ticket_limits_nonce');
?>
<h3>Quantity</h3>

        <p>
            <label for="quantity"><?php
        _e('Quantity', 'event-plugun');
?></label><br>
            <input type="number" placeholder="unlimited"  name="quantity" id="quantity" value="<?php
        echo $this->get_meta('quantity');
?>">
        </p>

        <p>
        <label for="used_quantity"><?php
        _e('Quantity Used', 'event-plugun');
?></label><br>
            <?php
        echo get_deplite_ticket_used_quantity($post->ID);
?>
        </p>    

       <p>
        <label for="available_quantity"><?php
        _e('Quantity Available', 'event-plugun');
?></label><br>
            <?php
        echo get_dep_lite_ticket_available_quantity($post->ID);
?>
        </p>    

<hr />
<h3>Tickets per order</h3>

        <p>
            <label for="min_tickets"><span class="dashicons dashicons-tickets-alt"></span> <?php
        _e('Minimum tickets per order', 'event-plugun');
?></label><br>
            <input type="number" placeholder="no min limit"  name="min_tickets" id="min_tickets" value="<?php
        echo $this->get_meta('min_tickets');
?>">
        </p>    <p>
            <label for="max_tickets"><span class="dashicons dashicons-tickets-alt"></span> <?php
        _e('Maximum tickets per order', 'event-plugun');
?></label><br>
            <input type="number" placeholder="no max limit" name="max_tickets" id="max_tickets" value="<?php
        echo $this->get_meta('max_tickets');
?>">
        </p>

<hr />
<h3>Checkins per ticket</h3>

<p>
            <label for="max_ticket_checkins"><span class="dashicons dashicons-yes"></span> <?php
        _e('Maximum checkins per ticket', 'event-plugun');
?></label><br>
            <input type="number" placeholder="no max limit"  name="max_ticket_checkins" id="max_ticket_checkins" value="<?php
        $max_checkins_allowed = $this->get_meta('max_ticket_checkins');
        echo (is_numeric($max_checkins_allowed) && $max_checkins_allowed > 0) ? $max_checkins_allowed : 1;
?>">
        </p>

     

<hr />
<h3>Tickets selling availability</h3>

<p>
<input type="radio" name="ticket_availability" id="ticket_availability_0" value="0" <?php
        echo ($this->get_meta('ticket_availability') != '1') ? 'checked' : '';
?>>
<label for="ticket_availability_0">Open ended until event ends</label><br>

        <input type="radio" name="ticket_availability" id="ticket_availability_1" value="1" <?php
        echo ($this->get_meta('ticket_availability') === '1') ? 'checked' : '';
?>>
<label for="ticket_availability_1">Date range</label><br>
    </p>



        <p>
            <label for="dates_for_selling_from"><span class="dashicons dashicons-calendar-alt"></span> <?php
        _e('Tickets availability date range', 'event-plugun');
?></label><br>
            From: <input type="text" class="datepicker-input" name="dates_for_selling_from" id="dates_for_selling_from" value="<?php
        echo $this->get_meta('dates_for_selling_from');
?>"> <input type="text" class="timepicker" name="dates_for_selling_from_time" id="dates_for_selling_from_time" value="<?php
        echo $this->get_meta('dates_for_selling_from_time');
?>">
To: <input type="text" class="datepicker-input"  name="dates_for_selling_to" id="dates_for_selling_to" value="<?php
        echo $this->get_meta('dates_for_selling_to');
?>"> <input type="text" class="timepicker" name="dates_for_selling_to_time" id="dates_for_selling_to_time" value="<?php
        echo $this->get_meta('dates_for_selling_to_time');
?>">
        </p>


        <hr />
<h3>Checkins availability</h3>

<p>
<input type="radio" name="ticket_checkin" id="ticket_checkin_0" value="0" <?php
        echo ($this->get_meta('ticket_checkin') != '1') ? 'checked' : '';
?>>
<label for="ticket_availability_0">Open ended until event ends</label><br>

        <input type="radio" name="ticket_checkin" id="ticket_checkin_1" value="1" <?php
        echo ($this->get_meta('ticket_checkin') === '1') ? 'checked' : '';
?>>
<label for="ticket_checkin_1">Date range</label><br>
    </p>


        <p>
            <label for="dates_for_checkin_from"><span class="dashicons dashicons-calendar-alt"></span> <?php
        _e('Checkin Date Range', 'event-plugun');
?></label><br>
            From: <input type="text" class="datepicker-input" name="dates_for_checkin_from" id="dates_for_checkin_from" value="<?php
        echo $this->get_meta('dates_for_checkin_from');
?>"> <input type="text" class="timepicker" name="times_for_checkin_from" id="times_for_checkin_from" value="<?php
        echo $this->get_meta('times_for_checkin_from');
?>">
To: <input type="text" class="datepicker-input"  name="dates_for_checkin_to" id="dates_for_checkin_to" value="<?php
        echo get_post_meta($post->ID, 'dates_for_checkin_to', true);
?>"> <input type="text" class="timepicker"  name="times_for_checkin_to" id="times_for_checkin_to" value="<?php
        echo get_post_meta($post->ID, 'times_for_checkin_to', true);
?>">
        </p>
<!--
<p>
            <label for="times_for_checkin_from"> <?php
        _e('Checkin Time Range', 'event-plugun');
?></label><br>
            From: <input type="text" class="timepicker" name="times_for_checkin_from" id="times_for_checkin_from" value="<?php
        echo $this->get_meta('times_for_checkin_from');
?>">
To: <input type="text" class="timepicker"  name="times_for_checkin_to" id="times_for_checkin_to" value="<?php
        echo get_post_meta($post->ID, 'times_for_checkin_to', true);
?>">
        </p>-->
        <script>
        jQuery(document).ready(function(){
            /*
            jQuery( ".datepicker-input" ).datepicker();
            jQuery( ".datepicker-input" ).datepicker( "option", "dateFormat", "yy-mm-dd");
            */

            jQuery( "#dates_for_selling_to" ).datepicker({ "dateFormat": "yy-mm-dd" , defaultDate: '<?php
        echo get_post_meta($post->ID, 'dates_for_selling_to', true);
?>' });
            jQuery( "#dates_for_selling_from" ).datepicker({ "dateFormat": "yy-mm-dd" , defaultDate: '<?php
        echo get_post_meta($post->ID, 'dates_for_selling_from', true);
?>' });


            jQuery( "#dates_for_checkin_to" ).datepicker({ "dateFormat": "yy-mm-dd" , defaultDate: '<?php
        echo get_post_meta($post->ID, 'dates_for_checkin_to', true);
?>' });
            jQuery( "#dates_for_checkin_from" ).datepicker({ "dateFormat": "yy-mm-dd" , defaultDate: '<?php
        echo get_post_meta($post->ID, 'dates_for_checkin_from', true);
?>' });

        })
            
        </script>

        <?php
        
    }
    
    public function ticket_pricing_html($post)
    {
        wp_nonce_field('_ticket_pricing_nonce', 'min_max_ticket_nonce');
?>


        <p>
            <label for="ticket_price"><span class="dashicons dashicons-money"></span> <?php
        _e('Ticket Price', 'event-plugun');
?></label><br>
            <input type="number" placeholder="free"  name="ticket_price" id="ticket_price" value="<?php
        echo $this->get_meta('ticket_price');
?>">
        </p>    <p>
            <label for="ticket_fee"><span class="dashicons dashicons-feedback"></span> <?php
        _e('Ticket fee', 'event-plugun');
?></label><br>
            <input type="number" placeholder="free" name="ticket_fee" id="ticket_fee" value="<?php
        echo $this->get_meta('ticket_fee');
?>">
        </p>




<h4>Fee type</h4>
<p>
<input type="radio" name="ticket_fee_type" id="ticket_fee_type_0" value="0" <?php
        echo ($this->get_meta('ticket_fee_type') != '1') ? 'checked' : '';
?>>
<label for="ticket_fee_type_0">Fixed per ticket</label><br>

        <input type="radio" name="ticket_fee_type" id="ticket_fee_type_1" value="1" <?php
        echo ($this->get_meta('ticket_fee_type') === '1') ? 'checked' : '';
?>>
<label for="ticket_fee_type_1">Percentage per ticket</label><br>
    </p>





        <?php
        
    }
    
    
    public function booking_event_details_html($post)
    {
        global $wpdb;
        wp_nonce_field('_booking_event_nonce', 'booking_event_nonce');
?>

<p>
        <label for="booking_event"><strong><?php
        _e('Event Name', 'event-plugun');
?>: </strong></label><br>
        <!--
        <select class="small-width" name="booking_event" id="booking_event">
        <option value="0">Select event</option>-->
        <div><?php
        echo get_post_meta($post->ID, "event_name", true);
?></div>
     </p>   
    <p> 
        <!--<label for="tickets"><?php
        _e('Tickets', 'event-plugun');
?>: </label><br>-->
        <div>
     <?php
        
        $event_id = get_post_meta($post->ID, "event_id", true);
        
        $args    = array(
            'post_type' => 'plugun-ticket',
            'meta_key' => 'ticket_event',
            'meta_value' => $event_id
        );
        $tickets = get_posts($args);
        
?>
                  <input name="process" value="edit_booking" type="hidden">
          <input name="event_id" value="<?php
        echo $event_id;
?>" type="hidden">
          <table class="table table-bordered">
             <thead>
                <tr>
                   <th>Ticket</th>
                   <th>Price</th>
                   <th>Spaces</th>
                </tr>
             </thead>
             <tbody class="tickets_body">
         <?php
        
        $ticket_purchased = get_post_meta($post->ID, "tickets", true);
        
        //echo '<pre>'; print_r($ticket_purchased); echo '</pre>';
        
        for ($i = 0, $count = count($tickets); $i < $count; $i++) {
            
            $ticket_id = $tickets[$i]->ID;
            $price     = get_post_meta($tickets[$i]->ID, 'ticket_price', true);
            
            if ($price != '' || $price != 0) {
                EpLiteCurrency::get_format($price);
            } else {
                $price = 'Free';
            }
            
            $min = get_post_meta($tickets[$i]->ID, "min_tickets", true);
            $max = get_post_meta($tickets[$i]->ID, "max_tickets", true);
            
            if (!$max) {
                $quantity = get_dep_lite_ticket_available_quantity($ticket_id); //get_post_meta($ticket_id, "quantity", true); 
                if ($quantity > 100 || $quantity < 0)
                    $max = 100;
                else
                    $max = $quantity;
            }
            
            
            
?>

                <tr>
                <td><?php
            echo $tickets[$i]->post_title;
?></td>
                <td><?php
            echo EpLiteCurrency::get_format($price);
?></td>
                <td><?php
            
            //echo  $ticket_purchased[$tickets[$i]->ID]
            
            $query = "SELECT SUM(IFNULL(quantity, 0)) FROM wp_posts booking 
                            INNER JOIN wp_postmeta booking_meta ON booking.ID = booking_meta.post_id
                            INNER JOIN wp_plugun_checkin checkins ON booking.ID = checkins.booking_id 
                            WHERE booking_meta.meta_key = 'event_id' AND booking_meta.meta_value = '{$event_id}'
                            AND ticket_id = '{$tickets[$i]->ID}' AND booking.ID = '{$post->ID}'";
            echo $wpdb->get_var($query);
?>
                
               
                </td>
                </tr>
                

                

        <?php
        }
?>
                    </tbody>
                </table>
        </div>
    </p>





        <p>
        <label for="quantity"><strong><?php
        _e('Name', 'event-plugun');
?>: </strong></label><br>
            <input type="text" placeholder="name"  name="name" id="name" value="<?php
        echo $this->get_meta('name');
?>">
        </p>

        <p>
        <label for="user_phone"><strong><?php
        _e('User Phone', 'event-plugun');
?>: </strong></label><br>
            <input type="text" placeholder="User Phone"  name="user_phone" id="user_phone" value="<?php
        echo $this->get_meta('user_phone');
?>">
        </p>

        <p>
         <label for="quantity"><strong><?php
        _e('Number of seats', 'event-plugun');
?>: </strong></label><br>
            <?php
        $query = "SELECT SUM(IFNULL(quantity, 0)) FROM wp_posts booking 
                            INNER JOIN wp_postmeta booking_meta ON booking.ID = booking_meta.post_id
                            INNER JOIN wp_plugun_checkin checkins ON booking.ID = checkins.booking_id 
                            WHERE booking_meta.meta_key = 'event_id' AND booking_meta.meta_value = '{$event_id}'
                            AND booking.ID = '{$post->ID}'";
        echo $wpdb->get_var($query);
?>
        </p>
        <p>
         <label for="amount"><strong><?php
        _e('Amount', 'event-plugun');
?>: </strong></label><br>
            <?php
        echo EpLiteCurrency::get_format($this->get_meta('amount'));
?>
        </p>
         <p>
         <label for="discount"><strong><?php
        _e('Discount', 'event-plugun');
?>: </strong></label><br>
            <input type="number" placeholder="discount"  name="discount" id="discount" value="<?php
        echo $this->get_meta('discount');
?>"  <?php
        if ($this->get_meta('booking_status') == "paid")
            echo ' readonly="readonly" ';
?>>
        </p>
         <p>
         <label for="fee"><strong><?php
        _e('Fee', 'event-plugun');
?>: </strong></label><br>
            <input type="number" placeholder="fee"  name="fee" id="fee" value="<?php
        echo $this->get_meta('fee');
?>"  <?php
        if ($this->get_meta('booking_status') == "paid")
            echo ' readonly="readonly" ';
?>>
        </p>


        <p>
         <label for="user_notes"><strong><?php
        _e('User comments', 'event-plugun');
?>: </strong></label><br>
        <textarea  rows="4" class="full-width"  placeholder="User comments" name="user_comments" id="user_comments"><?php
        echo $this->get_meta('user_comments');
?></textarea>
        </p>

        <p>
         <label for="admin_private_notes"><strong><?php
        _e('Admin private notes', 'event-plugun');
?>: </strong></label><br>
        <textarea rows="4" class="full-width" placeholder="Admin private notes" name="admin_private_notes" id="admin_private_notes"><?php
        echo $this->get_meta('admin_private_notes');
?></textarea>
        </p>

           <?php
        
    }
    
    
    
    
    
    
    
    
    /**
     * Save meta box fields.
     * @access public
     * @since  1.0.0
     * @param int $post_id
     * @return int $post_id
     */
    public function meta_box_save($post_id)
    {
        global $post, $messages, $wpdb;
        
        
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        
        
        
        // post metas
        $meta_posts = array(
            'event_date_start',
            'event_date_end',
            'all_day_event',
            'event_time_start',
            'event_time_end',
            'no_tickets',
            'physical_location',
            'webinar_url',
            'event_location',
            'event_city',
            'event_postal',
            'event_state',
            'event_country',
            'quantity',
            'min_tickets',
            'max_tickets',
            'max_ticket_checkins',
            // 'checkin_code',
            'ticket_availability',
            'dates_for_selling_from',
            'dates_for_selling_to',
            'dates_for_selling_from_time',
            'dates_for_selling_to_time',
            
            'ticket_checkin',
            'dates_for_checkin_from',
            'dates_for_checkin_to',
            'ticket_price',
            'ticket_fee',
            'ticket_fee_type',
            'ticket_event',
            'booking_status',
            'booking_event',
            'booking_ticket',
            'name',
            'seats',
            'amount',
            'discount',
            'fee',
            'user_comments',
            'admin_private_notes',
            "times_for_checkin_from",
            "times_for_checkin_to",
            "subject"
            
            //  "is_approved"
        );
        
        
        $to_replace = array(
            ':',
            '.',
            ' ',
            '-'
        );
        
        $post = get_post($post_id);
        
        if ($post->post_type == "plugun-api") {
            
            if (!class_exists("qrstr")) {
                
                require_once(DEP_LITE_PLUGIN_PATH . '/includes/phpqrcode/qrlib.php');
            }
            
            update_post_meta($post_id, "event_id", sanitize_text_field(@$_POST["event_id"]));
            update_post_meta($post_id, "api_key", sanitize_text_field(@$_POST["api_key"]));
            update_post_meta($post_id, "sms_enabled", sanitize_text_field(@$_POST["sms_enabled"]));
            update_post_meta($post_id, "api_status", (int) @$_POST["api_status"]);
            update_post_meta($post_id, "events", @$_POST["events"]);
            $qr_code_text = site_url() . "," . (int) @$_POST["api_key"];
            $file         = __DIR__ . "/" . sanitize_text_field(@$_POST["api_key"]) . ".png";
            
            QRcode::png($qr_code_text, $file);
            $qr_code = @file_get_contents($file);
            @unlink($file);
            update_post_meta($post_id, "api_qr_code", $qr_code);
            return $post_id;
        }
        
        $event_end_time = get_post_meta($post_id, "event_date_end", true);
        
        
        if ($event_end_time) {
            $event_end_time .= ' ' . get_post_meta($post_id, "event_time_end", true);
        }
        
        
        $warnings = array();
        
        if (isset($_POST['ticket_name'])) {
            $i = 0;
            
            
            foreach ($_POST['ticket_name'] as $key => $value) {
                
                $event_ticket_posts = array(
                    'ID' => sanitize_text_field($_POST['ticket_id'][$i]),
                    'post_author' => 1,
                    'post_date' => date('Y-m-d H:i:s'),
                    'post_date_gmt' => date('Y-m-d H:i:s'),
                    'post_content' => '',
                    'post_title' => $value,
                    'post_name' => $value,
                    'post_excerpt' => '',
                    'post_status' => 'publish',
                    'comment_status' => 'close',
                    'ping_status' => 'close',
                    'post_modified' => date('Y-m-d H:i:s'),
                    'post_modified_gmt' => date('Y-m-d H:i:s'),
                    'post_parent' => $post_id,
                    'post_type' => 'plugun-ticket',
                    'comment_count' => 0
                );
                
                
                
                
                if ($_POST['ticket_id'][$i] != 0) {
                    $wpdb->update($wpdb->posts, $event_ticket_posts, array(
                        'ID' => sanitize_text_field($_POST['ticket_id'][$i])
                    ), array(
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s'
                    ), array(
                        '%d'
                    ));
                    $ticket_id = sanitize_text_field($_POST['ticket_id'][$i]);
                } else {
                    $wpdb->insert($wpdb->posts, $event_ticket_posts, array(
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s'
                    ));
                    $ticket_id = $_POST['ticket_id'][$i] = $wpdb->insert_id;
                }
                
                
                
                $_POST['handled'][$i] = 1;
                
                
                
                update_post_meta($ticket_id, 'quantity', sanitize_text_field($_POST['ticket_spaces'][$i]));
                update_post_meta($ticket_id, 'min_tickets', sanitize_text_field($_POST['ticket_min'][$i]));
                update_post_meta($ticket_id, 'max_tickets', sanitize_text_field($_POST['ticket_max'][$i]));
                
                update_post_meta($ticket_id, 'dates_for_selling_from', sanitize_text_field($_POST['ticket_from_date'][$i]));
                update_post_meta($ticket_id, 'dates_for_selling_to_time', sanitize_text_field($_POST['ticket_to_time'][$i]));
                update_post_meta($ticket_id, 'dates_for_selling_from_time', sanitize_text_field($_POST['ticket_from_time'][$i]));
                update_post_meta($ticket_id, 'dates_for_selling_to', sanitize_text_field($_POST['ticket_to_date'][$i]));
                
                
                $checkin_time_from = sanitize_text_field($_POST['tickets_dates_for_checkin_from'][$i]);
                $checkin_time_to   = sanitize_text_field($_POST['tickets_dates_for_checkin_to'][$i]);
                
                if ($checkin_time_from) {
                    $checkin_time_from .= " " . sanitize_text_field($_POST['tickets_times_for_checkin_from'][$i]);
                }
                
                if ($checkin_time_to) {
                    $checkin_time_to .= " " . sanitize_text_field($_POST['tickets_times_for_checkin_to'][$i]);
                }
                
                $booking_time_from = sanitize_text_field($_POST['ticket_from_date'][$i]);
                
                if ($booking_time_from) {
                    $booking_time_from .= sanitize_text_field($_POST['ticket_from_time'][$i]);
                    
                    if ($booking_time_from > $checkin_time_to) {
                        $warnings[] = "Booking Time exceed the checkin end time for {$value}";
                    }
                }
                
                if ($event_end_time) {
                    if ($checkin_time_from > $event_end_time) {
                        $warnings[] = "Checkin start time of {$value} is going beyoud event close time";
                    }
                    
                    if ($checkin_time_to > $event_end_time) {
                        $warnings[] = "Checkin end time of {$value} is going beyoud event close time";
                    }
                }
                
                
                update_post_meta($ticket_id, 'dates_for_checkin_from', sanitize_text_field($_POST['tickets_dates_for_checkin_from'][$i]));
                update_post_meta($ticket_id, 'dates_for_checkin_to', sanitize_text_field($_POST['tickets_dates_for_checkin_to'][$i]));
                update_post_meta($ticket_id, 'times_for_checkin_from', sanitize_text_field($_POST['tickets_times_for_checkin_from'][$i]));
                update_post_meta($ticket_id, 'times_for_checkin_to', sanitize_text_field($_POST['tickets_times_for_checkin_to'][$i]));
                
                update_post_meta($ticket_id, 'max_ticket_checkins', sanitize_text_field($_POST['max_tickets_checkins'][$i]));
                
                
                //  update_post_meta($ticket_id, 'ticket_price', $_POST['ticket_price'][$i]);
                update_post_meta($ticket_id, 'ticket_price', sanitize_text_field($_POST['tickets_price'][$i]));
                update_post_meta($ticket_id, 'ticket_event', $post_id);
                
                $i++;
            }
            
            // echo '<pre>'; print_r($warnings); echo '</pre>'; exit;
            
            if (count($warnings) > 0) {
                //$this->warnings = $warnings;
                if (!session_id())
                    session_start();
                
                
                $_SESSION["warnings"] = $warnings;
                
                add_action('admin_notices', array(
                    $this,
                    "show_admin_notices"
                ));
            }
        }
        
        
        
        foreach ($meta_posts as $title => $a) {
            if (is_array(@$_POST[$a]))
                continue;
            if (isset($_POST[$a])) {
                if ($a == 'event_date_end') {
                    $date_start_number = str_replace($to_replace, "", $_POST[$a]);
                    update_post_meta($post_id, 'date_start_number', esc_attr($date_start_number));
                }
                
                update_post_meta($post_id, $a, esc_attr($_POST[$a]));
            } elseif ($this->post_type == 'plugun-event') {
                if (isset($_POST['physical_location'])) {
                    update_post_meta($post_id, 'physical_location', (int) $_POST['physical_location']);
                }
                if (!isset($_POST['all_day_event'])) {
                    update_post_meta($post_id, 'all_day_event', null);
                }
                if (!isset($_POST['no_tickets'])) {
                    update_post_meta($post_id, 'no_tickets', null);
                }
            }
            
            
        }
        
        
        if ($post->post_type == "plugun-ticket") {
            if (!@$_POST['dates_for_selling_from_time'] && !@$_POST['dates_for_selling_from'] && !@$_POST['dates_for_selling_to_time'] && !@$_POST['dates_for_selling_to']) {
                update_post_meta($post->ID, "ticket_availability", 0);
            }
            
            
            if (!@$_POST['dates_for_checkin_from'] && !@$_POST['times_for_checkin_from'] && !@$_POST['dates_for_checkin_to'] && !@$_POST['times_for_checkin_to']) {
                update_post_meta($post->ID, "ticket_checkin", 0);
            }
            
        }
        
        
        if ($post->post_type == "plugun-booking") {
            do_action("event_plugun_booking_status_changed", $post->ID, sanitize_text_field($_POST["booking_status"]));
        }
        
        if ($post->post_type == "plugun-template") {
            
            
            update_post_meta($post->ID, "event_plugun_template_subject", sanitize_text_field(@$_POST["event_plugun_template_subject"]));
            
        }
        
        
        
        
    } // End meta_box_save()
    
    
    function show_admin_notices()
    {
        
        $class = 'notice notice-error';
        
        $message = __('<div>' . implode('</div><div>', $this->warnings) . '</div>', 'sample-text-domain');
        
        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
    }
    
    
    
    // public function update_meta ($meta = array()){
    
    
    // }
    
    /**
     * Customise the "Enter title here" text.
     * @access public
     * @since  1.0.0
     * @param string $title
     * @return void
     */
    public function enter_title_here($title)
    {
        if (get_post_type() == 'plugun-event') {
            $title = __('Enter the event title here', 'event-plugun');
        }
        
        if (get_post_type() == 'plugun-ticket') {
            $title = __('Enter event ticket short name here. Standard, Regular etc', 'event-plugun');
        }
        
        
        return $title;
    } // End enter_title_here()
    
    /**
     * Get the settings for the custom fields.
     * @access public
     * @since  1.0.0
     * @return array
     */
    public function get_custom_fields_settings()
    {
        $fields = array();
        
        $fields['url'] = array(
            'name' => __('URL', 'event-plugun'),
            'description' => __('Enter a URL that applies to this thing (for example: http://domain.com/).', 'event-plugun'),
            'type' => 'url',
            'default' => '',
            'section' => 'info'
        );
        
        return apply_filters('event-plugun_custom_fields_settings', $fields);
    } // End get_custom_fields_settings()
    
    /**
     * Get the image for the given ID.
     * @param  int              $id   Post ID.
     * @param  mixed $size Image dimension. (default: "thing-thumbnail")
     * @since  1.0.0
     * @return string           <img> tag.
     */
    protected function get_image($id, $size = 'thing-thumbnail')
    {
        $response = '';
        
        if (has_post_thumbnail($id)) {
            // If not a string or an array, and not an integer, default to 150x9999.
            if ((is_int($size) || (0 < intval($size))) && !is_array($size)) {
                $size = array(
                    intval($size),
                    intval($size)
                );
            } elseif (!is_string($size) && !is_array($size)) {
                $size = array(
                    150,
                    9999
                );
            }
            $response = get_the_post_thumbnail(intval($id), $size);
        }
        
        return $response;
    } // End get_image()
    
    /**
     * Register image sizes.
     * @access public
     * @since  1.0.0
     */
    public function register_image_sizes()
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
    public function activation()
    {
        $this->flush_rewrite_rules();
    } // End activation()
    
    /**
     * Flush the rewrite rules
     * @access public
     * @since 1.0.0
     */
    private function flush_rewrite_rules()
    {
        $this->register_post_type();
        flush_rewrite_rules();
    } // End flush_rewrite_rules()
    
    /**
     * Ensure that "post-thumbnails" support is available for those themes that don't register it.
     * @access public
     * @since  1.0.0
     */
    public function ensure_post_thumbnails_support()
    {
        if (!current_theme_supports('post-thumbnails')) {
            add_theme_support('post-thumbnails');
        }
    } // End ensure_post_thumbnails_support()
    
    function enter_email_subject()
    {
        // echo "subject goes here";
        global $post;
?>
       <input name="event_plugun_template_subject" value="<?php
        echo get_post_meta($post->ID, "event_plugun_template_subject", true);
?>" id="subject" style="width: 100%"
            spellcheck="true" autocomplete="off" type="text" placeholder="Email subject goes here">
       <?php
    }
    
    function template_params()
    {
?>
<table width="100%" class="wp-list-table widefat fixed striped posts" >
<thead>
<tr><th colspan="2" style="text-align: center" ><strong>Common Variables</strong></th></tr>
</thead>
<tr>
<td>{{event_name}}</td>
<td>Name of the event</td>
</tr>
<thead>
<tr><th colspan="2" style="text-align: center" ><strong>Successfully Booking</strong> </th></tr>
</thead>
<tr>
<td>{{event_address}}</td>
<td>Event address</td>
</tr>
<tr>
<td>{{event_date_start}}</td>
<td>Event start date</td>
</tr>
<tr>
<td>{{event_date_end}}</td>
<td>Event end date</td>
</tr>
<tr>
<td>{{ticket_list_info}}</td>
<td>Ticket information</td>
</tr>
<tr>
<td>{{booking_amount}}</td>
<td>Total booking amount</td>
</tr>
<thead>
  <tr><th colspan="2" style="text-align: center"><strong>Booking Status Change Variables</strong></th></tr>
</thead>
  <tr>
<td>{{booking_status}}</td>
<td>Booking Status</td>
</tr>
<tr>
<td>{{event_address}}</td>
<td>Event address</td>
</tr>
<tr>
<td>{{event_date_start}}</td>
<td>Event start date</td>
</tr>
<tr>
<td>{{event_date_end}}</td>
<td>Event end date</td>
</tr>
<tr>
<td>{{ticket_list_info}}</td>
<td>Ticket information</td>
</tr>
<tr>
<td>{{booking_amount}}</td>
<td>Total booking amount</td>
<thead>
</tr>
   <tr><th colspan="2" style="text-align: center"><strong>Checked In Event Variables</strong></th></tr>
<tr>
</thead>
<td>{{booking_name}} </td>
<td>Name of the &nbsp;booking
</tr>
  <tr>
<td>{{booking_status}}</td>
<td>Booking Status</td>
</tr>
<tr>
<td>{{ticket_name}}</td>
  <td>Ticket name</td>
</tr>
<tr>
<td>{{ticket_id}}</td>
  <td>Ticket ID</td>
</tr>
<tr>
<td>{{checkin_log}}</td>
<td>CheckIn login attempt
</tr>

</table>
        <?php
    }
} // End Class
