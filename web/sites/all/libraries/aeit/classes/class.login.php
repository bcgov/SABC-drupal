<?php

$path_aeit_library = \Drupal::configFactory()->getEditable('sabc_private_data_form.settings')->get('path_aeit_library');
require_once(DRUPAL_ROOT . $path_aeit_library . 'classes/class.userProfile.php');

class login extends userProfile
{

    function __construct($v = TRUE)
    {
        parent::__construct($v);
    }

    /*
    *		USED TO VALIDATE USER/ RETRIEVE USERS GUID FROM THE IDENTITY MANAGEMENT STORE
    *		@params: $u (username) $p (password)
    *		@return: $user which is an array that contains users GUID and full name
    */

    function fnSystemLogin($values)
    {

        define('Drupal_visitor_environment', $values['environment']);
        $this->uid = $values['user_guid'];
        $this->WSDL = fnWS('WS-HOSTS', 'USER_ACCOUNT');
        $usrProfile = $this->fnRequest('getUserProfile', array('userGUID' => $values['user_guid']), 'get_user_profile' . $values['user_guid'], 14400);
        if (!isset($usrProfile->faultcode)) {
            $usrProfile->status = TRUE;
            $usrProfile->userProfile->SIN = $this->fnEncrypt($usrProfile->userProfile->SIN);
            $usrProfile->userProfile->userGUID = $this->fnEncrypt($usrProfile->userProfile->userGUID);
            $usrProfile->userProfile->userConsent = (!isset($usrProfile->userProfile->userConsent) || $usrProfile->userProfile->userConsent == 'N') ? FALSE : TRUE;

            $up = $usrProfile;
        } else {

            $this->WSDL = fnWS('WS-HOSTS', 'BCSC_USER_ACCOUNT');
            $usrProfile = $this->fnRequest('getUserProfile', array('userGUID' => $values['user_guid']), 'get_user_profile' . $values['user_guid'], 14400);
            if (!isset($usrProfile->faultcode)) {
                $usrProfile->status = TRUE;
                $usrProfile->userProfile->SIN = $this->fnEncrypt($usrProfile->userProfile->SIN);
                $usrProfile->userProfile->userGUID = $this->fnEncrypt($usrProfile->userProfile->userGUID);
                $usrProfile->userProfile->userConsent = (!isset($usrProfile->userProfile->userConsent) || $usrProfile->userProfile->userConsent == 'N') ? FALSE : TRUE;

                $up = $usrProfile;
            } else {
                //WE HAVE AN ERROR AND IT IS A VALID PROFILE ERROR
                if (isset($usrProfile->detail->ProfileFault)) {
                    $errors = array();
                    $errors['status'] = FALSE;
                    $errors['msg'] = $usrProfile->getMessage();
                    $errors['username'] = $usrProfile->getMessage();

                    $up = $errors;
                } //NOT A VALID PROFILE ERROR SO TRIGGER SYSTEM ERROR
                else {
                    $this->fnError('SYSTEM ERROR :: USER_ACCOUNT ->getUserProfile', $usrProfile->getMessage(), $usrProfile, $triggerDefault = true);
                }
            }

        }

        if (!empty($up->status)) {
            user_cookie_save(array('environment' => $values['environment']));
            $this->uid = $values['user_guid'];
            $user['status'] = TRUE;
            $user['uid'] = $this->fnEncrypt($values['user_guid']);
            $user['login_name'] = $up->userProfile->firstName . ' ' . $up->userProfile->familyName;
            $_SESSION['admin_login'] = $values['user_guid'];
            switch($values['environment']){
                case 'STUDENTAIDBC': $_SESSION['admin_env'] = 'studentaidbc.ca'; break;
                case 'UAT': $_SESSION['admin_env'] = 'uat.studentaidbc.ca'; break;
                case 'DEV': $_SESSION['admin_env'] = 'dev.studentaidbc.ca'; break;
            }

            $this->fnClearUserCache();

            return $user;
        }
    }

    function fnValidateUser($form_state)
    {

        if (isset($form_state['username'], $form_state['pswd']) &&
            !empty($form_state['username']) &&
            !empty($form_state['pswd']) &&
            empty($form_state['hpot'])) {

            $login = $this->fnLogin($form_state['username'], $form_state['pswd']);

            if (!empty($login)) {
                if ($login['status'] == TRUE) {
                    $form_state['uid'] = $login['uid'];
                    $form_state['login_name'] = $login['name'];
                    $form_state['status'] = true;
                    return $form_state;
                } else {
                    foreach ($login as $errorCode => $error) {
                        form_set_error($errorCode, $error);
                    }

                    return $form_state['uid'] = NULL;
                }
            } else {
                //login was false so that means we need to update users account
                $form_state['status'] = false;
                $form_state['uid'] = $this->uid;

                if (!empty($this->uid)) {
                    $up = $this->fnGetUserProfile();
                    $form_state['email'] = (isset($up->userProfile->emailAddress)) ? $up->userProfile->emailAddress : NULL;
                } else {
                    $form_state['email'] = NULL;
                }

                return $form_state;
            }
        } else if (isset($form_state['access_code']) && !empty($form_state['access_code'])) {

            $nosinlogin = $this->fnLoginNoSin($form_state['access_code']);
            if (!empty($nosinlogin)) {

                if ($nosinlogin['status'] == TRUE && strlen($form_state['access_code']) == 9) {
                    $claimYear = '20' . substr($form_state['access_code'], 7);
                    $claimYear .= $claimYear + 1;

                    if ($claimYear == arg(3)) {
                        $form_state['uid'] = $nosinlogin['uid'];
                        $form_state['login_name'] = $form_state['access_code'];
                        $form_state['status'] = true;
                        return $form_state;
                    }
                }

                form_set_error('access_code', 'Please check with the student/applicant as changes to the application have made this access code no longer valid. If the Appendix is still required a new code will have been issued to the student to forward to you.');
            } else {
                return $form_state['uid'] = NULL;
            }
        } elseif (isset($form_state['assuranceLevel'])) {
            $form_state['uid'] = $form_state['userGUID'];
            $form_state['login_name'] = $form_state['first_name'] . ' ' . $form_state['last_name'];
            $form_state['status'] = true;
            return $form_state;
        } else {

            if (!isset($form_state['access_code'])) {
                if (empty($form_state['username'])) {
                    form_set_error('username', 'User ID is required');
                }

                if (empty($form_state['pswd'])) {
                    form_set_error('pswd', 'Oops it looks like your password was not entered.');
                }
            }

            //IF FIELD IS FILLED IN THEN ROBOTS ARE INSERTING VALUE INTO FIELD
            if (!empty($form_state['hpot'])) {
                form_set_error('hpot', 'Sorry we could not log you in due to an invalid request made');
            }
        }
    }

