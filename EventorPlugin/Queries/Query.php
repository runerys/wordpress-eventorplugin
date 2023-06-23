<?php
/*
 * Base class for all Eventor queries.
 *
 * Handles:
 * - communication against API (through Curl)
 * - caching of complete html result
 *
 * Delegates to subclasses:
 * - setup of query url
 * - html rendering of result xml
 *
 * Main interest method: loadWithCacheKey
 * Design Pattern: Abstract Method
 *
 * Usage:
 * $query = new RealQuery();
 * $query->load();
 * OR $query->loadWithCacheKey($args['widget_id']);
 *
 * $html = $query->getHtml();
 */
abstract class Query
{
	private $xml;
	private $html;
	private $transient;
	private $parameterValues;

	/*** COMMON OVERRIDES START ***/
	
	// Return the url part after /api/
	protected abstract function getQueryUrl();
	
	// Input is xml from Eventor.
	// Return the Html ready to display.
	protected abstract function formatHtml($xml);

	/* Override to support widget parameters. The array returned may contain default values.	
	*  Example no defaults: return array('orgids', 'month');
	*  Example with defaults: return array('orgids' => $this->getOrgId(), 'month' => '06');
	*  IMPORTANT: Keys must be lowercase to support wp shortcodes.
	*/
	public function getSupportedParameters()
	{
		return array();
	}	
	/*** COMMON OVERRIDES END ***/
	
	/*** PROPERTY ACCESSORS START ***/
	public function setParameterValues($array)
	{
		$this->parameterValues = $array;
	}
	
	public function getParameterValues()
	{
		return $this->parameterValues;
	}
		
	public function getXml()
	{
		return $this->xml;
	}

	protected function setXml($xml)
	{
		$this->xml = $xml;
	}
	
	// Property getter
	public function getHtml()
	{
		return $this->html;
	}

	protected function setHtml($html)
	{
		$this->html = $html;
	}
	/*** PROPERTY ACCESSORS END ***/
	
	// Option helper
	protected function getOrgId()
	{
		return get_option(MT_EVENTOR_ORGID);
	}
	
	// Option helper
	protected function getEventorBaseUrl()
	{
		return get_option(MT_EVENTOR_BASEURL);
	}
		
	public function load()
	{
		$this->loadWithCacheKey('');
	}

	// Used to cache by widget instance: $queryInstance->loadWithCacheKey($args['widget_id']);
	public function loadWithCacheKey($cacheKey)
	{
		$this->initCache($cacheKey);

		$this->html = $this->cacheLoad();
		
		if($this->html === 0)
		{			
			$this->xml = $this->loadFromEventor();									
			$this->html = $this->formatHtml($this->xml);

			$this->cachePut($this->html);
		}
	}

	protected function initCache($cacheKey)
	{
		$cacheKey = get_class($this).$cacheKey;
		
		// Parameters must be included in cache key.
		$values = $this->getParameterValues();				
		foreach ($this->getSupportedParameters() as $key => $value)
		{			
			$cacheKey .= $values[$key];
		}
		
		$this->transient = $cacheKey;
	}

	protected function loadFromEventor()
	{
		$url = $this->getQueryUrl();
		$xml = $this->getXmlFromUrl($url);

		return $xml;
	}

	protected function cacheLoad()
	{
		$rv = get_transient($this->transient);

		if (false === $rv) {
			return 0;
		}

		return $rv;
	}

	protected function cachePut($html)
	{
		$this->updateCacheKeys($this->transient);

		set_transient($this->transient, $html, get_option(MT_EVENTOR_ACTIVITY_TTL));
	}

	protected function updateCacheKeys($newKey)
	{
		$keys = get_option(MT_EVENTOR_CACHE_KEYS);

		$newKey = $newKey . ";";

		// Add cache key if not already present.
		if (false === strpos($keys, $newKey))
		{
			$keys .= $newKey;

			update_option(MT_EVENTOR_CACHE_KEYS, $keys);
		}
	}
	
	// Actual call to Eventor
	protected function getXmlFromUrl($url)
	{		
		$url = get_option(MT_EVENTOR_BASEURL). '/api/' . $url;
		$headers = array('ApiKey' => get_option(MT_EVENTOR_APIKEY));		
		$response = wp_remote_get($url, array('headers' => $headers, 'sslverify' => false, 'timeout' => 60));
			
		$xml = $response['body'];
		
		return $xml;				
	}
}