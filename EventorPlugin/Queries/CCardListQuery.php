<?php
class CCardListQuery extends Query
{
	public function getSupportedParameters()
	{
		return array('orgid' => $this->getOrgId());		
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
		
		$html = '<table><th>Navn</th><th>EMIT</th><th>EMITAG</th><th>SI</th><th>Oppdatert</th>';
				
		foreach ($arr as $competitor)
		{
			$person = $competitor->Person;
			$firstname = $person->PersonName->Given;
			$lastname = $person->PersonName->Family;
			
			$modified = $person->ModifyDate->Date;
			
			$emit = '';
			$emiTag = '';
			$si = '';
			
			foreach($competitor->CCard as $ccard)
			{
				$type = $ccard->PunchingUnitType['value'];
				
				if ($type == 'Emit')
				{
					$emit = $ccard->CCardId;
				}

				if ($type == 'emiTag')
				{
					$emiTag = $ccard->CCardId;
				}
				
				if ($type == 'SI')
				{
					$si = $ccard->CCardId;
				}
			}			
			
			$html .= "<tr><td>$lastname, $firstname</td><td>$emit</td><td>$emiTag</td><td>$si</td><td>$modified</td></tr>";
		}
		$html .= '</table>';		

		return $html;		
	}
}
?>