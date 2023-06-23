<?php
class Utils
{
	public static function sortEvents($array)
	{
		$events = array();

		foreach ($array as $event) {
			$eventDate = $event->StartDate->Date;
			$name = $event->Name;

			$key = "$eventDate, $name";

			$events[(string)$key] = $event;
		}

		ksort($events);

		return $events;
	}
		
}
?>