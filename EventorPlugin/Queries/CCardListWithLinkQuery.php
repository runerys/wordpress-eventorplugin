<?php
class CCardListWithLinkQuery extends Query
{
	public function getSupportedParameters()
	{
		return array('orgid' => $this->getOrgId(), 'link' => '');		
	}
	
	protected function getQueryUrl()
	{
		$values = $this->getParameterValues();
		
		$orgId = $values['orgid'];		
		
		if (empty($orgId))
		{
			$orgId = '0';
		}
			
		$url = 'competitors?organisationId='.$orgId;
		
		return $url;
	}

	protected function formatHtml($xml)
	{							
		$doc = simplexml_load_string($xml);				
		$competitors = $doc;
		
		$p = $this->getParameterValues();
		$link = $p['link'];
		
		$arr = array();

		foreach ($competitors->Competitor as $competitor) 
		{
			$person = $competitor->Person;
			$firstname = $person->PersonName->Given;
			$lastname = $person->PersonName->Family;
			$name = "$lastname, $firstname";
			
			$arr[(string)$name] = $competitor;
		}
		ksort($arr);
		
		$html = '<table><th>Navn</th><th>EMIT</th><th>SI</th><th>Oppdatert</th>';
				
		foreach ($arr as $competitor)
		{
			$person = $competitor->Person;
			$firstname = $person->PersonName->Given;
			$lastname = $person->PersonName->Family;
			$personId = $person->PersonId;
			$modified = $person->ModifyDate->Date;
			
			$emit = '';
			$si = '';
			
			foreach($competitor->CCard as $ccard)
			{
				$type = $ccard->PunchingUnitType['value'];
				
				if ($type == 'Emit')
				{
					$emit = $ccard->CCardId;
				}
				
				if ($type == 'SI')
				{
					$si = $ccard->CCardId;
				}
			}			
			
			$linkHtml = "$lastname, $firstname";
			
			if($link != '')
			{
				$linkUrl = str_replace('{personid}', $personId, strtolower($link));
				$linkUrl = str_replace('{year}', date('Y'), strtolower($linkUrl));
			
				$linkHtml = "<a href='$linkUrl'>$linkHtml</a>";
			}
			
			$html .= "<tr><td>$linkHtml</td><td>$emit</td><td>$si</td><td>$modified</td></tr>";
		}
		$html .= '</table>';		

		return $html;		
	}
}
?>