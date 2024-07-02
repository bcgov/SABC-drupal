<?php

namespace Drupal\sabc_institution\Services;

use Drupal\sabc_institution\Services\Aeit;

/**
 * Custom Class for the sabc_institution module.
 */
class Application extends Aeit {

  private static $instance = NULL;

  public function __construct($v=TRUE){
    //parent::__construct($v);
    $this->aeit = $v;
  }

  public static function get_instance() {
    if(empty(self::$instance)) {
      self::$instance = new Application;
    }
    return self::$instance;
  }

  public function fnGetSchools($designationType = 'All', $additionalDetails = 'false', $accreditation = 'false'){

    //CALL THE APPROPRIATE WEB SERVICE
    $this->WSDL = \Drupal::service('sabc_institution.sabc')->fnWS('WS-HOSTS', 'GET_SCHOOLS');
    $this->$ws = \Drupal::service('sabc_institution.aeit')->fnRequest('getSchoolList', array(
          'designationType' => $designationType,
          'schoolName' => '',
          'schoolIDX' => '',
          'schoolCode' => '',
          'city' => '',
          'provinceCode' => '',
          'countryCode' => '',
          'countryProvCode' => '')
      , 'get_schools_list_'.$designationType.'', 3600
    );


    //MAKE SURE WE HAVE SCHOOLS
    if(isset($ws->SchoolList)) {
      return $ws;
    }
    else {
      return NULL;
    }
  }



  public function fnGetGrantSchools(){
    // A EFFACER
    $path_aeit_library = \Drupal::configFactory()->getEditable('sabc_private_data_form.settings')->get('path_aeit_library');
    require_once(DRUPAL_ROOT . $path_aeit_library . 'classes/class.MOSSWS.php');
    $test = new \MOSSWS();

    $path_aeit_library = \Drupal::configFactory()->getEditable('sabc_private_data_form.settings')->get('path_bccgg_file');
    if (($handle = fopen(DRUPAL_ROOT . $path_aeit_library, 'rb')) !== FALSE) {
      $nn = 0;
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
          $c = count($data);
            for ($x=0;$x<$c;$x++)
              {
                $csvarray[$nn][$x] = $data[$x];
              }
            $nn++;
        }
      fclose($handle);
    }


    $schoolname = array();
    for ($x=0;$x<count($csvarray);$x++){
      $schoolname[] .= $csvarray[$x][0];
    }
    foreach ($schoolname as $num => $name) {	}
    $totalschools = array_unique($schoolname);
    //array within array & use  array push
    $bigArray = array();
    foreach ($totalschools as $k => $v){
      array_push($bigArray, array('name' => rtrim($v)));
      //$val2 = array(json_decode($bigArray));
    }
    return $bigArray;
  }

  public function getGrantSchoolDetails(){
    $school_info = "";
    @$url = $_GET["q"];
    $pieces = explode("/", $url);
    $file = './sites/all/files/school-officials/FINAL_BCCGG_ALL_INSTITUTIONS_CSV.csv';
    $searchfor = $pieces[1];
    $Sname =   urldecode($pieces[1]);
    $temp = array();
      if (($handle = fopen("./sites/all/files/school-officials/FINAL_BCCGG_ALL_INSTITUTIONS_CSV.csv", "r")) !== FALSE) {
        $nn = 0;
        $temp['programs'] = array();


        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
          if (rtrim($Sname)==rtrim($data[0])){
            $temp['name'] = rtrim($data[0]);
            $temp['CIP#'] = $data[1];
            array_push($temp['programs'], array(
              'CIP CODE' => $data[2],
              'AREA of Study' => $data[3],
              'CREDENTIAL RECEIVED'=>$data[4]
            ));
          }
          $nn++;
        }
        fclose($handle);
      }
      return $temp;

    $contents = file_get_contents($file);
    $pattern = preg_quote($searchfor, '/');
    $pattern = "/^.*$pattern.*\$/m";
    if(preg_match_all($pattern, $contents, $matches)){
      $school_info .=implode("\n", $matches[0]);
    }
    else{
      // no results found
    }

    $Allinfo = array();
    foreach ($arrayOschools as $k => $v){
      array_push($Allinfo, array('name' => $v));
    }
  }

  public function fnGetSchoolDetails($schoolIDX){

    //CALL THE APPROPRIATE WEB SERVICE
    $this->WSDL = \Drupal::service('sabc_institution.sabc')->fnWS('WS-HOSTS', 'GET_SCHOOLS');

    $ws = $this->fnRequest('getSchoolDetails', array('schoolIDX' => $schoolIDX), 'get_school_details_'.$schoolIDX.'', 300);

    //MAKE SURE RESPONSE IS VALID
    if(isset($ws->schoolDetails)){

      if(is_array($ws->schoolDetails)){
        foreach($ws->schoolDetails as $school){

          if($school->DesignationStatus == 'Under Review'){
            $ws->schoolDetails->DesignationStatusDescript = 'This school is currently being reviewed by the Ministry of Advanced Education. The results of the review will determine whether this school will continue to be eligible for designation.';
          }

          if($school->DesignationStatus == 'Pending'){
            $ws->schoolDetails->DesignationStatusDescript = 'An application has been submitted by this post-secondary institution and a decision is still pending. You must wait until the school is designated before you can apply.';
          }

          if($school->DesignationStatus == 'Denied'){
            $school->DesignationStatus = 'Does Not Meet Criteria';
            $ws->schoolDetails->DesignationStatusDescript = 'This post-secondary institution does not meet the criteria to administer the StudentAid BC program.';
          }

          if($school->DesignationStatus == 'Designated'){
            $ws->schoolDetails->DesignationStatusDescript = 'This post-secondary institution meets the criteria to administer the StudentAid BC program.';
          }
        }
      }
      else
      {
        if($ws->schoolDetails->DesignationStatus == 'Under Review'){
          $ws->schoolDetails->DesignationStatusDescript = 'This school is currently being reviewed by the Ministry of Advanced Education. The results of the review will determine whether this school will continue to be eligible for designation.';
        }

        if($ws->schoolDetails->DesignationStatus == 'Pending'){
          $ws->schoolDetails->DesignationStatusDescript = 'An application has been submitted by this post-secondary institution and a decision is still pending. You must wait until the school is designated before you can apply.';
        }

        if($ws->schoolDetails->DesignationStatus == 'Denied'){
          $ws->schoolDetails->DesignationStatus = 'Does Not Meet Criteria';
          $ws->schoolDetails->DesignationStatusDescript = 'This post-secondary institution does not meet the criteria to administer the StudentAid BC program.';
        }

        if($ws->schoolDetails->DesignationStatus == 'Designated'){
          $ws->schoolDetails->DesignationStatusDescript = 'This post-secondary institution meets the criteria to administer the StudentAid BC program.';
        }
      }
      return $ws;
    }
    else
    {
      return false;
    }
  }

  public function fnValidateSchoolCode($sc){
    $inst = $this->fnGetSchoolDetails($sc);

    if(!empty($inst)){
      $school_code = $inst->schoolDetails->SchoolCode;
      return $school_code;
    } else {
      return FALSE;
    }
  }

}
