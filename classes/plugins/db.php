<?
/**
 * Static helper class of all sorts of database-related functions
 * @package webylene
 * @subpackage plugins
 */	 
class db
{			 
	/** 
	 * shorthand for $GLOBALS['database']->esc()
	 */		 
	function esc($var, $bool = false)
	{
		return $GLOBALS['database']->esc($var, $bool);
	}
	
	/**
	 * a quote_smart version of implode
	 */		 
	function implodeSmart($arr, $delimiter=', ')
	{
		foreach($arr as $k=>$a)
			$arr[$k]=db::esc($a);
		return(implode($delimiter, $arr));
	
	}
	
	/**
	 * build a SET blah=blahblah list from an array for INSERT queries, smartquoting along the way
	 * @example: db::buildSet(array('alpha'=>'face', 'round'=>'male')) returns "`alpha`='face', `round`='male'". 
	 * It's good stuff. 		 	
	 */		 
	function buildSet($arr)
	{
		foreach($arr as $key=>$value)
			$set[]="`$key`=" . db::esc($value);
		return (implode(', ', $set));
	}
	
	function SQLifyDate($date)
	{
		return date("Y-m-d H:i:s", (is_numeric($date) ? $date : strtotime($date)));
	}
	
}
?>
