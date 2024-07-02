<?php

$path_aeit_library = \Drupal::configFactory()->getEditable('sabc_private_data_form.settings')->get('path_aeit_library');
require_once(DRUPAL_ROOT . $path_aeit_library . 'aeit.inc');

class userProfile extends aeit{

    function __construct($v = true){
        parent::__construct($v);
    }

    /*
    *		METHOD TO CREATE A USER ACCOUNT/PROFILE FOR SABC
    *		@params: (array) of your posted form data
    *		@return:
    */
    function fnCreateUserProfile($p){
        $this->uid = NULL;
        $this->valid = true;
        //FORM FIELD MAPPINGS TO OUR WS
        $mappings = array('first_name'      							=> 'firstName',
            'middle_name'									=> 'middleName',
            'last_name'       							=> 'familyName',
            'birthdate'       							=> 'dateOfBirth',
            'social_insurance_number'		 	=> 'SIN',
            'gender_select'      					=> 'gender',
            'email'      									=> 'emailAddress',
            'Street1'      								=> 'addressLine1',
            'Street2'      								=> 'addressLine2',
            'City'      										=> 'city',
            'ProvState'										=> 'province',
            'Country'      								=> 'country',
            'PostZip'											=> 'postalCode',
            'Phone'      									=> 'phoneNumber',
            'user_id'      								=> 'userID',
            'password'      								=> 'userPassword',
            'question1'     								=> 'question1Number',
            'answer1'      								=> 'answer1',
            'question2'     								=> 'question2Number',
            'answer2'      								=> 'answer2',
            'question3'     								=> 'question3Number',
            'answer3'      								=> 'answer3',
            'Consent'											=> 'userConsent',
            'assuranceLevel'	              => 'assuranceLevel',
            'userGUID'									    => 'userGUID'
        );

        //ERROR CODE MAPPINGS TO OUR FORM FIELDS
        $errorMappings = array('SPSIM11' => 'birthdate',
            'SPSIM15' => 'social_insurance_number',
            'SPSIM21' => 'social_insurance_number',
            'SPSIM22' => 'social_insurance_number',
            'SPSIM16' => 'gender_select',
            'SPSIM14' => 'email',
            'SPSIM25' => 'ProvState',
            'SPSIM24' => 'Country',
            'SPSIM20' => 'social_insurance_number',
            'SPSIM17' => 'user_id',
            'SPSIM26' => 'user_id',
            'SPSIM09' => 'password',
            'SPSIM19' => 'question1',
            'SPSIM20.1' => 'social_insurance_number',
            'SPSIM23' => 'Consent');

        //values to ignore for BCSC ID
        $bcscIgnore = array('userConsent', 'question1Number', 'question2Number', 'question3Number', 'answer1', 'answer2', 'answer3');

        //values to ignore for SABC ID
        $sabcIgnore = array('assuranceLevel', 'userGUID');

        //Changed by Hemant: Added following because it was throwing error undefined variable
        $bcscRequired = array('dateOfBirth','social_insurance_number','email','Street1','City','Country','Phone','assuranceLevel','userGUID');
        // if (isset($_SESSION['bcsc_profile'])) {
        //     $saml = openssl_encrypt(json_encode($_SESSION['bcsc_profile']), "AES-128-CFB", '&jl8938l!_90kdkd98kedjao', 0, 'UijHyt6$9K!$ERri');
        //     user_capture_saml($uid, $saml);
        // }

        //CALL THE APPROPRIATE WEB SERVICE
        if (isset($p['values']['userGUID'])){
            $this->WSDL = fnWS('WS-HOSTS', 'BCSC_USER_ACCOUNT');
        } else {
            $this->WSDL = fnWS('WS-HOSTS', 'USER_ACCOUNT');
        }

        //PRE-POPULATE POOL QUESTIONS INTO OUR ARRAY THAT WE WILL BE PASSING ALONG WITH OUR WS REQUEST
        if (!isset($p['values']['userGUID'])){
            $ws = array('question1Pool' => 1, 'question2Pool' => 2, 'question3Pool' => 3);
        }

        //LOOP THROUGH ALL THE POSTED FORM VALUES AND FIND THE WS MAPPINGS THAT CORRESPOND TO OUR FIELD
        foreach($p['values'] as $k => $v){
            //loop in here if BCSC
            if (isset($p['values']['userGUID'])){
                //create ws data and make sure to ignore any unnecessary values that are not required for BCSC
                //Changed by Hemant: Added bcscIgnore Variable so that doesn't loop through bcscIgnore
                if(isset($mappings[$k]) && !empty($v) && !in_array($k, $bcscIgnore)){

                    if($k == 'email'){
                        $v = $this->fnSanitizeData($v, 'email');
                    }
                    if($k == 'first_name' || $k == 'middle_name' || $k == 'last_name' || $k == 'Street1' || $k == 'Street2'){
                        $v = $this->fnSanitizeData($v);
                    }

                    if($k == 'Phone')
                        $v = str_replace(array('(', ')', '-', ' '), '', $v);

                    if($k != 'password')
                        $ws[$mappings[$k]] = strtoupper($v);
                    else
                        $ws[$mappings[$k]] = $v;

                    if ($k == 'userGUID')
                        $ws[$mappings[$k]] = $this->fnDecrypt($v);
                    if ($k == 'assuranceLevel')
                        $ws[$mappings[$k]] = $v;
                }

            }
            else //loop in here if SABC ID
            {
                //create ws data and make sure to ignore any unnecessary values that are not required for SABC ID
                if(isset($mappings[$k]) && !empty($v) && !in_array($k, $sabcIgnore)){

                    if($k == 'email'){
                        $v = $this->fnSanitizeData($v, 'email');
                    }
                    if($k == 'first_name' || $k == 'middle_name' || $k == 'last_name' || $k == 'Street1' || $k == 'Street2'){
                        $v = $this->fnSanitizeData($v);
                    }

                    if($k == 'Phone')
                        $v = str_replace(array('(', ')', '-', ' '), '', $v);
                    if($k != 'password')
                        $ws[$mappings[$k]] = strtoupper($v);
                    else
                        $ws[$mappings[$k]] = $v;
                }
            }
        }

        //CALL CREATEUSERPROFILE WEBSERVICE
        $createProfile = $this->fnRequest('createUserProfile', $ws);

        //MAKE SURE WE DON'T HAVE ANY ERRORS
        if(!isset($createProfile->faultcode)){
            if(isset($createProfile->userProfile)){

                //SET OUR UID
                $this->uid = $createProfile->userProfile->userGUID;

                if (!isset($p['values']['userGUID'])){
                    $p['values']['username'] = $p['values']['user_id'];
                    $p['values']['pswd'] = $p['values']['password'];
                }

                /*
                // ADD JIRA CUSTOMER ACCOUNT
                // use $this->uid as password
                $url = fnWS('JIRA-API', '')."/rest/api/2/user";

                $data = json_encode(array(
                    'name' => $this->fnEncrypt($createProfile->userProfile->userGUID),
                    'password' => $this->fnEncrypt($createProfile->userProfile->userGUID),
                    'emailAddress' => $p['values']['email'],
                    'displayName' => ($p['values']['first_name'] . ' ' . $p['values']['last_name']),
                    'notification' => false
                ));

                $headers = array(
                    'Accept: application/json',
                    'Content-Type: application/json',
                    'Authorization: Basic '.fnWS('JIRA-AUTH', '')
                );

                $req = $this->fnCurlRequest('POST', $url, $data, $headers);

                if($req != 'error'){
                    //remove user from default group - jira-users
                    $url = fnWS('JIRA-API', '')."/rest/api/2/group/user?groupname=jira-users&username=".$this->fnEncrypt($createProfile->userProfile->userGUID);

                    $this->fnCurlRequest('DELETE', $url, '', $headers);

                    //add user to jira group jira-sssb-servicedesk-customers
                    $data = json_encode(array(
                        'name' => $this->fnEncrypt($createProfile->userProfile->userGUID)
                    ));

                    $url = fnWS('JIRA-API', '')."/rest/api/2/group/user?groupname=jira-sssb-servicedesk-customers";

                    $this->fnCurlRequest('POST', $url, $data, $headers);
                }
                */

                //LOG USER INTO DASHBOARD
                $l = new login(false);
                $p['values'] = $l->fnValidateUser($p['values']);
                $p['values']['status'] = 'success';
                return $p['values'];
            }
        }
        else
        {

            //WE HAVE AN ERROR AND IT IS A VALID PROFILE ERROR
            if(isset($createProfile->detail->ProfileFault)){

                if($createProfile->detail->ProfileFault->faultCode == 'SPSIM09')
                    form_set_error($errorMappings[$createProfile->detail->ProfileFault->faultCode], $createProfile->getMessage(). '<br>Must include at least one character from 3 of the 4 categories below: <ul><li>- English lower case characters (a-z)</li><li>- English upper case characters (A-Z)</li><li>- Base 10 digits (0-9)</li><li>- Special characters/symbols</li></ul>');
                else
                    form_set_error($errorMappings[$createProfile->detail->ProfileFault->faultCode], $createProfile->getMessage());

                $p['values']['status'] = 'failure';

                return $p['values'];
            }
            else
            {
                $this->fnError('SYSTEM ERROR :: USER_ACCOUNT -> createUserProfile', $createProfile->getMessage(),
                    $createProfile, $triggerDefault = true);

                $p['values']['status'] = 'failure';
                return $p['values'];
            }
        }
    }



