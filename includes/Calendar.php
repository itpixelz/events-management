<?php

if (!defined('WPINC')) {
    die;
}

class DepLiteCalendar
{
    
    protected $errors = array();
    protected $months = array(NULL, "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
    protected $weekDays = array(NULL, "Mon", "Tue", "Wed", "Thur", "Fri", "Sat", "Sun");
    protected $date;
    protected $year;
    protected $month;
    protected $day;
    
    
    function __construct($year = NULL, $month = NULL, $day = NULL)
    {
        
        if ($year) {
            $this->__set("year", $year);
        }
        if ($month) {
            $this->__set("month", $month);
        }
        if ($day) {
            $this->__set("day", $day);
        }
        
        add_action("wp", array(
            $this,
            "show"
        ));
        
        add_shortcode("event_plugun_calendar", array(
            $this,
            "integrate_calender"
        ));
        
        add_action('wp_enqueue_scripts', array(
            $this,
            'load_scripts'
        ));
        add_action('wp_enqueue_style', array(
            $this,
            'load_style'
        ));
        
        add_filter("plugun_calendar_events_date", array(
            $this,
            'get_events'
        ));
        
    }
    
    function get_events($date)
    {
        global $wpdb;
        $query  = "SELECT * FROM {$wpdb->prefix}posts 
                    LEFT JOIN {$wpdb->prefix}postmeta start_date ON {$wpdb->prefix}posts.ID = start_date.post_id AND start_date.meta_key = 'event_date_start'
                    LEFT JOIN {$wpdb->prefix}postmeta end_date ON {$wpdb->prefix}posts.ID = end_date.post_id AND end_date.meta_key = 'event_date_end'
                    WHERE '{$date}' BETWEEN DATE(start_date.meta_value) AND DATE(end_date.meta_value)  AND  post_type = 'plugun-event' ";
        $events = $wpdb->get_results($query);
        
        $content = '<ul class="day_e">';
        
        for ($i = 0, $count = count($events); $i < $count; $i++) {
            $content .= "<li class=\"entry event\"><a href=\"{$events[$i]->guid}\" target=\"_blank\">{$events[$i]->post_title}</a></li>";
        }
        
        $content .= '</ul>';
        
        return $content;
    }
    
    function load_scripts()
    {
        
        wp_enqueue_script('event-plugun-calendar-js', DEP_LITE_PLUGIN_URL . "/public/js/event-plugun-calendar.js");
        wp_enqueue_style('event-plugun-calendar-css', DEP_LITE_PLUGIN_URL . "/public/css/event-plugun-calendar.css");
        
    }
    
    function load_style()
    {
        wp_enqueue_style('event-plugun-calendar-css', DEP_LITE_PLUGIN_URL . "/public/css/event-plugun-calendar.css");
    }
    
    function integrate_calender($args)
    {
        $params = '';
        if (isset($args["year"])) {
            $params = $args["year"];
        }
        
        if (isset($args["month"])) {
            if (isset($args["year"])) {
                $params .= ",";
            } else
                $params .= "NULL, ";
            $params .= $args["month"];
        }
        echo '<script>draw(' . $params . ');</script>';
        $content = '<div id="event_plugun_calendar_container"></div>';
        return $content;
    }
    
    function show()
    {
        if (isset($_REQUEST["process"])) {
            switch ($_REQUEST["process"]) {
                case "show_plugun_event_calendar":
                    if (isset($_REQUEST["cal_year"])) {
                        $this->year = $_REQUEST["cal_year"];
                    }
                    
                    if (isset($_REQUEST["cal_month"])) {
                        $this->month = $_REQUEST["cal_month"];
                    }
                    
                    echo $this->draw();
                    exit;
                    break;
            }
        }
        
    }
    
    function __set($var, $value)
    {
        switch ($var) {
            case "year":
                if (!preg_match("#[0-9]+#", $var)) {
                    $errors["year"] = "Invalid value {$value} for year";
                }
                break;
            
            case "month":
                if (!preg_match("#[0-9]{1,2}#", $var) && $var < 1 && $var > 12) {
                    $errors["month"] = "Invalid value {$value} for month";
                }
                break;
            
            case "day":
                if (!preg_match("#[0-9]{1,2}#", $var) && $var < 1 && $var > 31) {
                    $errors["day"] = "Invalid value {$value} for day";
                }
                break;
                
        }
        
        $this->{$var} = (int) $value;
    }
    
    function draw()
    {
        
        if (!$this->year) {
            $this->year = date("Y");
        }
        
        if (!$this->month) {
            $this->month = date("m");
        }
        
        if (!$this->day) {
            $this->day = date("d");
        }
        
        $month_date = strtotime("{$this->year}-{$this->month}-01");
        if (in_array($this->month, array(
            1,
            3,
            5,
            7,
            8,
            10,
            12
        ))) {
            $last_day = 31;
        } else if (in_array($this->month, array(
                4,
                6,
                9,
                11
            ))) {
            $last_day = 30;
        } else {
            $last_day = ($this->year % 4 == 0) ? 29 : 28;
        }
        
        $start_day = date("D", $month_date);
        
        $start_day_index = array_search($start_day, $this->weekDays);
        
        $date    = 1;
        $weekday = 0;
        $start_day_index--;
        
        $table = '<div class="select_date"><div class="month-left"><select id="event_plugun_calendar_month1" class="event_plugun_calendar_month_changer">';
        
        for ($i = 1, $count = count($this->months); $i < $count; $i++) {
            $selected = ($i == $this->month) ? ' selected="selected" ' : " ";
            $table .= "<option value=\"{$i}\" {$selected}>{$this->months[$i]}</option>";
        }
        
        $table .= '</select></div><div class="years-left"><select id="event_plugun_calendar_year1" class="event_plugun_calendar_year_changer">';
        
        for ($i = 2014, $count = (int) date("Y") + 1; $i <= $count; $i++) {
            $selected = ($i == $this->year) ? ' selected="selected" ' : " ";
            $table .= "<option value=\"{$i}\" {$selected}>{$i}</option>";
        }
        
        $table .= '</select></div></div>';
        
        
        
        $table .= '<table class="calendar" width="100%"><thead><tr>';
        
        for ($i = 1, $count = count($this->weekDays); $i < $count; $i++) {
            $table .= '<th>' . $this->weekDays[$i] . '</th>';
        }
        
        $table .= '</tr></thead><tbody><tr>';
        
        while ($date <= $last_day) {
            $date_contents = "";
            if ($weekday % 7 == 0) {
                $table .= '</tr><tr>';
                //$weekday = 0;
            }
            if ($weekday >= $start_day_index) {
                $display = '<span class="day_r">' . $date . '</span>';
                
                $display .= apply_filters("plugun_calendar_events_date", /*$date_contents,*/ $this->year . '-' . $this->month . '-' . $date);
                $date++;
            } else
                $display = '';
            $style = ($weekday % 2 == 0) ? ' style="background-color: #eee" ' : '';
            $table .= '<td ' . $style . ' class="date day">' . $display . '</td>';
            $weekday++;
            
        }
        
        $table .= '</tr></tbody></table>';
        
        return $table;
    }
    
    function __toString()
    {
        return "<pre>" . print_r($this, true) . '</pre>';
    }
    
}