<?

	/**
	* YAML wrapper.
	*/
	class YAML
	{		 		
		private $useSyck = false;
		
		/**
		 * let's see if we can use syck.
		 */		 		
		function __construct()
		{
			//syck is preferred
			$this->useSyck=(function_exists('syck_load'));
		}
		
		/**
		 * load yaml from file.
		 * @param string $path path to yaml file. inclide_dir friendly.
		 * @return array		 	
		 */		 	 		
		public function load($path)
		{
			return $this->parse(file_get_contents($path));
		}
		
		/** 
		 * parse yaml string, using syck_load if available, Spyc otherwise
		 * @param string $YAML yaml string. no tabs!! 
		 * @return array
		 */		 		 		
		public function parse($YAML)
		{
			return ($this->useSyck ? syck_load($YAML) : Spyc::YAMLLoad($YAML));
		}
		
		/**
		 * write yaml from an array using the Spyc class
		 * @param array $array array to YAMLize
		 * @return string
		 */		 		 		 		
		public function dump($array)
		{
			return Spyc::dump($array);
		}
	}	
?>
