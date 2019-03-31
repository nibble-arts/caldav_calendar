<?php

/* Prevent direct access.
 */
if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

class Event {

	private $data = [];
	private $current = "";

	function __construct($vcal) {
		$this->data = [];

		$data = explode("BEGIN:",$vcal->getData());


		// iterate events
		// create data block
		foreach ($data as $block) {

			$fields = explode("\n",$block);
			$blockName = array_shift($fields);

			// 
			if (count($fields)) {

				# set block in array
				$this->data[$blockName] = [];


				$newData = [];

				# insert fields in block
				foreach (array_filter($fields) as $field) {

					$line = $this->split_line($field);

					// new command
					if ($line["name"]) {
						$current = $line["name"];
					}

					// add value
					if ($line["value"]) {
						$newData[$current] .= $line["value"];
					}

				}

				$this->data[$blockName] = $newData;
			}
		}
	}


	function split_line($string) {

		$line = ["name"=>false, "param"=>false, "value"=>false];

		// split field from value
		$stringAry = explode(":", $string);

		// split field from parameter
		$paramAry = explode(";", $stringAry[0]);

		// field is upper case => is field name
		if ($paramAry[0] == strtoupper($paramAry[0])) {

			$line["name"] = array_shift($paramAry);
			array_shift($stringAry);
			$line["value"] .= $this->special_chars(implode(":", $stringAry));

			if (isset($paramAry[1])) {
				$line["param"] = $paramAry[1];
			}
		}

		else {
			$line["value"] .= $this->special_chars(implode(":", $stringAry));
		}

		return $line;
	}



	// remove / or \ from special characters
	function special_chars($string) {

		$ret = str_replace("\\n", "<br>", trim($string));
		$ret = stripslashes($ret);

		return $ret;
	}



	// get value from block / field
	function get ($block = "",$field = "") {

		if (!$block)
			return $this->data;

		else {
			# block exists
			if (isset($this->data[strtoupper($block)])) {

				# no field => return block
				if (!$field)
					return ($this->data[strtoupper($block)]);

				# return field
				else {

					if (isset($this->data[strtoupper($block)][strtoupper($field)])) {
						return $this->data[strtoupper($block)][strtoupper($field)];
					}
					else
						return False;
				}

			}

			# block not found
			else
				return False;
		}
	}


	function render () {

		$ret = "";

		$title = $this->get("vevent","summary");
		$location = $this->get("vevent","location");
		$description = $this->render_url($this->get("vevent","description"));
		$description = $this->render_mail($description);

		// get start/end date
		$start = $this->get("vevent","dtstart");
		$end = $this->get("vevent","dtend");

		// correct end when all day
		if (strlen($start) == 8) {
			$end -= 1;
		}

		// separate date/time
		$start_date = dateFromISO($start);
		$end_date = dateFromISO($end);

		$start_time = timeFromISO($start);
		$end_time = timeFromISO($end);


		// write date ID in header
		$ret .= "<div class='tplcaldav_calendarin' id='" . $start_date["year"] . "-" . $start_date["mon"] . "-" . $start_date["date"] . "'>";

			$fields = explode(",", CALDAV_FIELDS);

			foreach ($fields as $field) {

				switch ($field) {

					case "date":
						$ret .= "<div class='tplcaldav_date'>";

							// same day
							if ($start_date["date"] == $end_date["date"] && $start_date["month"] == $end_date["month"] && $start_date["year"] == $start_date["year"])
								$ret .= human_date($start_date,"N d. m y");

							// same month and year
							elseif ($start_date["month"] == $end_date["month"] && $start_date["year"] == $start_date["year"])
								$ret .= human_date($start_date,"N d.") . " - " . human_date($end_date,"N d. m y");

							// same year
							elseif ($start_date["month"] != $end_date["month"] && $start_date["year"] == $start_date["year"])
								$ret .= human_date($start_date,"N d. m") . " - " . human_date($end_date,"N d.m y");

							// different year
							else
								$ret .= human_date($start_date,"N d. m y") . " - " . human_date($end_date,"N d.m y");

						$ret .= "</div>";
						break;

					case "time":
						# time
						if (CALDAV_SHOW_TIME == "yes") {
							if ($start_time != "00:00" and $end_time != "00:00")
								$ret .= "<div class='tplcaldav_time'>{$start_time} - {$end_time}</div>";
							else
								$ret .= "<div class='tplcaldav_time'>ganztägig</div>";
						}
						break;

					case "title":
							# title
							$ret .= "<div class='tplcaldav_title'>{$title}</div>";
						break;

					case "location":
						# location
						if ($location) {
							$location = str_replace("<br>", ", ", $location);
							$ret .= "<div class='tplcaldav_loc'><table><tr><td valign='top'>Ort: </td><td valign='top'>{$location}</td></tr></table></div>";
						}
						break;

				}
			}

			$ret .= "<div style='clear:both'></div>";

			# description
			$ret .= "<p class='tplcaldav_text'>" . $description . "</p>";


		$ret .= "</div>";

		return $ret;
	}



	// convert urls to links
	function render_url($string) {

		return $this->insert_link($string, "|https?\:\/\/[a-z0-9\/\#\_\.\-\?]+|i", '<a target="_blank" href="{}">{}</a>');

	}


	// convert email to link
	function render_mail($string) {

		return $this->insert_link($string, "|[a-z0-9\.\_\-]+\@[a-z0-9\.\_\-]+|i", '<a href="mailto:{}">{}</a>');

	}


	// insert links using a regular expression and format string
	// {} in the format is replaced by the value
	function insert_link($string, $pattern, $format) {

		$retArray = [];
		$ret = "";

		$cursor = 0;
		$length = strlen($string);

		// get all http/https links

		while (preg_match($pattern, $string, $match, PREG_OFFSET_CAPTURE)) {

			// save previous part
			if ($match[0][1] > 0) {
				array_push($retArray, substr($string, 0, $match[0][1]));

				// remote string
				$string = substr($string, $match[0][1]);
			}

			// insert value in format
			$linkString = $format;
			$linkString = str_replace("{}", $match[0][0], $linkString);

			// $linkString = '<a target="_blank" href="'.$match[0][0].'">'.$match[0][0].'</a>';

			// add link to array
			array_push($retArray, $linkString);

			// remove url from string
			$string = substr($string, strlen($match[0][0]));

		}

		// add last snippet
		if (strlen($string)) {
			array_push($retArray, $string);
		}

		return implode("", $retArray);
	}


	function toXML () {
		$ret = "";
		/*$ret .= "<?XML version='1.0' encoding='utf-8'?>";*/

		$ret .= "<event>";
			$ret .= "<summary>" . $this->get("vevent","summary") ."</summary>";
			$ret .= "<location>" . $this->get("vevent","location") ."</location>";	

			$ret .= "<start_date>" . dateFromISO($this->get("vevent","dtstart")) . "</start_date>";
			$ret .= "<start_time>".timeFromISO($this->get("vevent","dtstart")) . "</start_time>";

			$ret .= "<end_date>" . dateFromISO($this->get("vevent","dtend")) . "</end_date>";
			$ret .= "<end_time>".timeFromISO($this->get("vevent","dtend")) . "</end_time>";

			$ret .= "<description>" . $this->get("vevent","description") . "</description>";
		$ret .= "</event>";

		return $ret;
	}
}



function debug($text) {
	echo "<pre>";
		print_r($text);
	echo "</pre>";
}

?>
