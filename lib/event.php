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


				# insert fields in block
				foreach (array_filter($fields) as $field) {

					$kv = explode(":",$field);
					

					# split key with parameter
					if (count($param = explode(";",$kv[0])) > 1) {

						$kv[0] = $param[0];
						$paramData = explode("=",$param[1]);

						if (count($paramData) > 1) {
							$ary = [ $kv[1] ];
							$ary[$paramData[0]] = $paramData[1];

							$kv[1] = $ary;
						}
					}
					

					#not END
					if ($kv[0] != "END") {

						// check for numbers
						preg_match("$([0-9]+)$",$kv[0],$matches);

						// check for uppercase
						if (strtoupper($kv[0]) == $kv[0] and count($matches) == 0) {

							$current = $kv[0];
							$kv[1] = str_replace("\\n", "<br>", $kv[1]);

							# field exists
							if (isset($this->data[$blockName][$kv[0]])) {

								# is already multivalued
								if (is_array($this->data[$blockName][$kv[0]])) {
									array_push($this->data[$blockName][$kv[0]],$kv[1]);
								}

								# convert value to array and add new value
								else {
									$this->data[$blockName][$kv[0]] = [$this->data[$blockName][$kv[0]]];
									array_push($this->data[$blockName][$kv[0]],$kv[1]);
								}
							}
							else {
								$this->data[$blockName][$kv[0]] = $kv[1];
							}
						}

						// add new line
						else {

							// scip first char of new line
						$this->data[$blockName][$current] .= substr($kv[0],1);

							// \n to br
							$this->data[$blockName][$current] = str_replace("\\n", "<br>", $this->data[$blockName][$current]);
							$this->data[$blockName][$current] = str_replace("\\", "", $this->data[$blockName][$current]);
						}
					}
				}
			}
		}
	}


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
		$description = $this->get("vevent","description");

		$start_date = dateFromISO($this->get("vevent","dtstart"));
		$end_date = dateFromISO($this->get("vevent","dtend"));

		$start_time = timeFromISO($this->get("vevent","dtstart"));
		$end_time = timeFromISO($this->get("vevent","dtend"));

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
			$ret .= "<p class='tplcaldav_text'>" . $this->get("vevent","description") . "</p>";


		$ret .= "</div>";

		return $ret;
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
