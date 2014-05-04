<?php
/**
 * Renders the calendar with the fullcalendar template, and returns the JSON
 * events data.
 *
 * @package silverstripe-fullcalendar
 */
class FullCalendarControllerExtension extends Extension {

	public static $allowed_actions = array(
		'eventsdata'
	);

	/**
	 * @return string|array
	 */
	public function onAfterInit() {
		
		if (!$this->owner->UseFullCalendar) return array();

		Requirements::css('silverstripe-fullcalendar/thirdparty/jquery-fullcalendar/fullcalendar.css');
		
		Requirements::javascript('silverstripe-fullcalendar/thirdparty/jquery-fullcalendar/moment.min.js');
		// Requirements::javascript('silverstripe-fullcalendar/thirdparty/jquery-fullcalendar/jquery-ui.custom.min.js');
		Requirements::javascript('silverstripe-fullcalendar/thirdparty/jquery-fullcalendar/fullcalendar.min.js');
		Requirements::javascript('silverstripe-fullcalendar/javascript/FullCalendar.js');
	}

	/**
	 * Handles returning the JSON events data for a time range.
	 *
	 * @param  SS_HTTPRequest $request
	 * @return SS_HTTPResponse
	 */
	public function eventsdata($request) {
		$start = $request->getVar('start');
		$end   = $request->getVar('end');

		$result = array();

		$events = $this->owner->getEventList($start, $end);
		if ($events) foreach ($events as $evt) {
			$result[] = array(
				'id'        => $evt->ID,
				'title'     => $evt->Title,
				'start'     => $evt->StartDate . ($evt->StartTime !== null ? 'T' . $evt->StartTime : ''),
				'end'       => $evt->EndDate . ($evt->EndTime !== null ? 'T' . $evt->EndTime : ''),
				'allDay'    => $evt->AllDay ? true : false,
				'url'       => $evt->ClassName == 'CalendarDateTime' ? $evt->Link() : '',
				'className' => $evt->ClassName,
			);
		}

		$response = new SS_HTTPResponse(Convert::array2json($result));
		$response->addHeader('Content-Type', 'application/json');
		return $response;
	}

}