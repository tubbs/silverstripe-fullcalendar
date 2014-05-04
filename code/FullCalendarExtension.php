<?php
/**
 * Adds a field to the default calendar which allows an admin to enable or
 * disable the fullcalendar view.
 *
 * @package silverstripe-fullcalendar
 */
class FullCalendarExtension extends DataExtension {

	private $passed = false;

	public static $db = array(
		'UseFullCalendar' => 'Boolean(1)'
	);
	
	public function updateCMSFields(FieldList $fields) {

		if(!$this->passed) {

			// hack pour contourner le double appel de updateCMSFields
			// il faudrait enlever updateCMSFields dans la classe Calendar
			$this->passed = true;

			$locale = substr(Fluent::current_locale(), 0, 2);

			Requirements::css('silverstripe-fullcalendar/thirdparty/jquery-fullcalendar/fullcalendar.css');
			
			Requirements::javascript('silverstripe-fullcalendar/thirdparty/jquery-fullcalendar/moment.min.js');
			// Requirements::javascript('silverstripe-fullcalendar/thirdparty/jquery-fullcalendar/jquery-ui.custom.min.js');
			Requirements::javascript('silverstripe-fullcalendar/thirdparty/jquery-fullcalendar/fullcalendar.min.js');
			Requirements::javascript("silverstripe-fullcalendar/thirdparty/jquery-fullcalendar/lang/$locale.js");
	
			$request = Controller::curr()->getRequest();
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
					'url'       => $evt->ClassName == 'CalendarDateTime'
									? Controller::join_links('admin/pages/edit/show', $evt->Event()->ID)
									: Controller::join_links('admin/pages/edit/EditForm/field/Announcements/item', $evt->ID, 'edit'),
					'className' => $evt->ClassName,
				);
			}
			$response = Convert::array2json($result);
			
			$calendarView = _t('FullCalendarExtension.CalendarView', 'Calendar View');
			$fields->addFieldToTab(
				"Root.$calendarView",
				new LiteralField(
					'FullCalendar',
					"<div id='full-calendar'></div>
					<script type='text/javascript'>
					//<![CDATA[
					jQuery('#full-calendar').fullCalendar({
						header: {
							left: 'prev,next today',
							center: 'title',
							right: 'month,agendaWeek,agendaDay'
						},
						events: $response,
						lang: '$locale'
					});
					// jQuery('#full-calendar').fullCalendar('today');
					//]]>
					</script>"
				)
			);

		}

	}

	public function updateSettingsFields(FieldList $fields) {
		$fields->addFieldToTab('Root.Settings', CheckboxField::create('UseFullCalendar', _t('FullCalendarExtension.DisplayInFullLayout', 'Display in a full calendar layout?')));
	}

}
