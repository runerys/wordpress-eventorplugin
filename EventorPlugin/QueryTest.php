<div class="wrap">
<h2>Query Test</h2>
<p>
This page is for debugging queries during development. Submit the query type and inspect the xml, html and the rendering.
</p>
<form method="post">
<?php
require_once 'DebugFunctions.php';

function runQuery()
{
	$queryType = '';
	
	if(isset($_POST['querytype']))
	{
		$queryType = $_POST['querytype'];
	}
	
	echo 'Query <input type="text" name="querytype" size="50" value="' . $queryType .'" />';
	echo '<p class="submit"><input type="submit" name="update_parameters" id="update_parameters" value="Get parameters" /></p>';
	
	if (empty($queryType))
	{
		return;
	}
	
	$query = new $queryType();
	
	$supportedParameters = $query->getSupportedParameters();
	
	if (sizeof($supportedParameters) == 0)
	{
		echo '<p>No parameters</p>';
	}
	else 
	{
		foreach ($supportedParameters as $parameter => $defaultValue)
		{		
			$value = $_POST[$parameter];
	
			if(empty($value))
			{
				$value = $defaultValue;
			}
			
			echo '<p><label for="'. $parameter.'">'.$parameter.'<input type="text" id="'.$parameter.'" name="'.$parameter.'" value="'.$value.'" /></p>';		
		}			
	}	
	
	echo '<p class="submit"><input type="submit" value="Submit" /></p>';
	echo '<br/>';
		
	if (isset($_POST['update_parameters']))
		return;
	
	$query->setParameterValues($_POST);
	$query->load();
	$xml = $query->getXml();
	$html = $query->getHtml();

	$xmlString = formatXmlString($xml);

	echo 'Eventor Response<br /><textarea rows=30 cols=150>'.$xmlString.'</textarea>';
	echo '<br />Query Html<br /><textarea rows="10" cols="150">'.$html.'</textarea>';
	echo '<hr />Preview <br />'.$html;
}
runQuery();
?>
</form>
</div>