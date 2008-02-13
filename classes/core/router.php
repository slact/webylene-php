<?
/**
 * url router
 * @package webylene
 * @subpackage core
 */ 
class router
{
	
	private $topLevelKeywords=array('ref','title', 'destination', 'path', 'properties');
	private $properties=array('title','ref');
	/**
	 * perform routing
	 * @return mixed script path to load, false if no script found	 
	 */	 
	 
	function route($requestUrl)
	{
		//REQUEST_URI
		$router = cf('router');
		$url=parse_url($requestUrl);
		foreach($router['routes'] as $route)
		{	
			$route=$this->parseRoute($route); //this will greatly benefit from being cached
			if($this->walkPath($url['path'], $route['path']))
				return($this->arriveAtDestination($router['destinations'], $route));
		}
		
		//no match. 404.
		return($this->arriveAtDestination($router['destinations'], array('destination'=>$this->parseDestination($router['404']))));
	}
	
	/**
	 * reroute to 404
	 **/	 	
	function route404()
	{
		include($this->arriveAtDestination(cf('router', 'destinations'), array('destination'=>$this->parseDestination(cf('router','404')))));
		$this->curentRoute=array(
			'destination'=>$this->parseDestination(cf('router','404')),
			'parameters'=>array('ref'=>'404'));
		exit;
	}
	
	/**
	 * arrive at destination script. set params if necessary.
	 */	 	
	private function arriveAtDestination($destinationSettings, $route)
	{
		//set request params.
		foreach($route['destination']['param'] as $name=>$val)
			$_REQUEST[$name]=$val;
		
		return ($destinationSettings['location'] . DIRECTORY_SEPARATOR . $route['destination']['script'] . $destinationSettings['extension']);	
	}
	
	/**
	 * walks a path to arrive at destination
	 * @return boolean true on path walk success, falso otherwise 
	 */	 	 	
	private function walkPath($url, $path)
	{//see if the path matches
		$match=array('url'=>false, 'param'=>true);
		
		foreach($path['param'] as $param => $val)
		{//path params. it's an or.
			if($_REQUEST[$param]!=$val)
			{
				$match['param']=false;
				break;
			}
		}
		
		foreach($path['url'] as $furl)
		{//path urls. it's an and.
			if($this->oughtToPreg($furl) ? preg_match($furl, $url, $matches)!=0 : $url==$furl) //matched!
			{
				
				//TODO: make url match expansion less stupid.
				foreach($matches as $capture=>$value)
				{
					if(!is_numeric($capture))
					{
						$_REQUEST[$capture]=$value;
					}
				}
				
				$match['url']=true;
				break;
			}
		}
		return($match['url'] && $match['param']);
	
	}
	
	/**
	 * should, or should we not, match using regular expressions? 
	 * prepare $str
	 * @return boolean true if $str starts with a pipe, false otherwise
	 */	 	 	
	private function oughtToPreg(&$str)
	{
		if ($str[0]=='|')
		{//no regular expression. exact match
			$str=substr($str, 1, strlen($str)); //get rid of that forward-slash
			return false;
		}
		
		$str = '/^' . strtr($str, array('/'=>'\\/')) . '$/';
		return true;
	}

	private function parseDestination($contents)
	{
		if(is_array($contents))
		{//expanded destination notation
			if(!isset($contents['param'])) //no param.
				$contents['param']=array(); //make it so.
			if(!isset($contents['script']))
			{
				var_dump(array($key=>$contents));
				die("destination script path not set. that's bad. really bad. check config/urls.yaml");
			}
		}
		else //shortened destination notation. expand it!
			$contents=array('script'=>$contents, 'param'=>array());
		return($contents);
	}
	
