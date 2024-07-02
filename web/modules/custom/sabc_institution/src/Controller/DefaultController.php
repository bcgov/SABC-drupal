<?php

namespace Drupal\sabc_institution\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\sabc_institution\Services\Application;
use Drupal\sabc_institution\Services\Aeit;
use Drupal\sabc_institution\Services\Sabc;
use Drupal\sabc_institution\Services\SoapClient;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Default controller for the sabc_institution module.
 */
class DefaultController extends ControllerBase implements ContainerInjectionInterface {

  /**
  * @var \Drupal\sabc_instituion\Services\Application
  */
  protected $application;

  /**
  * @var \Drupal\sabc_instituion\Services\Aeit
  */
  protected $aeit;

  /**
  * @var \Drupal\sabc_instituion\Services\Sabc
  */
  protected $sabc;

  /**
  * Application, Aeit and Sabc constructor.
  *
  * @param \Drupal\sabc_institution\Services\Application $application
  * @param \Drupal\sabc_institution\Services\Aeit $aeit
  * @param \Drupal\sabc_institution\Services\Sabc $sabc
  * @param \Drupal\sabc_institution\Services\SoapClient $soapclient
  */
  public function __construct(Application $application, Aeit $aeit, Sabc $sabc, SoapClient $soapclient) {
    $this->application = $application;
    $this->aeit = $aeit;
    $this->sabc = $sabc;
    $this->soapclient = $soapclient;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $application = $container->get('sabc_institution.application');
    $aeit = $container->get('sabc_institution.aeit');
    $sabc = $container->get('sabc_institution.sabc');
    $soapclient = $container->get('sabc_institution.soapclient');
    return new static ($application, $aeit, $sabc, $soapclient);
  }

  /**************************************************************************
	 * Hook Menu - Setup the page for institution details
	 **************************************************************************/
	function sabc_institution_menu() {

		$items = array();

		$items['institution-data-load'] = array(
			'title' => '',
			'page callback' => 'sabcInstitutionDataLoad',
			'access arguments' => array('access content')
		);

		$items['institution/%'] = array(
			'title' => '',
			'page callback' => 'sabcInstitutionDetailsPage',
			'page arguments' => array(1),
			'access arguments' => array('access content')
		);

		// PHP Module – Menu Item
		$items['institution-details/%'] = array (
			'title' => 'Institution Ajax',
			'page callback' => 'sabcInstitutionDetailsPageAjax',
			'access arguments' => array('access content'),
			'page arguments' => array(1),
			'type' => MENU_CALLBACK,
		);

		$items['institution-data-opener'] = array(
			'title' => '',
			'page callback' => 'test_opener',
			'access arguments' => array('access content')
		);
		$items['school-details/%'] = array(
			'title' => '',
			'page callback' => 'school_detailed_records',
			'access arguments' => array('access content'),
			'page arguments' => array(1),
			'type' => MENU_CALLBACK,
		);


		return $items;
	}

  /**
   * {@inheritdoc}
   */
  public function sabcInstitutionDataLoad() {

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Get institutions.
    // $inst = new Application(FALSE);
    // $schools = $inst->fnGetSchools();
    $inst = Application::get_instance();
    $schools = $inst->fnGetSchools();

    // Required datatables when passing back.
    $data['recordsTotal'] = count($schools->SchoolList->School);
    $data['recordsFiltered'] = count($schools->SchoolList->School);
    $data['draw'] = 1;

    // start building json object
    $data['data'] = $schools->SchoolList->School;

    // Assign the datatable row id for each school.
    // DT_RowID - Datatable element defined for tr ids.
    foreach ($data['data'] as $school) {
      $school->DT_RowId = $school->SchoolIDX;
      unset($school->FullTimeEligible);
      unset($school->SchoolCode);
      unset($school->SchoolIDX);
      unset($school->PartTimeEligible);
    }
    // Output text in json format.
    return new JsonResponse($data);
  }

  /**
   * {@inheritdoc}
   */
  public function sabcInstitutionDetailsPage($institution_code) {
    $output = '';

    // Create a new SOAP Call.
    $inst = new Application(FALSE);

    // Query results based on the url/institution code.
    $institution_result = $inst->fnGetSchoolDetails($institution_code);
    $institution_data = $institution_result->schoolDetails;

    return $output;

  }

  /**
   * {@inheritdoc}
   */
  public function sabcInstitutionDetailsPageAjax($institution_code) {

    //Create a new SOAP Call
    $inst = new Application(FALSE);
    $output = '';

    //Query results based on the url/institution code
    $institution_result = $inst->fnGetSchoolDetails($institution_code);
    $institution_data = $institution_result->schoolDetails;

    $output = build_institution_output($institution_data);
    //mb_convert_encoding($a['DecStatus'], 'ISO-8859-1', 'UTF-8')
    $output = mb_convert_encoding($output, 'ISO-8859-1', 'UTF-8');
    if ($output == FALSE) {
      return new JsonResponse("Warning illegal character exists in the institution details, please return back to the results");
    }
    else {
      return new JsonResponse($output);
    }
  }

  public function test_opener() {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    $inst = new Application(FALSE);
    $schools1 = $inst->fnGetGrantSchools();
    $data1['recordsTotal'] = count($schools1);
    $data1['recordsFiltered'] = count($schools1);
    $data1['draw'] = 1;
    $data1['data'] = $schools1;
    return new JsonResponse($data1);
  }

  public function school_detailed_records() {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    $output = build_grants_output();
    if ($output == FALSE) {
      return new JsonResponse("Warning illegal character exists in the institution details, please return back to the results");
    }
    else {
      return new JsonResponse($output);
    }
  }

  public function soap($endpoint) {
    // Get the Symfony request component so that we can adapt the page request
    // accordingly.
    $request = $this->request->getCurrentRequest();

    // Respond appropriately to the different HTTP verbs.
    switch ($request->getMethod()) {
      case 'GET':
        // This is a get request, so we handle it by returning a WSDL file.
        $wsdlFileRender = $this->handleGetRequest($endpoint, $request);

        if ($wsdlFileRender == FALSE) {
          // If the WSDL file was not returned then we issue a 404.
          throw new NotFoundHttpException();
        }

        // Render the WSDL file.
        $wsdlFileOutput = \Drupal::service('renderer')->render($wsdlFileRender);

        // Return the WSDL file as output.
        $response = new Response($wsdlFileOutput);
        $response->headers->set('Content-type', 'application/xml; charset=utf-8');
        return $response;

      case 'POST':
        // Handle SOAP Request.
        $result = $this->handleSoapRequest($endpoint, $request);

        if ($result == FALSE) {
          // False should only be returned via a non-existent endpoint,
          // so we return a 404.
          throw new NotFoundHttpException();
        }

        // Return the response from the SOAP request.
        $response = new Response($result);
        $response->headers->set('Content-type', 'application/xml; charset=utf-8');
        return $response;

      default:
        // Not a GET or a POST request, return a 404.
        throw new NotFoundHttpException();
    }
  }

}
