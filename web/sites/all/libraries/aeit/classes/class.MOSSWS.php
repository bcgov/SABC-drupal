<?php
	if(!function_exists('__autoload')) {
		function __autoload($c){
			include 'class.'.$c.'.php';
		}
	}

	class MOSSWS {

		protected $wsdl; //LOCATION OF WEB SERVICE WSDL
		private $checkPermissions; // T/F FLAG TO TELL US IF WE NEED TO CHECK FOR PERMISSIONS (DEFAULT FALSE)
		public $authU; //WEB SERVICE USER
		public $authP; //WEB SERVICE PSWD
		public $domain; //THE DOMAIN OF OUR WEB SERVICE
		private $groupDetails = array(); //AN ARRAY THAT TELLS US ABOUT THE GROUPS WE ARE APART OF
		public $myGroups = array(); //A LIST OF GROUPS THAT YOU ARE APART OF AND WERE VALIDATED FOR
		protected $ln; //LIST NAME
		protected $vn; //VIEW NAME
		public $hasPermissions = FALSE; // T/F FLAG TO TELL US WHETHER WE WERE GRANTED PERMISSION TO AN AREA
		public $cacheFile;

		function __construct($domain = '', $section = '', $checkPermissions = FALSE, $checkListPermissions = FALSE, $ln = NULL){

      $mossws_config = \Drupal::configFactory()->getEditable('sabc_private_data_form.settings')->get();
			$this->authU = $mossws_config['mosswb']['login'];
			$this->authP = $mossws_config['mosswb']['password'];
			$this->domain = $domain;

			$this->checkPermissions = $checkPermissions;


			if($section != ''){
				$this->fnGetUserGroupPermissions($section);
			}

			if($checkListPermissions == TRUE){
				$this->ln = $ln;
				$this->fnCheckPermissions($section);
			}
		}

		/*
			fnAddItemToList - used to add an item to a particular list

			$wsdl: the wsdl location for the list
			$ln: the list id for the list we want to get data from
			$vn: the view name for the list we want to get data from
			$formInputs: the fields we are posting
			$autoPost: 0 = Do not auto publish, 1 = auto publish
		*/
		function fnAddItemToList($section, $ln, $vn, $formInputs, $autoPost = FALSE){

			$this->ln = $ln;
			$this->vn = $vn;
			$this->wsdl = $this->domain.''.$section.'/_vti_bin/Lists.asmx?wsdl';

			$xml = "<UpdateListItems xmlns='http://schemas.microsoft.com/sharepoint/soap/'>";
				$xml .= "<listName>".$ln."</listName>";
			 	$xml .= "<updates>";
					$xml .= "<Batch ListVersion='1' OnError='Continue'>";
						$xml .= "<Method Cmd='New' ID='1'>";
							if(is_array($formInputs)){
								while(list($k, $v) = each($formInputs)){
									$xml .= '<Field Name=\''.$k.'\'>'.$v.'</Field>';
								}
							}
						$xml .= "</Method>";
					$xml .= "</Batch>";
			 	$xml .= "</updates>";
			 $xml .= "</UpdateListItems>";

			$return = $this->fnWSCall('UpdateListItems', $xml);

			if(isset($return['UpdateListItemsResult']['Results']['Result']['row'])){

				$result = $return['UpdateListItemsResult']['Results']['Result']['row'];

				$results = array();
				$results['listItemID'] = $result['!ows_ID'];
				$results['owner'] = (isset($result['!ows_userid'])) ? $result['!ows_userid'] : 0;
				$results['status'] = $result['!ows__ModerationStatus'];
				$results['status'] = 'success';

				//PUBLISH ITEM
				if($autoPost == TRUE){
					$this->fnApproveListItem($ln, $results['listItemID']);
				}
			}
			else
			{
				$results['status'] = 'failed';
			}

			if($this->checkPermissions == TRUE){
				$this->fnCheckPermissions($section);
			}

			return $results;
		}

		function fnApproveListItem($ln, $lID){

			$this->ln = $ln;

			unset($xml);

			$xml = "<UpdateListItems xmlns='http://schemas.microsoft.com/sharepoint/soap/'>";
				$xml .= "<listName>".$ln."</listName>";
			 	$xml .= "<updates>";
					$xml .= '<Batch ListVersion="1" OnError="Continue">';
						$xml .= '<Method ID="1" Cmd="Moderate">';
							$xml .= '<Field Name="ID">'.$lID.'</Field>';
							$xml .= '<Field Name="_ModerationStatus">0</Field>';
						$xml .= '</Method>';
					$xml .= '</Batch>';
				$xml .= "</updates>";
			$xml .= "</UpdateListItems>";

			$call = $this->fnWSCall('UpdateListItems', $xml);
		}

		function fnUpdateItemToList($section, $ln, $vn, $formInputs, $autoPost = FALSE, $readOnly = NULL){

			$this->ln = $ln;
			$this->vn = $vn;
			$this->wsdl = $this->domain.''.$section.'/_vti_bin/Lists.asmx?wsdl';

			$xml = "<UpdateListItems xmlns='http://schemas.microsoft.com/sharepoint/soap/'>";
				$xml .= "<listName>".$ln."</listName>";
			 	$xml .= "<updates>";
					$xml .= "<Batch ListVersion='1' OnError='Continue'>";
						$xml .= "<Method Cmd='Update' ID='1'>";
							if(is_array($formInputs)){
								while(list($k, $v) = each($formInputs)){
									if($readOnly != NULL){
										$xml .= '<Field Name=\''.$k.'\' ReadOnly=\''.$readOnly.'\'>'.$v.'</Field>';
									}
									else
									{
										$xml .= '<Field Name=\''.$k.'\'>'.$v.'</Field>';
									}
								}
							}
						$xml .= "</Method>";
					$xml .= "</Batch>";
			 	$xml .= "</updates>";
			 $xml .= "</UpdateListItems>";

			$return = $this->fnWSCall('UpdateListItems', $xml);

			if(isset($return['UpdateListItemsResult']['Results']['Result']['row'])){

				$result = $return['UpdateListItemsResult']['Results']['Result']['row'];
				$results = array();
				$results['listItemID'] = $result['!ows_ID'];
				if(isset($result['!ows_userid'])){
					$results['owner'] = $result['!ows_userid'];
				}
				if(isset($result['!ows_ModerationStatus'])){
					$results['status'] = $result['!ows_ModerationStatus'];
				}
				$results['status'] = 'success';

				//PUBLISH ITEM
				if($autoPost == TRUE){
					$this->fnApproveListItem($ln, $results['listItemID']);
				}
			}
			else
			{
				$results['status'] = 'failed';
			}

			if($this->checkPermissions == TRUE){
				$this->fnCheckPermissions($section);
			}

			return $results;
		}


		function fnRemoveItemFromList($section, $ln, $vn, $fields){

			$this->ln = $ln;
			$this->vn = $vn;
			$this->wsdl = $this->domain.''.$section.'/_vti_bin/Lists.asmx?wsdl';

			$xml = "<UpdateListItems xmlns='http://schemas.microsoft.com/sharepoint/soap/'>";
				$xml .= "<listName>".$ln."</listName>";
			 	$xml .= "<updates>";
					$xml .= "<Batch ListVersion='1' OnError='Continue'>";
						$xml .= "<Method Cmd='Delete' ID='1'>";
							if(is_array($fields)){
								while(list($k, $v) = each($fields)){
									$xml .= '<Field Name=\''.$k.'\'>'.$v.'</Field>';
								}
							}
						$xml .= "</Method>";
					$xml .= "</Batch>";
			 	$xml .= "</updates>";
			 $xml .= "</UpdateListItems>";

			$return = $this->fnWSCall('UpdateListItems', $xml);

			if(isset($return['UpdateListItemsResult']['Results']['Result'])){
				$results['status'] = 'success';
			}
			else
			{
				$results['status'] = 'failed';
			}

			return $results;
		}

		function fnLoadCache($cacheFile){

			ini_set('memory_limit','50M');

			if(file_exists($cacheFile)){

				if (($handle = fopen($cacheFile, "r")) !== FALSE) {

					$content = NULL;

					while (!feof($handle)) {
					   $content .= fgets($handle);
					}

					$cache = unserialize($content);

					fclose($handle);

					$cFileLoaded = 'true';
				}
				else
				{
					$cFileLoaded = 'false';
				}
			}
			else
			{
				$cFileLoaded = 'false';
			}

			if(isset($cache['total']) && $cache['total'] > 0){
				$results = $cache;
			}
			else
			{
				$results['details'][0] = NULL;
				$results['total'] = 0;
			}

			return $results;
		}

		public function fnCreateCache($d){

			if($d['total'] > 0){

				$a = array();
				$a['total'] = $d['total'];
				$a['datePublished'] = date('Y-m-d h:i:s');
				$a['details'] = $d['details'];

				$cache = serialize($a);

				return $cache;
			}
			else
			{
				return false;
			}
		}

		/*
			section: subsite name
			ln: GUID listName
			vn: GUID viewName
			postID: the id of the item to look up in the comments section
		*/
		function fnRemoveCommentsFromPost($section, $ln, $vn, $field, $value){
			$comments = $this->fnRetrieveListItem($section, $ln, $vn, $field, 'Eq', $value);

			if(isset($comments['details'])){
				while(list($id, $val) = each($comments['details'])){
					if(is_array($val)){
						while(list($k, $v) = each($val)){
							if($k == "!ows_ID"){
								$delItem = $this->fnRemoveItemFromList($section, $ln, $vn, array('ID' => $v));
							}
						}
					}
					else
					{
						if($id == "!ows_ID"){
							$delItem = $this->fnRemoveItemFromList($section, $ln, $vn, array('ID' => $val));
						}
					}
				}
			}
		}


		/*
			ln: GUID listName
			vn: GUID viewName
		*/
		function fnRetrieveList($section, $ln, $vn, $query = '', $fields = '', $limit = 150){

			//CHECK IF WE HAVE A CACHE FILE
			if($this->cacheFile == NULL || !file_exists($this->cacheFile)){
				$this->ln = $ln;
				$this->vn = $vn;
				$this->wsdl = $this->domain.''.$section.'/_vti_bin/Lists.asmx?wsdl';
				$comparison = NULL;

				$xml = '<GetListItems xmlns="http://schemas.microsoft.com/sharepoint/soap/">';
					$xml .= '<listName>'.$ln.'</listName>';
					$xml .= '<viewName>'.$vn.'</viewName>';
					$xml .= '<rowLimit>'.$limit.'</rowLimit>';

					if($query != ''){
						if(is_array($query)){
							$xml .= '<query>';
								$xml .= '<Query>';
									$xml .= '<Where>';

										while(list($id, $val) = each($query)){
											if(count($query) > 1){

												if(is_array($val)){
													while(list($k, $v) = each($val)){
														$xml .= '<'.$v['comparison'].'>';
															$xml .= '<FieldRef Name="'.$v['fieldRef'].'" />';
															if($v['comparison'] != 'IsNull' && $v['comparison'] != 'IsNotNull'){
																$xml .= '<Value Type="'.$v['type'].'">'.$v['value'].'</Value>';
															}
														$xml .= '</'.$v['comparison'].'>';
													}

													$xml .= '</'.$comparison.'>';
												}
												else
												{
													if(empty($comparison)){
														$comparison = $val;
														$xml .= '<'.$val.'>';
													}
												}


											}
											else
											{
												$xml .= '<'.$val['comparison'].'>';
													$xml .= '<FieldRef Name="'.$val['fieldRef'].'" />';
													if($val['comparison'] != 'IsNull' && $val['comparison'] != 'IsNotNull'){
														$xml .= '<Value Type="'.$val['type'].'">'.$val['value'].'</Value>';
													}
												$xml .= '</'.$val['comparison'].'>';
											}
										}

									$xml .= '</Where>';
								$xml .= '</Query>';
							$xml .= '</query>';
						}
					}
				$xml .= '</GetListItems>';

				$return = $this->fnWSCall('GetListItems', $xml);

				$results = array();

				if(isset($return['GetListItemsResult']['listitems']['data']['row'])){
					while(list($k, $v) = each($return['GetListItemsResult']['listitems']['data']['row'])){

						if(is_array($fields)){
							reset($fields);

							while(list($id, $val) = each($fields)){

								if(isset($v['!'.$val.''])){
									$results['details'][$k][$val] = $v['!'.$val.''];
								}
							}
						}
						else
						{
							if(is_array($v)){
								while(list($id, $val) = each($v)){
									$results['details'][$k][$id] = $val;
								}
							}
							else
							{
								$results['details'][0][$k] = $v;
							}
						}
					}

					$results['total'] = $return['GetListItemsResult']['listitems']['data']['!ItemCount'];

				}
				else
				{
					$results['details'][0] = NULL;
					$results['total'] = 0;
				}

				if($this->checkPermissions == TRUE){
					$this->fnCheckPermissions($section);
				}

				//$results['cache'] = $this->fnCreateCache($results);

				return $results;
			}
			else
			{
				return $this->fnLoadCache($this->cacheFile);
			}
		}

		function fnRetrieveListItem($section, $ln, $vn, $fieldRef, $search, $value, $type, $fields = NULL){

			$this->ln = $ln;
			$this->vn = $vn;
			$this->wsdl = $this->domain.''.$section.'/_vti_bin/Lists.asmx?wsdl';

			$xml = '<GetListItems xmlns="http://schemas.microsoft.com/sharepoint/soap/">
						<listName>'.$ln.'</listName>
						<query>
							<Query>
   								<Where>
      								<'.$search.'>
         								<FieldRef Name="'.$fieldRef.'" />
							         	<Value Type="'.$type.'">'.$value.'</Value>
      								</'.$search.'>
   								</Where>
							</Query>
						</query>
					</GetListItems>';

			$return = $this->fnWSCall('GetListItems', $xml);

			$results = array();

			if(isset($return['GetListItemsResult']['listitems']['data']['row'])){
				while(list($k, $v) = each($return['GetListItemsResult']['listitems']['data']['row'])){

					if(is_array($fields)){

						reset($fields);

						while(list($id, $val) = each($fields)){

							if(isset($v['!'.$val.''])){
								$results['details'][$k][$val] = $v['!'.$val.''];
							}
						}
					}
					else
					{
						$results['details'][$k] = $v;
					}
				}

				$results['total'] = $return['GetListItemsResult']['listitems']['data']['!ItemCount'];
				if(isset($results['details']['!ows_userid'])){
					define('USERID', $results['details']['!ows_userid']);
				}
				else
				{
					if(!defined('USERID')){
						define('USERID', '');
					}
				}
			}
			else
			{
				$results['total'] = 0;
			}

			//$results['cache'] = $this->fnCreateCache($results);

			return $results;
		}

		function fnAddAttachment($ln, $lID, $fname, $attch){

			$xml = '<AddAttachment xmlns="http://schemas.microsoft.com/sharepoint/soap/">
					  <listName>'.$ln.'</listName>
					  <listItemID>'.$lID.'</listItemID>
					  <fileName>'.$fname.'</fileName>
					  <attachment>'.base64_encode($attch).'</attachment>
					</AddAttachment>';

			$return = $this->fnWSCall('AddAttachment', $xml);
		}

		public function fnGetUserDetails($section, $name){

			$this->wsdl = $this->domain.''.$section.'/_vti_bin/UserGroup.asmx?wsdl';

			$xml = '<GetUserInfo xmlns="http://schemas.microsoft.com/sharepoint/soap/directory/">
					  <userLoginName>'.$name.'</userLoginName>
					</GetUserInfo>';

			$return = $this->fnWSCall('GetUserInfo', $xml);
			print_r($return);
			if(isset($return['GetUserInfoResult']['GetUserInfo']['User'])){
				return $return['GetUserInfoResult']['GetUserInfo']['User'];
			}
		}

		public function fnResolvePrincipals($section, $name){

			$this->wsdl = $this->domain.''.$section.'/_vti_bin/People.asmx?wsdl';

			$xml = '<ResolvePrincipals xmlns="http://schemas.microsoft.com/sharepoint/soap/">
					  <principalKeys>
						<string>idir\\rugraham</string>
					  </principalKeys>
					  <principalType>All</principalType>
					  <addToUserInfoList>false</addToUserInfoList>
					</ResolvePrincipals>';

			$return = $this->fnWSCall('ResolvePrincipals', $xml);

			print_r($return);
		}

		protected function fnGetUserGroupPermissions($section){

			$this->wsdl = $this->domain.''.$section.'/_vti_bin/UserGroup.asmx?wsdl';

			$xml = '<GetGroupCollectionFromUser xmlns="http://schemas.microsoft.com/sharepoint/soap/directory/">
	      				<userLoginName>idir\\'.SM_UNIVERSALID.'</userLoginName>
    				</GetGroupCollectionFromUser>';

			$return = $this->fnWSCall('GetGroupCollectionFromUser', $xml);
			print_r($return);
			if(isset($return['GetGroupCollectionFromUserResult']['GetGroupCollectionFromUser']['Groups']['Group'])){
				$result = $return['GetGroupCollectionFromUserResult']['GetGroupCollectionFromUser']['Groups']['Group'];

				while(list($k, $v) = each($result)){
					if(is_array($v)){
						$this->groupDetails[$k] = $v;
					}
					else
					{
						$this->groupDetails[0] = $return['GetGroupCollectionFromUserResult']['GetGroupCollectionFromUser']['Groups']['Group'];
					}
				}
			}
		}

		private function fnCheckPermissions($section){

			$userID = strtolower('idir\\'.SM_UNIVERSALID);
			$this->wsdl = $this->domain.''.$section.'/_vti_bin/Permissions.asmx?wsdl';

			$xml = '<GetPermissionCollection xmlns="http://schemas.microsoft.com/sharepoint/soap/directory/">
					  <objectName>'.$this->ln.'</objectName>
					  <objectType>List</objectType>
					</GetPermissionCollection>';

			$return = $this->fnWSCall('GetPermissionCollection', $xml);

			if(isset($return['GetPermissionCollectionResult']['GetPermissionCollection']['Permissions']['Permission'])){
				$result = $return['GetPermissionCollectionResult']['GetPermissionCollection']['Permissions']['Permission'];

				while(list($k, $v) = each($result)){

					if(isset($v['!UserLogin']) && strtolower($v['!UserLogin']) == $userID){
						if($v['!MemberIsUser'] == TRUE){
							$this->hasPermissions = TRUE;
						}
					}
					else
					{
						while(list($id, $val) = each($this->groupDetails)){

							if(isset($v['!GroupName']) && strtolower(rtrim($val['!Name'], ' ')) == strtolower(rtrim($v['!GroupName'], ' '))){

								if($v['!MemberGlobal'] == TRUE){
									array_push($this->myGroups, $v['!GroupName']);
									$this->hasPermissions = TRUE;
								}
							}
						}
					}

					reset($this->groupDetails);
				}
			}
		}

		function fnRetrieveCalendarList($section, $ln, $vn, $start = '', $end = '', $query = '', $fields = '', $limit = 200){

			//CHECK FOR RECURRING DATA
			if($start != '' && $end != ''){

				$this->ln = $ln;
				$this->vn = $vn;
				$this->wsdl = $this->domain.''.$section.'/_vti_bin/Lists.asmx?wsdl';

				$xml = '<GetListItems xmlns="http://schemas.microsoft.com/sharepoint/soap/">';
					$xml .= '<listName>'.$ln.'</listName>';
					$xml .= '<viewName>'.$vn.'</viewName>';
					$xml .= '<rowLimit>'.$limit.'</rowLimit>';

					if($query != ''){
						if(is_array($query)){
							$xml .= '<query>';
								$xml .= '<Query>';
									$xml .= '<Where>';
										while(list($id, $val) = each($query)){
											$xml .= '<'.$val['comparison'].'>';
												$xml .= '<FieldRef Name="'.$val['fieldRef'].'" />';
												if($val['comparison'] != 'IsNull'){
													$xml .= '<Value Type="'.$val['type'].'">'.$val['value'].'</Value>';
												}
											$xml .= '</'.$val['comparison'].'>';
										}
									$xml .= '</Where>';
								$xml .= '</Query>';
							$xml .= '</query>';
						}
					}
				$xml .= '</GetListItems>';

				$return = $this->fnWSCall('GetListItems', $xml);

				$results = array();

				if(isset($return['GetListItemsResult']['listitems']['data']['row'])){
					while(list($k, $v) = each($return['GetListItemsResult']['listitems']['data']['row'])){

						if(is_array($fields)){
							reset($fields);

							while(list($id, $val) = each($fields)){

								if(isset($v['!'.$val.''])){
									$results['details'][$k][$val] = $v['!'.$val.''];
								}
							}
						}
						else
						{
							if(is_array($v)){
								while(list($id, $val) = each($v)){
									$results['details'][$k][$id] = $val;
								}
							}
							else
							{
								$results['details'][0][$k] = $v;
							}
						}
					}

					$results['total'] = $return['GetListItemsResult']['listitems']['data']['!ItemCount'];

				}
				else
				{
					$results['details'][0] = NULL;
					$results['total'] = 0;
				}

				if($this->checkPermissions == TRUE){
					$this->fnCheckPermissions($section);
				}

				return $results;
			}
		}


		/*
			fnAddEvent - used to add an item to an event to a calendar

			$wsdl: the wsdl location for the list
			$ln: the list id for the list we want to get data from
			$vn: the view name for the list we want to get data from
			$formInputs: the fields we are posting

		*/
		function fnAddEvent($section, $ln, $vn, $formInputs, $repeat = FALSE, $autoPost = FALSE){

			$this->ln = $ln;
			$this->vn = $vn;
			$this->wsdl = $this->domain.''.$section.'/_vti_bin/Lists.asmx?wsdl';

			$xml = "<UpdateListItems xmlns='http://schemas.microsoft.com/sharepoint/soap/'>";
				$xml .= "<listName>".$ln."</listName>";
			 	$xml .= "<updates>";
					$xml .= "<Batch ListVersion='1' OnError='Continue'>";
						$xml .= "<Method Cmd='New' ID='1'>";
							while(list($k, $v) = each($formInputs)){
								$xml .= '<Field Name=\''.$k.'\'>'.$v.'</Field>';
							}

							if($repeat == TRUE){
								$xml .= '<Field Name="EventType">1</Field>';
								$xml .= '<Field Name="fRecurrence">1</Field>';
								$xml .= '<Field Name="RecurrenceData">';
									$xml .= "&lt;recurrence&gt;&lt;rule&gt;&lt;firstDayOfWeek&gt;su&lt;/firstDayOfWeek&gt;&lt;repeat&gt;&lt;weekly su='TRUE' th='TRUE' fr='TRUE' sa='TRUE' weekFrequency='1' /&gt;&lt;/repeat&gt;&lt;windowEnd&gt;2010-05-23T12:00:00Z&lt;/windowEnd&gt;&lt;/rule&gt;&lt;/recurrence&gt;";
								$xml .= '</Field>';
							}

						$xml .= "</Method>";
					$xml .= "</Batch>";
			 	$xml .= "</updates>";
			 $xml .= "</UpdateListItems>";

			$return = $this->fnWSCall('UpdateListItems', $xml);

			if(isset($return['UpdateListItemsResult']['Results']['Result']['row'])){

				$result = $return['UpdateListItemsResult']['Results']['Result']['row'];

				$results = array();
				$results['listItemID'] = $result['!ows_ID'];
				$results['owner'] = $result['!ows_userid'];
				$results['status'] = $result['!ows__ModerationStatus'];
				$results['status'] = 'success';

				//PUBLISH ITEM
				if($autoPost == TRUE){
					$this->fnApproveListItem($ln, $results['listItemID']);
				}
			}
			else
			{
				$results['status'] = 'failed';
			}

			if($this->checkPermissions == TRUE){
				$this->fnCheckPermissions($section);
			}

			return $results;
		}

		function fnExcel($section, $ln, $vn, $path){

			$this->ln = $ln;
			$this->vn = $vn;
			$this->wsdl = $this->domain.''.$section.'/_vti_bin/excelservice.asmx?wsdl';

			$xml = '<OpenWorkbook xmlns="http://schemas.microsoft.com/office/excel/server/webservices">
					  <workbookPath>'.$this->domain.''.$section.''.$path.'</workbookPath>
					  <uiCultureName>en-US</uiCultureName>
					  <dataCultureName>en-US</dataCultureName>
					</OpenWorkbook>';

			$return = $this->fnWSCall('OpenWorkbook', $xml);

			return $this->fnGetWorkBook($return['OpenWorkbookResult']);
		}

		function fnGetWorkBook($k){
			ini_set('memory_limit','50M');
			$xml = '<GetRangeA1 xmlns="http://schemas.microsoft.com/office/excel/server/webservices">
					  <sessionId>'.$k.'</sessionId>
					  <sheetName>qry_Default_Data_All_WEBSITE</sheetName>
					  <rangeName>qry_Default_Data_All_WEBSITE</rangeName>
					  <formatted>false</formatted>
					</GetRangeA1>';

			$return = $this->fnWSCall('GetRangeA1', $xml);
			$results = array();

			if(isset($return['GetRangeA1Result']['anyType'][0])){
				$results['details'] = $return['GetRangeA1Result'];
				$results['status'] = 'success';
				$results['total'] = 1;

			}
			else
			{
				$results['details'] = NULL;
				$results['status'] = 'failed';
				$results['total'] = 0;
			}

			$this->fnCloseWorkBook($k);

			return $results;
		}

		function fnCloseWorkBook($k){
			$xml = '<CloseWorkbook xmlns="http://schemas.microsoft.com/office/excel/server/webservices">
					  <sessionId>'.$k.'</sessionId>
					</CloseWorkbook>';
			$return = $this->fnWSCall('CloseWorkbook', $xml);
		}

		function fnGetTags($tag){

			$this->wsdl = $this->domain.'/_vti_bin/socialdataservice.asmx?wsdl';
			$xml = '<GetAllTagUrlsByKeyword xmlns="http://microsoft.com/webservices/SharePointPortalServer/SocialDataService">
					  <keyword>'.$tag.'</keyword>
					</GetAllTagUrlsByKeyword>';

			$return = $this->fnWSCall('GetAllTagUrlsByKeyword', $xml);


		}

		function fnSearch($s, $p, $l){

			$this->wsdl = $this->domain.'/SABC/sabc-search/_vti_bin/search.asmx?wsdl';

			if(!empty($s)){
				//FOR PAGINATION
				$start = (($p-1) == 0) ? 1 : ($l * ($p-1) + 1);
				$xml = '<QueryEx xmlns="http://microsoft.com/webservices/OfficeServer/QueryService">
						  <queryXml>&lt;?xml version="1.0" encoding="utf-8" ?&gt;
							&lt;QueryPacket xmlns="urn:Microsoft.Search.Query" Revision="1000"&gt;
							&lt;Query domain="QDomain"&gt;
							 &lt;SupportedFormats&gt;
								&lt;Format&gt;urn:Microsoft.Search.Response.Document.Document&lt;/Format&gt;
							 &lt;/SupportedFormats&gt;
							 &lt;Range&gt;&lt;StartAt&gt;'.$start.'&lt;/StartAt&gt;&lt;Count&gt;'.$l.'&lt;/Count&gt;&lt;/Range&gt;
							 &lt;Context&gt;
							  &lt;QueryText language="en-US" type="STRING" &gt;'.$s.'  SCOPE:"SABC"&lt;/QueryText&gt;
							 &lt;/Context&gt;
							&lt;SortByProperties&gt;&lt;SortByProperty name="Rank" direction="Descending" order="1"/&gt;&lt;/SortByProperties&gt;

							 &lt;EnableStemming&gt;true&lt;/EnableStemming&gt;
							 &lt;TrimDuplicates&gt;true&lt;/TrimDuplicates&gt;
							 &lt;IgnoreAllNoiseQuery&gt;true&lt;/IgnoreAllNoiseQuery&gt;
							 &lt;ImplicitAndBehavior&gt;true&lt;/ImplicitAndBehavior&gt;
							 &lt;IncludeRelevanceResults&gt;true&lt;/IncludeRelevanceResults&gt;
							&lt;/Query&gt;&lt;/QueryPacket&gt;
							</queryXml></QueryEx>';

				$return = $this->fnWSCall('QueryEx', $xml);

				$results = array();

				if(isset($return['QueryExResult']['diffgram']['Results'])){
					$results['total'] = $return['QueryExResult']['schema']['element']['complexType']['choice']['element']['!msprop:TotalRows'];
					$results['time'] = $return['QueryExResult']['schema']['element']['!msprop:ElapsedTime']/1000;
					$results['queryTerm'] = $return['QueryExResult']['schema']['element']['!msprop:QueryTerms'];
					$results['spellingSuggestion'] = $return['QueryExResult']['schema']['element']['!msprop:SpellingSuggestion'];
					$results['Results'] = 	$return['QueryExResult']['diffgram']['Results'];
				}
				else
				{
					$results['total'] = 0;
					$results['time'] = NULL;
					$results['queryTerm'] = (isset($return['QueryExResult']['schema']['element']['!msprop:QueryTerms'])) ? $return['QueryExResult']['schema']['element']['!msprop:QueryTerms'] : NULL;
					$results['spellingSuggestion'] = (isset($return['QueryExResult']['schema']['element']['!msprop:SpellingSuggestion'])) ? $return['QueryExResult']['schema']['element']['!msprop:SpellingSuggestion'] : NULL;
					$results['Results'] = 'failed';
				}
			}
			else
			{
				$results['total'] = 0;
				$results['time'] = NULL;
				$results['queryTerm'] = (isset($return['QueryExResult']['schema']['element']['!msprop:QueryTerms'])) ? $return['QueryExResult']['schema']['element']['!msprop:QueryTerms'] : NULL;
				$results['spellingSuggestion'] = (isset($return['QueryExResult']['schema']['element']['!msprop:SpellingSuggestion'])) ? $return['QueryExResult']['schema']['element']['!msprop:SpellingSuggestion'] : NULL;
				$results['Results'] = 'failed';
			}
			//print_r($results);
			return $results;
		}

		function fnWSCall($wsCall, $xml){

			//$exceptions = array('System.ServiceModel.FaultException`1', 'socket read of headers timed out');
			//$eLoop=0; //NUMBER OF TIMES WE RETRIED TO CALL - WE ONLY RECALL IF IT IS AN EXCEPTION WE HAVE REGISTERED IN ARRAY;
      $aved_systemuser = \Drupal::configFactory()->getEditable('sabc_private_data_form.settings')->get('aved_systemuser');
			$client = new nusoap_client($this->wsdl, true);
			$client->setCredentials($this->authU, $this->authP);
			$client->soap_defencoding='UTF-8';

			//Invoke the Web Service
			$result = $client->call($wsCall, $xml);

			$err = $client->getError();

			if($err != NULL){
				$errorMsg = "A low level error occurred (no action is needed) on URL: ".$this->wsdl.".\n Error occurred on ".date('F m, Y')." at ".date('g:i:s a')."\n \n Error Message:".$err."\n \nCall Made: ".$wsCall." \n \n Request Made: ".$xml;
				mail($aved_systemuser, 'SharePoint webservice notification', $errorMsg);
				return $err;
			}

			//Error check
			if(isset($fault)) {
				$errorMsg = "A critical error has occurred on URL: ".$this->wsdl."\n Error occurred on ".date('F m, Y')." at ".date('g:i:s a')."\n \n Error Message:".$fault."\n \nCall Made: ".$wsCall." \n \n Request Made: ".$xml;
					mail($aved_systemuser, 'SharePoint webservice critical error', $errorMsg);
				return $fault;
			}
			else
			{
				return $result;
			}
		}

	}
?>
