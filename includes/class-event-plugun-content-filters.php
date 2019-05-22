<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly.

/**
 * Starter Plugin Post Type Class
 *
 * All functionality pertaining to post types in Starter Plugin.
 *
 * @package WordPress
 * @subpackage 	Events_Management_By_Dawsun	
 * @category Plugin
 * @author Matty
 * @since 1.0.0
 */
class Events_Management_by_Dawsun_Content_Filters
{
    
    /**
     * The upcoming events html.
     * @access public
     * @since  1.0.0
     * @var    string
     */
    public $up_coming_events_html = '';
    
    /**
     * Constructor function.
     * @access public
     * @since 1.0.0
     */
    public function __construct()
    {
        
        
        
add_filter( 'the_content', 'upcoming_event_filter' );
        
        
       
        
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
    
    
     

    public function upcoming_event_filter($content)
    {
         

        $this->up_coming_events_html .= '<div class="events-wrapper">';
        

        $args = [
            'post_type'      => 'plugun-event',
            'posts_per_page' => 10,
            'order' => 'ASC',
            'orderby' => 'meta_value_num',
            'meta_key' => 'date_start_number'
        ];
        $loop = new WP_Query($args);
        while ($loop->have_posts()) {
            $loop->the_post();


         $this->up_coming_events_html .=  ' <div class="event-looper">
               <h2><a href="'.get_the_permalink().'">'. get_the_title(). '</a></h2>'

            .'<p><span class="dashicons dashicons-location-alt"></span> '.$this->get_meta(get_the_id(), 'event_location').' <br />
            <span class="dashicons dashicons-calendar-alt"></span> '.$this->get_meta(get_the_id(), 'event_date_start').' - 
            '.$this->get_meta(get_the_id(), 'event_date_end').'</p>'.

            '<div class="event-description"><h3>Event Description:</h3>'.
                get_the_content()

                .'</div><div class="event-tickets">'. $this->event_tickets_content(get_the_id()) .'</div>'.
            '</div>
            <hr />
            ';
           
        }
    wp_reset_query();

        $this->up_coming_events_html .= '</div>';




        return $this->up_coming_events_html;
    
    
    
        
        
    }


    public function event_tickets_content($event_id){


        $ticket_content = '<h3>Tickets</h3>';

        $args = [
            'post_type'  => 'plugun-ticket',
            'meta_key'   => 'ticket_event',
            'meta_value' => $event_id
        ];
        $loop = new WP_Query($args);

        if ( $loop->have_posts() )
        {
            while ($loop->have_posts()) {
                $loop->the_post();


             $ticket_content .=  
                '<div class="ticket-looper"><span class="dashicons dashicons-tickets-alt"></span> 
                '. get_the_title(). ' - $'.$this->get_meta(get_the_id(), 'ticket_price').'<br />'
                .'</div>';
            }
        }
        else
        {
            $ticket_content .= '<p>no tickets</p>';
        }

        


    wp_reset_query();


        return $ticket_content;
    }

    
    
   
} // End Class