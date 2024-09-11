<?php

$path_aeit_library = \Drupal::configFactory()->getEditable('sabc_private_data_form.settings')->get('path_aeit_library');
require_once(DRUPAL_ROOT . $path_aeit_library . 'common.inc');

class eServices {

  function fnGetService($service, $action) {

    if(is_file('private://aeit/config/config.ini')){

			$host = (!isset($_COOKIE['Drupal_visitor_environment']) && !defined('Drupal_visitor_environment')) ? fnGetEnvironment() : ((isset($_COOKIE['Drupal_visitor_environment'])) ? $_COOKIE['Drupal_visitor_environment'] : Drupal_visitor_environment);
			$config = parse_ini_file('private://aeit/config/config.ini', true);

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

  function fnGetCurlRequest($action, $get_vars = false, $cid = NULL, $cacheExpire = 7200, $cookie_vals = '', $ret_cookies = false, $trace = false, $header = array()){

    $url = $this->fnGetService('WS-HOSTS', $action);

    $r = array();

    if(!empty($cookie_vals) && is_array($cookie_vals)){
      $cookie = implode('&', $cookie_vals);
    }
    else
    {
      if(!empty($cookie_vals)){
        $cookie = $cookie_vals;
      }
      else
      {
        $cookie = NULL;
      }
    }

    if($trace == true){
      if(headers_sent() == false){
        header('Content-type: text/plain');
      }
    }

    if(fnGetCache($cid) == false || empty($cid)){

      // create a new cURL resource
      $ch = curl_init();

      // TODO: The services are hosted by PCTIA and currently need authentication.
      $pctia_config = \Drupal::configFactory()->getEditable('sabc_private_data_form.settings')->get();
      $username = $pctia_config['pctia']['login'];
      $password = $pctia_config['pctia']['password'];

      // set URL and other appropriate options
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0);
      curl_setopt($ch, CURLOPT_TIMEOUT, 800); //timeout in seconds
      curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_NTLM);
      curl_setopt($ch, CURLOPT_USERPWD, $username.':'.$password);


      if (!empty($header)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
      }
      else {
        curl_setopt($ch, CURLOPT_HEADER, "Content-Type:application/xml");
      }

      if($trace == true){
        curl_setopt($ch, CURLOPT_STDERR, fopen('php://output', 'w'));
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
      }
      else
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

      if($trace == true)
        curl_setopt($ch, CURLOPT_HEADER, 1);
      else
        curl_setopt($ch, CURLOPT_HEADER, 0);

      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

      if(!empty($cookie)){
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_COOKIESESSION, 1);
      }

      //try & catch for curl request to get url
      try {
        // grab URL and pass it to the browser
        $ret = curl_exec($ch);

        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if($httpcode == 200 ){

          if($get_vars == true){

            $info = curl_getinfo($ch);

            if(isset($info['url'])){
              $url = parse_url($info['url']);

              parse_str($url['query'], $r);

              $r['query_string'] = $r;
            }
          }

          if($ret_cookies == true){
            // get cookie
            preg_match('/^Set-Cookie:\s*([^;]*)/mi', $ret, $m);

            $r['cookies'] = $m;
          }

          $r['response'] = $ret;

          if ($cid != NULL){
            fnSetCache($cid,$cacheExpire,$r);
          }
          return $r;
        }

        return false;

      }
      catch (Exception $e) {
        return false;
      }
    } else{
      $r = fnGetCache($cid);
    }
    return $r;
  }

  function fnGetCertifiedSchools() {

    return $this->fnGetCurlRequest('GET_CERTIFIED_SCHOOLS', false, 'fn_certified_schools',CACHE_PERMANENT);
  }

  // API Changed, the following four functions below are not used anymore. Just leaving it here incase we need it again.
  function fnGetRegisteredSchools() {

    return $this->fnGetCurlRequest('GET_REGISTERED_SCHOOLS', false, 'fn_registered_schools',302400);
  }
  function fnGetAccreditedSchools() {

    return $this->fnGetCurlRequest('GET_ACCREDITED_SCHOOLS', false, 'fn_accredited_schools',302400);
  }
  function fnGetSuspendedAndCancelledSchools() {

    return $this->fnGetCurlRequest('GET_SUSPENDED_SCHOOLS', false, 'fn_suspended_schools',302400);
  }
  function fnGetApplyingForAccreditationSchools() {

    return $this->fnGetCurlRequest('GET_APPLYING_SCHOOLS', false, 'fn_applying_schools',302400);
  }
}


?>
