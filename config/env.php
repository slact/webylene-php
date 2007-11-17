<?
//this is a special config file. sorry. it is necessary.

$env = (strpos($_SERVER['SERVER_NAME'],'dev.')===0) ? 'dev' : 'prod';
define('ENV',$env);

?>