    function fnLogin($u, $p)
    {

        //GET APPROPRIATE URL FOR WEB SERVICE METHOD
        $this->WSDL = fnWS('WS-HOSTS', 'USER_AUTH');
        $login = $this->fnRequest('logon', array('userID' => $u, 'userPassword' => $p));

        //CHECK TO MAKE SURE WE GET A RESPONSE
        if (!empty($login) && is_object($login)) {

            //CHECK TO MAKE SURE WE DON'T HAVE ERRORS
            if (!isset($login->faultcode) && isset($login->logonReturn)) {

                // CHECk IF OLD ILA ACCOUNT - USER WILL NEED TO UPDATE TO NEW SABC ACCOUNT
                if ($login->logonReturn->updateProfile == 'Y') {
                    //SET UID SO WE CAN REGISTER THAT THEY ARE LOGGED IN
                    $this->uid = $login->logonReturn->userGUID;

                    //RETURING FALSE RIGHT NOW BECAUSE WE DON'T WANT TO LOG THEM IN JUST YET - NEED TO UPDATE ACCOUNT FIRST
                    return FALSE;

                } else {

                    $user['status'] = TRUE;
                    $this->uid = $login->logonReturn->userGUID;
                    $user['uid'] = $this->fnEncrypt($login->logonReturn->userGUID);
                    $this->fnClearUserCache();

                    //GET USER ACCOUNT DETAILS - LOOKING FOR FIRSTNAME AND FAMILY NAME TO DISPLAY ON THE DASHBOARD
                    $up = $this->fnGetUserProfile();
                    $user['name'] = $up->userProfile->firstName . ' ' . $up->userProfile->familyName;

                    return $user;

                }

            } else {
                //CHECK TO SEE IF THIS IS A LOGIN ERROR
                if (isset($login->detail->CredentialFault)) {
                    $errors = array();
                    $errors['status'] = FALSE;
                    $errors['username'] = $login->getMessage();

                    return $errors;
                } //NOT A LOGIN IN ERROR SO TRIGGER SYSTEM ERROR
                else {
                    $errors = array();
                    $errors['status'] = FALSE;
                    $this->fnError('SYSTEM ERROR :: USER_AUTH -> logon', $login->getMessage(), $login, $triggerDefault = true);
                    return $errors;
                }
            }
        } else {
            //ERRORS IN OUR WEB SERVICE CALL NOT A VALID RESPONSE
            $errors = array();
            $errors['status'] = FALSE;

            return $errors;
        }
    }


    /*
    *		USED TO VALIDATE USER/ RETRIEVE USERS GUID FROM THE IDENTITY MANAGEMENT STORE
    *		@params: $u (username) $p (password)
    *		@return: $user which is an array that contains users GUID and full name
    */

    function fnLoginNoSin($u)
    {

        $u = trim($u);

        // bypass login
        if (isset($u) && $u != '') {

            // TODO: sanitize user input. Can be done here, but better to be done on LC side
            $user['status'] = TRUE;

            $this->uid = md5($u);
            $user['uid'] = $this->fnEncrypt(md5($u));
            $this->fnClearUserCache();
            return $user;
        } else {
            $errors = array();
            $errors['status'] = FALSE;
            return $errors;
        }
    }

