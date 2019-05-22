function watch_changes() {

    jQuery("#event_plugun_calendar_month1, #event_plugun_calendar_year1").change(function() {
        var month = jQuery("#event_plugun_calendar_month1").val();
        var year = jQuery("#event_plugun_calendar_year1").val();
        draw(year, month);
    });

}


function draw(year, month) {
    jQuery("#event_plugun_calendar_container").html(" Loading ....");
    jQuery.ajax({
        data: { "cal_year": year, "cal_month": month, "process": "show_plugun_event_calendar" },
        "type": "POST"
    }).done(function(html) {
        jQuery("#event_plugun_calendar_container").html(html);
        watch_changes();
    });
}