    /*
    *		USED TO GET USER PROFILE
    *		@params: $uid.  $uid only needs to be passed in if we are not authenticated
    *		@return (object) or void if system errors
    */
    function fnGetUserProfile($uid = NULL){
        global $user;

        if(!empty($uid))
            $this->uid = $uid;

        //CALL GET USER PROFILE SOAP WEB SERVICE
        if(in_array('bcsc_student', $user->roles) || in_array('bcsc_parent', $user->roles) || in_array('bcsc_spouse', $user->roles))
            $this->WSDL = fnWS('WS-HOSTS', 'BCSC_USER_ACCOUNT');
        else
            $this->WSDL = fnWS('WS-HOSTS', 'USER_ACCOUNT');

        //verify the user
        $this->WSDL  = fnWS('WS-HOSTS', 'USER_VERIFY');
        $verifyAccount = $this->fnRequest('getUserProfile', array('userGUID' => $this->uid), 'get_user_profile'.$this->uid, 14400);

        //OVERRIDE if we know already that the account is a BCSC account
        if(isset($verifyAccount->userProfile->assuranceLevel)){
            $this->WSDL = fnWS('WS-HOSTS', 'BCSC_USER_ACCOUNT');
        }else{
            $this->WSDL = fnWS('WS-HOSTS', 'USER_ACCOUNT');
        }

        $usrProfile = $this->fnRequest('getUserProfile', array('userGUID' => $this->uid), 'get_user_profile'.$this->uid, 14400);


        //MAKE SURE IT IS NOT AN ERROR
        if(!isset($usrProfile->faultcode)){
            $usrProfile->status = TRUE;
            $usrProfile->userProfile->SIN = $this->fnEncrypt($usrProfile->userProfile->SIN);
            $usrProfile->userProfile->userGUID = $this->fnEncrypt($usrProfile->userProfile->userGUID);
            $usrProfile->userProfile->userConsent = (!isset($usrProfile->userProfile->userConsent) || $usrProfile->userProfile->userConsent == 'N') ? FALSE : TRUE;

            return $usrProfile;
        }
        else
        {
            //WE HAVE AN ERROR AND IT IS A VALID PROFILE ERROR
            if(isset($usrProfile->detail->ProfileFault)){
                $errors = array();
                $errors['status'] = FALSE;
                $errors['msg'] = $usrProfile->getMessage();
                $errors['username'] = $usrProfile->getMessage();

                return $errors;
            }
            //NOT A VALID PROFILE ERROR SO TRIGGER SYSTEM ERROR
            else
            {
                $this->fnError('SYSTEM ERROR :: USER_ACCOUNT ->getUserProfile', $usrProfile->getMessage(), $usrProfile, $triggerDefault = true);
            }
        }
    }


