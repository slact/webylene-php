<?
/**
 * HTML tag generator. useful to templates
 */

class tag
{
	function generic($tag, $attr=array())
	{
		$special=array('innerHTML','noSelfClosing', 'errorTag', 'tag');
		return("<$tag " . tag::attributes(array_remove_key($attr, $special)) . ((empty($attr['innerHTML']) && !$attr['noSelfClosing']) ? '  />' : ">{$attr['innerHTML']}</$tag>"));
	}
	
	
	function select($attr=array())
	{
		tag::validate($attr);
		//tag::fill($attr, 'innerHTML'); //a little tricky.
		return tag::generic('select', $attr);
	}
	
	function textarea($attr=array())
	{
		tag::validate($attr);
		if(empty($attr['rows']))
			$attr['rows']='2';
		if(empty($attr['cols']))
			$attr['cols']='20';
		$attr['noSelfClosing']=true;
		tag::fill($attr, 'innerHTML');
		return tag::generic('textarea', $attr);
	}
	
	function input($attr=array())
	{
		tag::validate($attr);
		tag::fill($attr);
		return tag::generic('input', $attr);
	}
	
	function checkbox($attr=array())
	{
		$attr['type']='checkbox';
		return tag::input($attr);
	}
	
	/**
	 * fill an input
	 */	 	
	private function fill(&$tag, $value='value')
	{
		$requestValue=val($_REQUEST, self::htmlVarNameToArray($tag['name']));
		if($tag['type']=='checkbox')
		{
			if($requestValue!=null && $requestValue == $tag['value'])
				$tag['checked'] = 'checked'; 
		} 
		else if(($tag['name'] == "input" && $tag['type'] == "password") || $tag['name']=="password")
			$tag[$value]=""; //if it's a password input, clear it.
		else if(!empty($tag['name']) && empty($tag[$value]))
			$tag[$value]=$requestValue;	
	}
	
	private function validate(&$attr)
	{
		if(!empty($attr['name']))
		{
			$errorTags= !empty($attr['errorTag']) ? arrayPushed((array) $attr['errorTag'], $attr['name']) : $attr['name'];
			//var_dump(arrayPushed((array) $attr['errorTag'], $attr['name']));
			if($GLOBALS['err']->areThere($errorTags))
				$attr['class'] = "invalid " . $attr['class'];
		}
	}
	
	private function attributes($attributes)
	{
		unset($attributes['innerHTML']);
		
		foreach ($attributes as $attr => $val) 
			$out.= " $attr='" . addcslashes($val,"'") . "' ";
		return $out;
	}
	
	function async()
	{
		if (async())
			return "<input type=\"hidden\" name=\"mode\" value=\"async\" />";
	}
	
	/**
	 * incremental thingy. that's all.
	 */	 	
	function i()
	{
		static $i = 1;
		static $asynci=100;	
		return(async() ? $asynci++ : $i++);
	}
	
	private function htmlVarNameToArray($str)
	{
		preg_match_all('/(\\[(.+?)\\])|^([^\\[]+)/', $str, $result, PREG_PATTERN_ORDER);
		foreach($result[3] as $res)
			if(!empty($res))
				$fin[]=strtr($res, array(' '=>'_', '.'=>'_'));
		foreach($result[2] as $res)
			if(!empty($res))
				$fin[]=strtr($res, array(' '=>'_', '.'=>'_'));
		return $fin;
		
	}
}



?>