    function fnBCSCSAMLParser($data)
    {

        $cert = fnCert();

        $decoded = base64_decode($data);

        $dom = new DOMDocument;
        $dom->loadXML($decoded);

        //make sure we have EncryptedAssertion
        $encryptedAssertionNodes = $dom->getElementsByTagName('EncryptedAssertion');

        if ($encryptedAssertionNodes->length !== 0) {
            $filename = DRUPAL_ROOT . '/sites/' . BASE . '/certs/' . $cert . '.pem';
            $path_aeit_library = \Drupal::configFactory()->getEditable('sabc_private_data_form.settings')->get('path_aeit_library');
            require_once($path_aeit_library . 'classes/class.XMLSecEnc.php');
            require_once($path_aeit_library . 'classes/class.XMLSecurityKey.php');
            require_once($path_aeit_library . 'classes/class.XMLSecurityDSig.php');
            $objenc = new XMLSecEnc();
            $encData = $objenc->locateEncryptedData($dom);

            if (!$encData) {
                throw new Exception("Cannot process SAML response", 1);
            }

            $objenc->setNode($encData);
            $objenc->type = $encData->getAttribute("Type");

            if (!$objKey = $objenc->locateKey()) {
                throw new Exception("Error Processing Request", 1);
            }

            $key = null;
            if ($objKeyInfo = $objenc->locateKeyInfo($objKey)) {
                if ($objKeyInfo->isEncrypted) {
                    $objencKey = $objKeyInfo->encryptedCtx;
                    $objKeyInfo->loadKey($filename, true, false);
                    $key = $objencKey->decryptKey($objKeyInfo);
                } else {
                    // symmetric encryption key support
                    $objKeyInfo->loadKey($privateKey, false, false);
                }
            }

            if (empty($objKey->key)) {
                $objKey->loadKey($key);
            }

            $decrypted = $objenc->decryptNode($objKey, true);

            if ($decrypted instanceof DOMDocument) {
                return $decrypted;
            } else {
                $encryptedAssertion = $decrypted->parentNode;
                $container = $encryptedAssertion->parentNode;
                # Fix possible issue with saml namespace
                if (!$decrypted->hasAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:saml') &&
                    !$decrypted->hasAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:saml2') &&
                    !$decrypted->hasAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns') &&
                    !$container->hasAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:saml') &&
                    !$container->hasAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:saml2')
                ) {
                    if (strpos($encryptedAssertion->tagName, 'saml2:') !== false) {
                        $ns = 'xmlns:saml2';
                    } else if (strpos($encryptedAssertion->tagName, 'saml:') !== false) {
                        $ns = 'xmlns:saml';
                    } else {
                        $ns = 'xmlns';
                    }
                    $decrypted->setAttributeNS('http://www.w3.org/2000/xmlns/', $ns, 'urn:oasis:names:tc:SAML:2.0:assertion');
                }
                $container->replaceChild($decrypted, $encryptedAssertion);
                $dom = $decrypted->ownerDocument;
                $decoded = $dom->saveXML();
            }
        }

        $xml = simplexml_load_string($decoded);
        $xml->registerXPathNamespace('ns1', 'urn:oasis:names:tc:SAML:2.0:assertion');

        $responsedata = array();

        $result = $xml->xpath('//ns1:Attribute');
        foreach ($result as $value) {
            $child = $xml->xpath('//ns1:Attribute[@Name="' . $value['Name'] . '"]/ns1:AttributeValue');
            $v = $child[0];
            if ($value['Name'] == 'useridentifier') {
                $DID = preg_split("/[|:]/", (string)$v[0]);
                $responsedata['DID'] = $this->fnEncrypt($DID[2]);
            }
            $responsedata['' . $value['Name'] . ''] = (string)$v[0];
        }

        $_SESSION['bcsc_profile'] = $responsedata; //used to compare profile data after login

        return $responsedata;
    }

