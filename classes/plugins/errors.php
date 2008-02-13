<?

/**
 * the errorist
 * @package webylene
 * @subpackage plugins
 */ 
class errors
{
	private $errors=array();
	
	/**
	 * add new error.
	 * @param $association string or array
	*/
	function add($message, $association='main')
	{
		if(!is_array($association))
			$association=array($association);
		$this->errors[]=array('message'=>$message, 'association'=>$association);
	}
	
	/**
	 * any errors?
	 */
	function none()
	{
		return (empty($this->errors));
	}
	
	function reset()
	{
		unset($this->errors);
	}
	
	function areThere($association=null)
	{
		if ($association==null)
			return (!$this->none());
		else
		{
			foreach($this->errors as $error)
				if($this->doErrorTagsMatch($association, $error))
					return true;
			return false;
		}
	}

	private function doErrorTagsMatch($errorTags, $error)
	{		
		return(array_intersect((array) $errorTags, (array) $error['association'])== (array) $errorTags);
	}
	
	/**
	 * get errors associated with $association
	 * @return array relevant errors
	 */	 	
	function get($association=null)
	{
		foreach($this->errors as $one)
		{
			if($this->doErrorTagsMatch($association, $one))
				$errors[]=$one['message'];
		}
		return $errors;
	}
	
	function getRaw()
	{
		return $this->errors;
	}
	
	//pluginness follows
	function ___onInitializeGlobalClasses()
	{
		/**
		 * @global mixed $GLOBALS['errors'] global error manager object
		 * @global mixed $GLOBALS['err'] shorthand for $GLOBALS['err']
		 * @name errors
		 * @see errors
		 */    
		$GLOBALS['errors']=new errors;
		$GLOBALS['err']=&$GLOBALS['errors'];
		$GLOBALS['notices']=new errors;
	}
}
?>
