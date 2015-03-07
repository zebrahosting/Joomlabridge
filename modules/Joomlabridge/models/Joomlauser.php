<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Version of file: 1.1.4. at 2014-09-28
 ************************************************************************************/

require_once 'include/Webservices/Create.php';
require_once 'include/Webservices/Update.php';
require_once 'include/Webservices/Query.php';
require_once 'include/Webservices/Retrieve.php';
require_once 'include/Webservices/DescribeObject.php';
require_once 'includes/runtime/Globals.php';

class Joomlabridge_Joomlauser_Model extends Vtiger_Base_Model {

	const SKIP_UPDATE = 0; //0 - Force update Contacts; 1 - Skip already updated Contacts

	/**
	 * Function to get array  Joomla admin users group_ids.
	 * @param 
	 * @return $JoomlaAdmins - array
	 */
	public function getJoomlaadmins() {
		$JoomlaAdmins = array(
				'Super Users' 	=> 8, 
				'Administrator' => 7, 
				'Manager' 		=> 6
			);
		return $JoomlaAdmins;
	}
	
	/**
	 * Function to get array of Accounts/Organization field map.
	 * @param 
	 * @return $orgfields - array
	 */
	public function getOrgFields() {
		$orgfields = array(	
					'accountname',
					'accounttype', //set to 'Customer'
					'phone',
					'otherphone',
					'fax',
					'email1',
					'bill_street',
					'bill_pobox',
					'bill_city',
					'bill_state',
					'bill_code',
					'bill_country',
					'ship_street',
					'ship_pobox',
					'ship_city',
					'ship_state',
					'ship_code',
					'ship_country',
			);	//TO-DO: to handle vatid
		return $orgfields;
	}
	
	/**
	 * Function to get array of Contacts field map.
	 * @param 
	 * @return $contactsfields - array
	 */
	public function getContactsFields() {
		$contactsfields = array(	
					'salutationtype',
					'firstname', //set to 'Customer'
					'lastname',
					'phone',
					'otherphone',
					'fax',
					'email',
					'mailingstreet',
					'mailingcity',
					'mailingstate',
					'mailingzip',
					'mailingcountry',
					'otherstreet',
					'othercity',
					'otherstate',
					'otherzip',
					'othercountry',
			);	//TO-DO: to handle vatid
		return $contactsfields;
	}
	
	/**
	 * Function to get array of Contacts -> Accounts field map.
	 * @param 
	 * @return $mapfields - array
	 */
	public function getContactOrgMap() {
		$mapfields = array(	
					'accountname'	=> 'company',
					'phone'			=> 'phone',
					'otherphone'	=> 'otherphone',
					'fax'			=> 'fax',
					'email1'		=> 'email',
					'bill_street'	=> 'mailingstreet',
					'bill_pobox'	=> 'mailingpobox',
					'bill_city'		=> 'mailingcity',
					'bill_state'	=> 'mailingstate',
					'bill_code'		=> 'mailingzip',
					'bill_country'	=> 'mailingcountry',
					'ship_street'	=> 'otherstreet',
					'ship_pobox'	=> 'otherpobox',
					'ship_city'		=> 'othercity',
					'ship_state'	=> 'otherstate',
					'ship_code'		=> 'otherzip',
					'ship_country'	=> 'othercountry',
			); 	//TO-DO: to handle vatid
		return $mapfields;
	}
	
	/**
	 * Function to explode name to the first name and lastname
	 * @param $name -- string
	 * @return $names - array( $firstname, $lastname)
	 */
	public function explodeJNames($name) {
		$names = array( 'firstname' => '', 'lastname' => '' );
		$middletags = array(	'van', 'le',
								'de', 'der', 'den', 
								'te', 'ter', 'ten', 
								'thoe', 'thor', 
								'aan', 'op', 'in', 'uit',
								'over', 'onder', 'achter', 
								'bezuiden', 'boven', 'buiten', 'zonder', '-' );
		//Ref: http://en.wikipedia.org/wiki/Van_(Dutch)
		//split Joomla user's name into parts
		$nameelements = explode(' ', trim($name) );
		$nameindex = 0;
		if ( count($nameelements) > 1) {
			//identify the first name element that contains a '-' or any of the middle tags.		
			$j = 1;
			while ( $j <= count($nameelements) ) {
				$pos = strpos($nameelements[$j-1], '-');
				if ( in_array( strtolower($nameelements[$j-1]), $middletags) ||  $pos != false ) {
					$nameindex = $j;
					if ( $pos === 0 && 2 < $j) {
						$nameindex = $j-1;
					}
					$j = count($nameelements) + 1; //if the first occurrence is found exit from the while
				}
				$j++;
			}			
			if ( $nameindex > 1 ) { //right indexing
				//first name: concatenate all name elements (with space and trimming) before this index
				for( $i = 1; $i < $nameindex; $i++) {
					$names['firstname'] .= $nameelements[$i-1].' ';
				}			
				//last name: concatenate all name elements (with space and trimming) with this index to the end
				for( $i = $nameindex; $i <= count($nameelements); $i++) {
					$names['lastname'] .= $nameelements[$i-1]." ";
				}
				$names['firstname'] = trim( html_entity_decode( $names['firstname'] ) );
				$names['lastname'] = trim( html_entity_decode( $names['lastname'] ) );
			} else { //wrong indexing - no middle tags in the name or missing firstname
			
				$lastitem = count($nameelements)-1;
				$names['lastname'] = trim( html_entity_decode( $nameelements[$lastitem] ) );
				$names['firstname'] = '';
				for($i = 0; $i < count($nameelements)-1; ++$i) {
					$names['firstname'] .= $nameelements[$i]." ";
				}
				$names['firstname'] = trim( html_entity_decode( $names['firstname'] ) );
				
				if( in_array( strtolower($names['firstname']), $middletags) ) {
					//Missing first name: e.g. the name like "van Damn" or "de Boven"
					$names['firstname'] = '';
					$names['lastname'] = trim( html_entity_decode( $name ) );				
				}				
			}
		} else { //no explode
			$names['firstname'] = '';
			$names['lastname'] = trim( html_entity_decode( $name ) );
		}
		return $names;
	}