    function fnDashboardLogin($uid, $userName, $userRole = 'student')
    {

        global $user;
        $uid2 = $this->fnDecrypt($uid);

        $this->uid = $uid;

        if (isset($_SESSION['debug']) && $_SESSION['debug'] == 'guid') {
            watchdog('debug guid', 'decrypted guid: ' . $uid2 . ' encrypted guid ' . $this->uid . '', NULL, WATCHDOG_ERROR, $link = NULL);
            unset($_SESSION['debug']);
        }

        if (isset($userName)) {

            $args = arg();
            //CHECK IF USER EXISTS IN DRUPAL DB. $this->uid is the users GUID which has been set by login or create user profile
             if (isset($_SESSION['bcsc_profile'])) {
                 $saml = openssl_encrypt(json_encode($_SESSION['bcsc_profile']), "AES-128-CFB", '&jl8938l!_90kdkd98kedjao', 0, 'UijHyt6$9K!$ERri');
             }
            if ($bcscusrProfile = $this->fnGetBCSCUserProfile()) {
                $assuranceLevel1 = $bcscusrProfile->userProfile->assuranceLevel;
            } else {
                $assuranceLevel1 = null;
            }

            if ((user_load_by_name($uid) && !empty($uid) && $userRole == 'student') || $assuranceLevel1 == 3 || $assuranceLevel1 == 2) {

                if ($userRole == 'bcsc_student' || !isset($bcscuserProfile->status)) {

                    if (!user_load_by_name($uid)) {
                        //NO USER SO CREATE AN ACCOUNT FOR THEM
                        $this->createNewUser($uid, $userName, $userRole);
                    } else {

                        $this->fnClearUserCache();
                        $bcscusrProfile = $this->fnBcscLogin();
                        $user = user_load_by_name($uid);
                        $roles = user_roles(TRUE);
                        $rid = array_search($userRole, $roles);
                        $user->roles[$rid] = $userRole;
                        user_login_finalize($user->uid);
                        $account = user_save($user, array());
                    }


                    //compare bcsc profile data and update if different than get profile
                    if (isset($_SESSION['bcsc_profile'])) {
                        $updateProfile = array();

                        $fname = (isset($bcscusrProfile->userProfile->firstName)) ? strtolower($bcscusrProfile->userProfile->firstName) : NULL;
                        $trimmedFirstName = substr(trim($_SESSION['bcsc_profile']['givenname']), 0, 15);
                        if (strtolower($trimmedFirstName) != $fname) {
                            $updateProfile['first_name'] = $trimmedFirstName;
                        }

                        $lname = (isset($bcscusrProfile->userProfile->familyName)) ? strtolower($bcscusrProfile->userProfile->familyName) : NULL;
                        $trimmedLastName = substr(trim($_SESSION['bcsc_profile']['surname']), 0, 25);
                        if (strtolower($trimmedLastName) != $lname) {
                            $updateProfile['last_name'] = $trimmedLastName;
                        }

                        $middleName = str_replace($_SESSION['bcsc_profile']['givenname'], '', $_SESSION['bcsc_profile']['givennames']);
                        $currentMiddleName = (isset($bcscusrProfile->userProfile->middleName)) ? $bcscusrProfile->userProfile->middleName : NULL;
                        $trimmedMiddleName = substr(trim($middleName), 0, 15);
                        if (strtolower(trim($trimmedMiddleName)) != strtolower(trim($currentMiddleName))) {
                            $updateProfile['middle_name'] = $trimmedMiddleName;
                        }

                        if ($_SESSION['bcsc_profile']['identityassurancelevel'] != $bcscusrProfile->userProfile->assuranceLevel) {
                            $updateProfile['assuranceLevel'] = $_SESSION['bcsc_profile']['identityassurancelevel'];
                        }

                        if (isset($_SESSION['bcsc_profile']['sex']) && in_array(strtoupper($_SESSION['bcsc_profile']['sex'][0]), array('M', 'F')) && $_SESSION['bcsc_profile']['sex'][0] != strtoupper($bcscusrProfile->userProfile->gender)) {
                            $updateProfile['gender'] = $_SESSION['bcsc_profile']['sex'][0];
                        }

                        if (str_replace('-', '', $_SESSION['bcsc_profile']['birthdate']) != $bcscusrProfile->userProfile->dateOfBirth) {
                            $updateProfile['dateOfBirth'] = str_replace('-', '', $_SESSION['bcsc_profile']['birthdate']);
                        }

                        //check to make sure updateProfile is not empty.  If not empty then we need to update profile
                        if (!EMPTY($updateProfile)) {
                            $origValues = array(
                                'assuranceLevel' => $bcscusrProfile->userProfile->assuranceLevel,
                                'first_name' => (isset($bcscusrProfile->userProfile->firstName)) ? $bcscusrProfile->userProfile->firstName : NULL,
                                'last_name' => (isset($bcscusrProfile->userProfile->familyName)) ? $bcscusrProfile->userProfile->familyName : NULL,
                                'middle_name' => (isset($bcscusrProfile->userProfile->middleName)) ? $bcscusrProfile->userProfile->middleName : NULL,
                                'dateOfBirth' => $bcscusrProfile->userProfile->dateOfBirth,
                                'gender' => $bcscusrProfile->userProfile->gender,
                                'email' => $bcscusrProfile->userProfile->emailAddress,
                                'Street1' => $bcscusrProfile->userProfile->addressLine1,
                                'City' => $bcscusrProfile->userProfile->city,
                                'Country' => $bcscusrProfile->userProfile->country,
                                'Phone' => $bcscusrProfile->userProfile->phoneNumber
                            );

                            if (isset($bcscusrProfile->userProfile->postalCode) && !empty($bcscusrProfile->userProfile->postalCode)) {
                                $origValues['PostZip'] = $bcscusrProfile->userProfile->postalCode;
                            }
                            if (isset($bcscusrProfile->userProfile->province) && !empty($bcscusrProfile->userProfile->province)) {
                                $origValues['ProvState'] = $bcscusrProfile->userProfile->province;
                            }
                            if (isset($bcscusrProfile->userProfile->addressLine2) && !empty($bcscusrProfile->userProfile->addressLine2)) {
                                $origValues['Street2'] = $bcscusrProfile->userProfile->addressLine2;
                            }
                            //loop through updated values and overwrite the old value
                            foreach ($updateProfile as $k => $v) {
                                $origValues[$k] = $v;
                            }

                            //create payload data to be updated
                            $p = array('autoUpdate' => true, 'values' => $origValues);
                            $this->fnUpdateUserProfile($p);
                        } else {
                            //nothing needs updating so remove check
                            unset($_SESSION['bcsc_profile']);
                        }
                    }

                    setcookie('sabc_un', base64_encode($userName), 0, '/');

                    drupal_goto('/dashboard');

                }

                //define appendix form url - used for confirming role
                if (($userRole == 'spouse_no_sin') || ($userRole == 'parent_no_sin')) {

                    $appendixType = $args[4];
                    $programYear = $args[3];

                    // attempt to claim appendix
                    $ct = new application(false);

                    $autoClaim = $ct->fnClaimToken(
                        array(
                            'access_code' => $userName,
                            'program_year' => $programYear,
                            'appendix_type' => $appendixType,
                            'user_role' => $userRole
                        ),
                        $this->fnDecrypt($uid)
                    );

                    if (!empty($autoClaim) && $autoClaim['status'] == true) {

                        $user = user_load_by_name($uid);
                        $roles = user_roles(TRUE);
                        $rid = array_search($userRole, $roles);
                        $user->roles[$rid] = $userRole;

                        user_login_finalize($user->uid);
                        $account = user_save($user, array());

                        if ($autoClaim['statusCode'] == 429) {
                            $_GET['destination'] = 'dashboard/declaration/download/' . $appendixType . '/' . $autoClaim['applicationNumber'] . '/' . $autoClaim['documentGUID'];
                        } else {
                            $_GET['destination'] = 'dashboard/apply/appendix/' . $appendixType . '/' . $autoClaim['documentGUID'] . '/' . $programYear;
                        }
                    } else {
                        return false;
                    }
                }
                else {


                    $user = user_load_by_name($uid);

                    define("APPENDIXPATTERN", "/^dashboard\/appendix\/claim/");
                    //get the url redirect/destination of the user logging in
                    $destination = drupal_get_destination();
                    $destination_args = explode("/", $destination['destination']);


                    //match for appendix url
                    if (preg_match(APPENDIXPATTERN, $destination['destination'], $matches, PREG_OFFSET_CAPTURE)) {
                        //get appendix type
                        $appendixType = $destination_args[4];
                        //need to auto validate appendix claim code for user if it is already provided
                        if (isset($destination_args[5]) && !empty($destination_args[5])) {

                            //only try to claim if we know it is an APPENDIX1 or APPENDIX2
                            if (isset($destination_args[3]) && ($destination_args[4] == 'APPENDIX1' || $destination_args[4] == 'APPENDIX2')) {

                                $programYear = $destination_args[3];

                                $ct = new application(false);
                                $autoClaim = $ct->fnClaimToken(
                                    array(
                                        'access_code' => $destination_args[5],
                                        'program_year' => $destination_args[3],
                                        'appendix_type' => $destination_args[4]
                                    )
                                );

                                if ($autoClaim['status'] == true) {
                                    if (isset($autoClaim['statusCode']) &&
                                        $autoClaim['statusCode'] == 408) {
                                        $_GET['destination'] = 'dashboard/apply/appendix/' . $autoClaim['appendix_type'] . '/' . $autoClaim['documentGUID'] . '/' . $programYear;
                                    }
                                } else {
                                    $_GET['destination'] = 'dashboard';
                                }
                            }
                        }

                        //get roles from drupal
                        $roles = user_roles(TRUE);

                        //assign role based on appendix type
                        switch ($appendixType) {
                            case 'APPENDIX1':
                                $role_name = 'parent';
                                // Get the rid from the roles table.
                                $rid = array_search($role_name, $roles);
                                //assign the role to the user
                                $user->roles[$rid] = $role_name;
                                break;
                            case 'APPENDIX2':
                                $role_name = 'spouse';
                                // Get the rid from the roles table.
                                $rid = array_search($role_name, $roles);
                                //assign the role to the user
                                $user->roles[$rid] = $role_name;
                                break;
                        }
                    } else {
                        $roles = array('parent', 'spouse');
                        //remove parent or spouse from user roles
                        foreach ($roles as $role) {
                            $key = array_search($role, $user->roles);
                            if ($key) {
                                unset($user->roles[$key]);
                            }
                        }
                    }

                    user_login_finalize($user->uid);
                    $account = user_save($user, array());
                }
            } //account creation for accounts without SIN.
            else if (($userRole == 'spouse_no_sin') || ($userRole == 'parent_no_sin')) {

                $appendixType = $args[4];
                $programYear = $args[3];

                $user = user_load_by_name($uid);
                if($user == false){
                    $array["name"] = $uid;
                    $array["pass"] = user_password();
                    $array["status"] = 1;
                    $array["timezone"] = variable_get('date_default_timezone', 0);
                    $array['lastlogin'] = NULL;
                    //GET ALL ROLES AVAILABLE IN DRUPAL EXCEPT ANNON

                    $roles = user_roles(TRUE);
                    $rid = array_search($userRole, $roles);
                    $array['roles'] = array($rid => $userRole);

                    $account = user_save("", $array);
                    $user = $account;
                }

                // attempt to claim appendix
                $ct = new application(false);

                $autoClaim = $ct->fnClaimToken(
                    array(
                        'access_code' => $userName,
                        'program_year' => $programYear,
                        'appendix_type' => $appendixType,
                        'user_role' => $userRole
                    ),
                    $this->fnDecrypt($uid)
                );

                if (!empty($autoClaim) && $autoClaim['status'] == true) {
                    user_login_finalize($user->uid);

                    if ($autoClaim['statusCode'] == 429) {
                        $_GET['destination'] = 'dashboard/declaration/download/' . $appendixType . '/' . $autoClaim['applicationNumber'] . '/' . $autoClaim['documentGUID'];
                    } else {
                        $_GET['destination'] = 'dashboard/apply/appendix/' . $appendixType . '/' . $autoClaim['documentGUID'] . '/' . $programYear;
                    }
                } else {
                    return false;
                }
            } else //WE DON'T HAVE AN ACCOUNT
            {
                if ($userRole == 'bcsc_student' || $userRole == 'bcsc_parent' || $userRole == 'bcsc_spouse') {
                    $request = (isset($_SESSION['SAMLResponse'])) ? $_SESSION['SAMLResponse'] : base64_encode($_REQUEST["SAMLResponse"]);
                    $url = "dashboard/create";

                    drupal_goto($url);
                } else {

                    //NO USER SO CREATE AN ACCOUNT FOR THEM
                    $this->createNewUser($uid, $userName, $userRole);
                }
            }

            setcookie('sabc_un', base64_encode($userName), 0, '/');

            if (in_array($uid2, $badAccounts)) {
                drupal_set_message('We recently updated StudentAidBC and experienced an issue converting your account to our new system.  We require you to call Student Services at 1-800-561-1818 for further assistance with your account.  <br><br>Sorry for the inconvenience.', 'error');
            }
            return 'dashboard';
        } else
            return false;
    }