    function fnUpdateUserProfile($p){

        $this->valid = true;
        $mappings = array( 'gender'      									=> 'gender',
            'email'      									=> 'emailAddress',
            'Street1'      								=> 'addressLine1',
            'Street2'      								=> 'addressLine2',
            'City'      										=> 'city',
            'ProvState'										=> 'province',
            'Country'      								=> 'country',
            'PostZip'											=> 'postalCode',
            'Phone'      									=> 'phoneNumber',
            'question1'     								=> 'question1Number',
            'answer1'      								=> 'answer1',
            'question2'     								=> 'question2Number',
            'answer2'      								=> 'answer2',
            'question3'     								=> 'question3Number',
            'answer3'      								=> 'answer3',
            'user_id'      								=> 'userID',
            'new_password'									=> 'userPassword',
            'Consent'											=> 'userConsent',
            'assuranceLevel'								=> 'assuranceLevel',
            'first_name'										=> 'firstName',
            'last_name'										=> 'familyName',
            'middle_name'									=> 'middleName',
            'dateOfBirth'									=> 'dateOfBirth');


        //ERROR CODE MAPPINGS TO OUR FORM FIELDS
        $errorMappings = array('SPSIM02' => 'user_id',
            'SPSIM03' => 'user_id',
            'SPSIM06' => 'hpot',
            'SPSIM07' => 'user_id',
            'SPSIM08' => 'new_password',
            'SPSIM09' => 'new_password',
            'SPSIM14' => 'Email',
            'SPSIM18' => 'hpot',
            'SPSIM19' => 'PostZip',
            'SPSIM20' => 'PostZip',
            'SPSIM22' => 'Consent',
            'SPSIM23' => 'Country',
            'SPSIM24' => 'ProvState');

        //values to ignore for BCSC ID
        $bcscIgnore = array('Consent', 'question1', 'question2', 'question3', 'answer1', 'answer2', 'answer3');

        //values to ignore for SABC ID
        $sabcIgnore = array('assuranceLevel', 'userGUID', 'first_name', 'last_name', 'assuranceLevel');

        //PRE-POPULATE POOL QUESTIONS INTO OUR ARRAY THAT WE WILL BE PASSING ALONG WITH OUR WS REQUEST for sabc accounts
        if(!isset($p['values']['assuranceLevel'])) {
            $ws = array('question1Pool' => 1, 'question2Pool' => 2, 'question3Pool' => 3, 'userGUID' => $this->uid);
        }
        else {
            if(isset($p['autoUpdate']) && $p['autoUpdate'] == true)
                $ws = array('userGUID' => $this->fnDecrypt($this->uid));
            else
                $ws = array('userGUID' => $this->uid);
        }

        foreach($p['values'] as $k => $v){
            if($k == 'email'){
                $v = $this->fnSanitizeData($v, 'email');
            }
            if($k == 'middle_name' || $k == 'Street1' || $k == 'Street2'){
                $v = $this->fnSanitizeData($v);
            }

            if($k == 'Phone')
                $v = str_replace(array('(', ')', '-', ' '), '', $v);

            if($k == 'gender'){
                if($v == 'FEMALE')
                    $v = 'F';
                if($v == 'MALE')
                    $v = 'M';
            }

            if(isset($mappings[$k]) && !empty($v)) {
                if ($k == 'new_password') {
                    $ws['password'] = $p['values']['new_password'];
                    $ws['confirmationPassword'] = $p['values']['new_password'];
                } else {
                    if(isset($p['values']['assuranceLevel']) && !in_array($k, $bcscIgnore)) {
                        $ws[$mappings[$k]] = strtoupper($v);
                    }

                    elseif(!in_array($k, $sabcIgnore)) {
                        if($k == 'first_name' || $k == 'last_name'){
                            $v = $this->fnSanitizeData($v);
                        }
                        $ws[$mappings[$k]] = strtoupper($v);
                    }

                }
            }

        }

        if (empty($p['values']['assuranceLevel'])) {
            $this->WSDL = fnWS('WS-HOSTS', 'USER_ACCOUNT');
        } else {
            $this->WSDL = fnWS('WS-HOSTS', 'BCSC_USER_ACCOUNT');
        }

        $updateProfile = $this->fnRequest('updateUserProfile', $ws, 'update_user_profile'.$this->fnDecrypt($this->uid), 0);

        if(isset($updateProfile->faultcode)){
            //WE HAVE AN ERROR AND IT IS A VALID PROFILE ERROR
            if(isset($updateProfile->detail->ProfileFault)){
                form_set_error($errorMappings[$updateProfile->detail->ProfileFault->faultCode], $updateProfile->getMessage());
            }
            else
            {
                $this->fnError('SYSTEM ERROR :: USER_ACCOUNT -> updateUserProfile', $updateProfile->getMessage(), $updateProfile, $triggerDefault = true);
            }

            return FALSE;
        }
        else
        {
            //if all is good remove our check to auto update profile
            if(isset($_SESSION['bcsc_profile'])){
                unset($_SESSION['bcsc_profile']);
            }

            //CLEAR OLD CACHE
            $this->fnUpdateCache('get_user_profile'.$this->uid, 'cache_block', true);

            //Update profile was updated return TRUE
            return TRUE;
        }

    }


