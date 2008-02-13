<?

/**
 * everything runs from here. 
 * @package webylene
 * @subpackage core
 */
class core
{
	//events.
	public $events;
	
	/**
	 * set up the core ,trip some events, etcetera.
	 */
	function core()
	{
		$this->events = new events;
		
		//where will we be including from?...
		ini_set('include_path',ROOT. PATH_SEPARATOR . get_include_path());
		
		$GLOBALS['core']=&$this; //make sure everyone else can use this core
		
		//load first-order php configs. these are expected to only have define()s
		$this->loadFiles('config','php');
		
		//core libs assume nothing. they may be required for core classes, so load them first.
		$this->loadFiles('libs'.DIRECTORY_SEPARATOR.'core','php');
		$this->loadCoreClasses();
		
		$this->loadFiles('libs','php'); //load the regular libs
		
		/**
		 * @global array $GLOBALS['config'] config container. accessed via  cf()_
		 * @see cf() 
		 */ 
		$GLOBALS['config']=array();
		
		$this->yaml=new YAML;
		$this->loadFiles('config','yaml');
		
		
		$this->discoverTemplates();
		
		//any classes with events I need to be aware of?
		$this->loadClassesWithEvents();
		
		$this->event('configLoaded');
		
		$this->loadPlugins();

		$this->event('sessionStart');
		//now that all classes are loaded, we can start the session
		session_start();
		
		$this->event('initializeGlobalClasses');
		
		$this->event('dependenciesLoaded');
		
		$this->event('route');

		$this->event('theEnd');
		
	}
	
	/**
	 * bootstrap function to loop through a directory, including all files inside it.
	 */ 
	private function loadFiles($dir, $extension)
	{
		$dir=ROOT.DIRECTORY_SEPARATOR.$dir.DIRECTORY_SEPARATOR."*.$extension";
		foreach (glob($dir) as $file)
		{
			if($extension!='yaml')
				include_once("$file");
			else
			{
				//okay, we've loaded our configs. now let's load the RIGHT env-specific config.
				$config=$this->yaml->load("$file");
				if(!empty($config['env'][ENV]))
				{
					$config=array_merge_recursive($config, $config['env'][ENV]);
					unset($config['env']);
					
				}
					
				
				$GLOBALS['config']= array_merge($GLOBALS['config'], $config);
			}
		}
	}
	
	
	/**
	 * duh.
	 */
	private function loadPlugins()
	{
		$plugs = cf('plugins');
		foreach ((array) $plugs as $plugin)
		{
			if (!include_once(ROOT . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . "$plugin.php")) //does it load?
				echo ("Failed to load plugin $plugin. No such file found in plugins directory. You sure you installed it?<br />\n");
			if(!class_exists($plugin))//uh oh! the file exists, but it doesn't define the plugin as a class. that's unfortunate.
				echo ("Failed to load plugin $plugin. The file's there, but there's no $plugin class there. Probably the plugin author's fault.<br />\n");
			$this->events->registerClassEventListeners($plugin);
		}
	}
	
	/**
	 * load classes in classes/core. this needs a special function because core classes may have event listeners.
	 */
	private function loadCoreClasses()
	{	
		$dir=ROOT.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR;
		foreach (glob($dir."*.php") as $filename)
		{
			$filePath=pathInfo($filename); //php4 incompatibility 
			$className=$filePath['filename'];
			if($filePath['filename']!='core' && $filePath['extension']='php')
			{
				if (!include_once($filename)) //does it load?
					die("Couldn't load core class $className : file $filePath not found.");
				if(!class_exists($className))//uh oh! the file exists, but it doesn't define the plugin as a class. that's unfortunate.
					die("Core class file $className.php doesn't actually have said class declared.");				
				$this->events->registerClassEventListeners($className);
			}
		}
	}
	
	
	private function loadClassesWithEvents()
	{	
		$dir=ROOT.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR;
		foreach ((array) cf('classes with events') as $filename)
		{
			$filename=$dir.$filename.".php";
			$filePath=pathInfo($filename); //php4 incompatibility 
			$className=$filePath['filename'];
			if($filePath['filename']!='core' && $filePath['extension']='php')
			{
				if (!include_once($filename)) //does it load?
				{
					echo("Class w/event load failed: $className : file $filename not found.");
					return false;
				}	
				if(!class_exists($className))//uh oh! the file exists, but it doesn't define the plugin as a class. that's unfortunate.
				{
					echo("Class w/event load failed: $className.php doesn't actually have said class declared.");				
					return false;
				}
				else
					$this->events->registerClassEventListeners($className);
			}
		}
	}
	
	/**
	 * automatically discover templates in templates/ folder. a nice touch?
	 */
	private function discoverTemplates()
	{
		$dir=ROOT.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR;
		
		//explain this
		$templateFiles=array();
		foreach((array) cf('templates') as $template)
			$templateFiles[]=$dir.$template['path'];
		
		foreach (glob($dir."*.tmpl") as $filename)
		{
			$tmpl=pathinfo($filename);
			if(empty($GLOBALS['config']['templates'][$tmpl['filename']]) && ! in_array($filename, $templateFiles)) //explain this
				$GLOBALS['config']['templates'][$tmpl['filename']]=array(
					'path'=>$tmpl['basename']);			
		}
	}
	
	/**
	 * fire event -- call all $eventName listeners
	 * @param string $eventName
	 */
	function event($eventName)
	{
		$this->events->fire($eventName);
	}
}

/**
 * @package: webylene
 * @subpackage: core
 */
class events
{
	public $events=array();
	private $eventListenerKey="___on"; //three underscores! do we really need that many underscores?...
	
	/**
	 * fire event
	 */
	function fire($eventName)
	{
		foreach((array) $this->events[strtolower($eventName)] as $listener)
		{
			call_user_func($listener);
		}
	}
	
	/**
	 * add event listener for $eventName
	 * @param string $eventName
	 * @param callable listener function
	 */
	function addEventListener($eventName, $listener)
	{
		if(is_callable($listener)) //set event listener for the plugin if necessary
		{
			$this->events[strtolower($eventName)][]=$listener;
		}
		else
			return false;
		return true;
	}
	
	function registerClassEventListeners($className)
	{
		foreach((array) get_class_methods($className) as $method)
			if(strpos($method, $this->eventListenerKey)===0) //of $eventListenerKey is present at beginning of method name
				$this->addEventListener(substr($method, strlen($this->eventListenerKey)), array($className, $method));			
	}
}
?>