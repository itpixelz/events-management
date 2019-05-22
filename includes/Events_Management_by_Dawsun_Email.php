<?php 

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Events_Management_by_Dawsun_Email{

    protected $sender;
    protected $receivers;
    protected $subject;
    protected $message;
    protected $attachments = array();
    protected $template;
    protected $templateLayout;    
    protected $errors = array();
    protected $warnings = array();

    function __construct(){
        $this->templateLayout = __DIR__ . '/templates/layout.phtml';
    }

    function __set($var, $value){
        global $wpdb;
        switch($var){

            
            case "receiver":
            case "receivers":
                if(is_array($value)){
                    $value = implode(",", $value);
                }
                $query = "SELECT user_email, display_name FROM {$wpdb->prefix}users 
                            WHERE ID IN('{$value}') OR user_email IN('{$value}') OR user_login IN('{$value}')";
                $value = $wpdb->get_results($query, ARRAY_A); 
                $var = "receivers"; 
              //  echo '<pre>'; print_r($value); echo '</pre>'; exit;                   
            break;
            case "sender":
                $query = "SELECT user_email, display_name FROM {$wpdb->prefix}users 
                        WHERE ID = '{$value}' OR user_email='{$value}' OR user_login = '{$value}'";
                $value = $wpdb->get_row($query, ARRAY_A); 

               // echo '<pre>'; print_r($value); echo '</pre>' . $query; 

            break;

            case "subject":

            break;

            case "message":

            break;

            case "template":

            break;

            case "attachments":

            break;

            default:
                return false;
            break;            

        }

        $this->{$var} = $value;
    }


    function send(){
        

        //wp_mail();

        $this->template = apply_filters("event_plugun_email_template", $this->template, $this);

        if(is_array($this->template) && count($this->template) > 0 ){
            @extract($this->template);
          //  echo '<pre>'; print_r($this->template); echo '</pre>';
            ob_start();
            require_once($this->templateLayout);
            $this->message = ob_get_contents();
            ob_clean();
        }

        

        $this->sender = apply_filters("event_plugun_email_sender", $this->sender, $this);
        $this->receivers = apply_filters("event_plugun_email_receivers", $this->receivers, $this);
        $this->receivers = apply_filters("event_plugun_email_receiver", $this->receivers, $this);
        $this->subject = apply_filters("event_plugun_email_subject", $this->subject, $this);
        
        
        $this->attachments = apply_filters("event_plugun_email_attachments", $this->attachments, $this);

        $this->message = apply_filters("event_plugun_email_message", $this->message, $this);

        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=iso-8859-1';

        $receivers = $receivers_email = array();

        for($i =0, $count = count($this->receivers); $i < $count; $i++ ){
            $receivers[] = "{$this->receivers[$i]['display_name']} <{$this->receivers[$i]['user_email']}>";
            $receivers_email[] = $this->receivers[$i]['user_email'];            
        }

        $header = implode("\r\n", $headers);

        if($count > 0 ) $headers[] = "To: " . implode(", ", $receivers);
        $headers[] = "From: {$this->sender['display_name']} <{$this->sender['user_email']}>";


        // @file_put_contents(__DIR__ . "/Emails/" . $this->subject . '.html', $this->message);
        // @file_put_contents(__DIR__ . "/Emails/" . $this->subject . '.log', print_r($this, true));              

        if($count == 0 ) return ;        

        $is_sent = wp_mail( $receivers_email , $this->subject, $this->message, $header, $this->attachments );
       
        return $is_sent;
    }

    function __toString(){
        return '<pre>' . print_r($this, true) . '</pre>';
    }



}