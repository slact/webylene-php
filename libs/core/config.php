<?
//purpose: retrieve
function cf()
{
	$arr=func_get_args();
	$configValue=$GLOBALS['config'];
		foreach($arr as $property)
		{
			if($configValue!=null)
				$configValue=$configValue[$property];
			else
			{
				if (isset($GLOBALS['logger']))
					$GLOBALS['logger']->log('notice', 'Tried reaching a nonexistent config value: $GLOBALS[config][' . implode($arr, '][') . '].');
				return(null);
			}
		}		
		return($configValue);
}
?>
