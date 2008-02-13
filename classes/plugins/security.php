<?
/**
 * login/logout/registration
 * 
 * fires events: register, registrationSuccess, registrationFailure, login, loginSuccess, loginFailure, logout
 * @package webylene
 * @subpackage plugins
 */

class security
{
	function ___onTarget()
	{
		if ($_REQUEST['action']=='register')
		{
			$GLOBALS['core']->event('register');
			//validation
			if(empty($_REQUEST['username']))
				$GLOBALS['err']->add("Name can't be blank.", array('username', 'register'));
			if(user::exists($_REQUEST['username']))
				$GLOBALS['err']->add("Name already exists. choose another.", array('username', 'register'));
			if(empty($_REQUEST['password1']))
				$GLOBALS['err']->add("Password can't be blank.", array('password1', 'register'));
			elseif($_REQUEST['password1'] != $_REQUEST['password2'])
				$GLOBALS['err']->add("Passwords don't match.", array('password2', 'register'));
			elseif($_REQUEST['password1']==$_REQUEST['password2'] && $GLOBALS['err']->none())
			{
				//logout first, just in case
					if(user::whoAmI()=='temp')
						user::logout();
				if(user::register($_REQUEST['username'], $_REQUEST['password1']))
				{
					session_regenerate_id(); //sort of prevent session-hijacking
					$_REQUEST['password']=$_REQUEST['password1'];
					$_REQUEST['action']='login';
					$GLOBALS['state']='successful registration';
					$GLOBALS['core']->event('registrationSuccess');
				}
				else
				{
					$GLOBALS['err']->add("Unable to register for some reason. Please let us know about it.",'registration');
					$GLOBALS['core']->event('registrationFailure');
				}
			}
		}	
				
		if ($_REQUEST['action']=='login')
		{
			$GLOBALS['core']->event('login');
			if(empty($_REQUEST['username']))
				$GLOBALS['err']->add("You left out the name.", array('username', 'login'));
			if(empty($_REQUEST['password']))
				$GLOBALS['err']->add("You left out the password.", array('password', 'login'));
			if(!empty($_REQUEST['username']) && !empty($_REQUEST['password']))
			{
				if(!user::login($_REQUEST['username'],$_REQUEST['password']))
					$GLOBALS['err']->add("Wrong.", 'login');
				else
				{
					$loginSuccess=true;
					session_regenerate_id(); //prevent session hijacking.
				}
			}
			$GLOBALS['core']->event($loginSuccess ? 'loginSuccess' : 'loginFailure');
		}

		if($_REQUEST['action']=='logout')
		{
			$GLOBALS['core']->event('logout');
			session_regenerate_id(true); //kill old session.
			user::logout();
			header("Location: /");
			exit;
		}

		if(!user::loggedIn())
		{//login as temp user
			//user::loginTemp();
		}
	}
}


//var_dump($_SESSION['user']);

?>
