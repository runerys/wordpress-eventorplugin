<?php
class AddressListQuery extends Query
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
			
		$url = 'persons/organisations/'.$orgId.'?includeContactDetails=true';
		
		return $url;
	}

	protected function formatHtml($xml)
	{
		$doc = simplexml_load_string($xml);				
		$personList = $doc;
		
		$arr = array();

		foreach ($personList->Person as $person) 
		{
			$firstname = $person->PersonName->Given;
			$lastname = $person->PersonName->Family;
			$name = "$lastname, $firstname";
			
			$arr[(string)$name] = $person;
		}
		ksort($arr);
		
		$html = '<table><th>Navn</th><th>E-post</th><th>Telefon</th><th>Kategori</th>';
				
		foreach ($arr as $person)
		{
			$firstname = $person->PersonName->Given;
			$lastname = $person->PersonName->Family;
			
			$address = $person->Address;      
			$addressText = $address['street'];

			if(!empty($addressText))
				$addressText .= ', '.$address['zipCode'].' '.$address['city'];
			
			$addressText = htmlentities($addressText);

			$personName = "$lastname, $firstname";
			$personName = utf8_decode($personName);
			$personName = htmlentities($personName);
			
			$birthDate = $person->BirthDate->Date;
			$age = date('Y') - substr($birthDate,0,4); 
			
			$class = 'Barn';
			
			if($age > 39)
				$class = 'Veteran';
			else if ($age > 20)
				$class = 'Senior';
			else if ($age > 16)
				$class = 'Junior';
			else if ($age > 12)
				$class = 'Ungdom';

			// Ikke skriv ut Barn
			if(substr($class,0,1) == 'B')
				continue;

			$tele = $person->Tele;

			$email = $tele['mailAddress'];
			$mobile = $tele['mobilePhoneNumber'];
			$phone = $tele['phoneNumber'];

			if(!empty($mobile))
				$phone = $mobile;

			$html .= "<tr><td>$personName</td><td>$email</td><td>$phone</td><td>$class</td></tr>";
		}
		$html .= '</table>';		

		return $html;		
	}
}
?>