    function fnGetBCSCUserProfile()
    {
        $uid = $this->fnDecrypt($this->uid);

        //CALL GET USER PROFILE SOAP WEB SERVICE
        $this->WSDL = fnWS('WS-HOSTS', 'BCSC_USER_ACCOUNT');
        $bcscusrProfile = $this->fnRequest('getUserProfile', array('userGUID' => $uid), 'get_user_profile' . $this->uid, 14400);

        //MAKE SURE IT IS NOT AN ERROR
        if (!isset($bcscusrProfile->faultcode)) {
            $bcscusrProfile->status = TRUE;
            $bcscusrProfile->userProfile->SIN = $this->fnEncrypt($bcscusrProfile->userProfile->SIN);
            $bcscusrProfile->userProfile->userGUID = $this->fnEncrypt($bcscusrProfile->userProfile->userGUID);

            return $bcscusrProfile;
        } else {
            //WE HAVE AN ERROR AND IT IS A VALID PROFILE ERROR
            if (isset($bcscusrProfile->detail->ProfileFault)) {
                $errors = array();
                $errors['status'] = FALSE;
                $errors['msg'] = $bcscusrProfile->getMessage();

                return $errors;
            } //NOT A VALID PROFILE ERROR SO TRIGGER SYSTEM ERROR
            else {
                $this->fnError('SYSTEM ERROR :: USER_ACCOUNT->getUserBcscProfile', $bcscusrProfile->getMessage(), $bcscusrProfile, $triggerDefault = true);
            }
        }
    }

