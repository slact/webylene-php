<?
//what's the root?
define('ROOT',pathinfo(__FILE__, PATHINFO_DIRNAME));
function __autoload($class) //php5 autoloader.
{
	if (!@include_once(ROOT . DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . $class . ".php"))
		include_once(ROOT . DIRECTORY_SEPARATOR . "classes".DIRECTORY_SEPARATOR."core".DIRECTORY_SEPARATOR . $class . ".php");
}

new core(); //see classes/core/core.php if you're curious
?>