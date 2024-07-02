<?php

ini_set('default_socket_timeout', 40);

require_once('fnCommon.inc');

class aeit {

  public $WSDL; //WSDL LOCATION FOR SERVICE
  public $q; //QUERY STRING ARRAY
  public $valid = false;

  function fnCurlRequest($method, $url, $data = '', $headers = ''){

    $method = strtoupper($method);

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return results as variable, not print to screen
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    if(!empty($headers) && is_array($headers)){
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    if(!empty($data) && in_array($method, array('DELETE', 'POST', 'PUT'))){
      curl_setopt($ch, CURLOPT_POST, 1);  // Use POST method, as opposed to GET
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }

    $resp = curl_exec($ch);
    curl_close($ch);

    if ($resp === false){
      throw new Exception('Bad request made to: ' . $url);
      return 'error';
    }
    else {
      return $resp;
    }
  }

  function fnGetCurlRequest($url, $get_vars = false, $cid = NULL, $cacheExpire = 7200, $cookie_vals = '', $ret_cookies = false, $trace = false,

    $header = array()){

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

    if($this->fnGetCache($cid) == false || empty($cid)){

      // create a new cURL resource
      $ch = curl_init();

      // set URL and other appropriate options
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0);
      curl_setopt($ch, CURLOPT_TIMEOUT, 800); //timeout in seconds
      curl_setopt($ch, CURLOPT_HEADER, "Content-Type:application/xml");

      if (!empty($header)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
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
            $this->fnSetCache($cid,$cacheExpire,$r);
          }
          return $r;
        }

        return false;

      }
      catch (Exception $e) {
        return false;
      }
    } else{
      $r = $this->fnGetCache($cid);
    }
    return $r;
  }

  /*
  *  function fnRESTRequest
  * @params:
  *    $url: url of service
  *    $rt: Request Type. Defaults to GET
  *   $params: parameters passed along with request
  */
  function fnRESTRequest($url, $rt = 'GET', $params = NULL, $user = NULL, $pass = NULL, $return = NULL, $debug = false){

    //get url parameters and build url
    if($rt == 'GET'){
      if(!empty($params) && is_array($params)){
        $url = $url . '?' . http_build_query($params);
      }
    }

    $data = '';
    if($user){
      //set username and password if set
      $data = array(
        'user' => $user,
        'pass' => $pass,
      );
      $data = http_build_query($data, '', '&');
    }


    $headers = array();
    //setup headers
    $options = array(
      'method' => 'GET',
      'data' => $data
    );

    //Change request method  if set
    if(strtoupper($rt) == 'POST'){
      //set value to post
      $options['method'] = 'POST';
    }
    else if(strtoupper($rt) == 'PUT'){
      //set value to put
      $options['method'] = 'PUT';
    }
    else if(strtoupper($rt) == 'DELETE'){
      //set value to delete
      $options['method'] = 'DELETE';
    }

    try{
      $options['timeout'] = '60.0';
      $response = drupal_http_request($url, $options);

      if(isset($response->error)){

        $this->fnError('LC_SYSTEM_RESPONSE_ERROR!', $response->error, $response);
        $this->fnError('LC_SYSTEM_ERROR****', $url, $url);

        //return false;

      } else {
        if($response->code == 200){

          if($return == 'XML'){
            return new SimpleXMLElement($response->data);
          } else {
            return $response->data;
          }

        }
      }
    }
    catch(Exception $e){
      $this->fnError('SYSTEM_ERROR :: LC Error', 'An unexpected error occurred on url'.$url.'');
      return false;
    }

  }

  /*
  *  function fnBuildLivecycleUrl
  *   @params:
  *  $url: url of service
  *   $params: parameters passed along with request
  */
  function fnBuildLiveCycleUrl($url, $params = NULL){
    if(!empty($params) && is_array($params)){
      $type =  $params['type'];

      //build first part of livecycle url
      $livecycleURL = $params['livecycleDetails'];
      $url = $url . $type . '?' . http_build_query($livecycleURL);

      //build second part of livecycle url with the user parameters
      $userDetails = $params['userDetails'];
      //encode the query and concatenate it with the url
      $url = $url . '?' . urlencode(http_build_query($userDetails));
    }

    return $url;
  }

  function fnVerifyGUID(){

    //check to see if uid exists and isset.
    if(isset($this->uid) && !empty($this->uid)){

      //verify length and alpha numeric
      preg_match('/^[A-Za-z0-9]{32}$/', $this->uid, $match);

      //if valid inject uid information into cache
      if(!empty($match) && count($match) == 1){
        return $this->uid;
      }
      else {
        $uid = $this->fnDecrypt($this->uid);

        //try to decrypt uid incase it was encrypted and double check
        preg_match('/^[A-Za-z0-9]{32}$/',$uid, $match2);

        //if valid inject uid information into cache
        if(!empty($match2) && count($match2) == 1){
          return $uid;
        }
        else {
          watchdog('GUID Validation Error on Get Cache', 'GUID failed length and alpha numeric check. loaded GUID: '.$uid.'', NULL, WATCHDOG_ERROR, $link = NULL);
          drupal_goto('user/logout');
        }
      }
    }
  }

  function fnUpdateCache($cid, $bin, $wildcard = false){

    $verifyGUID=$this->fnVerifyGUID();
    if(!empty($verifyGUID)){
      $cid = $cid.'--'.$verifyGUID;
    }

    cache_clear_all($cid, $bin, $wildcard);
  }

  function fnSetCache($cid, $expire, $d, $cache = 'cache_block'){

    $expire = time() + $expire;

    if($cid != NULL){

      $verifyGUID=$this->fnVerifyGUID();
      if(!empty($verifyGUID)){
        $cid = $cid.'--'.$verifyGUID;
      }

      $data = $d;
      $data->guid = $this->fnEncrypt($this->uid);
      cache_set($cid, $data, $cache, $expire);
    }
  }

  function fnGetCache($cid, $reset = false){
    if ($cid == NULL) {
      return false;
    }
    else {
      $verifyGUID=$this->fnVerifyGUID();
      if(!empty($verifyGUID)){
        $cid = $cid.'--'.$verifyGUID;
      }
    }

    ${$cid} = &drupal_static(__FUNCTION__);

    if (!isset(${$cid})) {
      if (!$reset && ($cache = cache_get($cid, 'cache_block')) && !empty($cache->data)) {

        if(time() < $cache->expire){
          return $cache->data;
        }
        else
        {
          //CACHE IS EXPIRED SO CLEAR IT OUT
          $this->fnUpdateCache($cid, 'cache_block');
          return false;
        }
      }
      else
      {
        return false;
      }
    }
    else {
      if (!$reset && ($cache = cache_get($cid, 'cache_block')) && !empty($cache->data)) {
        ${$cid} = $cache->data;
        if(time() < $cache->expire)
          return $cache->data;
        else
          return false;
      }
      else
      {
        return false;
      }
    }
  }

  function fnRequest($action, $params = array(), $cid = NULL, $cacheExpire = 7200, $debug = false){
    ini_set("soap.wsdl_cache_enabled", 0);
    $aved_webservices = \Drupal::configFactory()->getEditable('sabc_private_data_form.settings')->get('aved_webservices');
    $aved_systemuser = \Drupal::configFactory()->getEditable('sabc_private_data_form.settings')->get('aved_systemuser');
    //flag to see if we have cache
    $cache = false;

    if(!empty($this->uid) || arg(1) == 'login' || arg(1) == 'login2' || $this->valid == true){
      //IF DEBUG IS TRUE OUTPUT ANY ERRORS
      if($debug == true){
        ini_set('display_errors', 1);
        $options = array('trace' => 1, 'exception' => true);
      }
      else{
        $options = array('exceptions' => true);
      }

      try {

        if(substr($this->WSDL, 0, 4) != 'http'){
            $config = parse_ini_file('private://aeit/config/config.ini', true);
            $service_name = $config['REV-SERVICES'][$this->WSDL];
            $this->WSDL = fnWS($service_name, $config['SERVICES'][$this->WSDL]);
        }

        if(!empty($options)){
          $client = new SoapClient($this->WSDL, $options);
        }
        else
        {
          $client = new SoapClient($this->WSDL);
        }

        if(count($params) > 0){
          if($this->fnGetCache($cid) == false){
            $response = $client->{$action}($params);
          }
          else
          {
            $cache = true;
            $response = $this->fnGetCache($cid);
          }
        }
        else
        {
            if($this->fnGetCache($cid) == false){
              $response = $client->{$action}();
            }
            else
            {
              $cache = true;
              $response = $this->fnGetCache($cid);
            }
        }

        if($debug == true){
          drupal_set_message('REQUEST:\n <pre>'. htmlentities($client->__getLastRequest()) .'</pre>\n', 'wsError error');
          drupal_set_message('RESPONSE:\n<pre>'.print_r($response, true).'</pre>\n', 'wsError error');
        }
      }
      catch(SoapFault $sf){

        return $sf;

      }
      catch(Exception $e){
        $message = 'Exception error:'. print_r($e, true);
        $headers = "From: " . $aved_webservices . "\r\n";
        mail($aved_systemuser, 'E-Services Error on action '. $action .'', $message, $headers, '-f '. $aved_webservices);
        watchdog('E-Services Error on action: '. $action .'', $e->faultstring, NULL, WATCHDOG_ERROR, $link = NULL);
      }


      if(isset($response) && !empty($response)){

        //CHECK TO MAKE SURE OUR REQUEST DOESN'T HAVE ANY ERRORS
        if(is_soap_fault($response)){
          $headers = "From: " . $aved_webservices . "\r\n";
          $message = "SOAP Fault: (faultcode: {$result->faultcode}, faultstring: {$result->faultstring}) \n";
          $message .= "REQUEST: ". print_r($params, true) ."";

          watchdog('E-Services Error on action: '. $action .'', $message, NULL, WATCHDOG_ERROR, $link = NULL);
          mail($aved_systemuser, 'E-Services Error on action '. $action .'', $message, $headers, '-f '. $aved_webservices);
        }
        else
        {
          if(is_object($response) && !empty($response)){

            if($cache == false && $cid != NULL){
              $this->fnSetCache($cid, $cacheExpire, $response);
            }

            //CHECK TO SEE IF WE HAVE AN ALERT IN CACHE
            if($this->fnGetCache('alert-'.$action.'') == true && $cache == false){
              $headers = "From: " . $aved_webservices . "\r\n";
                mail($aved_systemuser, 'RESOLVED action: '. $action .'', 'E-Service action: '. $action .' has been resolved', $headers, '-f '. $aved_webservices);

              $this->fnUpdateCache('alert-'.$action.'', 'cache_block', true);
            }

            return $response;
          }
          else
          {
            drupal_set_message('We are sorry there was a problem processing your request. Please refresh and try again.', 'wsError error');
            watchdog('E-Services Error on action: '. $action .'', 'We did not receive a valid response from web service. Object expected.', NULL, WATCHDOG_ERROR, $link = NULL);
            $headers = "From: " . $aved_webservices . "\r\n";
            mail($aved_systemuser, 'E-Services Error on action '. $action .'', 'We did not receive a valid response from web service.  Object expected.', $headers, '-f '. $aved_webservices);
            exit;
          }
        }

        if($debug == true){
          drupal_set_message('REQUEST:\n <pre>'. htmlentities($client->__getLastRequest()) .'</pre>\n', 'wsError error');
          drupal_set_message('RESPONSE:\n<pre>'.print_r($response, true).'</pre>\n', 'wsError error');
        }
      }
      else
      {
        drupal_set_message('We are currently experiencing technical difficulties and are working on restoring service.  Please check back shortly.', 'wsError error');
      }
    }
    else
    {
      return array('uicode', 0);
    }
  }

  function array_to_objecttree($array) {

    if (is_numeric(key($array))) { // Because Filters->Filter should be an array

      foreach ($array as $key => $value) {
            $array[$key] = $this->array_to_objecttree($value);
        }

      return $array;
      }

    $Object = new stdClass;

    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $Object->$key = $this->array_to_objecttree($value);
        }
      else
      {
            $Object->$key = $value;
        }
      }

    return $Object;
  }

  function fnError($title, $val, $additionalOutput = NULL, $triggerDefault = true){

    $aved_webservices = \Drupal::configFactory()->getEditable('sabc_private_data_form.settings')->get('aved_webservices');
    $output = print_r($_SERVER, true);
    $additionalOutput = (is_array($additionalOutput)) ? print_r($additionalOutput, true) : 'No additional output';

    $message = "ERROR Message: " . $val . "\n\n";
    $message .= 'USER GUID: '. $this->uid . "\n\n";
    $message .= "REQUEST: {$output} \n";
    $message .= "VARIABLES: {$additionalOutput} \n";

    if($triggerDefault == true){
      drupal_set_message('We are currently experiencing technical difficulties and are working on restoring service.  Please check back

shortly', 'wsError error', false);




    }
    $headers = "From: ". $aved_webservices . "\r\n";
    watchdog('Dashboard Drupal Error: '.$title.'', $message.' - uid: '.$this->uid, NULL, WATCHDOG_ERROR, $link = NULL);
    return false;

  }


      function fnSanitizeData($text, $type='text'){
          if($type == 'email') {
              //return str_replace(['(', ')', '[', ']', ':', '<', '>', "'", '"'], '', $text);
              $text = filter_var($text, FILTER_SANITIZE_EMAIL);
              return trim($text);
          }

          //return str_replace(['#', '.', '~', '!', '@', '$', '%', '^', '(', ')', '_', '+', '=', '{', '}', '[', ']', '|', '/', ':', '*', '<', '>', '?', ',', "'", '"'], '', $text);
          return str_replace(['"'], '', $text);
      }

  }
?>