	/**
	 * Function to create a new Contact instance by VTWS API using Joomla userdata
	 * @param <Array> $juserfield -- Joomla user data array pulled from Joomla SQL
	 * @return <Boolen> True with success and False on fail
	 */
	public static function CreateContactFromJoomlaUser($juserfield) {
		global $log, $current_user;
		$adb = PearDatabase::getInstance();
		$log->debug('ENTERING --> Joomlabridge_Joomlauser_Model::CreateContactFromJoomlaUser()');
		
		vglobal('JdataPull', true);
		
		$ActiveAdmin = Users::getActiveAdminId();
		$user_names = Joomlabridge_Joomlauser_Model::explodeJNames( $juserfield['name'] );
//		$log->debug('#!#!#!#!# Joomlabridge_Joomlauser_Model::CreateContactFromJoomlaUser() -- exploded JUser name : '.print_r($user_names, true) );
		$Hikashopdata = false;
		try {
			$hikashop_installed = false;
			$jsql = new Joomlabridge_SQLHost_Model;
			$HikaState = $jsql->get('hika_installed');
			$hostpart = explode('x',$juserfield['joomlahostid']);
			$hikashop_installed = $HikaState[ $hostpart[1] ];

			if (class_exists('Joomlabridge_HikaShopUser_Model') && $hikashop_installed ) {
				//extend data with Hikashop User data
				
				//Get data from Hikashop instance
				$Hikashopdata = Joomlabridge_HikaShopUser_Model::getHikashopUserData($juserfield['joomlahostid'], $juserfield['id']);
				$log->debug('#!#!#!#!# Joomlabridge_Joomlauser_Model::UpdateContactFromJoomlaUser() -- HIKADATA : '.print_r($Hikashopdata, true) );
				if( $Hikashopdata ) {
				
					$contacts_fields = Joomlabridge_Joomlauser_Model::getContactsFields();
					
					$data = array ();
					foreach ($contacts_fields as $key => $fieldname ) {
						if( !empty($Hikashopdata[$fieldname]) && isset($Hikashopdata[$fieldname]) )
								$data[$fieldname] = $Hikashopdata[$fieldname];
					}					
					
					$data['lastname'] = $Hikashopdata['middle_name'].' '.$Hikashopdata['lastname'];
					$data['leadsource'] = 'Web Site';
					$data['assigned_user_id'] = '19x'.$ActiveAdmin;	// 19=Users Module ID, 1=First user Entity ID, but we uses the real active admin user ID (may it is not 1)
					$data['description'] = 'Hikashop buyer, IP address: '.$Hikashopdata['user_ip'].', Original Created Time: '.date("Y-m-d H:i:s", (int)$Hikashopdata['hika_createdtime']);
								
				} else {
					//Use only the Joomla User data
					$data = array (
						'lastname' 		=> $user_names['lastname'],
						'firstname'		=> $user_names['firstname'],
						'leadsource'  	=> 'Web Site',
						'email'  		=> $juserfield['email'],
						'description'	=> 'created by automated process at data pull from Joomla',
						'assigned_user_id' => '19x'.$ActiveAdmin , 
						// 19=Users Module ID, 1=First user Entity ID, but we uses the real active admin user ID (may it is not 1)
					);
				}
			} else {
				//Use only the Joomla User data
				$data = array (
					'lastname' 		=> $user_names['lastname'],
					'firstname'		=> $user_names['firstname'],
					'leadsource'  	=> 'Web Site',
					'email'  		=> $juserfield['email'],
					'description'	=> 'created by automated process at data pull from Joomla',
					'assigned_user_id' => '19x'.$ActiveAdmin , 
					// 19=Users Module ID, 1=First user Entity ID, but we uses the real active admin user ID (may it is not 1)
				);
			}
		//@@@@@@@@@ trigger Event: Before Create Contact from Joomla
			$em = new VTEventsManager($adb);
			$ndata = array();
			//init data for the Custom Events  
			$ndata['contactid']		= 0;
			$ndata['joomlahostid']	= $juserfield['joomlahostid'];
			$ndata['juserid'] 		= $juserfield['id'];
			foreach ($data as $key => $value) {
				$ndata[ $key ] = $data[ $key ];
			}
			$em->triggerEvent('vtiger.contact.beforecreatefromjoomla', $ndata);
		//@@@@@@@@@ end trigger 
$log->debug('DATASET before create New Org - $data : '.print_r($data, true) );
			// Check the Company field and if it is set, create an Accounts (Company) for the Contact
			if( $Hikashopdata && isset($Hikashopdata['company']) && !empty($Hikashopdata['company']) && ($Hikashopdata['company'] != '') ) {
				//prepare data for the Accounts/Organization creation
				$org_fields_mapped = Joomlabridge_Joomlauser_Model::getContactOrgMap();			
				$orgdata = array();
				foreach ($org_fields_mapped as $orgf => $contactf) {	
					$orgdata[$orgf] = $Hikashopdata[$contactf];
				}
				$log->debug('Gathered org-data: '.print_r($orgdata, true) );
								
				// Create a new organization
				$NewOrganization = Joomlabridge_Joomlauser_Model::CreateOrgFromJoomla($orgdata);
				$log->debug('###!!!!!### Created new-org: '.print_r($NewOrganization, true) );
				if ($NewOrganization) {
					//get the Account_id
					$account_id = $NewOrganization['id'];
					//Set account_id for the Contacts: 	$thisContact['account_id'] = $account_id (from the Accounts record
					if( isset($account_id) && !empty($account_id) && ($account_id != 0) ) {
						$data['account_id'] = $account_id;
					}
				}
			}
			
			$newcontact = vtws_create('Contacts', $data, $current_user);
			$log->debug('Created New Contact: '.print_r($newcontact, true) );

		//@@@@@@@@@ trigger Event: After Create Contact from Joomla
			$em = new VTEventsManager($adb);
			$em->triggerEvent('vtiger.contact.aftercreatefromjoomla', $newcontact);
		//@@@@@@@@@ end trigger

		} catch (WebServiceException $ex) {
			$log->fatal('@@@ vtws_create - Contact - failed: '.print_r($ex->getMessage(), true) );
			return false;
		}
		
		if ( $newcontact ) {
			//Create the New Contact->Joomlabridge instance
			try {
				//User Level Picklist item mapping
				$juserlevelpicklistitems = Joomlabridge_FieldMap_Model::PicklistItemsToStore($juserfield['joomla_userlevels']);
				
				// Data mapping		
				$jbdata = array();
				$vtfields = Joomlabridge_FieldMap_Model::getJUserSyncVTFields();
				foreach ( $vtfields as $key => $value ) {
					$vtjname = explode( '_', $value );
					$jbdata[$value] = $juserfield[$vtjname[1]];							
				}
				// additional settings
				$jbdata['contactid'] 			= $newcontact['id'];
				$jbdata['jhostid'] 				= $juserfield['joomlahostid'];
				$jbdata['joomla_userlevels'] 	= $juserlevelpicklistitems; //prepared string to store
				$jbdata['last_pull_date'] 		= $juserfield['jlastsyncdate'];
				$jbdata['assigned_user_id'] 	= '19x'.$ActiveAdmin; // 19=Users Module ID, 1=First user Entity ID
				// For the new instance in the case of Pull action
				$jbdata['last_push_date'] 	= '0000-00-00 00:00:00';
				$jbdata['description'] 		= 'created by automated process at data Pull from Joomla';
				if( $Hikashopdata ) {
					$jbdata['hika_user'] 		= 1; //this user is a Hikashop user
				}
				
				$newbridge = vtws_create('Joomlabridge', $jbdata, $current_user);

				$log->debug('###!!!!!### Created New Joomlabridge: '.print_r($newbridge, true) );

				if ( $newbridge ) {
					$ContactIdParts = explode('x',$newcontact['id']);
					$JBridgeIdParts = explode('x',$newbridge['id']);
					$parentModuleModel = Vtiger_Module_Model::getInstance('Contacts');
					$relatedModule = Vtiger_Module_Model::getInstance('Joomlabridge');
					$relationModel = Vtiger_Relation_Model::getInstance($parentModuleModel, $relatedModule);
					$relationModel->addRelation($ContactIdParts[1], $JBridgeIdParts[1]);
				}

			} catch (WebServiceException $ex) {
				$log->fatal('@@@ vtws_create - Joomlabridge - failed: '.print_r($ex->getMessage(), true) );
				return false;
			}		
		
		}
		return true;
	}

