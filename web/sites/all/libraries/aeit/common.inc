<?php
  $protocol = explode('/', $_SERVER['SERVER_PROTOCOL']);

  define('HOST', ((isset($_SERVER['HTTP_X_FORWARDED_HOST'])) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['SERVER_NAME']));
  //define('BASE', $_SERVER['SERVER_NAME']);
  define('BASE', 'studentaidbc.ca');
  define('PROTOCOL', strtolower($protocol[0]));

  function fnUpdateCache($cid, $bin, $wildcard = false){
    cache_clear_all($cid, $bin, $wildcard);
  }

  function fnSetCache($cid, $expire, $d, $cache = 'cache_block'){

    if ($expire != CACHE_PERMANENT) {
      $expire = time() + $expire;
    }

    if($cid != NULL){
      $data = $d;
      cache_set($cid, $data, $cache, $expire);
    }
  }

  function fnGetCache($cid, $reset = false){

    if ($cid == NULL) {
      return false;
    }

    ${$cid} = &drupal_static(__FUNCTION__);

    if (!isset(${$cid})) {
      if (!$reset && ($cache = cache_get($cid, 'cache_block')) && !empty($cache->data)) {
        //${$cid} = $cache->data;
        if(time() < $cache->expire){
          return $cache->data;
        }
        else
        {
          //CACHE IS EXPIRED SO CLEAR IT OUT
          fnUpdateCache($cid, 'cache_block');
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

  function fnGetEnvironment() {
    $config = parse_ini_file('private://aeit/config/config.ini', true);

		$env = 'LOCALHOST';

		if (in_array(HOST, $config['ENVIRONMENTS'])) {
			foreach($config['ENVIRONMENTS'] as $key => $value) {
				if ($value == HOST) {
					$env = $key;
					break;
				}
			}
		}
		return $env;
  }

  function fnGetMoneyFormat($amount) {

    $amount = number_format((float)$amount, 2, '.', ',');

    return '$'.$amount;

  }
?>
