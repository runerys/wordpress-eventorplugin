
<?php

$hidden_field_name = 'mt_eventor_events_admin_submit_hidden';

require_once 'DebugFunctions.php';

// Dummy query for hooking up to the Query infrastructure
class EventsAdminQuery extends EventsFromOptionListQuery
{
	
	
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
			$name = utf8_decode($event->Name);

			$eventorUrl = $this->getEventorBaseUrl() . '/Events/Show/'.$eventId;
			$eventDate = $this->getNextDeadlineOrEventDate($event);

			$name = htmlentities($name);
			$eventDate = new DateTime($eventDate);
			$eventDate = $eventDate->format('j/n');

			$p = $_GET['page'];
			$deleteLink = $_SERVER['PHP_SELF'] . "?page=$p&deleteEvent=$eventId";
			
			$data .= "<li><a href=\"" . $eventorUrl . "\">" . $name . "</a> - " . $eventDate . " <a href='$deleteLink'>Delete</a></li>";
		}

		$data .= '</ul>';
		
		return $data;
	}
	
	public function loadWithCacheKey($cacheKey)
	{
		$this->setXml($this->loadFromEventor());
		$this->setHtml($this->formatHtml($this->getXml()));
	}
}

function printEventListWithDeleteLinks()
{
	$query = new EventsAdminQuery();
	$query->load();
	
	return $query->getHtml();
}

$deleteEventId = $_GET['deleteEvent'];

// Legg til ny eventids
if($_POST[$hidden_field_name] == 'Y')
{
	$opt_eventids = get_option( MT_EVENTOR_EVENTIDS );
	$posted_eventids = $_POST['newEvents'];	

	$original = explode(',', $opt_eventids);
	
	$new = array_map('trim', explode(',', $posted_eventids)); // removes whitespaces directly
	
	$merged_eventids = implode(',', array_merge($original, $new));
	
	update_option( MT_EVENTOR_EVENTIDS, $merged_eventids );
}
else if(!empty($deleteEventId))
{
// Slett eventId fra QueryString
	$opt_eventids = get_option( MT_EVENTOR_EVENTIDS );
	
	$original = explode(',', $opt_eventids);
	
	$arr = array_diff($original, array($deleteEventId));
	
	$merged_eventids = implode(',', $arr);
	
	update_option( MT_EVENTOR_EVENTIDS, $merged_eventids );
}

?>

	
<div class="wrap">
<h2>Eventor Event Links</h2>
<p>
This page is for editing events shown in the "EventsFromOptionListQuery".
</p>
<form method="post">
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
<table>
  <tr>
	<td>Add new eventIds</td><td><input type="text" name="newEvents" size="20" /></td><td><input type="submit" name="Submit" /> (input field supports comma separated values)</td>
  </tr>
</table>
</form>
<?php 	
	
	echo printEventListWithDeleteLinks();
?>
</div>