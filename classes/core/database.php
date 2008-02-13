<?
/**
 * @package webylene
 * @subpackage core
 */
class database {

	var $db; //database link resource
	
	function __construct($host, $user, $password, $db)
	{	
		if($host==false)
		{
			$this->db=false;
			return false;
		}
		$this->db=@mysql_connect($host, $user, $password, true);
		if(!$this->db)
			die("database error: ". mysql_error() . ". Check your database config.");
		if($this->errNo($this->db))
			die("database error: ". $this->errMsg($this->db) . ". Check your database config.");
		mysql_select_db($db, $this->db);
		if($this->errNo($this->db))
			die("database error: database $db doesn't exist. Check your database config.");
		register_shutdown_function(array(&$this, "kill"));
	}
	
	/**
	 * destructor
	 */	 
	function kill()
	{
		mysql_close($this->db);
	}
	
	/**
	 * returns results as associative array
	 */	 
	function niceQuery($query)
	{
		$result=$this->query($query);
		$array = array();
		while ($row = @mysql_fetch_assoc($result))
			array_push($array, $row);
		return $array;
	}

	/**
	 * returns result as resource link. If there are any errors, they get logged.
	 */	 
	function query($query) 
	{
		$result = $this->rawQuery($query);	
		if(mysql_errno($this->db))
		{
			$GLOBALS['logger']->log('db', mysql_error($this->db) . ". QUERY WAS: $query");
			return false;
		}
		elseif(cf('debug')>2)
		{
			$GLOBALS['logger']->log('notice', "SUCCESFULLY QUERIED: $query");
		}
		return $result;
	}
	
	
	/**
	 * INSERT INTO $database $qq;
	 * @param mixed $qq - array of fields 'n' values or string
	 * @paraqm string $table - where to insert
	 * returns insert_id on success, false on failure
	 */
	function insert($table, $qq)
	{
		return ($this->query("INSERT INTO `$table` " . (is_array($qq) ? db::buildSet($qq) : $qq) ." ;")===false ? false : $this->insertId());
	}
	
	/**
	 * just like mysql_fetch_assoc.
	 */	 	
	function fetchRow($resource)
	{
		return @mysql_fetch_assoc($resource);
	}
	
	/**
	 * status of last query. mysql_errno
	 */	 	
	function errNo()
	{
		return mysql_errno($this->db);
	}
	
	
	/**
	 * @return boolean true if there were no errors with the last query
	 */	
	function noError()
	{
		return ($this->errNo()==0);
	
	}
	
	function errMsg()
	{
		return mysql_error($this->db);
	}
	
	function whatWasThatInsertId()
	{
		return mysql_insert_id($this->db);
	}
	
	/**
	 * no logging, no niceness, no nothing. just do the query and return the resource link.
	 */	 
	function rawQuery($query)
	{
		$result=@mysql_query($query, $this->db);
		return $result;
	}
	
	/**
	 * Quote variable. no mysql_real_escape_string!
	 */ 
	private function quoteAsNeeded($value)
	{
		    // Stripslashes
		    if (get_magic_quotes_gpc()) {
		        $value = stripslashes($value);
		    }
		    $value = "'" . $value . "'";
		    return $value;
	}
	
	/**
	 * mysql_smart_quoter
	 * @see db->esc()
	 */	 	
	function esc($var,$bool=false)
	{
		return ($bool==false ? $this->quoteAsNeeded(mysql_real_escape_string($var, $this->db)) : $var==true);
	}
	
	
	//events
	function ___onInitializeGlobalClasses()
	{
		$db=cf('database');
		
		/**
		 * @global object $GLOBALS['database'] shared database object
		 * @global object $GLOBALS['db'] shorthand for $GLOBALS['database'] 
		 * @see database 
		 */ 
		$GLOBALS['database']=new database($db['host'],$db['username'],$db['password'], $db['db']);
		$GLOBALS['db']=&$GLOBALS['database'];
	}
}
?>
