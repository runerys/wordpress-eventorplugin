<?php
class PersonResultsForYearQuery extends Query
{
	public function getSupportedParameters()
	{
		return array('personid' => 0, 'y' => date('Y'));		
	}
	
	protected function getQueryUrl()
	{
		$values = $this->getParameterValues();
		$y = $values['y'];
		$personId = $values['personid'];
		
		$url = "results/person?personId=$personId&fromDate=$y-01-01&toDate=$y-12-31";
				
		return $url;
	}

	protected function formatHtml($xml)
	{				
		$currentYear = date('Y');	
		$values = $this->getParameterValues();
		$personId = $values['personid'];
		$yearFromQueryParameter = $values['y'];
		
		$yearLinkHtml = '';
		
		for($i=2011; $i <= $currentYear; $i++)
		{
			if($i == $yearFromQueryParameter)			
				$link = "<b>$yearFromQueryParameter</b>&nbsp;";
			else
				$link = "<a href='?personid=$personId&y=$i'>$i</a>&nbsp;";	
				
			$yearLinkHtml .= $link;			
		}
		
		$doc = simplexml_load_string($xml);				
		$resultListList = $doc;
		
		if(count($resultListList) == 0)
		{
			return '<h3>Ingen resultater funnet</h3><br />' . $yearLinkHtml;
		}
		
		foreach ($resultListList->ResultList as $resultList) 
		{
			$date = $resultList->Event->StartDate->Date;			
			$arr[(string)$date] = $resultList;
		}
		
		ksort($arr);
		
		$personName = $resultListList[0]->ResultList->ClassResult->PersonResult->Person->PersonName;
		$lastname = $personName->Family;
		$firstname = $personName->Given;
			
		$clubId = $resultListList[0]->ResultList->ClassResult->PersonResult->Organisation->OrganisationId;
		
		//if($clubId != $this->getOrgId())
		//{
		//	return '<h3>Kan ikke hente for personer fra annen klubb</h3>';
		//}	
							
		$html = "<h2>$firstname $lastname</h2>";
				
		$html .= $yearLinkHtml;
		
		$html .= "<table><th></th><th></th><th></th><th align='right'>Nr</th><th align='right'>Tid</th><th align='right'>Etter</th><th>Livelox</th>";
		
		$eventor = get_option(MT_EVENTOR_BASEURL);
		$eventLinkBase =  $eventor . '/Events/Show/';
		$resultLinkBase =  $eventor . '/Events/ResultList';
		
		foreach ($arr as $resultList) 
		{
			$eventId = $resultList->Event->EventId;
			$eventName = $resultList->Event->Name;
			$eventDate = $resultList->Event->StartDate->Date;
			
			$class = $resultList->ClassResult->EventClass->ClassShortName;
			$eventClassId = $resultList->ClassResult->EventClass->EventClassId;
			$noOfStarts = $resultList->ClassResult->EventClass->ClassRaceInfo['noOfStarts'];
			$time = $resultList->ClassResult->PersonResult->Result->Time;
			
			$timediff = $resultList->ClassResult->PersonResult->Result->TimeDiff;
			if(!empty($timediff))
			{
				$timediff = '+'.$timediff;
			}
			
			$resultPosition = $resultList->ClassResult->PersonResult->Result->ResultPosition;
			$competitorStatus = $resultList->ClassResult->PersonResult->Result->CompetitorStatus['value'];
			
			$eventLink = "<a href='$eventLinkBase$eventId'>$eventName</a>";
			$classResultLink = "<a href='$resultLinkBase?eventId=$eventId&eventClassId=$eventClassId'>$class</a>";
			
			if($competitorStatus == 'OK')
			{			
				$liveloxUrl=urlencode("https://www.livelox.com/Viewer?eventExternalIdentifier=1%3a$eventId-1&classExternalId=$eventClassId-1");
				$liveloxLink="<a href='https://eventor.orientering.no/Home/RedirectToLivelox?redirectUrl=$liveloxUrl'>livelox</a>";
				$html .= "<tr><td>$eventDate</td><td>$eventLink</td><td>$classResultLink</td><td align='right'>$resultPosition</td><td align='right'>$time</td><td align='right'>$timediff</td><td>$liveloxLink</td></tr>";
			}
		}
		
		$html .= '</table>';	
		
		return $html;		
	}
}
?>