    /*
        fnVerifyUser
        Method to perform a verification check.  The method calls an e-service to obtain the following:

            $userProfile = new userProfile();
            $isUserVerified = $userProfile->fnVerify();

            @params: $uid
            @return: (boolean)
    */
    function fnVerifyUser($uid = NULL){
        global $user;

        $isUserVerified = false;
        //error_log('1 isUserVerified: ' . $isUserVerified, 0);

        if(!empty($uid)){
            $this->uid = $uid;
        }
        //error_log('Verify User UID: ' . $this->uid, 0);

        // CALL GET USER VERIFY SOAP WEB SERVICE if user is a student or bcsc_student
        if ( in_array( 'student', $user->roles ) || in_array( 'bcsc_student', $user->roles ) ) {

            // Build and make web service call
            $this->WSDL  = fnWS('WS-HOSTS', 'USER_VERIFY');
            $action 	 = 'getUserProfile';
            $params 	 = array('userGUID' => $this->uid);
            //$cid 		 = 'verify_user'.$this->uid;
            $cid 		 = null;
            $cacheExpire = 0;
            $resposnse   = $this->fnRequest($action, $params, $cid, $cacheExpire);

            // Retreive verified field from web service response
            // Set TRUE or FALSE return value
            //$resposnse->userProfile->verified = 'srwe';
            if(!isset($resposnse->faultcode)){
                if ( !empty( $resposnse->userProfile->verified ) ) {
                    $verified = $resposnse->userProfile->verified;
                    //$isUserVerified = $verified == "Y" ? true : false;
                    if($verified === "Y"){
                        $isUserVerified = true;
                    }

                } else {
                    $this->fnError('SYSTEM ERROR :: USER_VERIFY ->getUserProfile', $resposnse->userProfile->verified, $resposnse, $triggerDefault = true);
                    //error_log('10 isUserVerified: ' . $isUserVerified, 0);
                }
                //error_log('E-Service verified: ' . $resposnse->userProfile->verified, 0);
                //error_log('PHP isUserVerified: ' . $isUserVerified, 0);

            }else{
                $this->fnError('SYSTEM ERROR :: USER_VERIFY FAULT CODE.', $resposnse->faultcode);
            }

        }

        //error_log('PHP fnVerifyUser isUserVerified: ' . $isUserVerified, 0);
        return $isUserVerified;

    }

    /*
        fnVerificationMethod
          Convenience method to add session data
          @params: (string) $key
          @params: (string) $value
     */
    function fnVerificationMethod($uid = NULL){
        global $user;

        if(!empty($uid)){
            $this->uid = $uid;
        }

        $verificationMethod = '';

        // CALL GET USER VERIFY SOAP WEB SERVICE if user is a student or bcsc_student
        if ( in_array( 'student', $user->roles ) || in_array( 'bcsc_student', $user->roles ) ) {

            // Build and make web service call
            $this->WSDL  = fnWS('WS-HOSTS', 'USER_VERIFY');
            $action 	 = 'getUserProfile';
            $params 	 = array('userGUID' => $this->uid);
            $cid 		 = 'verify_user'.$this->uid;
            $cacheExpire = 0;
            $resposnse   = $this->fnRequest($action, $params, $cid, $cacheExpire);

            // Retreive dataOfBirth fields from web service response
            $dateOfBirth = $resposnse->userProfile->dateOfBirth;

            // Calculate User Age
            $bday 	= new DateTime( $dateOfBirth );
            $today 	= new DateTime();
            $diff 	= $today->diff( $bday );
            $age 	= $diff->y;

            // Set Verification Method
            //$verificationMethod = ( $age < 19 ) ? 'attestation' : 'bcsc';
            $verificationMethod = 'bcsc';


        }

        return $verificationMethod;

    }


