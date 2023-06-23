<?php
class EventsForOrgsInMonthQuery extends EventUtilsQuery
{
	public function getSupportedParameters()
	{
		return array('orgids' => $this->getOrgId(), 'classificationids' => '');
	}
	
	protected function getQueryUrl()
	{
		$values = $this->getParameterValues();
		
		$orgIds = $values['orgids'];
		$day = date('d');
		$month = date('m');
		$year =  date('Y');
		$classificationIds = $values['classificationids'];
		
		$url = "events?fromDate=$year-$month-$day&toDate=$year-$month-31";

		if ($orgIds != "") {
			$url .= "&organisationIds=$orgIds";
		}
		
		if ($classificationIds != "") {
			$url .= "&classificationIds=$classificationIds";
		}
		
		return $url;
	}
	
	protected function formatHtml($xml)
	{
		$doc = simplexml_load_string($xml);

		$data = '<ul>';

		foreach ($this->sortEvents($doc->Event) as $event)
		{
			$eventId = $event->EventId;
			$name = $event->Name;

			$eventorUrl = $this->getEventorBaseUrl() . '/Events/Show/'.$eventId;

			$eventDate = $event->StartDate->Date;

			$name = htmlentities($name);
			$eventDate = new DateTime($eventDate);
			$eventDate = $eventDate->format('j/n');

			$data .= "<li><a href=\"" . $eventorUrl . "\">" . $name . "</a> - " . $eventDate . "</li>";
		}

		$data .= '</ul>';

		return $data;
	}
}
?>