<div class="wrap">
<h2>Eventor API Test</h2>
<p>
This page is for playing with the Eventor API. See the <a href=https://eventor.orientering.se/api/documentation>API documentation</a> for ideas.
</p>
<form method="post">
<?php

require_once 'DebugFunctions.php';

// Dummy query for hooking up to the Query infrastructure
class DebugQuery extends Query
{
	private $url;
	
	public function __construct($url)
	{
		$this->url = $url;	
	}
	
	function getQueryUrl()
	{
		return $this->url;
	}	
	
	function formatHtml($xml)
	{
		return '';
	}	
	
	public function loadWithCacheKey($cacheKey)
	{
		$this->xml = $this->loadFromEventor();											
	}
}

function doEventorApiCall()
{
	$eventorApiUrl = '';
	
	if(isset($_POST['eventorApiUrl']))
	{
		$eventorApiUrl = $_POST['eventorApiUrl'];
	}	
?>
	
		<?php echo get_option(MT_EVENTOR_BASEURL);?>/api/<input size="200" type="text" name="eventorApiUrl" value="<?php echo $eventorApiUrl; ?>" />
			
	<p class="submit">
		<input type="submit" value="Submit"> 
	</p>
	<?php 
		
	if (empty($eventorApiUrl))
	{
		return;
	}

	$query = new DebugQuery($eventorApiUrl);

	$query->load();
	$xml = $query->getXml();	

	$xmlString = formatXmlString($xml);

	echo 'Response<br /><textarea rows="30" cols="176">'.$xmlString.'</textarea>';
}
doEventorApiCall();
?>
</form>
</div>