    /*
        method to validate profile information is all good and used to format profile information for us to pass along to our e-service
        @params: (array) posted form data $form_state['values']
        @return: (array) $p or void if errors
    */
    function fnValidateProfile(&$p){
        //SET TO TRUE FOR NOW BUT IF WE FAIL ON ANY VALIDATION IT WILL BE SET TO FALSE
        $status = true;

        //CREATE PROFILE REQUIRED FIELDS
        $createProfRF = array('first_name'      							=> array('Req' => 't', 'Field' => 'First Name'),
            'last_name'       							=> array('Req' => 't', 'Field' => 'Last Name'),
            'birthdate'       							=> array('Req' => 't', 'Field' => 'Birthdate'),
            'social_insurance_number'		 	=> array('Req' => 't', 'Field' => 'Social Insurance Number'),
            'gender_select'      					=> array('Req' => 't', 'Field' => 'Gender'),
            'email'      									=> array('Req' => 't', 'Field' => 'E-mail Address'),
            'Street1'      								=> array('Req' => 't', 'Field' => 'Address Line 1'),
            'City'      										=> array('Req' => 't', 'Field' => 'City'),
            'Country'      								=> array('Req' => 't', 'Field' => 'Country'),
            'Phone'      									=> array('Req' => 't', 'Field' => 'Phone Number'),
            'user_id'      								=> array('Req' => 't', 'Field' => 'Desired User ID'),
            'password'      								=> array('Req' => 't', 'Field' => 'Password'),
            'question1'     								=> array('Req' => 't', 'Field' => 'Challenge Question 1'),
            'answer1'      								=> array('Req' => 't', 'Field' => 'Answer for question #1'),
            'question2'     								=> array('Req' => 't', 'Field' => 'Challenge Question 2'),
            'answer2'      								=> array('Req' => 't', 'Field' => 'Answer for question #2'),
            'question3'     								=> array('Req' => 't', 'Field' => 'Challenge Question 3'),
            'answer3'      								=> array('Req' => 't', 'Field' => 'Answer for question #3'),
            'Consent'											=> array('Req' => 't', 'Field' => 'Subscribe'),
            'DID'													=> array('Req' => 'f', 'Field' => 'userGUID'),
            'assuranceLevel'								=> array('Req' => 'f', 'Field' => 'assuranceLevel'),
        );

        //UPDATE PROFILE REQUIRED FIELDS
        $updateProfRF = array('gender'      									=> array('Req' => 't', 'Field' => 'Gender'),
            'email'      									=> array('Req' => 't', 'Field' => 'E-mail Address'),
            'Street1'      								=> array('Req' => 't', 'Field' => 'Address Line 1'),
            'City'      										=> array('Req' => 't', 'Field' => 'City'),
            'Country'      								=> array('Req' => 't', 'Field' => 'Country'),
            'Phone'      									=> array('Req' => 't', 'Field' => 'Phone Number'),
            'question1'     								=> array('Req' => 't', 'Field' => 'Challenge Question 1'),
            'answer1'      								=> array('Req' => 't', 'Field' => 'Answer for question #1'),
            'question2'     								=> array('Req' => 't', 'Field' => 'Challenge Question 2'),
            'answer2'      								=> array('Req' => 't', 'Field' => 'Answer for question #2'),
            'question3'     								=> array('Req' => 't', 'Field' => 'Challenge Question 3'),
            'answer3'      								=> array('Req' => 't', 'Field' => 'Answer for question #3'),
            'Prov'      										=> array('Req' => 'f', 'Field' => 'Province'),
            'State'      									=> array('Req' => 'f', 'Field' => 'State'),
            'PostZip'      								=> array('Req' => 'f', 'Field' => 'Postal / ZIP Code'),
            'Phone'      									=> array('Req' => 't', 'Field' => 'Phone Number'),
            'Consent'											=> array('Req' => 't', 'Field' => 'Subscribe'));

        $bcscRequired = array('dateOfBirth','social_insurance_number','email','Street1','City','Country','Phone','assuranceLevel','userGUID');

        foreach($p as $k => $v) {
            $p[$k] = trim($v);
        }

        if (!isset($p['userGUID'])){
            if(isset($p['uid']))
                $this->uid = $p['uid'];
        }

        //MAKE SURE HPOT IS EMPTY - IF NOT EMPTY IT MEANS ROBOT'S SUBMITTING FORM
        if(empty($p['hpot'])){

            //TRUNCATE NAMES IF FNAME IS LONGER THAN 15 AND LNAME IS LONGER THAN 25 CHARACTERS IF BC SERVICES CARD
            if(isset($p['assuranceLevel'])){
                if(isset($p['first_name']) && strlen($p['first_name']) >= 16){
                    $p['first_name'] = substr($p['first_name'], 0, 15);
                }

                if(isset($p['last_name']) && strlen($p['last_name']) > 25){
                    $p['last_name'] = substr($p['last_name'], 0, 25);
                }
            }


            //MAKE SURE BIRTHDAY FIELDS HAS BEEN PASSED TO US BEFORE VALIDATING.  IF NEW ACCOUNT THEN IT SHOULD ALWAYS BE THERE BUT ON EDIT IT WON'T.
            if(isset($p['month'], $p['day'], $p['year'])){

                //MAKE SURE YEAR IS 4 DIGITS LONG
                if(strlen($p['year']) == 4){
                    $birthdate = $p['year'].'-'.$p['month'].'-'.$p['day'];

                    //MAKE SURE DATE IS VALID
                    if(checkdate((int)$p['month'], (int)$p['day'], (int)$p['year'])){

                        //MAKE SURE BIRTHDATE IS NOT GREATER THAN OR EQUAL TO TODAY
                        if(strtotime($birthdate) >= strtotime(date('Y-m-d'))){
                            $status = false;
                            form_set_error('month', 'Birth date is invalid');
                        }
                        else if ($p['year'] < 1900) {
                            $status = false;
                            form_set_error('year', 'Please ensure birth year is after 1900. If you have any questions, please contact us.');
                        }
                        else
                            $p['birthdate'] = $p['year'].''.sprintf("%02s", $p['month']).''.sprintf("%02s", $p['day']); //DATE IS GOOD SO FORMAT TO PROPER FORMAT FOR E-SERVICE YYYYMMDD
                    }
                    else
                    {
                        $status = false;
                        form_set_error('month', 'Invalid date for birthday.'); //DATE IS INVALID
                    }
                }
                else
                {
                    $status = false;
                    form_set_error('year', 'Birth date year is invalid.  Please ensure year is formatted YYYY');
                }
            }

            if (!isset($p['userGUID'])){
                if(!empty($this->uid)){
                    //CHECK TO MAKE SURE PASSWORD MEETS MIN REQUIRE LENGTH
                    if(isset($p['new_password']) && strlen($p['new_password']) < 8 && strlen($p['new_password']) > 1){
                        $status = false;
                        form_set_error('new_password', 'Password must be at least 8 characters and must include one or more characters from at least 3 of the 4 categories below:<br><ul class="disc" style="color:#bd362f;"><li>English lower case (a-z)</li><li>English upper case characters (A-Z)</li><li>Base 10 digits (0-9)</li><li>Special characters/symbols (~!@#$%^&*_-+=`|\(){}[]:;"\'<>,.?/)</li>');
                    }
                    else if(isset($p['new_password']) && strlen($p['new_password']) > 20){
                        $status = false;
                        form_set_error('new_password', 'The length of your password is too long.  Password length can only be a maximum of 20 characters long.');
                    }

                }
            }
            //MAKE SURE SIN HAS BEEN PASSED TO US BEFORE VALIDATING.  IF NEW ACCOUNT THEN IT SHOULD ALWAYS BE THERE BUT ON EDIT IT WON'T.
            if(isset($p['social_insurance_number'])){
                //VALIDATE SIN
                $sin = str_replace(array('-', ' '), '', $p['social_insurance_number']); //FORMAT SIN TAKE OUT "-" AND SPACES

                //MAKE SURE SIN IS 9 DIGITS LONG
                if(strlen($sin) == 9){

                    //ENSURE SIN IS A NUMBER
                    if(!filter_var($sin, FILTER_VALIDATE_INT)){
                        $status = false;
                        form_set_error('social_insurance_number', 'Invalid SIN.  SIN can only contain numbers.');
                    }
                    else
                        $p['social_insurance_number'] = $sin; //SIN IS GOOD SO FORMAT TO PROPER FORMAT FOR E-SERVICE
                }
                else
                {
                    $status = false;
                    form_set_error('social_insurance_number', 'Invalid SIN.  SIN must contain 9 numbers and can be separated by spaces and dashes.');
                }
            }

            if (!isset($p['userGUID'])){
                //FORMAT CONSENT TO PROPER VALUE - IF WE DIDN'T GET CONSENT PASSED BACK TO US OR CONSENT WAS EMPTY SET TO "N" OTHERWISE SET TO "Y"
                $p['Consent'] = (!isset($p['Consent']) || empty($p['Consent'])) ?  'N' : 'Y';
                $p['userConsent'] = $p['Consent'];
            }
            //VALIDATE EMAIL
            $p['email'] = filter_var($p['email'], FILTER_SANITIZE_EMAIL);
            $p['email'] = trim($p['email']);
            if(!filter_var($p['email'], FILTER_VALIDATE_EMAIL)){
                $status = false;
                form_set_error('email', 'E-mail address is invalid');
            }

            //CONFIRM EMAIL MATCHES CONFIRMED EMAIL INPUT
            if(isset($p['confirm_email'], $p['email']) && $p['email'] != $p['confirm_email']){
                $status = false;
                form_set_error('confirm_email', 'Confirmed E-mail does not match.');
            }

            //VALIDATE PHONE
            $p['Phone'] = str_replace(array('(', ')', '+', '-', ' '), '', $p['Phone']);
            if(!filter_var($p['Phone'], FILTER_VALIDATE_FLOAT)){
                $status = false;
                form_set_error('Phone', 'Phone number is invalid');
            }
            else if(strlen($p['Phone']) != 10){
                $status = false;
                form_set_error('Phone', 'Phone number length is invalid');
            }

            //VALIDATE PROV/STATE IF CANADA OR USA
            if(isset($p['Country'])){

                //STRIP OUT ANY SPACES
                $p['PostZip'] = str_replace(' ', '', $p['PostZip']);

                if(isset($p['Country']) && $p['Country'] == 'CAN'){
                    //REMOVE SINGLE QUOTES AROUND RETURN
                    $p['ProvState'] = str_replace("'", '', $p['Prov']);
                }

                if(isset($p['Country']) && $p['Country'] == 'USA'){
                    $p['ProvState'] = $p['State'];
                }


                //IF CANADA
                if($p['Country'] == 'CAN'){
                    $provCodes = array('AB','BC','MB','NB','NL','NT','NS','NU','ON','PE','QC','SK','YT');
                    if(!in_array($p['ProvState'], $provCodes)){
                        $status = false;
                        form_set_error('Prov', 'Invalid Province code for Canada.');
                    }

                    if(strlen($p['PostZip']) != 6){
                        $status = false;
                        form_set_error('PostZip', 'Invalid Postal Code');
                    }
                    if(isset($p['PostZip']) && empty($p['PostZip'])){
                        $status = false;
                        form_set_error('PostZip', 'Postal Code is required');
                    }
                }
                //IF USA
                else if($p['Country'] == 'USA'){
                    $stateCodes = array('AL','AK','AS','AZ','AR','AA','AP','CA','CO','CT','DE','DC','FL','GA','HI','ID','IL','IN','IA','KS','KY','LA','ME','MD',
                        'MA','MI','MN','MS','MO','MT','NE','NV','NH','NJ','NM','NY','NC','ND','OH','OR','PA','RI','SC','SD','TN','TX','UT','VT',
                        'VA','WA','WV','WI','WY');

                    if(!in_array($p['ProvState'], $stateCodes)){
                        $status = false;
                        form_set_error('State', 'Invalid state code for United States of America.');
                    }

                    if(strlen($p['PostZip']) == 5){
                        if(isset($p['PostZip']) && empty($p['PostZip'])){
                            $status = false;
                            form_set_error('PostZip', 'Zip Code is required');
                        }
                        if (!preg_match('/^\d{5}$/', $p['PostZip'])) {
                            $status = false;
                            form_set_error('PostZip', 'Invalid Zip Code');
                        }
                    }
                    else
                    {
                        $status = false;
                        form_set_error('PostZip', 'Invalid Zip Code');
                    }
                }
            }

            if (!isset($p['userGUID'])){
                if(isset($p['user_id']) && !empty($p['user_id'])){
                    if(strlen($p['user_id']) < 8){
                        $status = false;
                        form_set_error('user_id', 'User ID must be at least 8 characters long.');
                    }
                }
            }

            if(isset($p['dateOfBirth'])){
                $p['dateOfBirth'] = date('Ymd', strtotime($p['dateOfBirth']));
            }
            //IF UID IS EMPTY THEN IT MEANS WE AREN'T LOGGED IN AND WE ARE CREATING AN ACCOUNT
            if(empty($this->uid)){

                if (isset($p['password']) && strlen($p['password']) > 20){
                    $status = false;
                    form_set_error('password','The length of your password is too long.  Password length can only be a maximum of 20 characters long.');
                }
                if (isset($p['password']) && strlen($p['password']) < 8){
                    $status = false;
                    form_set_error('password', 'Password must be at least 8 characters and must include one or more characters from at least 3 of the 4 categories below:<br><ul class="disc" style="color:#bd362f;"><li>English lower case (a-z)</li><li>English upper case characters (A-Z)</li><li>Base 10 digits (0-9)</li><li>Special characters/symbols (~!@#$%^&*_-+=`|\(){}[]:;"\'<>,.?/)</li>');
                }


                //MAKE SURE WE HAVE NO ERRORS
                if(!empty($status)){
                    //MAKE SURE WE HAVE ALL REQUIRED FIELDS FOR CREATE ACCOUNT
                    foreach($createProfRF as $k => $v){
                        //IF FIELD WAS NOT PASSED TO US OR FIELD IS EMPTY TRIGGER ERROR
                        if(!isset($p[$k]) && $v['Req'] == 't' || empty($p[$k]) && $v['Req'] == 't'){
                            if (isset($p['userGUID'])){

                                if($v['BCSC'] == 't'){
                                    form_set_error($k, $v['Field'].' is required.');
                                } else {
                                    unset($createProfRF[$k]);
                                }

                            } else {
                                form_set_error($k, $v['Field'].' is required.');
                            }
                        }
                        else
                            unset($createProfRF[$k]);
                    }
                    if(count($createProfRF) == 0){
                        return $p;
                    }
                }
            }
            //WE ARE LOGGED IN WE HAVE A UID SO IT MEANS WE ARE UPDATING PROFILE
            else
            {
                //MAKE SURE WE HAVE NO ERRORS


                if(!empty($status)){
                    //MAKE SURE WE HAVE ALL REQUIRED FIELDS FOR UPDATE ACCOUNT
                    foreach($updateProfRF as $k => $v){
                        //IF FIELD WAS NOT PASSED TO US OR FIELD IS EMPTY TRIGGER ERROR
                        if(!isset($p[$k]) && $v['Req'] == 't' || empty($p[$k]) && $v['Req'] == 't'){
                            if(isset($p['assuranceLevel'])){
                                if(in_array($k, $bcscRequired)){
                                    form_set_error($k, $v['Field'].' is required.');
                                } else {
                                    unset($updateProfRF[$k]);
                                }
                            }
                            else
                                form_set_error($k, $v['Field'].' field is required.');
                        }
                        else
                            unset($updateProfRF[$k]);
                    }

                    if(count($updateProfRF) == 0){
                        return $p;
                    }
                }


            }
        }
        else
        {
            return false;
        }
    }



