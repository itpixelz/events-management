/**
     * All of the code for your admin-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */

(function($) {
    'use strict';

    function showHideCheckbox(on, fordiv) {

        if ($(on).is(':checked')) {

            $(fordiv).hide(0);

        } else {
            $(fordiv).show(0);
        }

    }
    





    $(document).ready(function() {



        $('#template-logo').live('click',function() {
        formfield = $('#upload_image').attr('name');
        tb_show('', 'media-upload.php?type=image&amp;post_id=0&amp;TB_iframe=true');

        return false;
        });

        window.send_to_editor = function(html) {
            imgurl = $('img',html).attr('src');
            console.debug(imgurl);
            $('input[name=event_plugun_template_logo]').val(imgurl);
            $('#template-logo-preview').attr('src',imgurl);
            tb_remove();
        }


        // Deactivate all currently active menus

        $("wp-has-current-submenu wp-menu-open")

        showHideCheckbox('#no_tickets', '#if_no_tickets');

        $('input.timepicker').timepicker({

            timeFormat: 'h:mm p',
            interval: 30,
            // minTime: '10',
            // maxTime: '6:00pm',
            //defaultTime: '11',
            startTime: '10:00',
            dynamic: true,
            dropdown: true,
            scrollbar: true
        });





        var event_ticket_default = {
            id: 0,
            name: 'Standard Ticket',
            description: '',
            price: '0.00',
            spaces: '',
            min: '',
            max: '',
            availablity_from_date: '',
            availablity_to_date: '',
            availablity_from_time: '',
            availablity_to_time: '',

            tickets_dates_for_checkin_from: '',
            tickets_dates_for_checkin_to: '',
            tickets_times_for_checkin_from: '',
            tickets_times_for_checkin_to: '',
            max_tickets_checkins: '1'
        };
        console.log(event_ticket_default);

        $('#add_new_ticket').click(function() {


            $('#ticket_id').val(0),
                $('#ticket_name').val(event_ticket_default.name),
                $('#ticket_description').val(event_ticket_default.description),
                $('#tickets_price').val(event_ticket_default.price),
                $('#ticket_spaces').val(event_ticket_default.spaces),
                $('#ticket_min').val(event_ticket_default.min),
                $('#ticket_max').val(event_ticket_default.max),
                $('#ticket_available_from').val(event_ticket_default.availablity_from_date),
                $('#ticket_available_to').val(event_ticket_default.availablity_to_date),
                $('#ticket_available_from_time').val(event_ticket_default.availablity_from_time),
                $('#ticket_available_to_time').val(event_ticket_default.availablity_to_time),


                $('#tickets_dates_for_checkin_from').val(event_ticket_default.tickets_dates_for_checkin_from),
                $('#tickets_dates_for_checkin_to').val(event_ticket_default.tickets_dates_for_checkin_to),
                $('#tickets_times_for_checkin_from').val(event_ticket_default.tickets_times_for_checkin_from),
                $('#tickets_times_for_checkin_to').val(event_ticket_default.tickets_times_for_checkin_to),

                $('#max_tickets_checkin').val(event_ticket_default.max_tickets_checkins),



                $(this).hide();
            $('.new_ticket').show();

            return false;


        });



        $('#add_ticket').click(function() {

            var event_ticket = event_ticket_default;
            event_ticket = {
                id: $('#ticket_id').val(),
                name: $('#ticket_name').val(),
                description: $('#ticket_description').val(),
                price: $('#tickets_price').val(),
                spaces: $('#ticket_spaces').val(),
                min: $('#ticket_min').val(),
                max: $('#ticket_max').val(),
                availablity_from_date: $('#ticket_available_from').val(),
                availablity_to_date: $('#ticket_available_to').val(),
                availablity_from_time: $('#ticket_available_from_time').val(),
                availablity_to_time: $('#ticket_available_to_time').val(),


                tickets_dates_for_checkin_from: $('#tickets_dates_for_checkin_from').val(),
                tickets_dates_for_checkin_to: $('#tickets_dates_for_checkin_to').val(),
                tickets_times_for_checkin_from: $('#tickets_times_for_checkin_from').val(),
                tickets_times_for_checkin_to: $('#tickets_times_for_checkin_to').val(),

                max_tickets_checkins: $('#max_tickets_checkins').val()
            };

            //  console.log(event_ticket);


            add_ticket(event_ticket);
            $('.new_ticket').hide();
            $('#add_new_ticket').show();

            return false;
        });


        function add_ticket(event_ticket) {

            console.log(event_ticket);

            var forminputs_html = '<div style="display:none;"><input name="ticket_id[]" value="' + event_ticket.id + '" /><input name="ticket_name[]" value="' + event_ticket.name + '" /><input name="tickets_price[]" value="' + event_ticket.price + '" /><input name="ticket_min[]" value="' + event_ticket.min + '" /><input name="ticket_max[]" value="' + event_ticket.max + '" /><input name="ticket_from_date[]" value="' + event_ticket.availablity_from_date + '" /><input name="ticket_to_date[]" value="' + event_ticket.availablity_to_date + '" /><input name="ticket_to_time[]" value="' + event_ticket.availablity_to_time + '" /><input name="ticket_from_time[]" value="' + event_ticket.availablity_from_time + '" /><input name="ticket_spaces[]" value="' + event_ticket.spaces + '" />';
            forminputs_html += '<input name="tickets_dates_for_checkin_from[]" value="' + event_ticket.tickets_dates_for_checkin_from + '" /><input name="tickets_dates_for_checkin_to[]" value="' + event_ticket.tickets_dates_for_checkin_to + '" /><input name="tickets_times_for_checkin_from[]" value="' + event_ticket.tickets_times_for_checkin_from + '" /><input name="tickets_times_for_checkin_to[]" value="' + event_ticket.tickets_times_for_checkin_to + '" /><input name="max_tickets_checkins[]" value="' + event_ticket.max_tickets_checkins + '" /></div>';

            var ticket_html = '<th scope="row">' + forminputs_html + event_ticket.name + '<!-- <br /><a class="edit_ticket" ticket_id="' + event_ticket.id + '"  href="#">Edit</a> --></th><td>' + event_ticket.price + '</td><td>' + event_ticket.min + '/' + event_ticket.max + '</td><td>' + event_ticket.availablity_from_date + ' ' + event_ticket.availablity_from_time + '<br />' + event_ticket.availablity_to_date + ' ' + event_ticket.availablity_to_time + '</td><td>' + event_ticket.spaces + '</td><td></td>';

            if (event_ticket.id == 0) {
                $(".tickets_body").append('<tr id="ticket_' + event_ticket.id + '">' + ticket_html + '</tr>');
            } else {
                $("#ticket_" + event_ticket.id).html(ticket_html);
            }

            return false;





        }



        $('table').on("click", '.edit_ticket', function() {



            var ticket_id = $(this).attr('ticket_id');
            console.log(ticket_id);
            var event_ticket_default = {
                id: $('#ticket_' + ticket_id + ' .it-id').val(),
                name: $('#ticket_' + ticket_id + ' .it-name').val(),
                description: $('#ticket_' + ticket_id + ' .it-name').val(),
                price: $('#ticket_' + ticket_id + ' .it-price').val(),
                spaces: $('#ticket_' + ticket_id + ' .it-spaces').val(),
                min: $('#ticket_' + ticket_id + ' .it-min').val(),
                max: $('#ticket_' + ticket_id + ' .it-max').val(),
                availablity_from_date: $('#ticket_' + ticket_id + ' .it-from-date').val(),
                availablity_to_date: $('#ticket_' + ticket_id + ' .it-to-date').val(),
                availablity_from_time: $('#ticket_' + ticket_id + ' .it-from-time').val(),
                availablity_to_time: $('#ticket_' + ticket_id + ' .it-to-time').val(),

                tickets_dates_for_checkin_from: $('#ticket_' + ticket_id + ' .it-checkin-from-date').val(),
                tickets_dates_for_checkin_to: $('#ticket_' + ticket_id + ' .it-checkin-to-date').val(),
                tickets_times_for_checkin_from: $('#ticket_' + ticket_id + ' .it-checkin-from-time').val(),
                tickets_times_for_checkin_to: $('#ticket_' + ticket_id + ' .it-checkin-to-time').val(),


                max_tickets_checkins: $('#ticket_' + ticket_id + ' .it-max-checkins').val(),


            };

            console.log(event_ticket_default);

            $('#ticket_id').val(event_ticket_default.id),
                $('#ticket_name').val(event_ticket_default.name),
                $('#ticket_description').val(event_ticket_default.description),
                $('#tickets_price').val(event_ticket_default.price),
                $('#ticket_spaces').val(event_ticket_default.spaces),
                $('#ticket_min').val(event_ticket_default.min),
                $('#ticket_max').val(event_ticket_default.max),
                $('#ticket_available_from').val(event_ticket_default.availablity_from_date),
                $('#ticket_available_to').val(event_ticket_default.availablity_to_date),
                $('#ticket_available_from_time').val(event_ticket_default.availablity_from_time),
                $('#ticket_available_to_time').val(event_ticket_default.availablity_to_time)

            $('#tickets_dates_for_checkin_from').val(event_ticket_default.tickets_dates_for_checkin_from),
                $('#tickets_dates_for_checkin_to').val(event_ticket_default.tickets_dates_for_checkin_to),
                $('#tickets_times_for_checkin_from').val(event_ticket_default.tickets_times_for_checkin_from),
                $('#tickets_times_for_checkin_to').val(event_ticket_default.tickets_times_for_checkin_to),

                $('#max_tickets_checkins').val(event_ticket_default.max_tickets_checkins)


            $('.new_ticket').show();





            return false;


        });



        // $('.timeonlypicker-input').timepicker({
        // 	timeInput: false,
        // 	timeFormat: "hh:mm",
        // 	showHour: false,
        // 	showMillisecond: false,
        // 	showMinute: false
        // });





        // $('.timeonlypicker-input').text(
        // $.datepicker.formatTime('HH:mm z', { hour: 14, minute: 36, timezone: '+2000' }, {})
        // );


        var dateFormat = "yy-mm-dd",
            from = $("#event_date_end")
            .datepicker({
                defaultDate: "+1w",
                changeMonth: true,
                changeYear: true,
                numberOfMonths: 1,
                dateFormat: "yy-mm-dd"
            })
            .on("change", function() {
                to.datepicker("option", "maxDate", getDate(this));
            }),
            to = $("#event_date_start").datepicker({
                defaultDate: "+1w",
                changeMonth: true,
                changeYear: true,
                numberOfMonths: 1,
                dateFormat: "yy-mm-dd"
            })
            .on("change", function() {
                from.datepicker("option", "minDate", getDate(this));
            });


        var from1 = $("#ticket_available_to")
            .datepicker({
                defaultDate: "+1w",
                changeMonth: true,
                changeYear: true,
                numberOfMonths: 1,
                dateFormat: "yy-mm-dd"
            })
            .on("change", function() {
                to1.datepicker("option", "maxDate", getDate(this));
            }),
            to1 = $("#ticket_available_from").datepicker({
                defaultDate: "+1w",
                changeMonth: true,
                changeYear: true,
                numberOfMonths: 1,
                dateFormat: "yy-mm-dd"
            })
            .on("change", function() {
                from1.datepicker("option", "minDate", getDate(this));
            });


        var from2 = $("#tickets_dates_for_checkin_to")
            .datepicker({
                defaultDate: "+1w",
                changeMonth: true,
                changeYear: true,
                numberOfMonths: 1,
                dateFormat: "yy-mm-dd"
            })
            .on("change", function() {
                to2.datepicker("option", "maxDate", getDate(this));
            }),
            to2 = $("#tickets_dates_for_checkin_from").datepicker({
                defaultDate: "+1w",
                changeMonth: true,
                changeYear: true,
                numberOfMonths: 1,
                dateFormat: "yy-mm-dd"
            })
            .on("change", function() {
                from2.datepicker("option", "minDate", getDate(this));
            });




        function getDate(element) {


            var date;
            try {
                date = $.datepicker.parseDate(dateFormat, element.value);
            } catch (error) {
                date = null;
            }

            return date;
        }


       


        $('#all_day_event').change(function() {
            showHideCheckbox('#all_day_event', '#if_no_all_day');
        });

        $('#no_physical_location').change(function() {
            showHideCheckbox('#no_physical_location', '#if_no_physical_location');
        });
        $('#no_tickets').change(function() {
            showHideCheckbox('#no_tickets', '#if_no_tickets');
        });



        showHideCheckbox('#all_day_event', '#if_no_all_day');
        showHideCheckbox('#no_physical_location', '#if_no_physical_location');





    });


    function wp_deactivate_menus() {
        var $sidebar = jQuery("#adminmenu");
        var $active_menus = $sidebar.children('li.current, li.wp-has-current-submenu, li.wp-menu-open');

        // Close all open menus
        $active_menus.each(function() {
            var $this = jQuery(this);

            // Conditional classes
            if ($this.hasClass('wp-has-current-submenu'))
                $this.addClass('wp-not-current-submenu');

            // Unconditional classes
            $this
                .removeClass('current')
                .removeClass('wp-menu-open')
                .removeClass('wp-has-current-submenu')
                .addClass('wp-not-current-submenu');

            // Remove "current" from all submenu items, too
            $this.find('ul.wp-submenu li a.current').removeClass('current');
        });
    }

    // Activate a Wordpress menu and optionally highlight a submenu slug within that category
    // menu_id = String, such as "#my-menu-id". (Not necessarily an ID, but a selector to select the <li>) 
    // slug = String, such as "edit.php?post-type=page". Must be exactly the same href as the submenus a[href]
    function wp_activate_menu(menu_id, slug) {
        var $sidebar = jQuery("#adminmenu");
        var $menu = $sidebar.find(menu_id);

        if (!$menu || $menu.length < 1) return false;

        // Conditional classes
        if ($menu.hasClass('wp-has-submenu'))
            $menu.addClass('wp-has-current-submenu');

        // Unconditional classes
        $menu
            .addClass('current')
            .addClass('wp-menu-open')
            .removeClass('wp-not-current-submenu');

        if (typeof slug == 'undefined') return;

        // Begin activating the submenu
        var $submenu = $menu.find('a[href="' + slug + '"]');

        if (!$submenu || $submenu.length < 1) return;

        $submenu.parent('li').addClass('current');
    }




})(jQuery);