    /*
    * USED TO LOG IN A USER INTO THE DASHBOARD ONCE THEY HAVE BEEN VALIDATED THROUGH fnLogin WEB SERVICE
    * @params:
            - users userName - Indicates full name of user
            - users userRole - used to determine how to configure dashboard according to their role. By default it is a student role.
    */

    // SABC dashboard login

    function createNewUser($uid, $userName, $userRole = 'student')
    {

        global $user;
        $array["name"] = $uid;
        $array["pass"] = user_password();
        $array["status"] = 1;
        $array['timezone'] = variable_get('date_default_timezone', 0);
        $array['lastlogin'] = NULL;

        //GET ALL ROLES AVAILABLE IN DRUPAL EXCEPT ANNON
        $roles = user_roles(TRUE);

        //GET THE RID OF THE USERTYPE WE SELECTED
        $rid = array_search($userRole, $roles);

        $array['roles'] = array($rid => true);
        $account = user_save("", $array);

        $user = $account;

        if (isset($_SESSION['SAMLResponse'])) {

            if (isset($_SESSION['role'])) {
                $bcscUserRole = $_SESSION['role'];
                $bcscRole = user_role_load_by_name($bcscUserRole);
                user_multiple_role_edit(array($user->uid), 'add_role', $bcscRole->rid);
            }
        }

        unset($_SESSION['SAMLResponse']);
        unset($_SESSION['role']);

        user_login_finalize($user->uid);
    }

    function fnBcscLogin()
    {

        unset($_SESSION['SAMLResponse']);
        unset($_SESSION['role']);

        $up = $this->fnGetBCSCUserProfile();

        if (!empty($up->status)) {

            $user['status'] = TRUE;
            $user['uid'] = $this->uid;
            $name = NULL;

            if (isset($up->userProfile->firstName)) {
                $name .= $up->userProfile->firstName;
            }
            if (isset($up->userProfile->familyName)) {
                $name .= $up->userProfile->familyName;
            }

            $user['login_name'] = $name;
            $this->fnClearUserCache();

            return $up;

        }
    }

    /*
    * USED TO GET CHALLENGE QUESTIONS FOR USERS TO CHOOSE FROM
    * @params: VOID
    * @return: ARRAY OF CHALLENGE QUESTIONS
    */

    function fnVerifyBcscChallengeQuestion($uid, $answer)
    {
        $userGUID = $this->fnDecrypt($uid);
        $answer = intval($answer);
        $this->WSDL = fnWS('WS-HOSTS', 'BCSC_VERIFY_CHALLENGE');
        $response = $this->fnRequest('verifyBcscChallengeQuestion', array('userGUID' => $userGUID, 'answer' => $answer));
        return $response;
    }


    /*
     * Verify BCSC Challange Question/Answer
     * @params: (string) guid - user guid
     * @params: (int) answer - number between 1-100
     * @return: (object) verifyBcscChallengeQuestionResponse
     * 					-> (string) access
     *   				-> (string) locked
     *
     * 	 "verifyBcscChallengeQuestionResponse verifyBcscChallengeQuestion(verifyBcscChallengeQuestion $parameters)"
     *
     */

