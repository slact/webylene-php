<?
	class user
	{
		function login($username, $password, $hashed=false)
		{
			$password=!$hashed ? sha1($password) : $password; 
			$result=(val(val($GLOBALS['db']->niceQuery('SELECT count(*) as count FROM user WHERE username=' . db::esc($username) . ' AND password=' . db::esc($password) . ';'),0),'count'))==1;
			if($result)
			{
				//update 'last seen' field
				$GLOBALS['db']->niceQuery("UPDATE user SET lastSeen = NOW() WHERE username = " . db::esc($username) . ";");
				
				//put the user into the session
				$_SESSION['user']['username']=$username;
				$_SESSION['user']['hashedPassword']=$password;
				$_SESSION['user']['type']='user';
			}
			return $result;			
		}
		
		function register($username, $password)
		{
			$v=$GLOBALS['db']->query('INSERT INTO user SET username=' . db::esc($username) . ', password=' . db::esc(sha1($password)) . ", registered=NOW();");
			return ($v!==false); 
		}
		
		function exists($username)
		{
			return(val($GLOBALS['db']->niceQuery('SELECT count(*) as count FROM user WHERE username=' . db::esc($username) . ';'),0, 'count')==1);
		}
		
		function logout()
		{
			unset($_SESSION['user']);
		}
		
		
		/**
		 * am i logged in? (as a non-anonymous user)
		 **/		
		function loggedIn()
		{
			return(!empty($_SESSION['user']['username']) && $_SESSION['user']['username']!='temp');
		}
		
		/**
		 * @return string current username
		 */		 		
		function whoAmI()
		{
			return($_SESSION['user']['username']);
		}
		
		function name($id)
		{
			return val($GLOBALS['db']->niceQuery('SELECT username FROM user WHERE id=' . db::esc($id) . ';'), 0, 'username');
		}
		
		
		function getId($username=null)
		{
			//var_dump(val($GLOBALS['db']->niceQuery("SELECT id from user WHERE `username`=" . db::esc(($username==null ? self::whoAmI() : $username)) . ";"),0, 'id'));
			
			if($_SESSION['user']['type']=='user')
				return(val($GLOBALS['db']->niceQuery("SELECT id from user WHERE `username`=" . db::esc($username==null ? self::whoAmI() : $username) . ";"),0, 'id'));
			else
				return($_SESSION['user']['id']);
		}
		
		function getType()
		{
			return($_SESSION['user']['type']);
		}
		
		function myId()
		{
			return(
				self::loggedIn() ? 
					val($GLOBALS['db']->niceQuery("SELECT id from user WHERE `username`=" . db::esc(self::whoAmI()) . ";"),0, 'id') :  
					(self::isTemp() ? $_SESSION['user']['id'] : false));
		}
		
		function myName()
		{
			return($_SESSION['user']['username']);
		}
		
		function isTemp()
		{
			return($_SESSION['user']['type']=='temporaryUser');
		}
		
		function loginTemp()
		{
			$matchingTemps=$GLOBALS['db']->niceQuery("SELECT * FROM temporaryUser WHERE ip=" . db::esc($_SERVER['REMOTE_ADDR']) . ";");
			if(count($matchingTemps)==0)
			{//no match -> new temp.
				$res=$GLOBALS['db']->niceQuery("INSERT INTO temporaryUser SET ip=". db::esc($_SERVER['REMOTE_ADDR']) . ", lastSeen=NOW(), firstSeen=NOW();");
				self::logout();
				$_SESSION['user']['username']='temp';
				$_SESSION['user']['type']='temporaryUser';
				$_SESSION['user']['id']=$GLOBALS['db']->whatWasThatInsertId();
			}
			else if(count($matchingTemps)>=1)
			{//there's a match investigate.				
				//that's the guy!
				$GLOBALS['db']->query("UPDATE temporaryUser SET lastSeen=NOW() WHERE ip=". db::esc($_SERVER['REMOTE_ADDR']) . ";");
				self::logout();
				$_SESSION['user']['username']='temp';
				$_SESSION['user']['type']='temporaryUser';
				$_SESSION['user']['id']=$matchingTemps[0]['id'];
			}
			else
			{
				die("bad, but exaggerated. ERROR 0x05539 -- report to site admin.");
			}
		}
	}
