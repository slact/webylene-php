<?
/**
 * static class to access crisps
 * a crisp is a variable that persists for the next request, and then gets removed -- unless explicitly prolonged
 * @package webylene
 * @subpackage plugins
 */ 
class crisp
{
	static function exists($name)
	{
		return (isset($_SESSION['crisps'][$name]));
	}
	
	static function set($name, $val, $cleaner=array())
	{
		$_SESSION['crisps'][$name]=array('val' => $val, 'keep'=>true, 'cleaner'=>(array) $cleaner); 
	}
	
	/**
	 * attach a function to be called when it's time to remove a crisp. function gets passed the crisp value.
	 * @param string $crisp crisp name
	 * @param string $func function name. $func([crisp value]) will be called.
	 * @return boolean 	 	  
	 **/	 	
	static function attachCleaner($crisp, $func)
	{
		if(self::exists($crisp) && is_callable($func))
			$_SESSION['crisps'][$crisp]['cleaner'][]=$func;
		else
			return false;
		return true;
	}
	
	/**
	 * reset all cleaners associated with a crisp
	 **/	 	
	static function resetCleaners($crisp)
	{
		if(!self::exists($crisp))
			return false;
		$_SESSION['crisps'][$name]['cleaner']=array();
		return true;
	
	}
	
	
	/**
	 * retrieve crisp value
	 */	 	
	static function get($name)
	{
		if(!self::exists($name))
			return null;
		return $_SESSION['crisps'][$name]['val'];
	}
	
	/**
	 * renew crisp -- make sure it won't get erased next time
	 */	 	
	static function renew($name, $val="kwyjibow-f79ertwjkkh23")
	{
		if(!self::exists($name))
			return false;
		
		if($val!="kwyjibow-f79ertwjkkh23")
			$_SESSION['crisps'][$name]['val']=$val;
			
		$_SESSION['crisps'][$name]['keep']=true;
		return true;
	}
	
	static function renewAll()
	{
		foreach($_SESSION['crisps'] as $name => $val)
			self::renew($name); 
	}
	
	 /** 
	  * run in bootstrap to clean the crisps.
	  */	  	 
	function clean()
	{
		if(!isset($_SESSION['crisps']))
			$_SESSION['crisps']=array();
		foreach($_SESSION['crisps'] as $name=>$crisp)
		{
			if(!$crisp['keep'])
			{
				if(is_array($crisp['cleaner']))
				{
					foreach($crisp['cleaner'] as $cleaner)
					{
						if(is_callable($cleaner))
							call_user_func($cleaner, $crisp['val']);
					}
				}
				if(!$crisp['keep']) //re-check, in case a cleaner decided to renew the crisp
					unset($_SESSION['crisps'][$name]); 
			}
			else
				$_SESSION['crisps'][$name]['keep']=false;
		}
	}
	
	//pluginness
	static function ___onTheEnd()
	{
		crisp::clean();
	}
}

	

?>