    function fnRecoverUser($p)
    {

        $errorMappings = array('SPSIM15' => 'socialinsnum', 'SPSIM01.2' => 'hpot', 'SPSIM02' => 'hpot', 'SPSIM03' => 'hpot', 'SPSIM10' => 'hpot', 'SPSIM11' => 'day', 'SPSIM21' => 'hpot', 'SPSIM01.1' => 'hpot');

        //INDICATOR TO TELL US IF WE ARE ALL GOOD
        $status = '';

        //VALIDATE BIRTHDATE
        if (isset($p['month'], $p['day'], $p['year']) && !empty($p['month']) && !empty($p['day']) && !empty($p['year'])) {

            //MAKE SURE YEAR IS 4 DIGITS LONG
            if (strlen($p['year']) == 4) {
                $birthdate = $p['year'] . '-' . $p['month'] . '-' . $p['day'];

                //MAKE SURE DATE IS VALID
                if (checkdate((int)$p['month'], (int)$p['day'], (int)$p['year'])) {

                    //MAKE SURE BIRTHDATE IS NOT GREATER THAN OR EQUAL TO TODAY
                    if (strtotime($birthdate) >= strtotime(date('Y-m-d'))) {
                        form_set_error('birth_date', 'Birth date is invalid');
                        $status = false;
                    } else {
                        $dob = $p['year'] . '' . sprintf("%02s", $p['month']) . '' . sprintf("%02s", $p['day']); //DATE IS GOOD SO FORMAT TO PROPER FORMAT FOR E-SERVICE YYYYMMDD
                        $status = true;
                    }
                } else {
                    form_set_error('birth_date', 'Invalid date for birthday.'); //DATE IS INVALID
                    $status = false;
                }
            } else {
                form_set_error('birth_date', 'Birth date year is invalid.  Please ensure year is formatted YYYY');
                $status = false;
            }
        } else {
            form_set_error('birth_date', 'Birth date is invalid.');
            $status = false;
        }

        //VALIDATE SIN
        if (isset($p['socialinsnum'])) {
            //VALIDATE SIN
            $sin = str_replace(array('-', ' '), '', $p['socialinsnum']); //FORMAT SIN TAKE OUT "-" AND SPACES

            //MAKE SURE SIN IS 9 DIGITS LONG
            if (strlen($sin) == 9) {

                //ENSURE SIN IS A NUMBER
                if (!filter_var($sin, FILTER_VALIDATE_INT)) {
                    form_set_error('socialinsnum', 'Invalid SIN.  SIN can only contain numbers.');
                    $status = false;
                } else {
                    $p['socialinsnum'] = $sin; //SIN IS GOOD SO FORMAT TO PROPER FORMAT FOR E-SERVICE
                    $status = ($status === false) ? false : true;
                }
            } else {
                form_set_error('socialinsnum', 'Invalid SIN.  SIN can only contain 9 numbers along with separating spaces or dashes.');
                $status = false;
            }
        }

        //MAKE SURE LAST NAME IS NOT EMPTY
        if (isset($p['lastname']) && !empty($p['lastname'])) {
            $status = ($status === false) ? false : true;
        } else {
            form_set_error('lastname', 'Last name is required.');
            $status = false;
        }

        //VALIDATION PASSED GO AHEAD AND CALL E-SERVICE
        if ($status == true) {
            $this->WSDL = fnWS('WS-HOSTS', 'USER_AUTH');

            //RECOVER USER
            $recoverProfile = $this->fnRequest('recoverUser', array('familyName' => $p['lastname'], 'SIN' => $sin, 'dateOfBirth' => $dob));

            //MAKE SURE NO ERRORS WERE RETURNED
            if (!isset($recoverProfile->faultcode)) {
                //MAKE SURE WE GOT RESULTS
                if (isset($recoverProfile->recoverUserReturn)) {

                    //GET LIST OF CHALLENGE QUESTIONS SO THAT WE CAN DETERMINE USERS CHALLENGE QUESTIONS
                    $challenge = $this->fnGetChallengeQuestions();

                    //MAKE SURE WE GOT A LIST RETURNED TO US
                    if (!empty($challenge)) {
                        //ENCRYPT GUID BECAUSE WE WILL BE USING THIS TO PASS ALONG FROM STEP TO STEP FOR FORGOT PASSWORD RECOVERY
                        $recoverProfile->recoverUserReturn->guid = $this->fnEncrypt($recoverProfile->recoverUserReturn->guid);

                        //GET CHALLENGE QUESTION BASED ON WHAT QUESTION POOL AND QUESTION NUMBER WAS RETURNED TO US
                        $recoverProfile->recoverUserReturn->challengeQuestion = $challenge['pool' . $recoverProfile->recoverUserReturn->questionPoolNumber . ''][$recoverProfile->recoverUserReturn->questionNumber];

                        return $recoverProfile->recoverUserReturn;
                    }
                }
            } else {
                //WE HAVE AN ERROR AND IT IS A VALID PROFILE ERROR
                if (isset($recoverProfile->detail->CredentialFault)) {
                    if ($recoverProfile->detail->CredentialFault->faultCode == 'SPSIM15')
                        form_set_error($errorMappings[$recoverProfile->detail->CredentialFault->faultCode], $recoverProfile->getMessage());
                    else
                        form_set_error($errorMappings[$recoverProfile->detail->CredentialFault->faultCode], $recoverProfile->getMessage());
                } else {
                    $this->fnError('SYSTEM ERROR :: USER_AUTH -> recoverUser', $recoverProfile->getMessage(), $recoverProfile, $triggerDefault = true);
                }
            }
        } else {
            $errors = form_get_errors();
            if (!is_array($errors) || empty($errors))
                form_set_error('hpot', 'Sorry your request could not be processed properly.  Please refresh window and try again');

            return false;
        }
    }

    function fnGetChallengeQuestions()
    {

        //GET APPROPRIATE URL FOR WEB SERVICE METHOD
        $this->WSDL = fnWS('WS-HOSTS', 'CHALLENGE_QUESTIONS');
        $cq = $this->fnRequest('getChallengeQuestions');
        if (isset($cq->challengeQuestionPool)) {
            $questions = array();
            foreach ($cq->challengeQuestionPool as $k => $v) {
                $questions['pool' . $v->poolNumber . ''] = array();
                foreach ($v->challengeQuestion as $id => $val) {
                    $questions['pool' . $v->poolNumber . ''][$val->questionNumber] = $val->questionText;
                }
            }
            return $questions;
        } else
            return false;
    }

    function fnVerifyChallenge($p)
    {

        $status = '';

        //MAKE SURE USERS GUID IS PASSED
        if (isset($p['userGUID']) && !empty($p['userGUID'])) {
            $uGUID = $this->fnDecrypt($p['userGUID']);
            $status = ($status === false) ? false : true;
        } else {
            $status = false;
        }

        //MAKE SURE WE GOT OUR QUESTION POOL NUMBER
        if (isset($p['questionPool'])) {
            $qp = $p['questionPool'];
            $status = ($status === false) ? false : true;
        } else {
            $status = false;
        }

        //MAKE SURE WE GOT OUR QUESTION NUMBER
        if (isset($p['questionNumber']) && !empty($p['questionNumber'])) {
            $qn = $p['questionNumber'];
            $status = ($status === false) ? false : true;
        } else {
            $status = false;
        }

        //MAKE SURE WE GOT OUR ANSWER
        if (isset($p['answer']) && !empty($p['answer'])) {
            $answer = $p['answer'];
            $status = ($status === false) ? false : true;
        } else {
            $status = false;
        }

        if ($status === true) {
            $this->WSDL = fnWS('WS-HOSTS', 'CHALLENGE_QUESTIONS');
            $verifyChallenge = $this->fnRequest('verifyChallengeQuestion', array('userGUID' => $uGUID, 'questionPool' => $qp, 'questionNumber' => $qn, 'answer' => $answer));

            if (isset($verifyChallenge->userID)) {
                return $verifyChallenge;
            } else {
                $errorMappings = array('SPSIM26' => 'answer', 'SPSIM26.1' => 'hpot', 'SPSIM03' => 'hpot', 'SPSIM10' => 'hpot', 'SPSIM07' => 'hpot', 'SPSIM02' => 'hpot', 'SPSIM06' => 'hpot');
                //WE HAVE AN ERROR AND IT IS A VALID PROFILE ERROR
                if (isset($verifyChallenge->detail->ChanllengeQuestionFault)) {
                    form_set_error($errorMappings[$verifyChallenge->detail->ChanllengeQuestionFault->faultCode], $verifyChallenge->getMessage());
                } else {
                    $this->fnError('SYSTEM ERROR :: CHALLENGE_QUESTIONS -> verifyChallengeQuestion', $verifyChallenge->getMessage(), $verifyChallenge, $triggerDefault = true);
                }
            }
        } else {
            form_set_error('hpot', 'Sorry your request could not be processed properly.  Please refresh window and try again');
            return false;
        }
    }


