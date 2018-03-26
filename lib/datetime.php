<?php

/* Prevent direct access.
 */
if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

function time2iso($timestamp) {
	return date("Ymd\THis\Z",$timestamp);
}


function iso2time($string) {
	if (is_array($string))
		$string = $string[0];

	return strtotime($string);
}


function dateFromISO ($string) {
//	$dayArray = ["Montag","Dienstag","Mittwoch","Donnerstag","Freitag","Samstag","Sonntag"];
//	$monArray = ["Jänner","Februar","März","April","Mai","Juni","Juli","August","Spetember","Oktober","November","Dezember"];

	$dayArray = ["Mo","Di","Mi","Do","Fr","Sa","So"];
	$monArray = ["Jänner","Februar","März","April","Mai","Juni","Juli","August","Spetember","Oktober","November","Dezember"];

	$day = $dayArray[date("N",iso2time($string))-1];
	$month = $monArray[date("n",iso2time($string))-1];

	$date = date("d",iso2time($string));
	$year = date("Y",iso2time($string));
	$mon = date("m",iso2time($string));

	return ["dat"=>$day, "date"=>$date, "month"=>$month, "mon"=>$mon, "year"=>$year];
#	return date ("d.m.Y",iso2time($string));
}


function timeFromISO ($string) {
	return date ("H:i",iso2time($string));
}


function human_date($date,$format) {
	$ret = "";

	for ($i=0; $i<strlen($format); $i++) {

		$chr = substr($format, $i, 1);

		switch ($chr) {

			case "N":
				$ret .= $date["day"];
				break;

			case "d":
				$ret .= $date["date"];
				break;

			case "m":
				$ret .= $date["month"];
				break;

			case "y":
				$ret .= $date["year"];
				break;

			default:
				$ret .= $chr;
				break;
		}
	}

	return $ret;
}


?>