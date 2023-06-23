<?php
class ActivityDeadlinesQuery extends Query
{
	protected function getQueryUrl()
	{
		// Fetch one year ahead
		$fromDate = date("Y-m-d");
		$toDate = date("Y-m-d", strtotime("+1 year", strtotime($fromDate)));

		$url = "activities?organisationId=" . $this->getOrgId() . "&from=" . $fromDate . "&to=" . $toDate . "&includeRegistrations=false";

		return $url;
	}

	protected function formatHtml($xml)
	{
		$activities = array();
		$today = date("Y-m-d");

		$doc = simplexml_load_string($xml);
		$activityNodes = $doc;

		$arr = array();

		foreach ($activityNodes->Activity as $activity)
		{
			$deadline = $activity['registrationDeadline'];
			$name = $activity->Name;
				
			$key = "$deadline, $name";
				
			$arr[(string)$key] = $activity;
		}

		ksort($arr);

		$data = '<ul>';

		foreach ($arr as $activity)
		{
			$visibleFrom = date("Y-m-d", strtotime($activity['visibleFrom']));
			$visibleTo = date("Y-m-d", strtotime($activity['visibleTo']));
			
			if ($today >= $visibleFrom && $today <= $visibleTo) {
				$name = $activity->Name;
				//$name = utf8_decode($name);   
				//$name = str_replace('å', '&aring;', $name);
				$url = $activity['url'];
				$numRegistrations = $activity['registrationCount'];
				$registrationDeadline = $activity['registrationDeadline'];
					
				$date = new DateTime($registrationDeadline);
				$registrationDeadline = $date->format('j/n');

				 $data .= "<li><a href=\"" . $url . "\">" . htmlentities($name) . "</a> - (" . $numRegistrations . ") " . $registrationDeadline . "</li>";
				//$data .= "<li><a href=\"" . $url . "\">" . $name . "</a> - (" . $numRegistrations . ") " . $registrationDeadline . "</li>";
			}
		}

		$data .= '</ul>';

		return $data;
	}
}
?>