<?php
/*
 Plugin Name: EventorPlugin
 Plugin URI: http://nydalen.idrett.no/eventorplugin
 Description: Plugin for fetching data from Eventor
 Version: @version@
 Author: nsk
 Author URI: http://nydalen.idrett.no
 */

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

// Check out Eventor API documentation on https://eventor.orientering.se/api/documentation

define('MT_EVENTOR_BASEURL', 'mt_eventor_baseurl');
define('MT_EVENTOR_APIKEY', 'mt_eventor_apikey');
define('MT_EVENTOR_ORGID', 'mt_eventor_orgid');
define('MT_EVENTOR_ACTIVITY_TTL', 'mt_eventor_activity_ttl');
define('MT_EVENTOR_EVENTIDS', 'mt_eventor_eventids');
define('MT_EVENTOR_CACHE_KEYS', 'mt_eventor_cache_keys');

function loadQueries()
{
  @require_once dirname(__FILE__) ."/Queries/Query.php";
  foreach (glob(dirname(__FILE__) ."/Queries/*Query.php") as $filename)
  {        
      @require_once $filename;
  }
}

loadQueries();

add_action('widgets_init', 'add_widget');

// Hook for adding admin menus
add_action('admin_menu', 'eventor_add_pages');

add_shortcode('eventor', 'eventor_query_shortcode');

function add_widget()
{
	require_once 'EventorQueryWidget.php';
	register_widget('EventorQueryWidget');
}

// [eventor Query="EventsFromOptionListQuery"]
// With parameters: [eventor query="EventsForOrgsInMonthQuery" orgids="3,4,5", month="06"]
function eventor_query_shortcode($atts)
{	
	$queryType = $atts['query'];	
	$query = new $queryType();
	
	// 	overskriv attributter med verdier fra querystring
	foreach($atts as $key => $value)	
	{
		$qs = $_GET[$key];
		
		if(!empty($qs))
		{
			$atts[$key] = $qs;
		}
		
		//echo $atts[$key];
	}
	
	// Just push the whole array to the Query. It will pick relevant values.
	$query->setParameterValues($atts);
	$query->load();

	return $query->getHtml();	
}

function endsWith( $str, $sub )
{
	return ( substr( $str, strlen( $str ) - strlen( $sub ) ) == $sub );
}




// // Automatic include of Query classes
// function __autoload($class_name)
// {
 // echo $class_name;
	// $includeBase = 'Queries/';

	// if (!endsWith($class_name, 'Query'))
	// {
		// return;
	// }

	// $words = preg_split('/(?=[A-Z])/', $class_name);

	// if ($words[1] == 'Custom')
	// {
		// // Example: plugins/EventorPlugin-Nydalens/CustomNydalensQuery.php
		// $includeBase = dirname(__FILE__). '-' . $words[2] . '/';
	// }

	// $filename = $includeBase.$class_name . '.php';
	
  // $file = $filename;
  
  // @require_once($file);
	// include $filename;
  // return;
   // if (stristr(dirname(stream_resolve_include_path($file)), plugin_basename(__DIR__ ))) {
   
        // if (file_exists(stream_resolve_include_path($file)) && is_file(stream_resolve_include_path($file))) {
            
            // @require_once($file);

        // }

    // }
// }

// action function for above hook
function eventor_add_pages()
{
	add_menu_page('Eventor', 'Eventor', 'administrator', 'eventor', 'eventor_options_page');
	add_submenu_page('eventor', 'Options', 'Options', 'administrator', 'eventor', 'eventor_options_page');
	add_submenu_page('eventor', 'Events', 'Events', 'administrator', 'eventor_events', 'eventor_events_page');
	add_submenu_page('eventor', 'Eventor API', 'API Test', 'administrator', 'eventor_api_test', 'eventor_apitest_page');
	add_submenu_page('eventor', 'Eventor Query Test', 'Query Debug', 'administrator', 'eventor_query_test', 'eventor_querytest_page');
}

function eventor_events_page()
{    
	require_once 'EventorEventLinksPage.php';
}

function eventor_apitest_page()
{
	require_once 'EventorApiTest.php';
}

function eventor_querytest_page()
{
	require_once 'QueryTest.php';
}

