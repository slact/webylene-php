<?
/**
 * @package webylene
 * @subpackage core
 */
class logger 
	{
		var $db;
		var $logTable;
		
		function __construct() 
		{
			$this->cf=cf('logger');
			switch($this->cf['logTo'])
			{
				case 'db':
				case 'database':
					$this->db = new database($this->cf['database']['host'], $this->cf['database']['username'], $this->cf['database']['password'], $this->cf['database']['db']);
					$this->logTable=$this->cf['database']['table'];
					break;
				case 'nowhere':
					break;
				case 'file':
					die("logger error: logging to file not yet implemented. log to db please.");
					break;
				case 'output':
					break;
				default:
					die("logger error: unrecognized logTo medium in config. please clarify.");
			}
		}
		
		function __destruct() 
		{
			unset($this->db);
		}
		
		function log($type, $description) 
		{
			if($this->cf['logTo']!='nowhere')
			{
				if($this->cf['logTo']=='output')
				{
					if($type=='db' || $type=='error')
						echo "<div style='background: orange; color:black; font-weght:bold'> error ($type). $description </div>";
				}
				else
				{
					$trace=debug_backtrace();
					if(cf('debug')>2 && in_array($type, array('error', 'db')))
						echo("($type) $description in " . $this->db->esc($trace[1]['file']) . "on line " . $trace[1]['line'] . "<br />");
					if(cf('debug')>4)
						$GLOBALS['errors']->add("($type) $description", 'log');	
					$this->db->rawQuery("INSERT INTO " . $this->logTable . " SET type=" . $this->db->esc($type) . ", description=" . $this->db->esc($description) . ', file=' . $this->db->esc($trace[1]['file']) . ', line=' . $this->db->esc($trace[1]['line']).  ";") or die("INSERT INTO $logTable SET type=" . $this->db->esc($type) . ", description=" . $this->db->esc($description) . ', file=' . $this->db->esc($trace[1]['file']) . ', line=' . $this->db->esc($trace[1]['line']).  ";" . mysql_error($this->db->db));
				}
			}
		}
		
		function ___onConfigLoaded()
		{
			/**
			 * @global mixed $GLOBALS['logger'] logger object 
			 * @see logger
			 */  
			$GLOBALS['logger']=new logger(); 
		}
	}
?>
