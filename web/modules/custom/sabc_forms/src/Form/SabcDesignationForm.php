<?php

namespace Drupal\sabc_forms\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\file\Entity\File;
use Drupal\Core\Site\Settings;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SabcDesignationForm extends FormBase
{


  /**
   * Returns a unique string identifying the form.
   *
   * The returned ID should be a unique string that can be a valid PHP function
   * name, since it's used in hook implementation names such as
   * hook_form_FORM_ID_alter().
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId()
  {
    return 'sabc_designation_form';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {

    $form['#attached']['library'][] = 'sabc_forms/institution_designation';

    #Section 1: Institution Information

    $form['institution_information'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#attributes' => array(
        'id' => 'institution_information',
      ),
      '#title' => $this->t('Institution Information'),
      '#prefix' => Markup::create('<p class="alert alert-info">This application must be completed by B.C. private, out-of-province and international institutions wishing to become designated to administer federal and provincial student financial assistance. The form collects information about institution ownership, addresses, and contacts, as well as regulatory body and program information. StudentAid BC uses this data to help assess whether an institution meets the criteria for designation as specified in the <a href="https://studentaidbc.ca/sites/all/files/school-officials/policy_manual.pdf" target="_blank">StudentAid BC Policy Manual</a>.<br/>The Institution Designation Application must be completed by an institution official. Please ensure the information provided can be publicly verified. For more information on how to complete the application please review the <i>Designation Application Job Aid</i> in the <a href="https://studentaidbc.ca/sites/all/files/school-officials/admin_manual.pdf" target="_blank">StudentAid BC Administration Manual</a>.</p><div>'),
      '#suffix' => Markup::create('</div>'),
    );

    $form['institution_information']['row0'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row">'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['institution_information']['row0']['institution_information__legal_name'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => array('id' => 'institution_legal_name', 'title' => 'Name that identifies the institution for legal, administrative, taxation, or other official purposes. If your institution is a company or corporation that owns multiple educational institutions please provide the company/corporate legal name.'),
      '#title' => Markup::create('Legal Name <span data-toggle="popover" class="icon-info pull-right" data-trigger="hover" data-placement="auto" data-content="Name that identifies the institution for legal, administrative, taxation, or other official purposes. If your institution is a company or corporation that owns multiple educational institutions please provide the company/corporate legal name."></span>'),
      '#prefix' => Markup::create('<div class="col">'),
      '#suffix' => Markup::create('</div>'),
      '#default_value' => NULL
    );
    $form['institution_information']['row0']['institution_information__operating_name'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => array('id' => 'institution_operating_name', 'title' => 'Name (if different than the legal name) that the institution uses when offering programming and services to students.'),
      '#title' => Markup::create('Operating Name <span class="icon-info pull-right" data-toggle="popover" data-trigger="hover" data-placement="auto" data-content="Name (if different than the legal name) that the institution uses when offering programming and services to students."></span>'),
      '#prefix' => Markup::create('<div class="col">'),
      '#suffix' => Markup::create('</div>'),
      '#default_value' => NULL
    );
    $form['institution_information']['row0']['institution_information__institution_type'] = array(
      '#type' => 'select',
      '#title' => Markup::create('Institution Type'),
      //'#default_value' => 'B.C. Private',
      '#options' => array(
        'B.C. Private' => 'B.C. Private',
        'B.C. Public' => 'B.C. Public',
        'Out of Province' => 'Out of Province',
        'United States' => 'United States',
        'International' => 'International',
        'International Medical' => 'International Medical'
      ),
      '#required' => TRUE,
      '#attributes' => array('class' => array('col-sm-4', 'inline'), 'id' => 'institution_type'),
      '#prefix' => Markup::create('<div class="col">'),
      '#suffix' => Markup::create('</div>'),
    );

    $form['institution_information']['row1'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row">'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['institution_information']['row1']['institution_information__ownership'] = array(
      '#type' => 'select',
      '#required' => TRUE,
      '#title' => Markup::create('Ownership'),
      '#prefix' => Markup::create('<div class="col">'),
      '#suffix' => Markup::create('</div>'),
      //'#default_value' => 'Public',
      '#options' => array(
        'Public' => 'Public',
        'Private' => 'Private'
      )
    );
    $form['institution_information']['row1']['institution_information__operation_since'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#prefix' => Markup::create('<div class="col">'),
      '#title' => Markup::create('In continuous operation since <span class="icon-info pull-right" data-toggle="popover" data-trigger="hover" data-placement="auto" data-content="Insitutions operating in B.C. with ministerial consent under the Degree Authorization Act or that have a valid Designation Certificate issued under the Private Training Act are exempt from the two-year requirement."></span>'),
      '#suffix' => Markup::create('<div class="example"><span id="program_not_eligible8" class="alert alert-danger" style="display: none;">This institution is ineligible for StudentAid BC Designation, to be designated institutions must be in operation for over ten years.</span><span id="program_not_eligible8_1" class="alert alert-danger" style="display: none;">This institution is ineligible for StudentAid BC Designation, to be designated institutions must be in operation for over two years.</span></div></div>'),
      '#default_value' => NULL,
      '#attributes' => array(
        'placeholder' => 'MM/YYYY',
        'id' => 'institution_information__operation_since'
      )
    );
    $form['institution_information']['row1']['institution_information__institution_owner_name'] = array(
      '#type' => 'textfield',
      '#attributes' => array('id' => 'institution_owner_name'),
      '#title' => Markup::create('Owner\'s Name'),
      '#prefix' => Markup::create('<div class="col">'),
      '#suffix' => Markup::create('</div>'),
      '#default_value' => NULL
    );

    $form['institution_information']['row2.1'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row">'),
      '#suffix' => Markup::create('</div>'),
    );

    $prod_email = \Drupal::configFactory()->getEditable('sabc_private_data_form.settings')->get('prod_email');
    $form['institution_information']['row2.1']['institution_information__institution_address'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => array('id' => 'institution_address'),
      '#title' => Markup::create('Institution Address <span class="icon-info pull-right" data-toggle="popover" data-trigger="hover" data-placement="auto" data-content="Institutions offering programs at multiple campuses or companies/corporations that own multiple educational institutions may be required to submit a Designation Application for each campus or institution. Contact ' . $prod_email . ' for more information."></span>'),
      '#prefix' => Markup::create('<div class="col-md-12">'),
      '#suffix' => Markup::create('</div>'),
      '#default_value' => NULL
    );

    $form['institution_information']['row2.2'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row">'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['institution_information']['row2.2']['institution_information__institution_city'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => array('id' => 'institution_city'),
      '#title' => Markup::create('Institution City/Town'),
      '#prefix' => Markup::create('<div class="col">'),
      '#suffix' => Markup::create('</div>'),
      '#default_value' => NULL
    );
    $form['institution_information']['row2.2']['institution_information__institution_province'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => array('id' => 'institution_province'),
      '#title' => Markup::create('Institution Province/State'),
      '#prefix' => Markup::create('<div class="col">'),
      '#suffix' => Markup::create('</div>'),
      '#default_value' => NULL
    );
    $form['institution_information']['row2.2']['institution_information__institution_country'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => array('id' => 'institution_country'),
      '#title' => Markup::create('Institution Country'),
      '#prefix' => Markup::create('<div class="col">'),
      '#suffix' => Markup::create('</div>'),
      '#default_value' => NULL
    );
    $form['institution_information']['row2.2']['institution_information__institution_postal'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => array('id' => 'institution_postal'),
      '#title' => Markup::create('Institution Postal/Zip Code'),
      '#prefix' => Markup::create('<div class="col">'),
      '#suffix' => Markup::create('</div>'),
      '#default_value' => NULL
    );

    $form['institution_information']['row2.3'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row">'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['institution_information']['row2.3']['institution_information__mailing_address'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => array('id' => 'mailing_address'),
      '#title' => Markup::create('Mailing Address'),
      '#prefix' => Markup::create('<div class="col-md-12">'),
      '#suffix' => Markup::create('</div>'),
      '#default_value' => NULL
    );
    $form['institution_information']['row2.3.1'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row">'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['institution_information']['row2.3.1']['checkboxes'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="col">Same as Institution Address <input id="same_as_institution_address" type="checkbox"/><br/><br/>'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['institution_information']['row2.4'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row">'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['institution_information']['row2.4']['institution_information__mailing_city'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => array('id' => 'mailing_city'),
      '#title' => Markup::create('Mailing City/Town'),
      '#prefix' => Markup::create('<div class="col">'),
      '#suffix' => Markup::create('</div>'),
      '#default_value' => NULL
    );
    $form['institution_information']['row2.4']['institution_information__mailing_province'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => array('id' => 'mailing_province'),
      '#title' => Markup::create('Mailing Province/State'),
      '#prefix' => Markup::create('<div class="col">'),
      '#suffix' => Markup::create('</div>'),
      '#default_value' => NULL
    );

    $form['institution_information']['row2.4']['institution_information__mailing_country'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => array('id' => 'mailing_country'),
      '#title' => Markup::create('Mailing Country'),
      '#prefix' => Markup::create('<div class="col">'),
      '#suffix' => Markup::create('</div>'),
      '#default_value' => NULL
    );
    $form['institution_information']['row2.4']['institution_information__mailing_postal'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => array('id' => 'mailing_postal'),
      '#title' => Markup::create('Mailing Postal/Zip Code'),
      '#prefix' => Markup::create('<div class="col">'),
      '#suffix' => Markup::create('</div>'),
      '#default_value' => NULL
    );
    $form['institution_information']['row2.5'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row">'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['institution_information']['row2.5']['institution_information__email'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => Markup::create('Institution Email'),
      '#prefix' => Markup::create('<div class="col-md-6">'),
      '#suffix' => Markup::create('</div>'),
      '#default_value' => NULL
    );
    $form['institution_information']['row2.5']['institution_information__website'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => Markup::create('Website URL'),
      '#prefix' => Markup::create('<div class="col-md-6">'),
      '#suffix' => Markup::create('</div>'),
      '#default_value' => NULL
    );


    $form['institution_information']['row3'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row">'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['institution_information']['row3']['institution_information__phone_number'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => array('id' => 'institution_phone_number'),
      '#title' => Markup::create('Phone Number'),
      '#prefix' => Markup::create('<div class="col-md-6">'),
      '#suffix' => Markup::create('</div>'),
      '#default_value' => NULL,
      '#attributes' => array(
        'id' => 'institution_phone_number',
        'placeholder' => "Country code, area code, number"
      ),
    );
    $form['institution_information']['row3']['institution_information__fax_number'] = array(
      '#type' => 'textfield',
      '#title' => Markup::create('Fax Number'),
      '#prefix' => Markup::create('<div class="col-md-6">'),
      '#suffix' => Markup::create('</div>'),
      '#default_value' => NULL,
      '#attributes' => array(
        'id' => 'institution_fax_number',
        'placeholder' => "Country code, area code, number"
      ),
    );

    $form['institution_information']['row4'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row">'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['institution_information']['row4']['institution_information__have_sabc_code'] = array(
      '#type' => 'select',
      '#required' => TRUE,
      '#title' => Markup::create('Do you have an StudentAid BC Institution code?'),
      '#options' => array(
        'Yes' => $this->t('Yes'),
        'No' => $this->t('No'),
      ),
      '#attributes' => array('id' => 'institution_have_sabc_code'),
      '#prefix' => Markup::create('<div class="col-md-6">'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['institution_information']['row4']['institution_information__federal_location_code'] = array(
      '#type' => 'textfield',
      '#title' => Markup::create('SABC Institution Code <span class="icon-info pull-right" data-toggle="popover" data-trigger="hover" data-placement="auto" data-content="SABC Institution Code is the same as the Federal Location Code and can be found on the Master Designation List (MDL) at www.Canlearn.ca."></span>'),
      '#attributes' => array('maxlength' => '4', 'title' => 'SABC Institution Code is the same as the Federal Location Code and can be found on the Master Designation List (MDL) at www.Canlearn.ca.'),
      '#prefix' => Markup::create('<div class="col-md-6" id="institution_sabc_code">'),
      '#suffix' => Markup::create('</div>'),
      '#default_value' => NULL
    );

    $form['institution_information']['row5'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row">'),
      '#suffix' => Markup::create('</div>'),
    );

    $form['regulatory_information'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#attributes' => array(
        'id' => 'regulatory_information',
      ),
      '#title' => $this->t('Regulatory Information')
    );
    $form['regulatory_information']['row1'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row"  id="regulatory_information__institution_is_regulated">'),
      '#suffix' => Markup::create('</div>')
    );
    $form['regulatory_information']['row1']['regulatory_information__institution_is_regulated'] = array(
      '#type' => 'select',
      '#required' => false,
      '#title' => Markup::create('My institution is regulated by one of the (PTIB, DQAB, Private Act of B.C. Legislature, ITA and/or ICBC)  <span class="icon-info" data-toggle="popover" data-trigger="hover" data-placement="auto" data-content="Your institution must be in good standing with the appropriate educational accrediting, regulatory or government body in the jurisdiction where it is located, as per the Institution Designation for Student Financial Assistance chapter of the StudentAid BC Policy Manual. If your institution is not approved or regulated by the authority listed, it is not eligible to be designated."></span>'),
      '#prefix' => Markup::create('<div class="col-md-6" id="regulatory_body">'),
      '#suffix' => Markup::create('<span id="program_not_eligible10" class="alert alert-danger">You must be in good standing with on of the listed regulators to become an SABC designated institution.</span></div>'),
      '#options' => array(
        'Yes' => $this->t('Yes'),
        'No' => $this->t('No'),
      )
    );

    #Section 2: Institution Regulatory Information
    //Fields for B.C. Private institutions
    $form['regulatory_information']['bc_private'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row" id="regulatory_information_bc">'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['regulatory_information']['bc_private']['regulatory_information__bc_private_regulatory_body'] = array(
      '#type' => 'checkboxes',
      '#title' => Markup::create('Regulatory body that is the governing authority for your institution (select all that apply): '),
      '#prefix' => Markup::create('<div id="regulatory_body" class="col-md-6">'),
      '#suffix' => Markup::create('</div><div class="w-100"></div>'),
      //'#default_value' => 'ptip',
      '#options' => array(
        'Private Training Institutions Branch (PTIB)' => '&nbsp;Private Training Institutions Branch (PTIB)',
        'Degree Quality Assurance Board (DQAB)' => '&nbsp;Degree Quality Assurance Board (DQAB)',
        'Private Act of B.C. Legislature' => '&nbsp;Private Act of B.C. Legislature',
        'Industry Training Authority (ITA)' => '&nbsp;Industry Training Authority (ITA)',
        'Insurance Corporation of B.C. (ICBC)' => '&nbsp;Insurance Corporation of B.C. (ICBC)'
      )
    );
    $form['regulatory_information']['bc_private']['option'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="col-md-6">'),
      '#suffix' => Markup::create('</div><div class="w-100"></div>'),
    );
    $form['regulatory_information']['bc_private']['option']['regulatory_information__bc_private_regulatory_body_ptip_code'] = array(
      '#type' => 'textfield',
      '#title' => Markup::create('5-Digit PTIB Institution Code'),
      '#attributes' => array('id' => 'regulatory_information__bc_private_regulatory_body_ptip_code'),
      '#prefix' => Markup::create('<br /><div id="ptip_code">'),
      '#suffix' => Markup::create('</div>'),
    );

    //Fields for Out of Province institutions
    $form['regulatory_information']['out_province_institution'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row" id="regulatory_information_out_province">'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['regulatory_information']['out_province_institution']['regulatory_information__out_province_institution_name_regulatory_body'] = array(
      '#type' => 'textfield',
      '#title' => Markup::create('Name of provincial regulatory body that is the governing authority for your institution:'),
      '#prefix' => Markup::create('<div class="col-md-6">'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['regulatory_information']['out_province_institution']['regulatory_information__out_province_institution_provincial_approval'] = array(
      '#type' => 'radios',
      '#title' => Markup::create('Is your institution approved by your provincial government for the purpose of student financial assistance?'),
      '#prefix' => Markup::create('<div class="col-md-6" id="provincial_approval">'),
      '#suffix' => Markup::create('<span id="program_not_eligible9" class="alert alert-danger">This institution is ineligible, your institution must be approved by your provincial government for the purpose of student financial assistance.</span></div>'),
      //'#default_value' => 'No',
      '#options' => array(
        'Yes' => 'Yes',
        'No' => 'No'
      )
    );

    //Fields for International institutions
    $form['regulatory_information']['international_institution'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row" id="regulatory_information_international">'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['regulatory_information']['international_institution']['row1'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row">'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['regulatory_information']['international_institution']['row1']['regulatory_information__international_institution_name_regulatory_body'] = array(
      '#type' => 'textfield',
      '#title' => Markup::create('Name of governing authority or regulatory body that designated or approved your institution in your home country'),
      '#prefix' => Markup::create('<div class="col-md-6">'),
      '#suffix' => Markup::create('</div>')
    );
    $form['regulatory_information']['international_institution']['row1']['regulatory_information__international_institution_approval'] = array(
      '#type' => 'radios',
      '#title' => Markup::create('Is your institution approved by your country’s government for the purpose of student financial assistance?'),
      '#prefix' => Markup::create('<div class="col-md-6" id="international_approval">'),
      '#suffix' => Markup::create('<span id="program_not_eligible12" class="alert alert-danger">This institution is ineligible, your institution must be approved by your provincial government for the purpose of student financial assistance.</span></div>'),
      //'#default_value' => 'No',
      '#options' => array(
        'Yes' => 'Yes',
        'No' => 'No'
      )
    );
    $form['regulatory_information']['international_institution']['row2'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row">'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['regulatory_information']['international_institution']['row2']['regulatory_information__international_institution_listed_with'] = array(
      '#type' => 'checkboxes',
      '#title' => Markup::create('The institution is listed with the following (select all that apply):'),
      '#prefix' => Markup::create('<div class="col-md-6" id="international_listed_with">'),
      '#suffix' => Markup::create('<span id="program_not_eligible11" class="alert alert-danger">Your institution must be listed with one of the approved options to be eligible for StudentAid BC designation, please select from the list.</span></div>'),
      '#options' => array(
        'My institution is not listed with any of these options' => '&nbsp;My institution is not listed with any of these options',
        'International Association of Universities (website or handbook)' => '&nbsp;International Association of Universities (website or handbook)',
        'Association of Commonwealth Universities' => '&nbsp;Association of Commonwealth Universities',
        'Europa World of Learning: The International Guide to the Academic World' => '&nbsp;Europa World of Learning: The International Guide to the Academic World',
        'U.S. Department of Education has approved for Title IV funding' => '&nbsp;U.S. Department of Education has approved for Title IV funding'
      )
    );
    $form['regulatory_information']['international_institution']['row3'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row">'),
      '#suffix' => Markup::create('</div>'),
    );

    $form['regulatory_information']['international_institution']['row3']['regulatory_information__international_institution_us_dept_edu_iv_title'] = array(
      '#type' => 'textfield',
      '#title' => Markup::create('Please provide your Title IV code in the box below:'),
      '#prefix' => Markup::create('<div class="col-md-6" id="regulatory_information__international_institution_us_dept_edu_iv_title">'),
      '#suffix' => Markup::create('</div>')
    );


    //Fields for U.S. institutions
    $form['regulatory_information']['us_institution'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row" id="regulatory_information_us">'),
      '#suffix' => Markup::create('</div>'),
      // '#weight' => 10
    );

    /*
    $form['regulatory_information']['us_institution']['regulatory_information__us_institution_dept_education_approval'] = array(
      '#type' => 'radios',
      '#title' => Markup::create('Is your institution approved for Title IV funding by the U.S. Department of Education?'),
      '#prefix' => Markup::create('<div class="col-md-12" id="us_approval">'),
      '#suffix' => Markup::create('</div>'),
      '#options' => array(
        'Yes' => 'Yes',
        'No' => 'No'
      )
    );
    */
    //Title IV Code
    $form['regulatory_information']['us_institution']['regulatory_information__title_iv_code'] = array(
      '#type' => 'textfield',
      '#attributes' => array('id' => 'regulatory_information__title_iv_code'),
      '#title' => Markup::create('Your institution must be approved for Title IV funding by the US Department of Education, please provide your Title IV code in the box below:'),
      '#prefix' => Markup::create('<div class="col-md-6" id="regulatory_information_title_iv_container">'),
      '#suffix' => Markup::create('</div>'),
      '#default_value' => NULL
    );

    //Fields for International Medical institutions
    $form['regulatory_information']['international_medical_institution'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row" id="regulatory_information_international_medical">'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['regulatory_information']['international_medical_institution']['row1'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row">'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['regulatory_information']['international_medical_institution']['row1']['regulatory_information__international_medical_institution_name_regulatory_body'] = array(
      '#type' => 'textfield',
      '#title' => Markup::create('Name of governing authority or regulatory body that designated or approved your institution in your home country'),
      '#prefix' => Markup::create('<div class="col-md-6">'),
      '#suffix' => Markup::create('</div>')
    );
    $form['regulatory_information']['international_medical_institution']['row1']['regulatory_information__international_medical_institution_country_approval'] = array(
      '#type' => 'radios',
      '#title' => Markup::create('Is your institution approved by your country’s government for the purpose of student financial assistance?'),
      '#prefix' => Markup::create('<div class="col-md-6" id="international_medical_approval">'),
      '#suffix' => Markup::create('<span id="program_not_eligible13" class="alert alert-danger">This institution is ineligible, your institution must be approved by your provincial government for the purpose of student financial assistance.</span></div>'),
      //'#default_value' => 'No',
      '#options' => array(
        'Yes' => 'Yes',
        'No' => 'No'
      )
    );
    $form['regulatory_information']['international_medical_institution']['row2'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row">'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['regulatory_information']['international_medical_institution']['row2']['regulatory_information__international_medical_institution_listed_with'] = array(
      '#type' => 'checkboxes',
      '#title' => Markup::create('The institution is listed with the following (select all that apply):'),
      '#prefix' => Markup::create('<div class="col-md-6" id="international_medical_listed_with">'),
      '#suffix' => Markup::create('</div>'),
      //'#default_value' => '1',
      '#options' => array(
        'International' => '&nbsp;International Association of Universities (website or handbook)',
        'Association' => '&nbsp;Association of Commonwealth Universities',
        'Europa' => '&nbsp;Europa World of Learning: The International Guide to the Academic World',
        'US' => '&nbsp;U.S. Department of Education as approved for Title IV funding',
      )
    );
    $form['regulatory_information']['international_medical_institution']['row2']['regulatory_information__international_medical_institution_listed_with2'] = array(
      '#type' => 'checkboxes',
      '#title' => Markup::create('The institution is listed with the following (select all that apply):'),
      '#prefix' => Markup::create('<div class="col-md-6" id="international_medical_institution_listed_with">'),
      '#suffix' => Markup::create('</div>'),
      //'#default_value' => '1',
      '#options' => array(
        'FAIMER' => '&nbsp;FAIMER International Medical Directory',
        'World' => '&nbsp;World Health Organization – World Directory of Medical Schools',
        'Approved' => '&nbsp;Approved by a member of the Federation of Medical Regulatory Authorities of Canada'
      )
    );
    $form['regulatory_information']['international_medical_institution']['row3'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row">'),
      '#suffix' => Markup::create('</div>'),
    );

    $form['regulatory_information']['international_medical_institution']['row3']['regulatory_information__international_medical_institution_us_dept_edu_iv_title'] = array(
      '#type' => 'textfield',
      '#title' => Markup::create('Please provide your Title IV code in the box below:'),
      '#prefix' => Markup::create('<div class="col-md-6" id="regulatory_information__international_medical_institution_us_dept_edu_iv_title">'),
      '#suffix' => Markup::create('</div>')
    );


    #Section 3: Institution Contacts
    $form['institution_contacts'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Institution Contacts'),
    );

    //Legal Authority
    $form['institution_contacts']['row1'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row">'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['institution_contacts']['row1']['legal_authority'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('
        <div>
          <div class="col-md-12">
            <h3>Legal Authority <span class="icon-info" data-toggle="popover" data-trigger="hover" data-placement="auto" data-content="The Legal Authority is the individual who is legally authorized to sign a designation agreement and/or commit the institution to the terms and conditions of designation."></span></h3>
          </div>'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['institution_contacts']['row1']['legal_authority']['institution_contacts__legal_authority_first_name'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => array('id' => 'institution_contact_legal_first_name'),
      '#title' => Markup::create('First Name of Legal Authority'),
      '#prefix' => Markup::create('<div class="col-md-6">'),
      '#suffix' => Markup::create('</div>'),
      '#default_value' => NULL
    );
    $form['institution_contacts']['row1']['legal_authority']['institution_contacts__legal_authority_last_name'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => array('id' => 'institution_contact_legal_last_name'),
      '#title' => Markup::create('Last Name of Legal Authority'),
      '#prefix' => Markup::create('<div class="col-md-6">'),
      '#suffix' => Markup::create('</div>'),
      '#default_value' => NULL
    );
    $form['institution_contacts']['row1']['legal_authority']['institution_contacts__legal_authority_title'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => array('id' => 'institution_contact_legal_title'),
      '#title' => Markup::create('Title/Position of Legal Authority'),
      '#prefix' => Markup::create('<div class="col-md-6">'),
      '#suffix' => Markup::create('</div>'),
      '#default_value' => NULL
    );
    $form['institution_contacts']['row1']['legal_authority']['institution_contacts__legal_authority_email'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => array('id' => 'institution_contact_legal_email'),
      '#title' => Markup::create('Legal Authority Email'),
      '#prefix' => Markup::create('<div class="col-md-6">'),
      '#suffix' => Markup::create('</div>'),
      '#default_value' => NULL
    );
    $form['institution_contacts']['row1']['legal_authority']['institution_contacts__legal_authority_phone'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => array('id' => 'institution_contact_legal_phone', 'placeholder' => 'Country code, area code, number'),
      '#title' => Markup::create('Legal Authority Phone'),
      '#prefix' => Markup::create('<div class="col-md-6">'),
      '#suffix' => Markup::create('</div>'),
      '#default_value' => NULL
    );
    $form['institution_contacts']['row1']['legal_authority']['institution_contacts__legal_authority_bceid'] = array(
      '#type' => 'textfield',
      '#attributes' => array('class' => array('short'), 'id' => 'institution_contact_legal_bceid', 'title' => 'Partner portal access is used to complete designation process, update location and program information, submit Appendix 3 information.'),
      '#title' => Markup::create('Legal Authority Business BCeID. <span class="icon-info pull-right" data-toggle="popover" data-trigger="hover" data-placement="auto" data-content="Partner portal access is used to complete designation process, update location and program information, submit Appendix 3 information."></span>'),
      '#prefix' => Markup::create('<div class="col-md-6">'),
      '#suffix' => Markup::create('<small><i>To be completed if this individual is to be set up as the administrator for the institution’s use of the online Partner Portal</i></small></div>'),
      '#default_value' => NULL
    );

    //Primary Contact
    $form['institution_contacts']['row2'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row">'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['institution_contacts']['row2']['primary_contact'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('
        <div>
            <div class="col-md-12">
                <h3>Primary contact  <span class="icon-info" data-toggle="popover" data-trigger="hover" data-placement="auto" data-content="Primary Institution Contact will be main contact for SABC communication."></span></h3>
            </div>'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['institution_contacts']['row2']['primary_contact']['institution_contacts__primary_contact_same_as_legal_authority'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="col-md-12">same as Legal Authority <input id="same_as_legal_authority" type="checkbox"/><br/><br/>'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['institution_contacts']['row2']['primary_contact']['institution_contacts__primary_contact_first_name'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => array('id' => 'institution_contact_legal_first_name2'),
      '#title' => Markup::create('First Name of Primary Contact'),
      '#prefix' => Markup::create('<div class="col-md-6">'),
      '#suffix' => Markup::create('</div>'),
      '#default_value' => NULL
    );
    $form['institution_contacts']['row2']['primary_contact']['institution_contacts__primary_contact_last_name'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => array('id' => 'institution_contact_legal_last_name2'),
      '#title' => Markup::create('Last Name of Primary Contact'),
      '#prefix' => Markup::create('<div class="col-md-6">'),
      '#suffix' => Markup::create('</div>'),
      '#default_value' => NULL
    );
    $form['institution_contacts']['row2']['primary_contact']['institution_contacts__primary_contact_title'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => array('id' => 'institution_contact_legal_title2'),
      '#title' => Markup::create('Title/Position of Primary Contact'),
      '#prefix' => Markup::create('<div class="col-md-6">'),
      '#suffix' => Markup::create('</div>'),
      '#default_value' => NULL
    );
    $form['institution_contacts']['row2']['primary_contact']['institution_contacts__primary_contact_phone'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => array('id' => 'institution_contact_legal_phone2', 'placeholder' => 'Country code, area code, number'),
      '#title' => Markup::create('Primary Contact Phone'),
      '#prefix' => Markup::create('<div class="col-md-6">'),
      '#suffix' => Markup::create('</div>'),
      '#default_value' => NULL
    );
    $form['institution_contacts']['row2']['primary_contact']['institution_contacts__primary_contact_email'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => array('class' => array('short'), 'id' => 'institution_contact_legal_email2', 'title' => 'This address will be used for all StudentAid BC communication for this institution.'),
      '#title' => Markup::create('Primary Contact Email <span class="icon-info pull-right" data-toggle="popover" data-trigger="hover" data-placement="auto" data-content="This address will be used for all SABC communication for this institution."></span>'),
      '#prefix' => Markup::create('<div class="col-md-6">'),
      '#suffix' => Markup::create('<small><i>This address will be used for all StudentAid BC communication for this institution.</i></small></div>'),
      '#default_value' => NULL
    );
    $form['institution_contacts']['row2']['primary_contact']['institution_contacts__primary_contact_bceid'] = array(
      '#type' => 'textfield',
      '#attributes' => array('class' => array('short'), 'id' => 'institution_contact_legal_bceid2', 'title' => 'Partner portal access is used to complete designation process, update location and program information, submit Appendix 3 information.'),
      '#title' => Markup::create('Primary Contact Business BCeID <span class="icon-info pull-right" data-toggle="popover" data-trigger="hover" data-placement="auto" data-content="Partner portal access is used to complete designation process, update location and program information, submit Appendix 3 information."></span>'),
      '#prefix' => Markup::create('<div class="col-md-6">'),
      '#suffix' => Markup::create('<small><i>To be completed  if this individual is to be set up as the administrator for the institution’s use of the online Partner Portal.</i></small></div>'),
      '#default_value' => NULL
    );

    #Program Information
    $form['program_information'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Program Information')
    );
    $form['program_information']['row1'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row">
        <div class="col-md-12">
            <h3>Institutions must have at least one eligible program in order to qualify for designation with StudentAid BC.</h3>
        </div>'),
      '#suffix' => Markup::create('</div>'),
    );

    $form['program_information']['row1']['program_information__program_name'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => array('id' => 'program_information_name', 'title' => 'Program name'),
      '#title' => Markup::create('Program name'),
      '#prefix' => Markup::create('<div class="col-md-6">'),
      '#suffix' => Markup::create('</div><div class="w-100"></div>'),
      '#default_value' => NULL
    );
    $form['program_information']['row1']['program_information__previously_eligible'] = array(
      '#type' => 'radios',
      //'#required' => TRUE,
      '#attributes' => array(
        'title' => 'Has this program been eligible for StudentAid BC funding before?',
        'required' => 'required',
      ),
      '#title' => Markup::create('Has this program been eligible for StudentAid BC funding before?'),
      '#prefix' => Markup::create('<div class="col-md-6" id="international_medical_approval">'),
      '#suffix' => Markup::create('</div><div class="w-100"></div>'),
      //'#default_value' => '1',
      '#options' => array(
        'Yes' => 'Yes',
        'No' => 'No'
      )
    );

    $form['program_information']['row1']['program_information__sabc_program_code'] = array(
      '#type' => 'textfield',
      '#attributes' => array('id' => 'program_information_program_code', 'title' => 'SABC Program Code(if known)'),
      '#title' => Markup::create('SABC Program Code(if known)'),
      '#prefix' => Markup::create('<div class="col-md-6" id="program_information_program_code_container">'),
      '#suffix' => Markup::create('</div><div class="w-100"></div>'),
      '#default_value' => NULL
    );
    $form['program_information']['row1.1'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row">'),
      '#suffix' => Markup::create('</div><div class="w-100"></div>'),
    );
    $form['program_information']['row1.1']['program_information__credential'] = array(
      '#type' => 'select',
      '#required' => TRUE,
      '#title' => Markup::create('Credential (select one): <span class="icon-info" data-toggle="popover" data-trigger="hover" data-placement="auto" data-content="Your program must lead to a formal post-secondary credential in order to be eligible for SABC Funding. Certificates of completion or statements of completion are not considered formal credentials."></span>'),
      '#options' => array(
        'Diploma' => $this->t('Diploma'),
        'Certificate' => $this->t('Certificate'),
        'Degree' => $this->t('Degree'),
        'Masters' => $this->t('Masters'),
        'Doctorate' => $this->t('Doctorate'),
        'Other' => $this->t('Other (specify)'),
      ),
      '#attributes' => array('id' => 'program_information_credential', 'class' => array('col-sm-6', 'inline')),
      '#prefix' => Markup::create('<div class="col-md-6">'),
      '#suffix' => Markup::create('<span id="program_not_eligible" class="alert alert-danger">This program is not eligible.</span></div><div class="w-100"></div>'),
    );
    $form['program_information']['row1.1']['program_information__credential_extra'] = array(
      '#type' => 'textfield',
      '#attributes' => array('id' => 'program_information_credential_extra', 'title' => 'Other Credential (specify)'),
      '#title' => Markup::create('Other Credential (specify)'),
      '#prefix' => Markup::create('<div class="col-md-6" id="program_information_credential_extra_container">'),
      '#suffix' => Markup::create('</div><div class="w-100"></div>'),
      '#default_value' => NULL
    );
    $form['program_information']['row2'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row">'),
      '#suffix' => Markup::create('</div><div class="w-100"></div>'),
    );
    $form['program_information']['row2']['program_information__program_description'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => array('id' => 'program_information_description', 'title' => 'Program description'),
      '#title' => Markup::create('Program description'),
      '#prefix' => Markup::create('<div class="col-md-6">'),
      '#suffix' => Markup::create('</div><div class="w-100"></div>'),
      '#default_value' => NULL
    );
    $form['program_information']['row2']['program_information__total_weeks'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => array('id' => 'program_information_total_weeks'),
      '#title' => Markup::create('Total weeks of study, not including breaks, to complete this program (include practice education):'),
      '#prefix' => Markup::create('<div class="col-md-6">'),
      '#suffix' => Markup::create('<span id="program_not_eligible2" class="alert alert-danger">This program is not eligible.</span></div><div class="w-100"></div>'),
      '#default_value' => NULL
    );
    $form['program_information']['row2']['br'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<br/><div class="row">'),
      '#suffix' => Markup::create('</div><div class="w-100"></div>'),
    );
    $form['program_information']['row2']['program_information__admission_requirements'] = array(
      '#type' => 'radios',
      '#attributes' => array(
        'required' => 'required',
      ),
      '#title' => Markup::create('Admission requirements (select one): <span class="icon-info" data-toggle="popover" data-trigger="hover" data-placement="auto" data-content="All students must have either graduated from high school or equivalent, or be 19 years of age or older for the program to be eligible. This requirement must apply to all students within a program, not only those applying for SABC funding."></span>'),
      '#prefix' => Markup::create('<div class="col-md-6">'),
      '#suffix' => Markup::create('</div><div class="w-100"></div>'),
      '#options' => array(
        'All students must have a minimum of a high school diploma or equivalent' => 'All students must have a minimum of a high school diploma or equivalent',
        'There is no minimum academic requirement' => 'There is no minimum academic requirement',
      )
    );
    $form['program_information']['row2']['program_information__age'] = array(
      '#type' => 'textfield',
      '#attributes' => array('id' => 'program_information_admission_requirements_extra', 'title' => 'Minimum age'),
      '#title' => Markup::create('Enter minimum age'),
      '#prefix' => Markup::create('<div class="col-md-6" id="program_information_admission_requirements_extra_container">'),
      '#suffix' => Markup::create('<span id="program_not_eligible3" class="alert alert-danger">If this is not an academic credit-based program, this program is not eligible.</span></div><div class="w-100"></div>'),
      '#default_value' => NULL
    );

    $form['program_information']['row2']['program_information__average_hours'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => array('id' => 'program_information_average_hours', 'title' => 'Average number of hours per week of study:'),
      '#title' => Markup::create('Average number of hours per week of study:'),
      '#prefix' => Markup::create('<div class="col-md-6">'),
      '#suffix' => Markup::create('</div><div class="w-100"></div>'),
      '#default_value' => NULL
    );
    $form['program_information']['row2']['program_information__total_number_years'] = array(
      '#type' => 'select',
      '#required' => TRUE,
      '#title' => Markup::create('Total number of years to complete this program:'),
      '#options' => array(
        '12 Weeks to <1 Year' => $this->t('12 Weeks to <1 Year'),
        '1 Year to <2 Years' => $this->t('1 Year to <2 Years'),
        '2 Years to <3 Years' => $this->t('2 Years to <3 Years'),
        '3 Years to <4 Years' => $this->t('3 Years to <4 Years'),
        '4 Years to <5 Years' => $this->t('4 Years to <5 Years'),
        '5 Years or more' => $this->t('5 Years or more'),
      ),
      '#attributes' => array('id' => 'program_information_number_years', 'class' => array('col-sm-6', 'inline')),
      '#prefix' => Markup::create('<div class="col-md-6">'),
      '#suffix' => Markup::create('</div><div class="w-100"></div>'),
    );
    $form['program_information']['row2']['br3'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<br/><div class="row">'),
      '#suffix' => Markup::create('</div><div class="w-100"></div>'),
    );

    $form['program_information']['row2']['program_information__course_load_type'] = array(
      '#type' => 'radios',
      '#attributes' => array(
        'required' => 'required',
      ),
      '#title' => Markup::create('Program course load calculation is (select one): <span class="icon-info" data-toggle="popover" data-trigger="hover" data-placement="auto" data-content="The program of studies must be offered and delivered on a full-time basis at 100 percent of a full course load. See Chapter 2 of the StudentAid BC Policy Manual for further information on the full time definitions of credit and hours based programs."></span>'),
      '#prefix' => Markup::create('<div class="col-md-6">'),
      '#suffix' => Markup::create('</div><div class="w-100"></div>'),
      '#options' => array(
        'Credits based' => 'Credits based',
        'Hours based' => 'Hours based',
      )
    );
    $form['program_information']['row2']['program_information__course_load_credits_met'] = array(
      '#type' => 'radios',
      '#title' => Markup::create('Is this program considered full-time by your institution?'),
      '#attributes' => array(),
      '#prefix' => Markup::create('<div class="col-md-6" id="program_information_program_load_option1">'),
      '#suffix' => Markup::create('<span id="program_not_eligible4" class="alert alert-danger">This program is ineligible for StudentAid BC designation - the program must be 100% full time.</span></div><div class="w-100"></div>'),
      '#options' => array(
        'Yes' => 'Yes',
        'No' => 'No'
      )
    );
    $form['program_information']['row2']['program_information__course_load_hours_met'] = array(
      '#type' => 'radios',
      '#title' => Markup::create('Is this program offered at 20 or more instruction hours per week (15 or more Instruction Hours for Aviation Programs)?'),
      '#attributes' => array('id' => 'program_information_program_20'),
      '#prefix' => Markup::create('<div class="col-md-6" id="program_information_program_load_option2">'),
      '#suffix' => Markup::create('<span id="program_not_eligible5" class="alert alert-danger">This program is not eligible.</span></div><div class="w-100"></div>'),
      '#options' => array(
        'Yes' => 'Yes',
        'No' => 'No'
      )
    );
    $form['program_information']['row2']['br4'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<br/><div class="row">'),
      '#suffix' => Markup::create('</div><div class="w-100"></div>'),
    );
    $form['program_information']['row2']['program_information__program_delivery'] = array(
      '#type' => 'radios',
      '#required' => TRUE,
      '#title' => Markup::create('Program delivery method: <span class="icon-info" data-toggle="popover" data-trigger="hover" data-placement="auto" data-content="Online education/learning is a program delivery method that involves the course content delivered via the internet, intranet, audio/video files, or satellite. This delivery method includes both in classroom or out of classroom education."></span>'),
      '#attributes' => array(
        'required' => 'required',
      ),
      '#prefix' => Markup::create('<div class="col-md-6">'),
      '#suffix' => Markup::create('</div><div class="w-100"></div>'),
      '#options' => array(
        'On site' => '&nbsp;On site',
        'Online/blended' => '&nbsp;Online/blended <br/><small><i>If your institution is outside B.C., to be eligible for StudentAid BC designation you must provide a program that is 100% onsite as part of your designation application.
<br/>To assess your online program as part of your designation application you must provide an onsite equivalent of the program for assessment by completing and adding a <a href="https://studentaidbc.ca/sites/all/files/school-officials/confirmation-distance-eligibility.pdf" target="_blank">Confirmation of StudentAid BC Distance Education Eligibility</a> form and supporting documentation to the supporting documentation section below.</i></small>'
      )
    );
    $form['program_information']['row2']['program_information__practice_education'] = array(
      '#type' => 'radios',
      '#required' => TRUE,
      '#title' => Markup::create('Is Practice Education required for completion of this program? <span class="icon-info" data-toggle="popover" data-trigger="hover" data-placement="auto" data-content="Work-integrated learning must be required for graduation and make up no more than 50% of the total program, with the following exception: practicums can be up to 20% of the total program,  and preceptorships can be up to 10% of the total program."></span>'),
      '#attributes' => array(
        'required' => 'required',
      ),
      '#prefix' => Markup::create('<div class="col-md-6">'),
      '#suffix' => Markup::create('</div><div class="w-100"></div>'),
      '#options' => array(
        'Yes' => 'Yes',
        'No' => 'No'
      )
    );
    $form['program_information']['row2']['program_information__required_education'] = array(
      '#type' => 'checkboxes',
      '#title' => Markup::create('What type of Work Integrated Learning is required for completion of this program (select all that apply):'),
      '#prefix' => Markup::create('<div class="col-md-6" id="program_information_type_practice_container">'),
      '#suffix' => Markup::create('</div><div class="w-100"></div>'),
      '#options' => array(
        'Practicum' => '&nbsp;Practicum',
        'Preceptership' => '&nbsp;Preceptership',
        'Other Work Integrated Learning (e.g. internship, paid work term, co-op)' => '&nbsp;Other Work Integrated Learning (e.g. internship, paid work term, co-op)',
      )
    );

    $form['program_information']['row2']['type_practice']['program_information__type_practicum_percent'] = array(
      '#type' => 'textfield',
      '#attributes' => array('id' => 'program_information_type_practice1', 'maxlength' => "3"),
      '#title' => Markup::create('What percentage of the program is composed of Practicum?'),
      '#prefix' => Markup::create('<div class="col-md-6" id="program_information_type_practice1_container">'),
      '#suffix' => Markup::create('</div><div class="w-100"></div>'),
      '#default_value' => NULL
    );
    $form['program_information']['row2']['type_practice']['program_information__type_practicum_hours'] = array(
      '#type' => 'textfield',
      '#attributes' => array('id' => 'program_information_type_practice_hour1', 'maxlength' => "3"),
      '#title' => Markup::create('How many hours of the program is composed of Practicum?'),
      '#prefix' => Markup::create('<div class="col-md-6" id="program_information_type_practice_hour1_container">'),
      '#suffix' => Markup::create('</div><div class="w-100"></div>'),
      '#default_value' => NULL
    );
    $form['program_information']['row2']['type_practice']['program_information__type_preceptership_percent'] = array(
      '#type' => 'textfield',
      '#attributes' => array('id' => 'program_information_type_practice3', 'maxlength' => "3"),
      '#title' => Markup::create('What percentage of the program is composed of Precptership?'),
      '#prefix' => Markup::create('<div class="col-md-6" id="program_information_type_practice3_container">'),
      '#suffix' => Markup::create('</div><div class="w-100"></div>'),
      '#default_value' => NULL
    );
    $form['program_information']['row2']['type_practice']['program_information__type_preceptership_hours'] = array(
      '#type' => 'textfield',
      '#attributes' => array('id' => 'program_information_type_practice_hour3', 'maxlength' => "3"),
      '#title' => Markup::create('How many hours of the program is composed of Precptership?'),
      '#prefix' => Markup::create('<div class="col-md-6" id="program_information_type_practice_hour3_container">'),
      '#suffix' => Markup::create('</div><div class="w-100"></div>'),
      '#default_value' => NULL
    );
    $form['program_information']['row2']['type_practice']['program_information__type_internship_percent'] = array(
      '#type' => 'textfield',
      '#attributes' => array('id' => 'program_information_type_practice4', 'maxlength' => "3"),
      '#title' => Markup::create('What percentage of the program is composed of Work Integrated Learning?'),
      '#prefix' => Markup::create('<div class="col-md-6" id="program_information_type_practice4_container">'),
      '#suffix' => Markup::create('</div><div class="w-100"></div>'),
      '#default_value' => NULL
    );
    $form['program_information']['row2']['type_practice']['program_information__type_internship_hours'] = array(
      '#type' => 'textfield',
      '#attributes' => array('id' => 'program_information_type_practice_hour4', 'maxlength' => "3"),
      '#title' => Markup::create('How many hours of the program is composed of Work Integrated Learning?'),
      '#prefix' => Markup::create('<div class="col-md-6" id="program_information_type_practice_hour4_container">'),
      '#suffix' => Markup::create('</div><div class="w-100"></div>'),
      '#default_value' => NULL
    );

    $form['program_information']['row2']['program_information__esl'] = array(
      '#type' => 'radios',
      '#title' => Markup::create('Does the program have English as a Second Language (ESL) content? <span class="icon-info" data-toggle="popover" data-trigger="hover" data-placement="auto" data-content="ESL content can make up to 20% of a program, programs with more that 20% ESL are ineligible for SABC funding."></span>'),
      '#attributes' => array(
        'required' => 'required',
      ),
      '#prefix' => Markup::create('<div class="col-md-6">'),
      '#suffix' => Markup::create('</div><div class="w-100"></div>'),
      '#options' => array(
        'Yes' => 'Yes',
        'No' => 'No'
      )
    );
    $form['program_information']['row2']['program_information__esl_20_percentage_or_less'] = array(
      '#type' => 'radios',
      '#title' => Markup::create('Is the ESL content less than or equal to 20% of the total program content?'),
      '#prefix' => Markup::create('<div class="col-md-6" id="program_information_esl_percentage_container">'),
      '#suffix' => Markup::create('<span id="program_not_eligible6" class="alert alert-danger">This program is ineligible for StudentAid BC designation, ESL can not make up more than 20% of course content. </span></div><div class="w-100"></div>'),
      '#options' => array(
        'Yes' => 'Yes',
        'No' => 'No'
      )
    );
    $form['program_information']['row2']['br6'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<br/><div class="row">'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['program_information']['row2']['program_information__joint_program'] = array(
      '#type' => 'radios',
      '#attributes' => array(
        'required' => 'required',
      ),
      '#title' => Markup::create('Is the program offered jointly or in partnership with other institutions? <span class="icon-info" data-toggle="popover" data-trigger="hover" data-placement="auto" data-content="If any portion of this program is offered at a partner institution, that institution must also be designated by SABC. Designated institutions are listed in the \'Apply for a loan\' section of the SABC website. "></span>'),
      '#prefix' => Markup::create('<div class="col-md-6">'),
      '#suffix' => Markup::create('</div><div class="w-100"></div>'),
      '#options' => array(
        'Yes' => 'Yes',
        'No' => 'No'
      )
    );
    $form['program_information']['row2']['program_information__partner_institution'] = array(
      '#type' => 'textfield',
      '#attributes' => array('id' => 'program_information_partner_institution'),
      '#title' => Markup::create('Name of the partner Institutions:'),
      '#prefix' => Markup::create('<div class="col-md-6" id="program_information_partner_institution_container">'),
      '#suffix' => Markup::create('</div><div class="w-100"></div>'),
      '#default_value' => NULL
    );
    $form['program_information']['row2']['br7'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<br/><div class="row">'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['program_information']['row2']['program_information__all_fees_to_home_institution'] = array(
      '#type' => 'radios',
      '#title' => Markup::create('Does the student pay all required fees to your institution?'),
      '#prefix' => Markup::create('<div class="col-md-6" id="program_information_student_payments_container">'),
      '#suffix' => Markup::create('</div><div class="w-100"></div>'),
      '#options' => array(
        'Yes' => 'Yes',
        'No' => 'No'
      )
    );
    $form['program_information']['row2']['program_information__partner_designated'] = array(
      '#type' => 'radios',
      '#title' => Markup::create('Are the other institutions designated by StudentAid BC?'),
      '#prefix' => Markup::create('<div class="col-md-6" id="program_information_designated_sabc_container">'),
      '#suffix' => Markup::create('<span id="program_not_eligible7" class="alert alert-danger">This program is ineligible for StudentAid BC Designation, all partner institutions must be SABC designated.  </span></div><div class="w-100"></div>'),
      '#options' => array(
        'Yes' => 'Yes',
        'No' => 'No'
      )
    );

    $form['program_information']['row3']['program_information__total_hours_study_program'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => array('id' => 'total_hours_study_program'),
      '#title' => Markup::create('Total hours of study to complete this program?'),
      '#default_value' => NULL
    );

    $form['program_information']['row3']['program_information__total_weeks_study_program'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => array('id' => 'total_weeks_study_program'),
      '#title' => Markup::create('Total weeks of study per year, not including break, in this program (include practice education) average for all years of the program <span class="icon-info" data-toggle="popover" data-trigger="hover" data-placement="auto" data-content="The maximum length for any year of a program is 52 weeks including breaks. For programs with multiple start and end dates, choose the longest scheduled break when counting the weeks including breaks."></span>'),
      '#default_value' => NULL
    );

    $form['program_information']['row3']['program_information__total_hours_study_per_year'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => array('id' => 'total_hours_study_per_year'),
      '#title' => Markup::create('Total hours of study per year of this program <span class="icon-info" data-toggle="popover" data-trigger="hover" data-placement="auto" data-content="Identify the number of hours of study for this year of the program. Include all hours for all types of WIL terms. Do not include time for institution breaks."></span>'),
      '#default_value' => NULL
    );

    $form['program_information']['education_costs'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('
        <br />
        <div id="education_costs"><h3>Education costs (approved by Regulating Authority)</h3>'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['program_information']['education_costs']['row4']['program_information__actual_tution'] = array(
      '#type' => 'textfield',
      '#attributes' => array('id' => 'actual_tution'),
      '#title' => Markup::create('Actual tuition <span class="icon-info" data-toggle="popover" data-trigger="hover" data-placement="auto" data-content="See the StudentAid BC Policy Manual for more information on eligible program costs. "></span>'),
      '#default_value' => NULL
    );
    $form['program_information']['education_costs']['row4']['program_information__mandatory_fees'] = array(
      '#type' => 'textfield',
      '#attributes' => array('id' => 'mandatory_fees'),
      '#title' => Markup::create('Mandatory fees'),
      '#default_value' => NULL
    );
    $form['program_information']['education_costs']['row4']['program_information__program_related_costs'] = array(
      '#type' => 'textfield',
      '#attributes' => array('id' => 'program_related_costs'),
      '#title' => Markup::create('Program related costs'),
      '#default_value' => NULL
    );
    $form['program_information']['education_costs']['row4']['program_information__exceptional_expenses'] = array(
      '#type' => 'textfield',
      '#attributes' => array('id' => 'exceptional_expenses'),
      '#title' => Markup::create(' Exceptional expenses'),
      '#default_value' => NULL
    );


    #Program Dates
    $form['program_dates'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Program Dates')
    );
    $form['program_dates']['row1'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create(
        '<div>
                <div class="row">
                  <div class="col-md-12">
                    <h3 style="background-color:#ccc; display:block; padding:5px;">Please enter the relevant program dates for the current program year.</h3>
                    <h4 style="background-color:#ccc; display:block; padding:5px;">Program start and end dates <span class="icon-info" data-toggle="popover" data-trigger="hover" data-placement="auto" data-content="Provide start and end dates for each year of the program."></span></h4>
                  </div>
                </div>'),
      '#suffix' => Markup::create('</div><br />'),
    );

    //Program start and end dates
    $form['program_dates']['row1']['year1'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row" id="program_dates_container_year1"><div class="col-md-12"><strong>Year 1:</strong></div>'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['program_dates']['row1']['year1']['program_dates__start_dates_year1'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => array('id' => 'program_dates_start_year1', 'placeholder' => 'yyyy-mm-dd'),
      '#title' => Markup::create('Start date:'),
      '#prefix' => Markup::create('<div class="col-md-4 ml-3">'),
      '#suffix' => Markup::create('</div>'),
      '#default_value' => NULL
    );
    $form['program_dates']['row1']['year1']['program_dates__end_dates_year1'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => array('id' => 'program_dates_end_year1', 'placeholder' => 'yyyy-mm-dd'),
      '#title' => Markup::create('End date:'),
      '#prefix' => Markup::create('<div class="col-md-4">'),
      '#suffix' => Markup::create('</div>'),
      '#default_value' => NULL
    );
    $form['program_dates']['row1']['year1']['apply_all_checkboxes'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="col align-self-center" id="apply_all_program_dates_container">
    <input id="apply_all_program_dates" type="checkbox"/> <label for="apply_all_program_dates">Apply to all years of program</label>'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['program_dates']['row1']['year2'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row" id="program_dates_container_year2"><div class="col-md-12"><strong>Year 2: </strong></div>'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['program_dates']['row1']['year2']['program_dates__start_dates_year2'] = array(
      '#type' => 'textfield',
      '#attributes' => array('id' => 'program_dates_start_year2', 'placeholder' => 'yyyy-mm-dd'),
      '#title' => Markup::create('Start date:'),
      '#prefix' => Markup::create('<div class="col-md-4 ml-3">'),
      '#suffix' => Markup::create('</div>'),
      '#default_value' => NULL
    );
    $form['program_dates']['row1']['year2']['program_dates__end_dates_year2'] = array(
      '#type' => 'textfield',
      '#attributes' => array('id' => 'program_dates_end_year2', 'placeholder' => 'yyyy-mm-dd'),
      '#title' => Markup::create('End date:'),
      '#prefix' => Markup::create('<div class="col-md-4">'),
      '#suffix' => Markup::create('</div><div class="col-md-4"></div>'),
      '#default_value' => NULL
    );
    $form['program_dates']['row1']['year3'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row" id="program_dates_container_year3"><div class="col-md-12"><strong>Year 3: </strong></div>'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['program_dates']['row1']['year3']['program_dates__start_dates_year3'] = array(
      '#type' => 'textfield',
      '#attributes' => array('id' => 'program_dates_start_year3', 'placeholder' => 'yyyy-mm-dd'),
      '#title' => Markup::create('Start date:'),
      '#prefix' => Markup::create('<div class="col-md-4 ml-3">'),
      '#suffix' => Markup::create('</div>'),
      '#default_value' => NULL
    );
    $form['program_dates']['row1']['year3']['program_dates__end_dates_year3'] = array(
      '#type' => 'textfield',
      '#attributes' => array('id' => 'program_dates_end_year3', 'placeholder' => 'yyyy-mm-dd'),
      '#title' => Markup::create('End date:'),
      '#prefix' => Markup::create('<div class="col-md-4">'),
      '#suffix' => Markup::create('</div><div class="col"></div>'),
      '#default_value' => NULL
    );
    $form['program_dates']['row1']['year4'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row" id="program_dates_container_year4"><div class="col-md-12"><strong>Year 4: </strong></div>'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['program_dates']['row1']['year4']['program_dates__start_dates_year4'] = array(
      '#type' => 'textfield',
      '#attributes' => array('id' => 'program_dates_start_year4', 'placeholder' => 'yyyy-mm-dd'),
      '#title' => Markup::create('Start date:'),
      '#prefix' => Markup::create('<div class="col-md-4 ml-3">'),
      '#suffix' => Markup::create('</div>'),
      '#default_value' => NULL
    );
    $form['program_dates']['row1']['year4']['program_dates__end_dates_year4'] = array(
      '#type' => 'textfield',
      '#attributes' => array('id' => 'program_dates_end_year4', 'placeholder' => 'yyyy-mm-dd'),
      '#title' => Markup::create('End date:'),
      '#prefix' => Markup::create('<div class="col-md-4">'),
      '#suffix' => Markup::create('</div><div class="col"></div>'),
      '#default_value' => NULL
    );
    $form['program_dates']['row1']['year5'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row" id="program_dates_container_year5"><div class="col-md-12"><strong>Year 5: </strong></div>'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['program_dates']['row1']['year5']['program_dates__start_dates_year5'] = array(
      '#type' => 'textfield',
      '#attributes' => array('id' => 'program_dates_start_year5', 'placeholder' => 'yyyy-mm-dd'),
      '#title' => Markup::create('Start date:'),
      '#prefix' => Markup::create('<div class="col-md-4 ml-3">'),
      '#suffix' => Markup::create('</div>'),
      '#default_value' => NULL
    );
    $form['program_dates']['row1']['year5']['program_dates__end_dates_year5'] = array(
      '#type' => 'textfield',
      '#attributes' => array('id' => 'program_dates_end_year5', 'placeholder' => 'yyyy-mm-dd'),
      '#title' => Markup::create('End date:'),
      '#prefix' => Markup::create('<div class="col-md-4">'),
      '#suffix' => Markup::create('</div><div class="col"></div>'),
      '#default_value' => NULL
    );
    $form['program_dates']['row1']['button1'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row">'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['program_dates']['row1']['button1']['action'] = array(
      '#type' => 'button',
      '#attributes' => array('id' => 'add_dates_year', 'class' => array('btn', 'btn-primary', 'btn-small')),
      '#prefix' => Markup::create('<div class="col-md-12">'),
      '#suffix' => Markup::create('</div>'),
      '#value' => 'Add additional start date',
    );

    //Scheduled program breaks
    $form['program_dates']['row2'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('
        <div class="program_dates">
            <div class="row">
                <div class="col-md-12">
                    <h4 style="background-color:#ccc; display:block; padding:5px;">Scheduled program breaks.<br/><small>Please enter start and end dates for all program breaks.  <span class="icon-info" data-toggle="popover" data-trigger="hover" data-placement="auto" data-content="Identify each of the scheduled breaks that are planned for the program year (August 1 to July 31 ). Breaks should not exceed two weeks, except for the calendar year-end break (December to January) which can be three weeks long. Do not include break dates that are prior to or after the study period."></span></small></h4>
                </div>
            </div>'),
      '#suffix' => Markup::create('</div>'),
    );

    //year 1
    $form['program_dates']['row2']['breaks1'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row" id="program_breaks_container_year1_breaks">'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['program_dates']['row2']['breaks1']['year1_1'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('
        <div class="col-md-12" id="program_breaks_container_year1_1"><strong>Year 1: </strong>'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['program_dates']['row2']['breaks1']['year1_1']['program_breaks__start_year1_1'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => array('id' => 'program_breaks_start_year1_1', 'placeholder' => 'yyyy-mm-dd'),
      '#title' => Markup::create('Start date:'),
      '#prefix' => Markup::create('<div class="col-md-4">'),
      '#suffix' => Markup::create('</div>'),
      '#default_value' => NULL
    );
    $form['program_dates']['row2']['breaks1']['year1_1']['program_breaks__end_year1_1'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => array('id' => 'program_breaks_end_year1_1', 'placeholder' => 'yyyy-mm-dd'),
      '#title' => Markup::create('End date:'),
      '#prefix' => Markup::create('<div class="col-md-4">'),
      '#suffix' => Markup::create('</div>'),
      '#default_value' => NULL
    );
    $form['program_dates']['row2']['breaks1']['year1_1']['checkboxes'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="col-md-4 align-self-center"><input id="apply_all_program_breaks" type="checkbox"/> <label for="apply_all_program_breaks">Apply to all years of program</label>'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['program_dates']['row2']['breaks1']['button1'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="col-md-12">'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['program_dates']['row2']['breaks1']['button1']['action'] = array(
      '#type' => 'button',
      '#attributes' => array('id' => 'add_breaks_year1', 'class' => array('btn', 'btn-primary', 'btn-small')),
      '#value' => 'Add another break for year 1'
    );

    //year 2
    $form['program_dates']['row2']['breaks2'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row" id="program_breaks_container_year2_breaks">'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['program_dates']['row2']['breaks2']['year2_1'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="col-md-12" id="program_breaks_container_year2_1"><br/><strong>Year 2: </strong>'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['program_dates']['row2']['breaks2']['year2_1']['program_breaks__start_year2_1'] = array(
      '#type' => 'textfield',
      '#attributes' => array('id' => 'program_breaks_start_year2_1', 'placeholder' => 'yyyy-mm-dd'),
      '#title' => Markup::create('Start date:'),
      '#prefix' => Markup::create('<div class="col-md-4" id="program_breaks_start_container_year2_1">'),
      '#suffix' => Markup::create('</div>'),
      '#default_value' => NULL
    );
    $form['program_dates']['row2']['breaks2']['year2_1']['program_breaks__end_year2_1'] = array(
      '#type' => 'textfield',
      '#attributes' => array('id' => 'program_breaks_end_year2_1', 'placeholder' => 'yyyy-mm-dd'),
      '#title' => Markup::create('End date:'),
      '#prefix' => Markup::create('<div class="col-md-4" id="program_breaks_end_container_year2_1">'),
      '#suffix' => Markup::create('</div><div class="col"></div>'),
      '#default_value' => NULL
    );

    $form['program_dates']['row2']['breaks2']['button2'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="col-md-12">'),
      '#suffix' => Markup::create('</div><br/>'),
    );
    $form['program_dates']['row2']['breaks2']['button2']['action'] = array(
      '#type' => 'button',
      '#attributes' => array('id' => 'add_breaks_year2', 'class' => array('btn', 'btn-primary', 'btn-small')),
      '#value' => 'Add another break for year 2',
    );

    //year 3
    $form['program_dates']['row2']['breaks3'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row" id="program_breaks_container_year3_breaks">'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['program_dates']['row2']['breaks3']['year3_1'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="col-md-12" id="program_breaks_container_year3_1"><br/><strong>Year 3: </strong>'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['program_dates']['row2']['breaks3']['year3_1']['program_breaks__start_year3_1'] = array(
      '#type' => 'textfield',
      '#attributes' => array('id' => 'program_breaks_start_year3_1', 'placeholder' => 'yyyy-mm-dd'),
      '#title' => Markup::create('Start date:'),
      '#prefix' => Markup::create('<div class="col" id="program_breaks_start_container_year3_1">'),
      '#suffix' => Markup::create('</div>'),
      '#default_value' => NULL
    );
    $form['program_dates']['row2']['breaks3']['year3_1']['program_breaks__end_year3_1'] = array(
      '#type' => 'textfield',
      '#attributes' => array('id' => 'program_breaks_end_year3_1', 'placeholder' => 'yyyy-mm-dd'),
      '#title' => Markup::create('End date:'),
      '#prefix' => Markup::create('<div class="col" id="program_breaks_end_container_year3_1">'),
      '#suffix' => Markup::create('</div><div class="col"></div>'),
      '#default_value' => NULL
    );

    $form['program_dates']['row2']['breaks3']['button3'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="col-md-12">'),
      '#suffix' => Markup::create('</div><br/>'),
    );
    $form['program_dates']['row2']['breaks3']['button3']['action'] = array(
      '#type' => 'button',
      '#attributes' => array('id' => 'add_breaks_year3', 'class' => array('btn', 'btn-primary', 'btn-small')),
      '#value' => 'Add another break for year 3',
    );

    //year 4
    $form['program_dates']['row2']['breaks4'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row" id="program_breaks_container_year4_breaks">'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['program_dates']['row2']['breaks4']['year4_1'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="col-md-12" id="program_breaks_container_year4_1"><br/><strong>Year 4: </strong>'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['program_dates']['row2']['breaks4']['year4_1']['program_breaks__start_year4_1'] = array(
      '#type' => 'textfield',
      '#attributes' => array('id' => 'program_breaks_start_year4_1', 'placeholder' => 'yyyy-mm-dd'),
      '#title' => Markup::create('Start date:'),
      '#prefix' => Markup::create('<div class="col" id="program_breaks_start_container_year4_1">'),
      '#suffix' => Markup::create('</div>'),
      '#default_value' => NULL
    );
    $form['program_dates']['row2']['breaks4']['year4_1']['program_breaks__end_year4_1'] = array(
      '#type' => 'textfield',
      '#attributes' => array('id' => 'program_breaks_end_year4_1', 'placeholder' => 'yyyy-mm-dd'),
      '#title' => Markup::create('End date:'),
      '#prefix' => Markup::create('<div class="col" id="program_breaks_end_container_year4_1">'),
      '#suffix' => Markup::create('</div><div class="col"></div>'),
      '#default_value' => NULL
    );
    $form['program_dates']['row2']['breaks4']['button4'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="col-md-12">'),
      '#suffix' => Markup::create('</div><br/>'),
    );
    $form['program_dates']['row2']['breaks4']['button4']['action'] = array(
      '#type' => 'button',
      '#attributes' => array('id' => 'add_breaks_year4', 'class' => array('btn', 'btn-primary', 'btn-small')),
      '#value' => 'Add another break for year 4',
    );

    //year 5
    $form['program_dates']['row2']['breaks5'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row" id="program_breaks_container_year5_breaks">'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['program_dates']['row2']['breaks5']['year5_1'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="col-md-12" id="program_breaks_container_year5_1"><br/><strong>Year 5: </strong>'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['program_dates']['row2']['breaks5']['year5_1']['program_breaks__start_year5_1'] = array(
      '#type' => 'textfield',
      '#attributes' => array('id' => 'program_breaks_start_year5_1', 'placeholder' => 'yyyy-mm-dd'),
      '#title' => Markup::create('Start date:'),
      '#prefix' => Markup::create('<div class="col" id="program_breaks_start_container_year5_1">'),
      '#suffix' => Markup::create('</div>'),
      '#default_value' => NULL
    );
    $form['program_dates']['row2']['breaks5']['year5_1']['program_breaks__end_year5_1'] = array(
      '#type' => 'textfield',
      '#attributes' => array('id' => 'program_breaks_end_year5_1', 'placeholder' => 'yyyy-mm-dd'),
      '#title' => Markup::create('End date:'),
      '#prefix' => Markup::create('<div class="col" id="program_breaks_end_container_year5_1">'),
      '#suffix' => Markup::create('</div><div class="col"></div>'),
      '#default_value' => NULL
    );

    $form['program_dates']['row2']['breaks5']['button5'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="col-md-12">'),
      '#suffix' => Markup::create('</div><br/>'),
    );
    $form['program_dates']['row2']['breaks5']['button5']['action'] = array(
      '#type' => 'button',
      '#attributes' => array('id' => 'add_breaks_year5', 'class' => array('btn', 'btn-primary', 'btn-small')),
      '#value' => 'Add another break for year 5',
    );


    #Upload Documents
    $form['upload_documents'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => Markup::create('Supporting Documentation Upload <span class="icon-info" data-toggle="popover" data-trigger="hover" data-placement="auto" data-content="Supporting information is part of your Designation Application, failing to provide this information will cause delays in processing your application. If the program you are including for review is delivered online upload your Confirmation of StudentAid BC Distance Education Eligibility form to avoid processing delays."></span>'),
    );

    $form['upload_documents']['row1'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('
        <div class="container">
          <div class="row">'),
      '#suffix' => Markup::create('
          </div>
        </div>'),
    );

    $form['upload_documents']['row1']['upload_academic_calendar'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="col-md-12">'),
      '#suffix' => Markup::create('</div>'),
    );

    $form['upload_documents']['row1']['upload_academic_calendar']['file_calendar'] = array(
      '#type' => 'managed_file',
      '#name' => 'file_outline',
      '#required' => TRUE,
      '#attributes' => array('id' => 'upload_academic_calendar', 'title' => ' Upload Academic Calendar'),
      '#title' => Markup::create(' Upload Academic Calendar'),
      '#upload_validators' => array(
        'file_validate_extensions' => array('pdf doc docx'),
        'file_validate_size' => array(2516582), // 2.4MB
      ),
      '#upload_location' => 'temporary://',
    );

    $form['upload_documents']['row1']['upload_program_outline'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="col-md-12">'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['upload_documents']['row1']['upload_program_outline']['file_outline'] = array(
      '#type' => 'managed_file',
      '#name' => 'file_outline',
      '#required' => TRUE,
      '#attributes' => array('id' => 'upload_program_outline', 'title' => ' Upload Program Outline'),
      '#title' => Markup::create(' Upload Program Outline'),
      '#prefix' => Markup::create('<div class="col-md-12">'),
      '#suffix' => Markup::create('</div>'),
      '#upload_validators' => array(
        'file_validate_extensions' => array('pdf doc docx'),
        'file_validate_size' => array(2516582), // 2.4MB
      ),
      '#upload_location' => 'temporary://',
    );

    $form['upload_documents']['row1']['upload_additional_information'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="col-md-12">'),
      '#suffix' => Markup::create('</div>'),
    );
    $form['upload_documents']['row1']['upload_additional_information']['file_additional_info'] = array(
      '#type' => 'managed_file',
      '#name' => 'file_additional_info',
      '#attributes' => array('id' => 'upload_additional_information', 'title' => ' Upload Additional Information'),
      '#title' => Markup::create(' Upload Additional Information'),
      '#prefix' => Markup::create('<div class="col-md-12">'),
      '#suffix' => Markup::create('</div>'),
      '#upload_validators' => array(
        'file_validate_extensions' => array('pdf doc docx'),
        'file_validate_size' => array(2516582), // 2.4MB
      ),
      '#upload_location' => 'temporary://',
    );

    $form['declaration'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Declaration')
    );

    $form['declaration']['row1'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row">'),
      '#suffix' => Markup::create('</div>'),
    );

    $form['declaration']['row1']['declaration__first_name'] = array(
      '#type' => 'textfield',
      '#attributes' => array('id' => 'declaration_first_name', 'title' => 'First name of individual completing the Designation Application'),
      '#title' => Markup::create('First name of individual completing the Designation Application'),
      '#prefix' => Markup::create('<div class="col-md-6">'),
      '#suffix' => Markup::create('</div>'),
      '#required' => TRUE,
    );
    $form['declaration']['row1']['declaration__last_name'] = array(
      '#type' => 'textfield',
      '#attributes' => array('id' => 'declaration_last_name', 'title' => 'Last name of individual completing the Designation Application'),
      '#title' => Markup::create('Last name of individual completing the Designation Application'),
      '#prefix' => Markup::create('<div class="col-md-6">'),
      '#suffix' => Markup::create('</div>'),
      '#required' => TRUE,
    );
    $form['declaration']['row1']['declaration__title'] = array(
      '#type' => 'textfield',
      '#attributes' => array('id' => 'declaration_title', 'title' => 'Title/Position of individual completing the Designation Application'),
      '#title' => Markup::create('Title/Position of individual completing the Designation Application'),
      '#prefix' => Markup::create('<div class="col-md-6">'),
      '#suffix' => Markup::create('</div>'),
      '#required' => TRUE,
    );
    $form['declaration']['row1']['declaration__email'] = array(
      '#type' => 'textfield',
      '#attributes' => array('id' => 'declaration_email', 'title' => 'Email address of individual completing the Designation Application'),
      '#title' => Markup::create('Email address of individual completing the Designation Application'),
      '#prefix' => Markup::create('<div class="col-md-6">'),
      '#suffix' => Markup::create('</div>'),
      '#required' => TRUE,
    );
    $form['declaration']['row1']['declaration__acknowledge_information_accurate'] = array(
      '#type' => 'checkbox',
      '#attributes' => array('id' => 'declaration_acknowledge_information_accurate', 'title' => 'I acknowledge that the information provided is accurate.'),
      '#title' => Markup::create('&nbsp; I acknowledge that the information provided is accurate.'),
      '#prefix' => Markup::create('<div class="col-md-12">'),
      '#suffix' => Markup::create('</div>'),
      '#required' => TRUE,
      '#default_value' => "Yes"
    );
    $form['declaration']['row1']['declaration__acknowledge_is_authorized'] = array(
      '#type' => 'checkbox',
      '#attributes' => array('id' => 'declaration_acknowledge_is_authorized', 'title' => 'I acknowledge that I am authorized to submit a Designation Application on behalf of this institution.'),
      '#title' => Markup::create('&nbsp; I acknowledge that I am authorized to submit a Designation Application on behalf of this institution.'),
      '#prefix' => Markup::create('<div class="col-md-12">'),
      '#suffix' => Markup::create('</div>'),
      '#required' => TRUE,
      '#default_value' => "Yes"
    );


    $form['hpot'] = array(
      '#type' => 'hidden',
      '#value' => '',
    );

    $sitekey1 = \Drupal::configFactory()->getEditable('sabc_private_data_form.settings')->get('captcha_sitekey_1');
    $form['captcha']['row1'] = array(
      '#type' => 'item',
      '#prefix' => Markup::create('<div class="row"><div class="g-recaptcha" data-sitekey="'.$sitekey1.'"></div>'),
      '#suffix' => Markup::create('</div>'),
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Submit',
      '#prefix' => Markup::create('<div class="form-actions"><a href="/" class="btn btn-large btn-link">Cancel</a>'),
      '#attributes' => array('class' => array('btn btn-large pull-right btn-success'), 'formnovalidate' => ''),
      '#suffix' => Markup::create('</div>')
    );

    return $form;
  }


  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $request_it = \Drupal::configFactory()->getEditable('sabc_private_data_form.settings')->get('request_it');
    $email_data = "";
    $section = "";
    $array = [];

    $form_input = $form_state->getUserInput();
    $response = new RedirectResponse('designation-form-submitted');
    $form_state->setResponse($response);

    foreach ($form_input as $key => $input) {

      if ($key != 'file_calendar' && $key != 'file_outline' && $key != 'file_additional_info') {
        $new_section = explode("__", $key);

        if (!empty($input) && strtoupper($new_section[0] != 'G-RECAPTCHA-RESPONSE') && strtoupper($new_section[0]) != "HPOT" && strtoupper($new_section[0]) != "OP" && strtoupper($new_section[0]) != "FORM_BUILD_ID" && strtoupper($new_section[0]) != "FORM_ID" && strtoupper($new_section[0]) != "FORM_TOKEN") {
          if ($new_section[0] != $section) {
            $section = $new_section[0];
            $email_data .= '<br/><strong>' . strtoupper($new_section[0]) . "</strong><br />";
          }

          $label = strtoupper(str_replace("_", " ", $new_section[1]));

          //if the input is a single input such as declaration__acknowledge_information_accurate
          if (!is_array($input)) {
            //for some reason Drupal's $label adds ! to the passed input. ie: PH! ONE
            if ($label == 'ACKNOWLEDGE INFORMATION ACCURATE' && $input == '1')
              $email_data .= $label . ': Yes<br/>';
            elseif ($label == 'ACKNOWLEDGE IS AUTHORIZED' && $input == '1')
              $email_data .= $label . ': Yes<br/>';
            else $email_data .= $label . ': ' . htmlentities($input) . '<br/>';

            if (stripos($label, 'END YEAR') !== false || stripos($label, 'END DATES YEAR') !== false) {
              $email_data .= '<br/>';
            }

            $array[$label] = $input;
          } //if the input is an array such as program_information__partner_designated
          else {
            foreach ($input as $val) {
              if (!empty($val) && !is_array($val)) {
                $email_data .= $label . ': ' . $val . '<br/>';
                $array[$label] = $val;
              }
            }
          }
        }
        //ignore anything that comes after ACKNOWLEDGE IS AUTHORIZED
        if ($label == 'ACKNOWLEDGE IS AUTHORIZED')
          break;
      }
    }


    $headers = array(
      "MIME-Version" => "1.0",
      "Content-Type" => "text/html; charset=ISO-8859-1",
      "From" => "RequestIT <" . $request_it . ">",
      "Content-Transfer-Encoding" => "base64",
    );

    foreach ($headers as $key => $value) {
      $params['headers'][$key] = $value;
    }


    $email_data = rtrim(chunk_split(base64_encode($email_data)));

    $params['body_admin'] = base64_decode($email_data);
    $params['subject'] = 'New Institution Designation Application';
    $language = \Drupal::languageManager()->getDefaultLanguage()->getId();
    $env = Settings::get('site_environment', 'PROD');

    if ($env == 'DEV') {
      $to = \Drupal::configFactory()->getEditable('sabc_private_data_form.settings')->get('dev_email');
    } else if ($env == 'TEST') {
      $to = \Drupal::configFactory()->getEditable('sabc_private_data_form.settings')->get('uat_email');
    } else {
      $to = \Drupal::configFactory()->getEditable('sabc_private_data_form.settings')->get('prod_email');
    }

    //$to = 'hemantsanvaria@gmail.com';
    $to_primary_contact = $form_input['institution_contacts__primary_contact_email'];

    $message_applicant ='
        <p>Institution Official,</p>

        <p>Thank you for your designation application, this email is to confirm it has been received by the Ministry of Post-Secondary Education and Future Skills.</p>
        <p>Please allow approximately 4 - 6 weeks for review and processing time. We will contact you with the outcome once our review is complete.</p>
        <p>Thank you,</p>

        <p>
        Institution and Program Designation Team<br>
        StudentAid BC<br>
        Ministry of Post-Secondary Education and Future Skills<br>
        E-Mail: designat@gov.bc.ca<br>
        </p>';

    if (empty($to)) {
      $messenger = \Drupal::messenger();
      $messenger->addMessage('Error with recipient email address. Please try again later or contact the administrator.', 'error');
      return;
    }
    /** @var \Drupal\symfony_mailer\EmailFactoryInterface $emailFactory */
    $emailFactory = \Drupal::service('email_factory');
    $email = $emailFactory->newTypedEmail('sabc_designation', 'designation')
      ->setTo($to)
      ->setSubject($params['subject'])
      ->setBody(['#markup' => Markup::create($params['body_admin'])]);

    if (isset($form_input['file_calendar']['fids'])) {
      $fid = $form_input['file_calendar']['fids'];
      $this->attach_files($fid, $email);
    }

    if (isset($form_input['file_outline']['fids'])) {
      $fid = $form_input['file_outline']['fids'];
      $this->attach_files($fid, $email);
    }

    if (isset($form_input['file_additional_info']['fids']) && !empty($form_input['file_additional_info']['fids'])) {
      $fid = $form_input['file_additional_info']['fids'];
      $this->attach_files($fid, $email);
    }

    try {
      $email->send();
      $messenger = \Drupal::messenger();
      $messenger->addMessage('Mail has been sent.', 'status');
    }
    catch (\Exception $e) {
      $messenger = \Drupal::messenger();
      $messenger->addMessage('An error occurred while submitting the form. Please try again later or contact the administrator.', 'error');

      // Log error
      \Drupal::logger('sabc_designation')->error('An error occurred while submitting the form to Designat. : @message', ['@message' => $e->getMessage()]);
    }


    //SEND COPY TO INSTITUTION
    /** @var \Drupal\symfony_mailer\EmailFactoryInterface $emailFactory */
    $emailFactory = \Drupal::service('email_factory');
    $email = $emailFactory->newTypedEmail('sabc_designation', 'sabc_designation_applicant')
      ->setTo($to_primary_contact)
      ->setSubject('Institution Designation Application')
      ->setBody(['#markup' => Markup::create($message_applicant)]);

    try {
      $email->send();
    }
    catch (\Exception $e) {
      // Log error
      \Drupal::logger('sabc_designation')->error('An error occurred while submitting the form to the institution. : @message', ['@message' => $e->getMessage()]);
    }
  }

  public function attach_files($fid, $email)
  {
    $file = File::load($fid);
    $email->attachFromPath(
      $file->getFileUri(),
      $file->getFilename() ?? null,
      $file->getMimeType() ?? null
    );

  }

  /**
   * Validate the title and the checkbox of the form
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   */
  public function validateForm(array &$form, FormStateInterface $form_state)
  {
    parent::validateForm($form, $form_state);

    \Drupal::logger('sabc_forms')->notice("Validation entered");

    // Call the function post_captcha
    $res = $this->post_captcha($_POST['g-recaptcha-response']);

    if (!$res['success']) {
      // What happens when the CAPTCHA wasn't checked
      $form_state
        ->setErrorByName('recaptch', $this->t('Please go back and make sure you check the security CAPTCHA box.'));
    }

    \Drupal::logger('sabc_forms')->notice("Validation finished");
  }

  public function post_captcha($user_response)
  {
    $fields_string = '';
    $sitekey2 = \Drupal::configFactory()->getEditable('sabc_private_data_form.settings')->get('captcha_sitekey_2');
    $fields = array(
      'secret' => $sitekey2,
      'response' => $user_response
    );
    foreach ($fields as $key => $value)
      $fields_string .= $key . '=' . $value . '&';
    $fields_string = rtrim($fields_string, '&');

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
    curl_setopt($ch, CURLOPT_POST, count($fields));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, True);

    $result = curl_exec($ch);
    curl_close($ch);

    return json_decode($result, true);
  }

}
