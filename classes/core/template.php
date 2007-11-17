<?
class template
{
	
	/**
	 * output a template, and do it properly.
	 * @param array $t variables available to the template.	 
	 */	 
	public function out($templateName, $t=array())
	{
		if(!template::verify($templateName))
			return false;
		template::pageOut($templateName, $t);
	}
	
	public function getOutput($templateName, $t=array())
	{
		ob_start();
		template::out($templateName, $t);
		$r=ob_get_contents();
		ob_end_clean();
		return $r;
	}
	
	/**
	 * make sure template's set, and file it points to exists.
	 */	 
	private function verify($templateName)
	{
		if(is_array($templateName))
		{
			if(empty($templateName['path']) || !template::includable($templateName['path']))
			{
				$GLOBALS['logger']->log('error', "Template's path (". $templateName['path'] .") is bogus");
				return false;
			}
		}
		elseif(!(is_array(cf('templates', $templateName))))
		{
			$GLOBALS['logger']->log('error', "Attempted to use template '$templateName' which does not exist in config");
			return false;
		}
		elseif(!template::includable(cf('templates', $templateName, 'path')))
		{	
			$GLOBALS['logger']->log('error', "Template $templateName's path is bogus");
			return false;
		}
		return true;
	}
	
	/**
	 * template + router helper. 
	 * @return mixed boolean if ref given, otherwise current ref (string)
	*/
	
	function pageRef($ref=null)
	{
		$currentRef=$GLOBALS['router']->getProperty('ref');
		return (($ref==null) ? $currentRef : ($ref==$currentRef));
	}
	
	function pageTitle()
	{
		
		return !empty($GLOBALS['page']['title']) ? $GLOBALS['page']['title'] : $GLOBALS['router']->getProperty('title');
	}
	
	private function pageOut($templateName, $t=array())
	{//include template for whatever purpose

		$layout=!empty($GLOBALS['layout']) ? $GLOBALS['layout'] : cf('layouts', 'default');
		
		if(self::get($templateName, 'data')!='')
		{
			$t=array_merge($t, include(self::get($templateName, 'data')));
		}
		if(self::get($templateName, 'stub')==true || self::get($templateName, 'standalone')==true || empty($layout))
		{
			template::plainInclude(self::get($templateName), $t);
		}
		else
		{
			//what layout should i use?
			$t['child']=self::get($templateName); //let the page layout know what to include
			$t['css'] = array_merge((array) $t['css'],(array) self::get($templateName, 'css'), (array) self::get($layout, 'css'));
			$t['js'] = array_merge((array) $t['js'],(array) self::get($layout, 'js'), (array) self::get($templateName, 'js'));
			template::plainInclude(self::get($layout), $t);
		}
	}
	
	/**
	 * language hack -- parameter isolator.
	 */	 	
	private function plainInclude($template, $t=null)
	{	
		include(ROOT.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR . $template['path']);
	}
	
	/**
	 * include the child from parent template (i.e. include contents.tmpl from layout.tmpl)
	 */	 	
	public function myChild($t)
	{
		self::plainInclude($t['child'], $t);
	}
	
	public function getPageTitle()
	{
		return($GLOBALS['page']['title']);
	}
	
	public function setPageTitle($title)
	{
		$GLOBALS['page']['title']=$title;
	}
	
	/**
	 * cf() shortener. equivalent to cf('templates', $whatever, $whateverElse....);
	 * @see: cf()
	 */	 	 	
	public function get()
	{
		$args=func_get_args(); //stupid php interpreter keeping me away from one-liners
		array_unshift($args,'templates'); //see what i mean?...
		return call_user_func_array('cf', $args);
	}
	
	/**
	 * am I, a filename, reachable for an include()?
	 */ 
	private function includable($filename)
	{
		return((($fp=@fopen(ROOT.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$filename, 'r', true))!==false) && @fclose($fp));
	}
	
	public function setLayout($name)
	{
		$GLOBALS['layout']=$name;
	}
}
?>
