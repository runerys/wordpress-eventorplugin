<?php
class EventorQueryWidget extends WP_Widget {
	const QUERIES_DIR = '/Queries/';
	const SUPERCLASS = 'Query';

	private $availableQueries;

	function EventorQueryWidget() {
		parent::WP_Widget(false, 'Eventor Query');   
	}

	function initAvailableQueries()
	{
		$this->loadQueryClasses(dirname(__FILE__) . self::QUERIES_DIR);
		
		// Load all queries in parallel folders, starting with "EventorPlugin-"
		$pluginQueryDirs = glob("" . dirname(__FILE__) . "-*");		
		foreach ($pluginQueryDirs as $pluginQueryDir)
		{
			$this->loadQueryClasses($pluginQueryDir . '/');
		}
						
		$classes = get_declared_classes();

		$this->availableQueries = array();

		foreach ($classes as $clazz) 
		{
			$reflect = new ReflectionClass($clazz);

			if ($reflect->isSubclassOf(self::SUPERCLASS))
			{
				array_push($this->availableQueries, $clazz);
			}
		}
	}

	function loadQueryClasses($searchDir)
	{		
		$availabledQueries = glob("" . $searchDir . "*Query.php");

		foreach ($availabledQueries as $availableQuery)
		{
			$tmp = substr($availableQuery, $this->last_index_of('/', $availableQuery));
			$current_class = substr($tmp, 0, strpos($tmp, '.'));

			if (!class_exists($current_class))
			{
				include ($availableQuery);
			}
		}
	}

	function last_index_of($sub_str,$instr)
	{
		if(strstr($instr,$sub_str)!="")
		{
			return(strlen($instr)-strpos(strrev($instr),$sub_str));
		}
			
		return(-1);
	}

	// Lay out the widget config form
	function form($instance)
	{
		$this->initAvailableQueries();
		
		$title = esc_attr($instance['title']);
		$query =  esc_attr($instance['query']);
		?>
	<p>
		<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"	name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</label>
	</p>
	<p>
		<label for="<?php echo $this->get_field_id('query'); ?>"><?php _e('Query:'); ?>
			<select class="widefat" id="<?php echo $this->get_field_id('query'); ?>" name="<?php echo $this->get_field_name('query'); ?>">
			<?php
			foreach ($this->availableQueries as $availableQuery)
			{
				$selected = false;
		
				if ($availableQuery == $query)
				{
					$selected = true;
				}
			?>
				<option value="<?php echo $availableQuery; ?>"
			<?php 
				if ($selected) echo " selected=\"yes\""; ?>><?php echo $availableQuery; ?>
				</option>
			<?php
			}
			?>
			</select> 
		</label>
	</p>
	<p>Save to see eventual parameters below.</p>
		<?php
		
		if (empty($query))
		{
			return;
		}
			
		$queryInstance = new $query();
		
		$supportedParameters = $queryInstance->getSupportedParameters();
		
		foreach ($supportedParameters as $parameter => $defaultValue)
		{
			$value = $instance[$parameter];
					
			if (empty($value))
			{
				$value = $defaultValue;
			}
		
			echo '<p><label for="'. $this->get_field_id($parameter).'">'.$parameter.'<input class="widefat" type="text" id="'.$this->get_field_id($parameter).'" name="'.$this->get_field_name($parameter).'" value="'.$value.'" /></p>';		
		}		
	}
	
	function update($new_instance, $old_instance)
	{
		echo 'TESTING';		
		// processes widget options to be saved
		return $new_instance;
	}

	// Emit widget html
	function widget($args, $instance)
	{
        
		// TODO: Hide surrounding html based on widget new config setting 'hide_wordpress_widget_html'.
		$args['title'] = $instance['title'];
		echo $args['before_widget'] . $args['before_title'] . $args['title'] . $args['after_title'];

		// Instantiate query object dynamically from widget config.
		$queryType = $instance['query'];
		$query = new $queryType();

		$query->setParameterValues($instance);
				
		// provide widget_id for separate caching.
		$query->loadWithCacheKey($args['widget_id']);

		echo $query->getHtml();

		echo $args['after_widget'];
	}
}
?>