	/**
	 * parse path declaration
	 */	 	
	private function parsePath($contents)
	{
		if(is_array($contents))
		{
			if(!isset($contents['url']))
			{//possibly shorthand path notation.
				//let's find out if it's shorthand.
				$shorthandPath=true;
				foreach( $contents as $k=>$v)
				{
					if(!is_numeric($k)) //there was a non-number index. someone was trying to specify a property. therefore, not shorthand.
						$shorthandPath=false;
				}
				if($shorthandPath)
					$contents=array('url'=>(is_array($contents) ? $contents : array($contents)), 'param'=>array());
				else
				{//it's not a shorthand. are there params?
					if(!empty($contents['param']))
					{
						if(empty($contents['param']))
							$contents['param']=array();
					}
					else
					{//there's no path specified. error.
						die("error parsing full route, shorthand path notation: no path found");
					}
				}
			}
			else
			{//full path notation
				if(!is_array($contents['url']))
					$contents['url']=array($contents['url']);
				if(!isset($contents['param']))
					$contents['param']=array();
			}
		
		}
		else //shorthand single path notation. expand it!
			$contents=array('url'=>array($contents), 'param'=>array());
		
		return $contents;
	}
	
	/**
	 * parses a route
	 */	 	
	function parseRoute($contents)
	{
		//goddamn php
		$knownTopLevel=array();
		foreach((array) $this->topLevelKeywords as $kW)
		{
			if(isset($contents[$kW]))
			{
				$knownTopLevel[$kW]=$contents[$kW];
				unset($contents[$kW]);
			}
		}
		
		if (is_array($contents))
		{//exapanded route notation
			if(!empty($knownTopLevel['destination']) && !empty($knownTopLevel['path']))
			{ //expanded route notation
				$contents=array('destination'=>$this->parseDestination($knownTopLevel['destination']),
								'path'=>$this->parsePath($knownTopLevel['path']),
								'properties'=>$this->parseProperties($knownTopLevel));
			}
				
			else
			{ //maybe shorthand: 1 destination - 1 or many paths notation
				foreach($contents as $k=>$v)
				{
					if(!empty($k) && (!empty($v) || ($v === "0")))
					{	
						$contents = array(
							'destination'=> $this->parseDestination($k),
							'path'=> $this->parsePath($v),
							'properties'=>$this->parseProperties($knownTopLevel));
					}
					else
						die("Unexpected property '$k' while parsing route.");
				}
			}
		}
		else
		{//probably route for a setting
			die("error: i don't understand route!...");
		}
		
		//get the ref
		if(empty($contents['properties']['ref']))
			$contents['properties']['ref']=$contents['destination']['script'];
			
		//properties 'n' such
		$this->currentRoute=$contents;
		return $contents;
	}
	
	
	public function isCurrent($ref)
	{
		return ($this->currentRoute['properties']['ref']==$ref);
	}
	
	/**
	 * id (sorta) of the current route
	*/
	function currentRoute()
	{
		return(!empty($this->currentRoute['properties']['ref']) ? $this->currentRoute['properties']['ref'] : $this->currentRoute['destination']['script']);
	
	}
	
	//unlike the rest, this accepts the whole route.
	function parseProperties($content)
	{
		//are properties explicitly specified or no?
		$props = isset($content['properties']) ? $content['properties'] : $content;
		$done=array();
		foreach($this->properties as $ok)
		{
			if(isset($content[$ok]))
				$done[$ok]=$props[$ok];
		}
		return $done;
	}
	
	/*
	 * get current route property $prop - useful for title and ref
	 */
	function getProperty($prop)
	{
		return (isset($this->currentRoute['properties'])) ? $this->currentRoute['properties'][$prop] : false;
	}
	
	//event listeners n' stuff
	function ___onInitializeGlobalClasses()
	{
		$GLOBALS['router'] = new router;
	}
	
	function ___onRoute()
	{	
		$path=($GLOBALS['router']->route($_SERVER['SCRIPT_URI']));

		$GLOBALS['core']->event('target');
	
		if(!(include(ROOT.DIRECTORY_SEPARATOR.$path)))
			$GLOBALS['logger']->log("error", "router error: routed to $path, but file wasn't there.");
	}
}
?>
