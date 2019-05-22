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
class Events_Management_by_Dawsun_Shotcodes
{

    /**
     * The upcoming events html.
     * @access public
     * @since  1.0.0
     * @var    string
     */
    public $up_coming_events_html = '';
    /**
     * The upcoming events html.
     * @access public
     * @since  1.0.0
     * @var    string
     */
    public $up_coming_event_filter_html = '';

    /**
     * Constructor function.
     * @access public
     * @since 1.0.0
     */
    public function __construct()
    {
        add_action('init', array(
            $this,
            'short_codes_int'
        ));
        add_filter('the_content', array(
            $this,
            'up_coming_event_filter'
        ));
        add_filter('widget_text', 'do_shortcode');
    } // End __construct()



    public function get_meta($post_id, $value)
    {
        $field = get_post_meta($post_id, $value, true);
        if (!empty($field)) {
            return is_array($field) ? stripslashes_deep($field) : stripslashes(wp_kses_decode_entities($field));
        } else {
            return false;
        }
    }


    public function short_codes_int()
    {
        add_shortcode('plugun_events', array(
            $this,
            'up_coming_events'
        ));
    }

    public function up_coming_events($attr = array())
    {
        $this->up_coming_events_html .= '<div class="events-wrapper">';


        $current_date_num = date('YmdHi');


        $args = array(
            'post_type' => 'plugun-event',
            'posts_per_page' => 10,
            'order' => 'ASC',
            'orderby' => 'meta_value_num',
            'meta_key' => 'date_start_number',
            'meta_query' => array(
                                array(
                                    'key'     => 'date_start_number',
                                    'value'   => $current_date_num,
                                    'compare' => '>=',
                                )
                                ),
        
            );

        if(isset($attr["category_id"])){
            if(strstr($attr["category_id"], ",")) $categories = explode(",", $attr["category_id"] );
            else $categories = array($attr["category_id"]);
            $args['tax_query'] = array(array(
                'taxonomy' => "plugun-event-category" ,
                'field' => 'term_id',
                'terms' => $categories,
                'operator' => 'IN'
            ) )  ;
        }

        if(isset($attr["event_id"])){
            if(strstr($attr["event_id"], ",")) $events = explode(",", $attr["event_id"] );
            else $events = array($attr["event_id"]);
            $args['post__in'] = $events;
        }    

        $loop = new WP_Query($args);
        while ($loop->have_posts()) {
            $loop->the_post();

            $event_start = $this->get_meta(get_the_id(), 'event_date_start');
            $event_end = $this->get_meta(get_the_id(), 'event_date_end');
            $event_dates = $this->date_ranges_events($event_start, $event_end);




            $this->up_coming_events_html .= ' <div class="event-looper">
               <h2><a href="' . get_the_permalink() . '">' . get_the_title() . '</a></h2>' . '<p><span class="dashicons dashicons-location-alt"></span> ' . $this->get_meta(get_the_id(), 'event_location') . ' <br />
            <span class="dashicons dashicons-calendar-alt"></span> ' . $event_dates . '</p>' . '<div class="event-description"></div>
            <!-- <div class="event-tickets">' . $this->event_tickets_content(get_the_id()) . '</div> -->' . '</div>
            <hr />
            ';
        }
        wp_reset_query();

        $this->up_coming_events_html .= '</div>';




        return $this->up_coming_events_html;
    }

    public function date_ranges_events($event_start, $event_end)
    {
        $event_dates = $event_start . ' - ' . $event_end;




        if ($event_start == $event_end) {
            $event_dates = $event_start;
        }


        $event_dates .= '<br />';
        $event_dates .= '<span class="dashicons dashicons-clock"></span> ';
        $event_dates .= ($this->get_meta(get_the_id(), 'all_day_event') == null) ? $this->get_meta(get_the_id(), 'event_time_start') . ' - ' . $this->get_meta(get_the_id(), 'event_time_end') : "All day event";



        return $event_dates;
    }


