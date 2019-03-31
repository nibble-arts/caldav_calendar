<?php

require_once ('calDAV/SimpleCalDAVClient.php');
require_once ("lib/calparser.php");
require_once ("lib/datetime.php");


define('CALDAV_CAL_BASE', $pth['folder']['plugins']);
define('CALDAV_CAL_URL', $plugin_cf['caldav_calendar']['url']);
define('CALDAV_CAL_USER', $plugin_cf['caldav_calendar']['user']);
define('CALDAV_CAL_PASSWORD', $plugin_cf['caldav_calendar']['password']);
define('CALDAV_MAX_DAYS', $plugin_cf['caldav_calendar']['show_max_days']);
define('CALDAV_SHOW_TIME', $plugin_cf['caldav_calendar']['show_time']);
define('CALDAV_FIELDS', $plugin_cf['caldav_calendar']['list_fields']);

define('CALDAV_CAL_NOT_FOUND', $plugin_tx['caldav_calendar']['cal_not_found']);



function caldav_calendar($calendar, $format="") {

	$ret = "";
	$cal = new SimpleCalDAVClient();

	try {

		$cal->connect(CALDAV_CAL_URL, CALDAV_CAL_USER, CALDAV_CAL_PASSWORD);

		$calendars = $cal->FindCalendars();

		// calender found on server
		if (isset($calendars[$calendar])) {

			$cal->setCalendar($calendars[$calendar]);

			$events = $cal->getEvents(time2iso(time()));

			$eventArray = [];

			$data = new CalParser($events);
			$data->sort("VEVENT","DTSTART");

			//TODO
			// switch display format
			switch ($format) {

				case "list":
				default:
				// create event list
				$ret .= "<div id='plge_calendar'>";

					$i = 0;
					while (($event = $data->get()) && ($i < CALDAV_MAX_DAYS || CALDAV_MAX_DAYS == 0)) {
						$ret .= $event->render();
						$i++;
					}

				$ret .= "</div>";

			}
		}

		// celender not found on server
		else
			$ret .= "Calendar ''".$calendar."'' not found";

		return $ret;

	}

	// connection to CALDav server failed
	catch (Exception $e) {
		return "<div class='xh_fail'>".CALDAV_CAL_NOT_FOUND."</div>";
	}
}
?>