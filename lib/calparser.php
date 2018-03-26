<?php

/* Prevent direct access.
 */
if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

include_once ("event.php");


class CalParser {

	private $data = [];
	private $cursor = 0;


	# ====================================
	# create calendar
	function __construct($events) {
		foreach ($events as $event) {
			array_push ($this->data, new Event ($event));
		}
	}


	# ====================================
	# sort events by field of block
	function sort ($block,$field,$dir = "") {
		$sort = [];

		foreach ($this->data as $event) {
			$sortval = $event->get($block,$field);

			if (is_array ($sortval))
				$sortval = $sortval[0];

			$sort[$sortval] = $event;
		}

		# choose sort direction
		if (strtoupper($dir) == "DESC")
			krsort ($sort);
		else
			ksort($sort);

		$this->data = $sort;
	}


	# ====================================
	# get event by idx or cursor
	function get ($idx = "") {
		if ($idx)
			return $this->get_event($idx);
		else {
			if ($this->cursor < $this->count())	
				return $this->get_event($this->cursor++);
		}
	}


	# ====================================
	# reset cursor
	function reset () {
		$this->cursor = 0;
	}


	# ====================================
	# get event count
	function count() {
		return count($this->data);
	}


	# ====================================
	# get event by id
	private function get_event ($idx) {
		if (isset($idx)) {

			# try index as key
			if (array_key_exists($idx, $this->data))
				return $this->data[$idx];

			# try integer index translation
			else {
				$keys = array_keys ($this->data);

				if (array_key_exists($idx,$keys)) {
					$key = $keys[$idx];

					if (array_key_exists($key, $this->data))
						return $this->data[$key];
					else
						return False;
				}
				else
					return False;
			}
		}
	} 
}

?>