function eventor_options_page()
{
	$hidden_field_name = 'mt_eventor_submit_hidden';

	// Read in existing option value from database
	$opt_baseurl_val = get_option( MT_EVENTOR_BASEURL );
	$opt_apikey_val = get_option( MT_EVENTOR_APIKEY );
	$opt_orgid_val = get_option( MT_EVENTOR_ORGID );
	$opt_act_ttl_val = get_option( MT_EVENTOR_ACTIVITY_TTL );
	$opt_eventids_val = get_option( MT_EVENTOR_EVENTIDS );

	// See if the user has posted us some information
	// If they did, this hidden field will be set to 'Y'
	if( $_POST[ $hidden_field_name ] == 'Y' ) {
		// Read their posted value
		$opt_baseurl_val = $_POST[ MT_EVENTOR_BASEURL ];
		$opt_apikey_val = $_POST[ MT_EVENTOR_APIKEY ];
		$opt_orgid_val = $_POST[ MT_EVENTOR_ORGID ];
		$opt_act_ttl_val = $_POST[ MT_EVENTOR_ACTIVITY_TTL ];
		$opt_eventids_val = $_POST[ MT_EVENTOR_EVENTIDS ];		
		$opt_clear_cache = $_POST[ MT_EVENTOR_CACHE_KEYS ];

		// Save the posted value in the database
		update_option( MT_EVENTOR_BASEURL, $opt_baseurl_val );
		update_option( MT_EVENTOR_APIKEY, $opt_apikey_val );
		update_option( MT_EVENTOR_ORGID, $opt_orgid_val);
		update_option( MT_EVENTOR_ACTIVITY_TTL, $opt_act_ttl_val );
		update_option( MT_EVENTOR_EVENTIDS, $opt_eventids_val );

		if ($opt_clear_cache == "on")
		{
			$cacheKeys = explode(";", get_option(MT_EVENTOR_CACHE_KEYS));
				
			foreach ($cacheKeys as $key)
			{
				delete_transient($key);
				delete_option(MT_EVENTOR_CACHE_KEYS);
			}
		}
		?>
<div class="updated">
<p><strong><?php _e('Eventor settings saved.', 'mt_trans_domain' ); ?></strong></p>
</div>
		<?php

	}

	// Now display the options editing screen

	echo '<div class="wrap">';

	// header

	echo "<h2>" . __( 'Eventor Plugin Options', 'mt_trans_domain' ) . "</h2>";
	?>

<form name="form1" method="post" action=""><input type="hidden"
	name="<?php echo $hidden_field_name; ?>" value="Y">
<table>
<tr>
	<td><?php _e("Base URL:", 'mt_trans_domain' ); ?> </td>
	<td><input type="text" name="<?php echo MT_EVENTOR_BASEURL; ?>" value="<?php echo $opt_baseurl_val; ?>" size="50"></td>
	<td></td>
</tr>
<tr>
	<td><?php _e("API Key:", 'mt_trans_domain' ); ?></td>
	<td><input type="text" name="<?php echo MT_EVENTOR_APIKEY; ?>" value="<?php echo $opt_apikey_val; ?>" size="50"></td>
	<td></td>
</tr>
<tr>
	<td><?php _e("Organisation ID:", 'mt_trans_domain' ); ?></td>
	<td><input type="text" name="<?php echo MT_EVENTOR_ORGID; ?>" value="<?php echo $opt_orgid_val; ?>" size="50"></td>
	<td></td>
</tr>
<tr>
	<td><?php _e("Cache Time (sec):", 'mt_trans_domain' ); ?></td>
	<td><input type="text" name="<?php echo MT_EVENTOR_ACTIVITY_TTL; ?>" value="<?php echo $opt_act_ttl_val; ?>" size="50"></td>
	<td></td>
</tr>
<tr>
	<td><?php _e("Widget List EventIds:", 'mt_trans_domain' ); ?></td>
	<td><input type="text" name="<?php echo MT_EVENTOR_EVENTIDS; ?>" value="<?php echo $opt_eventids_val; ?>" size="50"></td>
	<td><i>Events of interest. Used in certain widgets.</i></td>
</tr>
<tr>
	<td><?php _e("Clear cache: ", 'mt_trans_domain');?></td>
	<td><input type="checkbox" name="<?php echo MT_EVENTOR_CACHE_KEYS; ?>" /></td>
	<td></td>
</tr>
</table>

<p class="submit">
	<input type="submit" name="Submit" value="<?php _e('Update Options', 'mt_trans_domain' ) ?>" />
</p>


</form>
	<?php
}
?>