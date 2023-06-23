<?php
class EventsFromOptionListQuery extends Query
{
	protected function getQueryUrl()
	{
		// Fetch one year ahead
		$fromDate = date("Y-m-d");
		$toDate = date("Y-m-d", strtotime("+1 year", strtotime($fromDate)));

		$url = "events?eventIds=" . get_option(MT_EVENTOR_EVENTIDS) . "&from=" . $fromDate . "&to=" . $toDate . "&includeEntryBreaks=true";

		return $url;
	}
	
	protected function formatHtml($xml)
	{
		$events = array();

		$doc = simplexml_load_string($xml);
		$eventNodes = $doc;

		foreach ($eventNodes->Event as $event) 
		{
			$date = $this->getNextDeadlineOrEventDate($event);
			$name = $event->Name;
			
			$key = "$date, $name";
			
			$arr[(string)$key] = $event;
		}
		
		ksort($arr);
				
		$data = '<ul>';

		foreach ($arr as $event)
		{
			$eventId = $event->EventId;
			$name = $event->Name;

			$eventorUrl = $this->getEventorBaseUrl() . '/Events/Show/'.$eventId;

			$eventDate = $this->getNextDeadlineOrEventDate($event); 
			$name = htmlentities($name);
			$eventDate = new DateTime($eventDate);
			$eventDate = $eventDate->format('j/n');

			$data .= "<li><a href=\"" . $eventorUrl . "\">" . $name . "</a> - " . $eventDate . "</li>";
		}

		$data .= '</ul>';

		return $data;
	}
	
	protected function getNextDeadlineOrEventDate($event)
	{
		$date = $event->StartDate->Date;
		return $date;
		 
		$arr = array();
		
		$today = "2012-04-10"; //date("Y-m-d");
		
		foreach($event->EntryBreak as $break)
		{
			$breakDate = $break->ValidToDate->Date;
			
			echo $breakDate ." ";
			//if($breakDate >= $today)
			//{
				$arr[(string)$breakDate] = $breakDate;
			//}
		}
		
		ksort($arr);
		
		echo count($arr);
		
		if (count($arr) > 0)
			return $arr[0];
			
		return $date;
	}	
	
}
?>