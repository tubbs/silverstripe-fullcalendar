;(function($) {
	$("#full-calendar").fullCalendar({
		events: $("#full-calendar").data("events")
	});
})(jQuery);