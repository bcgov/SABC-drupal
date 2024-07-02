<?php

namespace Drupal\sabc_institution\Services;

/**
 * Class Sabc
 *
 * Derived from aeit/fnCommon.inc library.
 */
class Sabc {

  public static function get_instance() {
    if(empty(self::$instance)) {
      self::$instance = new Application;
    }
    return self::$instance;
  }

	public function fnCert(){
		$env = $this->fnEnvironment();
		$config = parse_ini_file('private://aeit/config/config.ini', true);

		if(isset($config['CERTS'])){
			return $config['CERTS'][$env];
		}
		else {
			return NULL;
		}
	}

	public function fnWS($service, $action, $customBase = null){

    $protocol = explode('/', $_SERVER['SERVER_PROTOCOL']);

    define('HOST', ((isset($_SERVER['HTTP_X_FORWARDED_HOST'])) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['SERVER_NAME']));
    //define('BASE', $_SERVER['SERVER_NAME']);
    define('BASE', 'studentaidbc.ca');
    define('PROTOCOL', strtolower($protocol[0]));
    define('PROTOCOL', 'http');

        if(isset($_SESSION['admin_login'])){
            $customBase = $_SESSION['admin_env'];
        }

		$base = BASE;
		if(!is_null($customBase)){
			$base = $customBase;
		}

    $config_url = 'private://aeit/config/config.ini';
    $config = parse_ini_file($config_url, true);

		if(is_file($config_url)){
			$host = (!isset($_COOKIE['Drupal_visitor_environment']) && !defined('Drupal_visitor_environment')) ? $this->fnEnvironment() : ((isset($_COOKIE['Drupal_visitor_environment'])) ? $_COOKIE['Drupal_visitor_environment'] : Drupal_visitor_environment);
			if(!is_null($customBase)){
				switch($customBase){
                    case 'studentaidbc.ca': $host = 'WWW'; break;
                    case 'dev.studentaidbc.ca': $host = 'DEV'; break;
                    case 'uat.studentaidbc.ca': $host = 'UAT'; break;
					default: $host = 'WWW';
				}
			}
			$config = parse_ini_file($config_url, true);

			if(isset($action) && !empty($action))
				return (isset($config[$service])) ? $config[$service][$host] .'/'. $config['SERVICES'][$action] : $service .'/'. $config['SERVICES'][$action];
			else
				return $config[$service][$host];
		}
		else
		{
			return false;
		}
	}

	public function fnLoadClass($fname, $dir = NULL){

		$path = (!empty($dir)) ? $dir.'/'.$fname : $fname;
    $path_aeit_library = \Drupal::configFactory()->getEditable('sabc_private_data_form.settings')->get('path_aeit_library');
		//check to see if file exists
		if(is_file($path_aeit_library.$path))
			include_once($path_aeit_library.$path);
		else
			return false;
	}

	public function fnEnvironment(){

		$config = parse_ini_file('private://aeit/config/config.ini', true);

		$env = 'LOCALHOST';

		if (in_array(HOST, $config['ENVIRONMENTS']))
		{
			foreach($config['ENVIRONMENTS'] as $key => $value)
			{
				if ($value == HOST)
				{
					$env = $key;
					break;
				}
			}
		}
		return $env;
	}

	public function fnFormatPhoneNumber($ph){

		if(!empty($ph)){
			if(strlen($ph) == 10)
				return "(".substr($ph, 0, 3).") ".substr($ph, 3, 3)."-".substr($ph,6);
			if(strlen($ph) == 11)
				return substr($ph, 0, 1) . " (".substr($ph, 1, 3).") ".substr($ph, 4, 3)."-".substr($ph,7);
		}
		else
			return NULL;
	}

	public function fnBCSCLogin(){
		$env = $this->fnEnvironment();
		$config = parse_ini_file('./config/config.ini', true);

		if(isset($config['BCSC'])){
			return $config['BCSC'][$env];
		}
		else {
			return NULL;
		}
	}

  public function fnDetermineEnv()
  {
    return $this->fnEnvironment();
  }
}
?>