    function fnGetProfileRoleData() {

        $app = new application();
        $applications = $app->fnGetApplications();
        $appendices = $app->fnGetAppendixList();

        $result = array();
        $result['parent'] = false;
        $result['spouse'] = false;
        $result['newApplicant'] = false;
        $result['applications'] = array();
        $result['appendices'] = array();

        $index = 0;
        foreach($applications as $key => $type) {
            if ($key == "NotSubmitted" || $key == "Submitted") {
                foreach($applications[$key] as $application) {
                    $details = $app->fnGetApplicationDetails($application[0]['ApplicationNumber']);
                    $applicationEvents = $details->applicationDetails->applicationTimeline->EventCategories->eventCategory;
                    foreach($applicationEvents as $applicationEvent) {
                        // find where in the timeline the applicant is in or what the application is waiting for
                        if (isset($applicationEvent->eventCategoryCode) &&
                            ($applicationEvent->eventCategoryCode == "Waiting" ||
                                $applicationEvent->eventCategoryCode == "Missing Info" ||
                                $applicationEvent->eventCategoryCode == "Missing Information")) {

                            $result['applications'][$index] = new stdClass();
                            $result['applications'][$index]->category = $applicationEvent->eventCategoryName;
                            $outstandingDocs = array();
                            // find out what we're waiting for
                            if (!empty($applicationEvent->eventItems)) {
                                if (is_array($applicationEvent->eventItems->eventItem)) {

                                    foreach($applicationEvent->eventItems->eventItem as $event) {
                                        if ($event->eventCode == "Waiting" &&
                                            ($event->eventType == "Appendix 1" ||
                                                $event->eventType == "Appendix 2" ||
                                                $event->eventType == "Appendix 3")) {
                                            $outstandingDocs[] .= $event->eventType;
                                        }
                                    }
                                }
                                else {
                                    if ($applicationEvent->eventItems->eventItem->eventCode == "Waiting" /*&&
													 ($event->eventType == "Appendix 1" ||
														$event->eventType == "Appendix 2" ||
														$event->eventType == "Appendix 3")*/) {
                                        $outstandingDocs[] .= $applicationEvent->eventItems->eventItem->eventType;
                                    }
                                }
                            }
                            if (!empty($outstandingDocs)) {
                                $result['applications'][$index]->outstanding = $outstandingDocs;
                            }
                            $index++;
                        }
                    }
                }
            }
        }

        if ($applications['totalApps'] == 0) {
            // this account has never had an application
            $result['newApplicant'] = true;
        }
        else {
            // this account has applications, but could still be considered new if their last application was over 2 years
            // find the latest submitted application.

            $latestDate = 0;
            // find if the application with the latest end date
            // checking both submitted and not submitted because the user could have a not submitted application sitting in their account
            if (!empty($applications['Submitted'])) {
                $latestSubmitted = array_shift($applications['Submitted']);
                $studyEndDate = strtotime($latestSubmitted[0]['StudyEndDate']);
                $latestEndDate = $studyEndDate;
            }
            if (!empty($applications['NotSubmitted'])) {
                $latestNotSubmitted = array_shift($applications['NotSubmitted']);
                $studyEndDate = strtotime($latestNotSubmitted[0]['StudyEndDate']);
                if ($studyEndDate > $latestEndDate) {
                    $latestEndDate = $studyEndDate;
                }
            }

            $today = strtotime("today");
            $years = strtotime('+2 years', $latestEndDate);
            // consider the account as a new user if it has been at least two years since their last application
            if ($today > $years ) {
                $result['newApplicant'] = true;
            }
        }

        $result['parent'] = ($appendices['Appendix1']['total'] > 0) ? true : false;
        $result['spouse'] = ($appendices['Appendix2']['total'] > 0) ? true : false;

        $outstandingDocs = array();

        foreach ($appendices as $appendixDetails) {

            if (isset($appendixDetails['NotSubmitted']) && is_array($appendixDetails['NotSubmitted'])) {

                foreach ($appendixDetails['NotSubmitted'] as  $applicationAppendices) {

                    foreach ($applicationAppendices as $appendix) {
                        $applID = $appendix['ApplicationNumber'];

                        $appendixDetails = $app->fnGetAppendixDetails($appendix['FormGUID'], $applID);

                        if ($appendixDetails->appendixTimeline->appendixTimelineCode == 'Waiting') {
                            $outstandingDocs[$applID] = array();
                            $outstandingDocs[$applID][] .= $appendixDetails->formType;
                        }
                    }
                }
            }
        }

        $result['appendices'] = new stdClass();
        $result['appendices']->outstanding = $outstandingDocs;

        return $result;
    }
}
?>
