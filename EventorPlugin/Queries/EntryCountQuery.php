<?php
class EntryCountQuery extends Query
{
	public function getSupportedParameters()
	{
		return array('eventid' => '0', 'orgid' => $this->getOrgId());		
	}
	
	protected function getQueryUrl()
	{
		$values = $this->getParameterValues();
		
		$orgId = $values['orgid'];
		$eventId = $values['eventid'];
		
		if (empty($orgId))
		{
			$orgId = '0';
		}
			
		$url = 'competitorcount?eventIds='.$eventId.'&organisationIds='.$orgId;
		
		return $url;
	}

	protected function formatHtml($xml)
	{					
		$values = $this->getParameterValues();		
		$orgId = $values['orgid'];
		
		$doc = simplexml_load_string($xml);				
		$competitorCount = $doc->CompetitorCount;

		// Use selected orgId if set
		if (!empty($orgId) && $orgId != '0')
		{
			$competitorCount = $competitorCount->OrganisationCompetitorCount;				 
		}
		
		$numberOfEntries = $competitorCount['numberOfEntries'];

		return $numberOfEntries;		
	}
}
?>