    public function up_coming_event_filter($content)
    {
        if (!is_singular('plugun-event') && !is_tax('plugun-event-category')) {
            return $content;
        }

        $this->up_coming_event_filter_html .= '<div class="events-wrapper">';

        global $post;


        $event_start = $this->get_meta(get_the_id(), 'event_date_start');
        $event_end = $this->get_meta(get_the_id(), 'event_date_end');
        $event_dates = $this->date_ranges_events($event_start, $event_end);

        $this->up_coming_event_filter_html .= '<div class="event-looper">

            <p><span class="dashicons dashicons-location-alt"></span> ' . $this->get_meta(get_the_id(), 'event_location') . ' <br />
            <span class="dashicons dashicons-calendar-alt"></span> ' . $event_dates . '</p>' .
            '<div class="event-description"><strong>Event Description:</strong><br />' . get_the_content() . '</div>
            <div class="event-location"><br /><p><strong>Event Location:</strong><br />';


            if ($this->get_meta(get_the_id(),'physical_location') == 1) {
                $this->up_coming_event_filter_html .= $this->get_meta(get_the_id(),'event_location');
                $this->up_coming_event_filter_html .= ($this->get_meta(get_the_id(),'event_city') != '') ? ',<br />' . $this->get_meta(get_the_id(),'event_city') : "";
                $this->up_coming_event_filter_html .= ($this->get_meta(get_the_id(),'event_postal') != '') ? ',<br />' . $this->get_meta(get_the_id(),'event_postal') : "";
                $this->up_coming_event_filter_html .= ($this->get_meta(get_the_id(),'event_state') != '') ? ',<br />' . $this->get_meta(get_the_id(),'event_state') : "";
                $this->up_coming_event_filter_html .= ($this->get_meta(get_the_id(),'event_country') != '') ? ',<br />' . $this->get_meta(get_the_id(),'event_country') : "";

                if(get_option("event_plugun_google_map_api")){
                ?>
<!--                
<script>
    	var address = "<?php echo $this->get_meta(get_the_id(),'event_location') ?>, <?php echo $this->get_meta(get_the_id(),'event_city') ?>";
      function initMap() {

        var map = new google.maps.Map(document.getElementById('map'), {
          zoom: 15,
        });
        var geocoder = new google.maps.Geocoder();
        //geocoder.getUiSettings().setMapToolbarEnabled(true);
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
    -->


                <?php



                //$this->up_coming_event_filter_html .= '<div id="map" style="height: 400px; width: 300px;"></div>';


                dep_get_gmap_route($this->get_meta(get_the_id(),'event_location') . ", " . $this->get_meta(get_the_id(),'event_city'));
              }  
            } else {
				$webinar = get_post_meta(get_the_id(), "webinar_url", true);
				if($webinar){
					$this->up_coming_event_filter_html .= '<a href="'.$webinar.'" target="_blank">Webinar Link</a>';
				}
				else $this->up_coming_event_filter_html .= 'No physical location';
            }

            $this->up_coming_event_filter_html .= '</p></div>


            <div class="event-tickets">'
             . $this->event_tickets_content(get_the_id()) . '</div>' . '</div>
            ';



        $this->up_coming_event_filter_html .= '</div>';




        return $this->up_coming_event_filter_html;
    }


    public function event_tickets_content($event_id)
    {
 

		$errors = EpLite_Custom_Error::get_by_entity("buy_event_tickets");
		
		$default = $_POST;
		
        $ticket_content = '<strong>Bookings</strong><br />';

        $booking_dates = deplite_booking_opening_closing_dates($event_id);

        $ticket_content .= '<p> <span>Booking Start Date: </span> <strong>' . date("F d, Y h:i A", $booking_dates["booking_open"]) . "</strong></p>";        
        $ticket_content .= '<p> <span>Booking Close Date: </span> <strong>' . date("F d, Y h:i A", $booking_dates["booking_close"]) . "</strong></p>";     

 		
		$success_msg = DepLiteRegistry::get("user_booking_saved");
		
		if($success_msg) {
			echo '<div class="success success-block">'.$success_msg.'</div>';
            $booking_id = DepLiteRegistry::get("booking_id");
          

            if(@$_POST["payment"]){

                $class = $_POST["payment"];

                $payment = new $class();

                $payment->form($booking_id);

            }


		}

        else {
		
		if(count($errors) > 0){
					$ticket_content .= '<ul class="error-list errors">';
					foreach($errors as $source => $messages){
						$ticket_content .= '<li class="error">' .$messages[0] . '</li>';
					}					
					$ticket_content .= '</ul>';
				}

        $ticket_ids = get_deplite_available_tickets($event_id);    

       

        if(count($ticket_ids) == 0) return $ticket_content;    

        $args = array(
            'post_type' => 'plugun-ticket', //  'any'
            'meta_key' => 'ticket_event',
            'meta_value' => $event_id,
            'post__in' => $ticket_ids
        );

        $loop = new WP_Query($args);

 
        if ($loop->have_posts()) {
            $ticket_content .= '
          <form method="POST">
          <input name="user_booking_wpnonce" value="'.wp_create_nonce('eplite_user_booking').'" type="hidden">
		  <input name="action" value="buy_event_tickets" type="hidden">
		  <input name="event_id" value="' . $event_id . '" type="hidden">
          <table class="table table-bordered">
             <thead>
                <tr>
                   <th>Ticket</th>
                   <th>Price</th>
                   <th>Spaces</th>
                </tr>
             </thead>
             <tbody class="tickets_body">';

            while ($loop->have_posts()) {
                $loop->the_post();
				global $post;
				$ticket_id = $post->ID;
                $price = get_deplite_ticket_price(get_the_id()); 

                if ($price != '' || $price != 0) {
                    $price = EpLiteCurrency::get_format( $price) ;
                } else {
                    $price = 'Free';
                }
				
				$min = 	get_post_meta($ticket_id, "min_tickets", true);
				$max = 	get_post_meta($ticket_id, "max_tickets", true);
				
				if(!$max){
					$quantity = get_dep_lite_ticket_available_quantity($ticket_id);//get_post_meta($ticket_id, "quantity", true); 
					if($quantity > 100 || $quantity < 0) $max = 100;
					else $max = $quantity;
				} 
				
				
				
				
                $ticket_content .= '

                <tr>
                <td>' . get_the_title() . '</td>
                <td>' . $price . '</td>
                <td>
                <select name="ticket_qty['.$ticket_id.']"><option value="0">0</option>';
					
                for($i=$min; $i <= (int) $max; $i++ ){
					$selected = ($i == @$default["ticket_qty"][$ticket_id]) ? ' selected="selected" ' : '';
					$ticket_content .='<option value="'.$i.'"  '.$selected.'>'.$i.'</option>';
				}  
                $ticket_content .='</select>
                </td>
                </tr>' ;
            }

            $ticket_content .= '</tbody></table>';
			
				
				
			$ticket_content .= '	
            <label for="event_booking_name">Name</label>
            <input type="text" id="event_booking_name" name="event_booking_name" value="'.@$default["event_booking_name"].'" />

            <label for="event_booking_phone">Phone</label>
            <input type="tel" id="event_booking_phone" name="event_booking_phone" value="'.@$default["event_booking_phone"].'" />

            <label for="event_booking_email">Email</label>
            <input type="email" id="event_booking_email" name="event_booking_email"  value="'.@$default["event_booking_email"].'" />

            <label for="event_booking_ownership">Ownership</label>
            <select id="event_booking_ownership" name="ownership">
                  <option value="multiple_owner">Multiple Owner</option>
                  <option value="single_owner">Single Owner</option>
            </select>';
            $paymnet_methods = array();
            if(function_exists("load_payments")){
                $paymnet_methods = load_payments();
            }
            
          
            if( count( $paymnet_methods ) > 0 ){
            $ticket_content .= '            
            <label for="event_booking_ownership">Payment Method</label>
            <select id="event_booking_payment" name="payment">';
                  foreach($paymnet_methods as $name =>$obj){
                    $ticket_content .= '<option value="'.get_class($obj).'">'.get_class($obj).'</option>';           
                  }
            $ticket_content .= '            </select>';

            }

            $ticket_content .= '            

            <label for="event_booking_comment">Comments</label>
            <textarea id="event_booking_comment" name="event_booking_comment">'.@$default["event_booking_comment"].'</textarea>
            <input type="submit" value="Book Now" />
            </from>';
        } else {
            $ticket_content .= '<p>Tickets are not avaiabale.</p>';
        }

        wp_reset_query();

        }

        return $ticket_content;
    }
} // End Class