    function fnChangePassword($p)
    {

        $status = '';

        if (isset($p['values']['uguid']) && !empty($p['values']['uguid'])) {
            $userGUID = $this->fnDecrypt($p['values']['uguid']);
            $status = ($status === false) ? false : true;
        } else if (isset($p['values']['userID']) && !empty($p['values']['userID'])) {
            $userGUID = $p['values']['userID'];
        } else {
            $status = false;
        }

        if (isset($p['values']['new_password']) && !empty($p['values']['new_password'])) {
            $pswd = $p['values']['new_password'];
            $status = ($status === false) ? false : true;
        } else {
            $status = false;
        }


        if (!empty($status)) {
            $this->WSDL = fnWS('WS-HOSTS', 'USER_AUTH');
            $changePassword = $this->fnRequest('changePassword', array('userGUID' => $userGUID, 'password' => $pswd, 'confirmationPassword' => $pswd));

            if (!isset($changePassword->faultcode)) {
                $p['redirect'] = 'dashboard/login';
                return $p;
            } else {
                $errorMappings = array('SPSIM07' => 'hpot', 'SPSIM08' => 'new_password', 'SPSIM09' => 'new_password', 'SPSIM02' => 'hpot', 'SPSIM03' => 'hpot', 'SPSIM06' => 'hpot');

                //WE HAVE AN ERROR AND IT IS A VALID PROFILE ERROR
                if (isset($changePassword->detail->CredentialFault)) {
                    form_set_error($errorMappings[$changePassword->detail->CredentialFault->faultCode], $changePassword->getMessage());

                } else {
                    $this->fnError('SYSTEM ERROR :: USER_AUTH -> changePassword', $changePassword->getMessage(), $changePassword, $triggerDefault = true);
                }
            }
        } else {
            form_set_error('hpot', 'Sorry your request could not be processed properly.  Please refresh window and try again');
            return false;
        }
    }

    function fnDeactivateProfile($uguid)
    {

        $status = '';

        if (!empty($uguid)) {
            $userGUID = $this->fnDecrypt($uguid);
            $status = ($status === false) ? false : true;
        } else {
            $status = false;
        }

        $this->WSDL = fnWS('WS-HOSTS', 'USER_AUTH');
        $deactivate = $this->fnRequest('deactivateProfile', array('userGUID' => $userGUID));

        if (!isset($deactivate->faultcode)) {
            return TRUE;
        } else {
            $errorMappings = array('SPSIM02' => 'hpot', 'SPSIM07' => 'hpot', 'SPSIM03' => 'hpot');

            //WE HAVE AN ERROR AND IT IS A VALID PROFILE ERROR
            if (isset($deactivate->detail->CredentialFault)) {
                form_set_error($errorMappings[$deactivate->detail->CredentialFault->faultCode], $deactivate->getMessage());
            } else {
                $this->fnError('SYSTEM ERROR :: USER_AUTH -> deactivateProfile', $deactivate->getMessage(), $deactivate, $triggerDefault = true);
            }
        }
    }

    function fnResolveGUID($recoveryGUID)
    {
        if (!empty($recoveryGUID)) {
            $this->WSDL = fnWS('WS-HOSTS', 'USER_AUTH');
            $recover = $this->fnRequest('resolveRecoveryGuid', array('recoveryGuid' => $recoveryGUID));

            if (!isset($recover->faultcode)) {
                return $recover->resolveRecoveryGuidReturn;
            } else {
                $errorMappings = array(
                    'SPSIM12' => 'user_guid',
                    'SPSIM07' => 'user_guid',
                    'SPSIM02' => 'hpot',
                    'SPSIM03' => 'hpot',
                    'SPSIM13' => 'hpot');

                //WE HAVE AN ERROR AND IT IS A VALID PROFILE ERROR
                if (isset($recover->detail->CredentialFault)) {
                    form_set_error($errorMappings[$recover->detail->CredentialFault->faultCode], $recover->getMessage());
                } else {
                    $this->fnError('SYSTEM ERROR :: USER_AUTH -> resolveRecoveryGuid', $recover->getMessage(), $recover, $triggerDefault = true);
                }
            }
        }

        return false;
    }

    function fnEmailRecovery($uguid)
    {

        $status = '';

        if (!empty($uguid)) {
            $userGUID = $this->fnDecrypt($uguid);
            $status = ($status === false) ? false : true;
        } else {
            $status = false;
        }

        $this->WSDL = fnWS('WS-HOSTS', 'USER_AUTH');
        $recover = $this->fnRequest('requestEmailRecovery', array('userGUID' => $userGUID));

        if (!isset($recover->faultcode)) {
            return TRUE;
        } else {
            $errorMappings = array('SPSIM07' => 'hpot', 'SPSIM02' => 'hpot', 'SPSIM14' => 'hpot');

            //WE HAVE AN ERROR AND IT IS A VALID PROFILE ERROR
            if (isset($recover->detail->CredentialFault)) {
                form_set_error($errorMappings[$recover->detail->CredentialFault->faultCode], $recover->getMessage());
            } else {
                $this->fnError('SYSTEM ERROR :: USER_AUTH -> requestEmailRecovery', $recover->getMessage(), $recover, $triggerDefault = true);
            }
        }
    }
}

?>