	/**
	 * Function to update a Contact by VTWS API using Joomla userdata
	 * @param <String> $wsid -- webservice ID of the Contact to be updated
	 * @param <Array>  $juserfield -- Joomla user data pulled from Joomla SQL
	 * @return <Boolen> True with success and False in failor
	 */
	public static function UpdateContactFromJoomlaUser($wsid, $juserfield) {
		global $log, $current_user;
		$log->debug("ENTERING --> Joomlabridge_Joomlauser_Model::UpdateContactFromJoomlaUser( $wsid, + juserfield_data )");
		
		vglobal('JdataPull', true);
		
		$ActiveAdmin = Users::getActiveAdminId();
		
		//User Level Picklist item mapping
		$juserlevelpicklistitems = Joomlabridge_FieldMap_Model::PicklistItemsToStore($juserfield['joomla_userlevels']);
		$Hikashopdata = false;		
		// Get Contact data to perform Update (else the not provided fields will be deleted)
		try {
			$thisContact = vtws_retrieve($wsid, $current_user);
//			$log->debug('### Retrieve Contact data: '.print_r($thisContact, true) );

		} catch (WebServiceException $ex) {
			$log->fatal('@@@ vtws_retrieve - Contact - failed: '.print_r($ex->getMessage(), true) );
			return false;
		}							
		// Contact Update - Update email field or address fields
		try {
			$hikashop_installed = false;
			$jsql = new Joomlabridge_SQLHost_Model;
			$HikaState = $jsql->get('hika_installed');
			$hostpart = explode('x',$juserfield['joomlahostid']);
			$hikashop_installed = $HikaState[ $hostpart[1] ];
			
			$log->debug("#!#!#!#!# JHOST: ".$hostpart[1]." , HikaState : ".print_r($HikaState, true) );

			if (class_exists('Joomlabridge_HikaShopUser_Model') && $hikashop_installed ) {
				//extend data with Hikashop User data
				
				//Get data from Hikashop instance
				$Hikashopdata = Joomlabridge_HikaShopUser_Model::getHikashopUserData($juserfield['joomlahostid'], $juserfield['id']);
				$log->debug('#!#!#!#!# Joomlabridge_Joomlauser_Model::UpdateContactFromJoomlaUser() -- HIKADATA : '.print_r($Hikashopdata, true) );
				if( $Hikashopdata ) {
					
					$contacts_fields = Joomlabridge_Joomlauser_Model::getContactsFields();
					
					foreach ($contacts_fields as $key => $fieldname ) {
						if( !empty($Hikashopdata[$fieldname]) && isset($Hikashopdata[$fieldname]) )
							$thisContact[$fieldname] = $Hikashopdata[$fieldname];
					}
					$thisContact['lastname'] = $Hikashopdata['middle_name'].' '.$Hikashopdata['lastname'];
					$thisContact['leadsource'] = 'Web Site';
					$thisContact['description'] = 'Updated from Joomla at '.$juserfield['jlastsyncdate'].', Hikashop buyer, IP address: '
							.$Hikashopdata['user_ip'].', Original created time in HikaShop: '.date("Y-m-d H:i:s", (int)$Hikashopdata['hika_createdtime']);
								
				} else {
					$log->debug('#!#!#!#!# NOT AVAILABLE - HIKADATA, using JUser data ONLY : '.print_r($hikashop_installed, true) );
					//Use only the Joomla User data
					$thisContact['email'] 		= $juserfield['email'];
					$thisContact['description'] = 'Updated from Joomla at '.$juserfield['jlastsyncdate'];				
// special repair names			
					$user_names = Joomlabridge_Joomlauser_Model::explodeJNames( $juserfield['name'] );
					// $log->debug('#!#!#!#!# Joomlabridge_Joomlauser_Model::UpdateContactFromJoomlaUser() -- exploded JUser name : '.print_r($user_names, true) );

					$thisContact['lastname'] 		= $user_names['lastname'];
					$thisContact['firstname'] 		= $user_names['firstname'];
// end of names repair --- this part is should be removed after tests
				}
			} else {
				$log->debug('#!#!#!#!# NOT REQUIRED - HIKADATA, using JUser data : '.print_r($hikashop_installed, true) );
				//Use only the Joomla User data
				$thisContact['email'] 		= $juserfield['email'];
				$thisContact['description'] = 'Updated from Joomla at '.$juserfield['jlastsyncdate'];

// special repair names			
				$user_names = Joomlabridge_Joomlauser_Model::explodeJNames( $juserfield['name'] );
				$log->debug('#!#!#!#!# Joomlabridge_Joomlauser_Model::UpdateContactFromJoomlaUser() -- exploded JUser name : '.print_r($user_names, true) );

				$thisContact['lastname'] 		= $user_names['lastname'];
				$thisContact['firstname'] 		= $user_names['firstname'];
// end of names repair --- this part is should be removed after tests
			}
			$log->debug('#!#!#!#!# THIS CONTACT before update : '.print_r($thisContact, true) );
			
//// Update Accounts related to the Contact if it exists  [account_id]

			// Check the account_id field and if it is set, update the Account (Company) for the Contact
			if( $Hikashopdata && isset($thisContact['account_id']) && !empty($thisContact['account_id']) && ($thisContact['account_id'] != '') ) {
				//prepare data for the Accounts/Organization updates
				$org_fields_mapped = Joomlabridge_Joomlauser_Model::getContactOrgMap();			
				$orgdata = array();
				foreach ($org_fields_mapped as $orgf => $contactf) {	
					$orgdata[$orgf] = $Hikashopdata[$contactf];
				}
				
				$log->debug('Prepared org-data for update: '.print_r($orgdata, true) );
				$log->debug('Related Contact data: '.print_r($thisContact, true) );
			
				//update organization process
				$updatedorg = Joomlabridge_Joomlauser_Model::UpdateOrgFromJoomla($thisContact['account_id'], $Hikashopdata);
			
			} else {
				// check if it needs to create a new organization				
				if( $Hikashopdata && isset($Hikashopdata['company']) && !empty($Hikashopdata['company']) && ($Hikashopdata['company'] != '') ) {
					//prepare data for the Accounts/Organization creation
					$org_fields_mapped = Joomlabridge_Joomlauser_Model::getContactOrgMap();			
					$orgdata = array();
					foreach ($org_fields_mapped as $orgf => $contactf) {	
						$orgdata[$orgf] = $Hikashopdata[$contactf];
					}
					// $log->debug('Gathered org-data: '.print_r($orgdata, true) );
					
					// Create a new organization
					$NewOrganization = Joomlabridge_Joomlauser_Model::CreateOrgFromJoomla($orgdata);
					$log->debug('###!!!!!### Created new-org: '.print_r($NewOrganization, true) );
					if ($NewOrganization) {
						//get the Account_id
						$account_id = $NewOrganization['id'];
						
						//Set account_id for the Contacts: 	$thisContact['account_id'] = $account_id (from the Accounts record
						if( isset($account_id) && !empty($account_id) && ($account_id != 0) ) {
							$thisContact['account_id'] = $account_id;
						}
					}
				}
			}
			
			$updatecontact = vtws_update($thisContact, $current_user);
			$log->debug('###!!!!!### Updated Contact: '.print_r($updatecontact, true) );
		//@@@@@@@@@ trigger Update Contact from Joomla Event
			$adb = PearDatabase::getInstance();
			$em = new VTEventsManager($adb);
			$data = array();
			//init data for the Custom Events  
			$data['contactid']		= $thisContact['id'];
			$data['joomlahostid']	= $juserfield['joomlahostid'];
			$data['juserid'] 		= $juserfield['id'];
			foreach ($thisContact as $key => $value) {
				$data[ $key ] = $thisContact[ $key ];
			}
			$em->triggerEvent('vtiger.contact.updatefromjoomla', $data);
		//@@@@@@@@@ end trigger 

		} catch (WebServiceException $ex) {
			$log->fatal('@@@ vtws_update - Contact - failed: '.print_r($ex->getMessage(), true) );
			return false;
		}

		// Data mapping						
		$jbdata = array();
		$vtfields = Joomlabridge_FieldMap_Model::getJUserSyncVTFields();
		foreach ( $vtfields as $key => $value ) {
			$vtjname = explode( '_', $value );
			$jbdata[$value] = $juserfield[$vtjname[1]];							
		}
		// additional settings
		$jbdata['contactid'] 			= $wsid;
		$jbdata['jhostid'] 				= $juserfield['joomlahostid'];
		$jbdata['joomla_userlevels'] 	= $juserlevelpicklistitems; //prepared string to store
		$jbdata['last_pull_date'] 		= $juserfield['jlastsyncdate'];
		$jbdata['assigned_user_id'] 	= '19x'.$ActiveAdmin; // 19=Users Module ID, 1=First user Entity ID
		
		if( $Hikashopdata ) {
			$jbdata['hika_user'] 		= 1; //this user is a Hikashop user
		}
		
		// Check the Joomlabridge instance to this Contact
		$JB2Instance = Joomlabridge_Joomlauser_Model::getJoomlabridgebyContactID($juserfield['joomlahostid'], $wsid);
//		$log->debug('###!!!!!### getJoomlabridgebyContactID query: '.print_r($JB2Instance, true) );
		
		if ( !$JB2Instance ) {
			//The Contact->Joomlabridge instance has NOT found so let create a new Joomlabridge instance
			$log->debug('###!!!!!### Joomla user id = '.print_r($wsid, true).' did not find in the Joomlabridge module.');							
		
			try {
				// For the new instance in the case of Pull action
				$jbdata['last_push_date'] 	= '0000-00-00 00:00:00';
				$jbdata['description'] 		= 'created by automated process at data Pull from Joomla';

				$newbridge = vtws_create('Joomlabridge', $jbdata, $current_user);

//				$log->debug('###!!!!!### Created New Joomlabridge: '.print_r($newbridge, true) );
				if ( $newbridge ) {
					$ContactIdParts = explode('x',$wsid);
					$JBridgeIdParts = explode('x',$newbridge['id']);
					$parentModuleModel = Vtiger_Module_Model::getInstance('Contacts');
					$relatedModule = Vtiger_Module_Model::getInstance('Joomlabridge');
					$relationModel = Vtiger_Relation_Model::getInstance($parentModuleModel, $relatedModule);
					$relationModel->addRelation($ContactIdParts[1], $JBridgeIdParts[1]);
				}
				
			} catch (WebServiceException $ex) {
				$log->fatal('@@@ vtws_create - Joomlabridge - failed: '.print_r($ex->getMessage(), true) );
				return false;
			}
		
		} else {
			//Update the Joomlabridge instance to this Contact
			$log->debug('###!!!!!### Joomlabridge to update for Contact ID: '.print_r($wsid, true));
			// Get Joomlabridge data to perform Update (else the not provided fields will be deleted)
			$jbwsid = $JB2Instance[0]['id'];
			try {
				$thisJBInstance = vtws_retrieve($jbwsid, $current_user);
//				$log->debug('### Retrieve Joomlabridge data: '.print_r($thisJBInstance, true) );

			} catch (WebServiceException $ex) {
				$log->fatal('@@@ vtws_retrieve - Joomlabridge - failed: '.print_r($ex->getMessage(), true) );
				return false;
			}
			
			// Joomlabridge record Update
			try {
				// Data mapping	for the update					
				foreach ( $vtfields as $key => $value ) {
					$vtjname = explode( '_', $value );
					$thisJBInstance[$value] = $juserfield[$vtjname[1]];							
				}
				
//				$log->debug('###!!!!!### Updated Joomlabridge, used Userfields '.print_r($juserfield, true) );
				
				$thisJBInstance['joomla_userlevels'] 	= $juserlevelpicklistitems; //prepared string to store
				$thisJBInstance['last_pull_date'] 		= $juserfield['jlastsyncdate'];
				
				if( $Hikashopdata ) {
					$thisJBInstance['hika_user'] 		= 1; //this user is a Hikashop user
				}
				
				$updateJB = vtws_update($thisJBInstance, $current_user);
//				$log->debug('###!!!!!### Updated Joomlabridge: '.print_r($updateJB, true) );

			} catch (WebServiceException $ex) {
				$log->fatal('@@@ vtws_update - Joomlabridge - failed: '.print_r($ex->getMessage(), true) );
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Function to create Organization (Accounts) from the Joomla/Hikashop user data
	 * @param Array $data -- all available data gathered from the Joomla/Hikashop instance
	 * @return Array/Object - of the record or FALSE
	 */
	public function CreateOrgFromJoomla($data) {
		global $log, $current_user;
		$log->debug("ENTERING --> Joomlabridge_Joomlauser_Model::CreateOrgFromJoomla( Data - array )");		
		vglobal('JdataPull', true);
		$ActiveAdmin = Users::getActiveAdminId();
		
//		$module = 'Accounts';  
		try {	

			$data['assigned_user_id'] = '19x'.$ActiveAdmin;	// 19=Users Module ID, 1=First user Entity ID, but we uses the real active admin user ID (may it is not 1)
			$data['accounttype'] = 'Customer';
			
			$neworg = vtws_create('Accounts', $data, $current_user);
			$log->debug('Created New Organization/Account: '.print_r($neworg, true) );			
			
			return $neworg;
			
		} catch (WebServiceException $ex) {
			$log->fatal('@@@ vtws_create - Accounts, at Joomlabridge_Joomlauser_Model::CreateOrgFromJoomla() - failed: '.print_r($ex->getMessage(), true) );
			return false;
		}
/*
			$parentModuleModel = Vtiger_Module_Model::getInstance('Contacts');
			$parentRecordId = $Contact_record_id;
			$relatedModule = Vtiger_Module_Model::getInstance('Joomlabridge');
			$relatedRecordId = $Joomlabridge_record_id;

			$relationModel = Vtiger_Relation_Model::getInstance($parentModuleModel, $relatedModule);
			$relationModel->addRelation($parentRecordId, $relatedRecordId);
*/
	}
	
	/**
	 * Function to update Organization (Accounts) from the Joomla/Hikashop user data
	 * @param Array $data -- all available data gathered from the Joomla/Hikashop instance
	 * @return Array/Object - of the record or FALSE
	 */
	public function UpdateOrgFromJoomla($wsid, $data) {
		global $log, $current_user;
		$log->debug("ENTERING --> Joomlabridge_Joomlauser_Model::UpdateOrgFromJoomla( $wsid, data )");

		// Get Organization data to perform Update (else the not provided fields will be deleted)
		try {
			$thisorg = vtws_retrieve($wsid, $current_user);
//			$log->debug('### Retrieve Organization data: '.print_r($thisorg, true) );

		} catch (WebServiceException $ex) {
			$log->fatal('@@@ vtws_retrieve - Organization - failed: '.print_r($ex->getMessage(), true) );
			return false;
		}
		
//		$module = 'Accounts';
		try {
			//field mapping
			$org_fields_mapped = Joomlabridge_Joomlauser_Model::getContactOrgMap();			
			foreach ($org_fields_mapped as $orgf => $hikaf) {
				if( isset($data[$hikaf]) && !empty($data[$hikaf]) ) {
					$thisorg[$orgf] = $data[$hikaf];
				}
			}
			$thisorg['description'] = 'Updated from Joomla - Hikashop';
			
			$updatedorg = vtws_update($thisorg, $current_user);
			$log->debug('###!!!!!### Updated Organization: '.print_r($updatedorg, true) );			
			
			return $updatedorg;
			
		} catch (WebServiceException $ex) {
			$log->fatal('@@@ vtws_update - Accounts, at Joomlabridge_Joomlauser_Model::UpdateOrgFromJoomla() - failed: '.print_r($ex->getMessage(), true) );
			return false;
		}
/*
			$parentModuleModel = Vtiger_Module_Model::getInstance('Contacts');
			$parentRecordId = $Contact_record_id;
			$relatedModule = Vtiger_Module_Model::getInstance('Joomlabridge');
			$relatedRecordId = $Joomlabridge_record_id;

			$relationModel = Vtiger_Relation_Model::getInstance($parentModuleModel, $relatedModule);
			$relationModel->addRelation($parentRecordId, $relatedRecordId);
*/
	}	
	

	/**
	 * Returns the List of Matching Contact records with the Email Address
	 * @param Email Address $email
	 * @return Array/Object - of the record or FALSE
	 */
	public function getContactbyEmail($email) {
		global $log;
		$log->debug("ENTERING --> Joomlabridge_Joomlauser_Model::getContactbyEmail( $email )");		
		
		$module = 'Contacts';
		try {			
			$results = Joomlabridge_Joomlauser_Model::lookupModuleRecordsWithEmail($module, $email);
			return $results;

		} catch (WebServiceException $ex) {
			$log->fatal('@@@ Joomlabridge_Joomlauser_Model::lookupModuleRecordsWithEmail($module, $email) -- failed: '.print_r($ex->getMessage(), true) );
			return false;
		}		
	}
	
	/**
	 * Returns the Matching Joomlabridge record defined by contactid and HostId
	 * @param Integer $HostId - Joomla Host Instance ID of the Bridge record (ws format: 36x5)
	 * @param Integer $contactid - vtiger Contact record ID related to the Bridge record (ws format: 12x21)
	 * @return Array/Object - of the record or FALSE
	 */	
	public static function getJoomlabridgebyContactID($HostId, $contactid) {
		global $log, $current_user;
		$log->debug("ENTERING --> Joomlabridge_Joomlauser_Model::getJoomlabridgebyContactID( $HostId, $contactid )");
		
		$module = 'Joomlabridge';
		try {
			$query = "SELECT * FROM ".$module." WHERE contactid = $contactid  AND jhostid = $HostId;";		
			$qresults = vtws_query( $query, $current_user );

			return $qresults;

		} catch (WebServiceException $ex) {
			$log->fatal('@@@ vtws_query -- Joomlabridge -- failed: '.print_r($ex->getMessage(), true) );
			return false;
		}	
	}
	
	/**
	 * Returns the Matching (multiple) Joomlabridges record defined by contactid only
	 * @param Integer $contactid - vtiger Contact record ID related to the Bridge record (ws format: 12x21)
	 * @return Array/Object - of the record or FALSE
	 */	
	public static function getJoomlabridgesByConID($contactid) {
		global $log, $current_user;
		$log->debug("ENTERING --> Joomlabridge_Joomlauser_Model::getJoomlabridgesByConID( $contactid )");
		
		$module = 'Joomlabridge';
		try {
			$query = "SELECT * FROM ".$module." WHERE contactid = $contactid ;";		
			$qresults = vtws_query( $query, $current_user );

			return $qresults;

		} catch (WebServiceException $ex) {
			$log->fatal('@@@ vtws_query -- Joomlabridge -- failed: '.print_r($ex->getMessage(), true) );
			return false;
		}	
	}

	/**
	 * Returns the Matching Joomlabridge record defined by Joomla UserID ('juser_id') and HostId
	 * @param Integer $HostId  - Joomla Host Instance ID of the Bridge record
	 * @param Integer $juserid - Joomla User ID
	 * @return Array/Object - of the record or FALSE
	 */	
	public static function getContactJoomlaBridgebyJUserID($HostId, $juserid) {
		global $log, $current_user;
		$log->debug("ENTERING --> Joomlabridge_Joomlauser_Model::getContactJoomlaBridgebyJUserID( $HostId, $juserid )");
		
		$module = 'Joomlabridge';
		try {
			$query = "SELECT * FROM ".$module." WHERE juser_id = $juserid AND jhostid = $HostId;";		
			$qresults = vtws_query( $query, $current_user );

			return $qresults;

		} catch (WebServiceException $ex) {
			$log->fatal('@@@ vtws_query -- Joomlabridge -- failed: '.print_r($ex->getMessage(), true) );
			return false;
		}	
	}
	
	/**
	 * Returns the Matching Contact records with the Joomla User ID, stored in the 'juserid' field
	 * @param Integer $juserid - Joomla User ID
	 * @return Array/Object - of the record or FALSE
	 */	
	public static function getLastPullDateJoomlaBridgebyJUserID($HostId, $juserid) {
		global $log, $current_user;
		$log->debug("ENTERING --> Joomlabridge_Joomlauser_Model::getLastPullDateJoomlaBridgebyJUserID( $HostId, $juserid )");
		
		$module = 'Joomlabridge';
		try {
			$query = "SELECT last_pull_date FROM ".$module." WHERE juser_id = $juserid AND jhostid = $HostId;";		
			$qresults = vtws_query( $query, $current_user );
			if ( $qresults ) {
				$lastpulldate = $qresults[0]['last_pull_date'];
			} else {
				$lastpulldate = '1980-01-01 00:00:01'; //practically null, or nothing
			}
//			$log->debug("###!!!!!### getLastPullDateJoomlaBridgebyJUserID( $HostId, $juserid ): ".print_r($lastpulldate, true));
			return $lastpulldate;

		} catch (WebServiceException $ex) {
			$log->fatal('@@@ vtws_query -- Joomlabridge -- failed: '.print_r($ex->getMessage(), true) );
			return false;
		}	
	}

	/**
	 * Function to Pull Joomla user data for multiple Joomla instances and sync into the VT Contacts / Joomlabridges by CronJob
	 * @param mixed $JHostId -- Id of the Joomla Host instance (in ws format e.g. 36x5)
	 * @param int $offset -- Database query limit start or offset
	 * @param int $row_count -- Database query limit row_count
	 * @return <Array> of synchronization statistic data
	 */		
	public function syncJoomlaUsertoVT($JHostId, $offset, $row_count) {
		global $log, $current_user;
		$log->debug("ENTERING --> Joomlabridge_Joomlauser_Model::syncJoomlaUsertoVT( $JHostId, $offset, $row_count )");
		
		vglobal('JdataPull', true); //this is the pull only routin
		
		//get Joomla SQL data
		$jdata_array = Joomlabridge_Joomlauser_Model::getJSQLUserData($JHostId, $offset, $row_count);	
//		$log->debug('$jdata_array to sync: '.print_r($jdata_array, true));
		
		$sync_stat = array();
		$sync_stat['handled'] = 0;
		$sync_stat['created'] = 0;
		$sync_stat['updated'] = 0;
		$sync_stat['skipped'] = 0;
		$sync_stat['failed'] = 0;
		$sync_stat['error_message'] = '';
		// a loop for all queried Joomla user data
		// $jdata_array ( array( $juserIds array( $juserfield array( $userdata) ) ) )
		foreach ( $jdata_array as $juserId => $juserfield) {
			$sync_stat['handled']++;
			if ( $juserfield['is_joomlaadmin'] == 0 ) { // 'is_joomlaadmin' = 1 filtered
//				$log->debug('Joomla user data to sync: '.print_r($juserfield, true));
				
				$ContactAction = ''; //Choose to right Contact action
				//Check by Joomla User e-mal - Does this user already exist in the Contacts module?
				$ContactInstance = Joomlabridge_Joomlauser_Model::getContactbyEmail( $juserfield['email'] );
//				$log->debug('###!!!!!### ContactInstance by email: '.print_r($ContactInstance, true));
				
				if ( !$ContactInstance ) {
					//The Contact instance has NOT found so double check by JUserID
//					$log->debug('###!!!!!### Joomla user email did not find in the Contacts module: '.print_r($juserfield['email'], true));
					$JBInstance = Joomlabridge_Joomlauser_Model::getContactJoomlaBridgebyJUserID($juserfield['joomlahostid'], $juserfield['id']);
					
					if ( !$JBInstance ) {
					//The Contact/Joomlabridge instance has NOT found so it is a New Contact to create
//					$log->debug('###!!!!!### Joomla user id did not find in the Joomlabridge module: '.print_r($juserfield['id'], true));
					
					$ContactAction = 'NEW';
					} else {
						//The Joomlabridge instance has found so we should prepare the update/sync and make relation if it is necessary
						$ContactAction = 'UPDATE';
					}
				} else {
					//The Contact instance has found so we should prepare the update/sync
					$ContactAction = 'UPDATE';
					$wsid = $ContactInstance[0]['wsid'];
					$contact_id = $ContactInstance[0]['id'];
				}
				switch ($ContactAction) {
					case 'NEW':
						$time_start = microtime(true);
						// params: $juserfield 	-> Joomla user data pulled from Joomla SQL							
						if ( !Joomlabridge_Joomlauser_Model::CreateContactFromJoomlaUser($juserfield) ) {
							$log->fatal('@@@ Joomlabridge_Joomlauser_Model::CreateContactFromJoomlaUser($juserfield) --- failed at $juserfield = '.print_r($juserfield, true) );
							$sync_stat['failed']++;
							$sync_stat['error_message'] .= 'Joomlabridge_Joomlauser_Model::CreateContactFromJoomlaUser failed at JUserId : '.
											$juserfield['id'].' ('.$juserfield['name'].') in JoomlaInstance ID : '.$juserfield['joomlahostid'];
						} else {
							$sync_stat['created']++;
							$time_end = microtime(true);
							$time = $time_end - $time_start;
			
							$log->debug("##### Creating time NEW Contact : ".print_r($time, true) );
						}
						break;
					case 'UPDATE':
					
						$ldate = Joomlabridge_Joomlauser_Model::getLastPullDateJoomlaBridgebyJUserID( $juserfield['joomlahostid'], $juserfield['id'] );
					
						$lastsyncdate = new DateTime($ldate);
						$lastvisitdate = new DateTime($juserfield['lastvisitdate']);
												
						if ( $lastvisitdate < $lastsyncdate  && self::SKIP_UPDATE ) {
							//the Contact already synchronized -> nothing to do
							$sync_stat['skipped']++;
						
						} else {
							$time_start = microtime(true);
							// params: $wsid 		-> webservice ID of the Contact to be updated
							// params: $juserfield 	-> Joomla user data pulled from Joomla SQL
							if ( !Joomlabridge_Joomlauser_Model::UpdateContactFromJoomlaUser($wsid, $juserfield) ) {
								$log->fatal('@@@ Joomlabridge_Joomlauser_Model::UpdateContactFromJoomlaUser($wsid, $juserfield) --- failed at $wsid = '.
												print_r($wsid, true). ' and $juserfield = '.print_r($juserfield, true) );
								$sync_stat['failed']++;
								$sync_stat['error_message'] .= 'Joomlabridge_Joomlauser_Model::UpdateContactFromJoomlaUser failed at Contact WSID : '.$wsid.
												', JUserId : '.$juserfield['id'].' ('.$juserfield['name'].') in Joomla_Instance ID : '.$juserfield['joomlahostid'];
							} else {
								$sync_stat['updated']++;
								$time_end = microtime(true);
								$time = $time_end - $time_start;
				
								$log->debug("##### Updating time EXISTING Contact : ".print_r($time, true) );
							}				
						}					
						break;
					default:
					   $log->fatal('???? - FATAL - Joomlabridge_Joomlauser_Model::syncJoomlaUsertoVT() ---- something wrong: no New and no Update ????');
				}					
			} else {
				//admin user --> skipp sync
				$sync_stat['skipped']++;
			}
		} // end loop --- go to the next userdata instance in this SQL query
//		$log->debug("RETURN --> Joomlabridge_Joomlauser_Model::syncJoomlaUsertoVT( $JHostId, $offset, $row_count ) ".print_r($sync_stat, true) );
		return $sync_stat;
	}
	
	/**
	 * Function to Pull Joomla user data by UserIds and sync into the VT Contacts / Joomlabridges
	 * @param mixed $JHostId -- Joomla SQL Host Id in ws format (e.g. 36x5)
	 * @param int $juserIds -- -- Joomla User Id
	 * @return <Array> of synchronization statistic data
	 */		
	public function syncJoomlaUsertoVTbyIDs($JHostId, $juserIds) {
		global $log, $current_user;
		$log->debug("ENTERING --> Joomlabridge_Joomlauser_Model::syncJoomlaUsertoVTbyIDs( $JHostId, $juserIds )");
		
		vglobal('JdataPull', true); //this is the pull only routin
		
		//get Joomla SQL data by 
		$jdata_array = Joomlabridge_Joomlauser_Model::getJSQLUserDatabyIDs($JHostId, $juserIds);
		
		$sync_stat = array();
		$sync_stat['handled'] = 0;
		$sync_stat['created'] = 0;
		$sync_stat['updated'] = 0;
		$sync_stat['skipped'] = 0;
		$sync_stat['failed'] = 0;
		$sync_stat['error_message'] = '';
		// $jdata_array ( $juserIds array( $juserfield array( $userdata) ) )

		foreach ( $jdata_array as $juserId => $juserfield) {
			$sync_stat['handled'] = 1;
			if ( $juserfield['is_joomlaadmin'] == 0 ) { // 'is_joomlaadmin' = 1 filtered
				// $log->debug('Joomla user data to sync: '.print_r($juserfield, true));
				
				$ContactAction = ''; //Choose to right Contact action
				//Check by Joomla User e-mal - Does this user already exist in the Contacts module?
				$ContactInstance = Joomlabridge_Joomlauser_Model::getContactbyEmail($juserfield['email']);
				// $log->debug('###!!!!!### ContactInstance by email: '.print_r($ContactInstance, true));
				
				if ( !$ContactInstance ) {
					//The Contact instance has NOT found so double check by JUserID
					// $log->debug('###!!!!!### Joomla user email did not find in the Contacts module: '.print_r($juserfield['email'], true));
					$JBInstance = Joomlabridge_Joomlauser_Model::getContactJoomlaBridgebyJUserID($juserfield['joomlahostid'], $juserfield['id']);
					
					if ( !$JBInstance ) {
						//The Contact/Joomlabridge instance has NOT found so it is a New Contact to create
						// $log->debug('###!!!!!### Joomla user id did not find in the Joomlabridge module: '.print_r($juserfield['id'], true));
						
						$ContactAction = 'NEW';
					} else {
						//The Joomlabridge instance has found so we should prepare the update/sync and make relation if it is necessary
						$ContactAction = 'UPDATE';
					}
				} else {
					//The Contact instance has found so we should prepare the update/sync
					$ContactAction = 'UPDATE';
					$wsid = $ContactInstance[0]['wsid'];
					$contact_id = $ContactInstance[0]['id'];
				}
				switch ($ContactAction) {
					case 'NEW':
						$time_start = microtime(true);
						// params: $juserfield 	-> Joomla user data pulled from Joomla SQL							
						if ( !Joomlabridge_Joomlauser_Model::CreateContactFromJoomlaUser($juserfield) ) {
							$log->fatal('@@@ Joomlabridge_Joomlauser_Model::CreateContactFromJoomlaUser($juserfield) --- failed at $juserfield = '.print_r($juserfield, true) );
							$sync_stat['failed'] = 1;
							$sync_stat['error_message'] .= 'Joomlabridge_Joomlauser_Model::CreateContactFromJoomlaUser failed at JUserId : '.
															$juserfield['id'].' ('.$juserfield['name'].') in JoomlaInstance ID : '.$juserfield['joomlahostid'];
						} else {
							$sync_stat['created'] = 1;
							$time_end = microtime(true);
							$time = $time_end - $time_start;
			
							$log->debug("##### Creating time NEW Contact : ".print_r($time, true) );
						}
						break;
					case 'UPDATE':
					
						$ldate = Joomlabridge_Joomlauser_Model::getLastPullDateJoomlaBridgebyJUserID( $juserfield['joomlahostid'], $juserfield['id'] );
					
						$lastsyncdate = new DateTime($ldate);
						$lastvisitdate = new DateTime($juserfield['lastvisitdate']);
						
						if ( $lastvisitdate < $lastsyncdate  && self::SKIP_UPDATE ) {
							//the Contact already synchronized -> nothing to do
							$sync_stat['skipped'] = 1;
						
						} else {
							$time_start = microtime(true);
							// params: $wsid 		-> webservice ID of the Contact to be updated
							// params: $juserfield 	-> Joomla user data pulled from Joomla SQL
							if ( !Joomlabridge_Joomlauser_Model::UpdateContactFromJoomlaUser($wsid, $juserfield) ) {
								$log->fatal('@@@ Joomlabridge_Joomlauser_Model::UpdateContactFromJoomlaUser($wsid, $juserfield) --- failed at $wsid = '.
									print_r($wsid, true). ' and $juserfield = '.print_r($juserfield, true) );
								$sync_stat['failed'] = 1;
								$sync_stat['error_message'] .= 'Joomlabridge_Joomlauser_Model::UpdateContactFromJoomlaUser failed at Contact WSID : '.$wsid.
												', JUserId : '.$juserfield['id'].' ('.$juserfield['name'].') in Joomla_Instance ID : '.$juserfield['joomlahostid'];								
							} else {
								$sync_stat['updated'] = 1;
								$time_end = microtime(true);
								$time = $time_end - $time_start;
				
								$log->debug("##### Updating time EXISTING Contact : ".print_r($time, true) );
							}						
						}
						
						break;
					default:
					   $log->fatal('???? - FATAL - Joomlabridge_Joomlauser_Model::syncJoomlaUsertoVT() ---- something wrong: no New and no Update ????');
				}					
			} else {
				//admin user --> skipp sync
				$sync_stat['skipped'] = 1;
			}
		} // end loop (only one record on the second level)
//		$log->debug("RETURN --> Joomlabridge_Joomlauser_Model::syncJoomlaUsertoVTbyIDs( $JHostId, $juserIds ) ".print_r($sync_stat, true) );
		return $sync_stat;
	}
	
	/**
	 * Function to get Joomla Users data by $JHostIdx and by batch
	 * @param mixed $JHostIdx -- Joomla SQL Host Id in ws format (e.g. 36x5)
	 * @param int $offset -- SQL query offset
	 * @param int $row_count -- SQL quantity of row in a batch
	 * @return <Array> of Joomla Users data
	 */		
	public function getJSQLUserData($JHostIdx, $offset, $row_count) {
		global $log, $current_user;
		$log->debug("ENTERING --> Joomlabridge_Joomlauser_Model::getJSQLUserData( $JHostIdx, $offset, $row_count )");
	
		//define the Hosts array
		$JHosts = array($JHostIdx);
		
		//empty array for the query results
		$jresrepair = array();

		//get sync real date-time
		$vt_now=new DateTimeField(null);
		$syncdate = $vt_now->getDisplayDate($current_user);  
		$synctime = $vt_now->getDisplayTime($current_user);
		
		//Loop by all hosts (in this case only 1 loop)
		foreach ( $JHosts as $JHostId ) {

			$jtables = array();
			//get Joomla SQL tables and fields by JHostId
			$jtables = Joomlabridge_FieldMap_Model::getJUserSyncPullFields($JHostId);
			$prefix = Joomlabridge_SQLHost_Model::getJDBprefix($JHostId);
			$usergtable = implode("", array($prefix, "user_usergroup_map"));
			
			foreach ( $jtables as $jtable => $jfields) {
				//Open the Joomla SQL by JHostId
				$joomlaDB = Joomlabridge_SQLHost_Model::getJoomlaSQLaccess($JHostId);

				$jqueryfields = implode(", ", $jfields);
				$instances = array();

//				$time_start = microtime(true);
				$jresult = $joomlaDB->pquery("SELECT $jqueryfields FROM $jtable LIMIT $offset,$row_count ", array() );
				$noofrows = $joomlaDB->num_rows($jresult);
				if ( $noofrows ) {
					for($i=0; $i<$noofrows; $i++){
						$instances[] = $joomlaDB->fetchByAssoc($jresult, $i, false); //false to suppress unnecessary htmlentities encoding
					}
					foreach ( $instances as $instance) {					
						//get Joomla user Id of this data instance
						$juserId = $instance['id'];
						
						foreach ( $jfields as $jfield) {
							$jresrepair[$juserId][$jfield] = $instance[$jfield];
						}
						$jresrepair[$juserId]['joomlahostid'] = $JHostId;
						$jresrepair[$juserId]['joomla_userlevels'] = array();
						$jresrepair[$juserId]['is_joomlaadmin'] = 0;
						$jresrepair[$juserId]['jlastsyncdate'] = $syncdate." ".$synctime;
						
						$UserGroupDatas = array();
						//get user Level grouping data
						$juserlevels = $joomlaDB->pquery('SELECT * FROM '.$usergtable.' WHERE user_id = ?', array($juserId));
						if ($joomlaDB->num_rows($juserlevels)) {
							while ($gr = $joomlaDB->fetch_array($juserlevels)) { 
								$UserGroupDatas[] = new self($gr);
							}
							$jlevels = array();
							foreach ( $UserGroupDatas as $grdata) {
								$jlevels[] = $grdata->get('group_id');
								if ( in_array( $grdata->get('group_id'), self::getJoomlaadmins() ) ) {
									$jresrepair[$juserId]['is_joomlaadmin'] = 1;
								}
							}
							$jresrepair[$juserId]['joomla_userlevels'] = $jlevels;
						}
					} //end loop of object -> array					
				}
				//Close the Joomla SQL
				
//				$time_end = microtime(true);
//				$time = $time_end - $time_start;
				
//				$log->debug("##### Joomlabridge_Joomlauser_Model::getJSQLUserData() - $JHostId - Query TIME : ".print_r($time, true) );
//				$log->debug("##### Joomlabridge_Joomlauser_Model::getJSQLUserData() - $JHostId - NUMBER of rows : ".print_r($joomlaDB->num_rows($jresult), true) );				

				$joomlaDB->disconnect();				

			}
		} //end loop by all hosts (here 1)
//		$log->debug("##### Joomlabridge_Joomlauser_Model::getJSQLUserData( $JHostId, $offset, $row_count ) Return data : ".print_r($jresrepair, true) );
		return $jresrepair;
	}

	/**
	 * Function to get Joomla Users data by Ids
	 * @param mixed $JHostId -- Joomla SQL Host Id in ws format (e.g. 36x5)
	 * @param int $juserId -- Joomla User Id
	 * @return <Array> of Joomla Users data
	 */		
	public function getJSQLUserDatabyIDs($JHostId, $juserId) {
		global $log;
		global $current_user;
		$log->debug("ENTERING --> Joomlabridge_Joomlauser_Model::getJSQLUserDatabyIDs( $JHostId, $juserId )");
	
		//empty array for the query results
		$jresrepair = array();
		
		//get sync real date-time
		$vt_now = new DateTimeField(null);
		$syncdate = $vt_now->getDisplayDate($current_user);  
		$synctime = $vt_now->getDisplayTime($current_user);
		
		$jtables = array();
		//get Joomla SQL tables and fields by JHostId
		$jtables = Joomlabridge_FieldMap_Model::getJUserSyncPullFields($JHostId);
		$prefix = Joomlabridge_SQLHost_Model::getJDBprefix($JHostId);
		$usergtable = implode("", array($prefix, "user_usergroup_map"));
		
		foreach ( $jtables as $jtable => $jfields) {
			//Open the Joomla SQL by JHostId
			$joomlaDB = Joomlabridge_SQLHost_Model::getJoomlaSQLaccess($JHostId);

			$jqueryfields = implode(", ", $jfields);

			$jresult = $joomlaDB->pquery('SELECT '.$jqueryfields.' FROM '.$jtable.' WHERE id = ?;', array($juserId));
			
			if ($joomlaDB->num_rows($jresult)) {
				$data = $joomlaDB->getNextRow($jresult, false); //false to suppress unnecessary htmlentities encoding
		
				foreach ( $jfields as $jfield) {
					$jresrepair[$juserId][$jfield] = $data[$jfield];
				}
				
				$jresrepair[$juserId]['joomlahostid'] = $JHostId;
				$jresrepair[$juserId]['joomla_userlevels'] = array();
				$jresrepair[$juserId]['is_joomlaadmin'] = 0;
				$jresrepair[$juserId]['jlastsyncdate'] = $syncdate." ".$synctime;
				
				$UserGroupDatas = array();
				//get user Level grouping data
				$juserlevels = $joomlaDB->pquery('SELECT * FROM '.$usergtable.' WHERE user_id = ?', array($juserId));
				if ($joomlaDB->num_rows($juserlevels)) {
					while ($gr = $joomlaDB->fetch_array($juserlevels)) { 
						$UserGroupDatas[] = new self($gr);
					}
					$jlevels = array();
					foreach ( $UserGroupDatas as $grdata) {
						$jlevels[] = $grdata->get('group_id');
						if ( in_array( $grdata->get('group_id'), self::getJoomlaadmins() ) ) {
							$jresrepair[$juserId]['is_joomlaadmin'] = 1;
						}
					}
					$jresrepair[$juserId]['joomla_userlevels'] = $jlevels;
				}
				
			}
			//Close the Joomla SQL
			$joomlaDB->disconnect();

		}
//		$log->debug("##### Joomlabridge_Joomlauser_Model::getJSQLUserDatabyIDs( $JHostId, $juserId ) : ".print_r($jresrepair, true) );
		return $jresrepair;
	}

	/**
	 * Function to get summary of the Joomla Users and SQL Hosts before sync
	 * @param --
	 * @return <Array> of synchronization summary data
	 */	
	public function getJSQLUserInfo() {
		global $log;
		$log->debug('ENTERING --> Joomlabridge_Joomlauser_Model::getJSQLUserInfo() ');
	
		//get all Joomla SQL Hosts
		$JHosts = Joomlabridge_SQLHost_Model::getHostIds();
		if ($JHosts) {
			//empty array for the query results
			$jsqldata = array();
			$i = 0;
			//Loop by all hosts
			foreach ( $JHosts as $JHostId ) {

				$jtables = array();
				//get Joomla SQL tables and fields by JHostId
				$jtables = Joomlabridge_FieldMap_Model::getJUserSyncPullFields($JHostId);
				
				foreach ( $jtables as $jtable => $jfields) {
					//Open the Joomla SQL by JHostId
					$joomlaDB = Joomlabridge_SQLHost_Model::getJoomlaSQLaccess($JHostId);

					$instances = array();
					$jresult = $joomlaDB->pquery("SELECT id FROM $jtable", array());

					if ($joomlaDB->num_rows($jresult)) {
						while ($data = $joomlaDB->fetch_array($jresult)) {
							$instances[] = new self($data);
						}
						$jresrepair = array();
						foreach ( $instances as $instance) {
							//get Joomla user Id of this data instance and HostId
	//						$jsqldata[$i]['index'] = $i+1;	//the array index is enough
							$jsqldata[$i]['hostid'] = $JHostId;
							$jsqldata[$i]['juserid'] = $instance->get('id');
							++$i;
						} //end loop of object -> array					
					}
					//Close the Joomla SQL
					$joomlaDB->disconnect();
				}
			} //end loop by all hosts
	//		$log->debug('EXITING --> Joomlabridge_Joomlauser_Model::getJSQLUserInfo() RETURN: '.print_r($jsqldata, true) );
			return $jsqldata;
		} else {
			return false;
		}
	}

	/**
	 * Helper function to scan for relations
	 */
	public function ws_describe($module) {
		require_once 'include/Webservices/DescribeObject.php';
		global $log, $current_user;
		$log->debug('ENTERING --> Joomlabridge_Joomlauser_Model::ws_describe()' );	
		$BridgeCache = new Joomlabridge_Joomlauser_Model;
		$myCache = $BridgeCache->get('wsDescribeCache');
		if ( !isset( $myCache[$module] ) ) {
			try {
				$myCache[$module] = vtws_describe( $module, $current_user );
				$BridgeCache->set('wsDescribeCache', $myCache[$module]);
			} catch (WebServiceException $ex) {
				$log->fatal('@@@ vtws_describe -- Joomlabridge_Joomlauser_Model::ws_describe() -- failed: '.print_r($ex->getMessage(), true) );
				return false;			
			}
		}
		return $myCache[$module];
	}

	/**
	 * Funtion used to build Web services query
	 * @param String $module -- Name of the module
	 * @param String $text -- Search String
	 * @param String $type -- Type of fields Phone, Email etc
	 * @return String
	 */
	public function buildSearchQuery($module, $text, $type) {
		global $log, $current_user;
		$log->debug('ENTERING --> Joomlabridge_Joomlauser_Model::buildSearchQuery()');
		$describe = Joomlabridge_Joomlauser_Model::ws_describe($module);
		$whereClause = '';
		foreach($describe['fields'] as $field) {
			if (strcasecmp($type, $field['type']['name']) === 0) {
				$whereClause .= sprintf( " %s LIKE '%%%s%%' OR", $field['name'], $text );
			}
		}
		return sprintf( "SELECT %s FROM %s WHERE %s;", $describe['labelFields'], $module, rtrim($whereClause, 'OR') );
	}

	/**
	 * Returns the List of Matching records with the Email Address
	 * @global Users Instance $currentUserModel
	 * @param String $module
	 * @param Email Address $email
	 * @return Array
	 */
	public function lookupModuleRecordsWithEmail($module, $email) {
		global $log, $current_user;
		$log->debug("ENTERING --> Joomlabridge_Joomlauser_Model::lookupModuleRecordsWithEmail( $module, $email )");
		
		$query = Joomlabridge_Joomlauser_Model::buildSearchQuery($module, $email, 'EMAIL');		
		$qresults = vtws_query( $query, $current_user );
		$describe = Joomlabridge_Joomlauser_Model::ws_describe($module);
		$labelFields = explode(',', $describe['labelFields']);

		$results = array();
		foreach($qresults as $qresult) {
			$labelValues = array();
			foreach($labelFields as $fieldname) {
				if(isset($qresult[$fieldname])) $labelValues[] = $qresult[$fieldname];
			}
			$ids = vtws_getIdComponents($qresult['id']);
			$results[] = array( 'wsid' => $qresult['id'], 'id' => $ids[1], 'label' => implode(' ', $labelValues));
		}
		return $results;
	}	

}
?>
