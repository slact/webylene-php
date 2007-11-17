<?

/**
 * $array[$key][$key2][...]...
 */ 
function val()
{
	$arg=func_get_args();
	$k=array_shift($arg);
	if(count($arg)==1 && is_array(car($arg)))
	{
		$arg=car($arg);
	}
	foreach($arg as $key)
		$k=$k[$key];
	return $k; 
}


/**
 * non-modifying array_push
 * @return array array_push($arr, $val)
 */
function arrayPushed($arr, $val)
{
	array_push($arr, $val);
	return $arr;
}

/**
 * are we in async (ajax etc.) mode?
 */
function async()
{
    return($_REQUEST['async'] || $_REQUEST['mode']=='async'); 
}

/**
 * extract specified keys from array, modifying the array in the process.
 */ 
function array_extract(&$array, $key)
{
	if(!is_array($key) && array_key_exists($key, $array))
	{
		$r[$key]=$array[$key];
		unset($array[$key]);
	}
	else
	{
		foreach($key as $k)
		{
			if(array_key_exists($k, $array))
			{
				$r[$k]=$array[$k];
				unset($array[$k]);
			}
		}
	}
	return $r;
}

/**
 * feeling lispy...
 */
function array_remove_key($array, $key)
{
	if(!is_array($key) && array_key_exists($key, $array))
		unset($array[$key]);
	else
		foreach($key as $k)
			if(array_key_exists($k, $array))
				unset($array[$k]);
	return $array;
}

/**
 * feeling lispy...
 */ 
function car($arr)
{
	return array_shift($arr);
}

/**
 * feeling lispy...
 */
function cdr($arr)
{
	array_shift($arr);
	return $arr;
}

?>
