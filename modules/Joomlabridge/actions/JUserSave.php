<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Joomlabridge_JUserSave_Action extends Vtiger_Action_Controller {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('JUserUpdate');
		$this->exposeMethod('JUserNew');
		$this->exposeMethod('ResetPassword');
		$this->exposeMethod('CheckDuplicatedJHost');
	}

	function checkPermission(Vtiger_Request $request) {
		return;
	}
	
	function preProcess(Vtiger_Request $request) {
		return true;
	}

	function postProcess(Vtiger_Request $request) {
		return true;
	}

	public function process(Vtiger_Request $request) {
		$mode = $request->get('mode');
		
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}
	
	/**
	 * Function to create Random password for the New Joomla User.
	 * @param $request
	 */
	public function JUserNew(Vtiger_Request $request){
		global $log, $current_user;
		$log->debug('ENTERING --> Joomlabridge_JUserSave_Action::JUserNew() ');
		
		$newJUser = array();
		
		$newJUser['juser_passwordc'] = Joomlabridge_JUser_Helper::genRandomPassword();
		$newJUser['juser_password'] = Joomlabridge_JUser_Helper::hashPassword($newJUser['juser_passwordc']);
		
		//get registration real date-time
		$vt_now = new DateTimeField(null);
		$regdate = $vt_now->getDisplayDate($current_user);  
		$regtime = $vt_now->getDisplayTime($current_user);
		$regdatetime = $regdate." ".$regtime;
		
		$newJUser['juser_registerdate'] = $regdatetime;
		
		$log->debug('Joomlabridge_JUserSave_Action::JUserNew(): '.print_r($newJUser, true) );

		$response = new Vtiger_Response();
		$response->setResult($newJUser);
		$response->emit();

	}
	
	/**
	 * Function to Reset Password for the existing Joomla User.
	 * @param $request
	 */
	public function ResetPassword(Vtiger_Request $request){
		require_once 'include/Webservices/Utils.php';
		require_once 'include/Webservices/Update.php';
		require_once 'include/Webservices/Retrieve.php';
		global $log, $current_user;
		$log->debug('ENTERING --> Joomlabridge_JUserSave_Action::ResetPassword() ');
		
		$RecordId = $request->get('record');
		
		$JUserData = array();
		
		$JUserData['juser_passwordc'] = Joomlabridge_JUser_Helper::genRandomPassword();
		$JUserData['juser_password'] = Joomlabridge_JUser_Helper::hashPassword($JUserData['juser_passwordc']);
		
		//get real date-time of the action
		$vt_now = new DateTimeField(null);
		$pushdate = $vt_now->getDisplayDate($current_user);  
		$pushtime = $vt_now->getDisplayTime($current_user);
		$pushdatetime = $pushdate." ".$pushtime;
		
		$JUserData['last_push_date'] = $pushdatetime;
		
		$log->debug('Joomlabridge_JUserSave_Action::ResetPassword(): DATA CHANGE -- '.print_r($JUserData, true) );

		$wsid = vtws_getWebserviceEntityId('Joomlabridge', $RecordId);		
		
		//Update this Joomlabridge instance
		$log->debug('###!!!!!### Joomlabridge to update for Record_ID: '.print_r($wsid, true));
		// Get Joomlabridge data to perform Update (else the not provided fields will be deleted)
		try {
			$thisJBInstance = vtws_retrieve($wsid, $current_user);
			$log->debug('### Retrieve Joomlabridge data: '.print_r($thisJBInstance, true) );

		} catch (WebServiceException $ex) {
			$log->fatal('@@@ vtws_retrieve - Joomlabridge - failed: '.print_r($ex->getMessage(), true) );
			$result = false;
		}
		
		// Joomlabridge record Update
		try {
			// Data mapping	for the update					
			foreach ( $JUserData as $key => $value ) {
				$thisJBInstance[$key] = $JUserData[$key];							
			}
	
			$updateJB = vtws_update($thisJBInstance, $current_user);
//				$log->debug('###!!!!!### Updated Joomlabridge: '.print_r($updateJB, true) );
			$result = true;
		} catch (WebServiceException $ex) {
			$log->fatal('@@@ vtws_update - Joomlabridge - failed: '.print_r($ex->getMessage(), true) );
			$result = false;
		}		
		
		$response = new Vtiger_Response();
		$response->setResult(array($result));
		$response->emit();

	}
	
	/**
	 * Function to Update Existing JUsers.
	 * @param $request
	 */
	public function JUserUpdate(Vtiger_Request $request){
		global $log;
		$log->debug('ENTERING --> Joomlabridge_JUserSave_Action::JUserUpdate() ');
/*		
		$host = $request->get('host');
		$item = $request->get('item');

		$syncstat =	Joomlabridge_Joomlauser_Model::syncJoomlaUsertoVTbyIDs( $host, $item );
		$hostname = Joomlabridge_SQLHost_Model::getHostNameById( $host );
		
		if ( $syncstat ) {
			$values = array( 'success' => 1, 'created' => $syncstat['created'], 'updated' => $syncstat['updated'], 'skipped' => $syncstat['skipped'] , 'host' => $hostname, 'item' => $item );
		} else {
			$values = array( 'success' => 0, 'created' => 0, 'updated' => 0, 'skipped' => 0 , 'host' => $hostname, 'item' => $item );
		}

		$response = new Vtiger_Response();
		$response->setResult($values);
		$response->emit();
*/

	}
	
	/**
	 * Function to Check Duplicated JHostId.
	 * @param $request
	 */
	public function CheckDuplicatedJHost(Vtiger_Request $request){
		global $log;
		$log->debug('ENTERING --> Joomlabridge_JUserSave_Action::CheckDuplicatedJHost() ');
		
		require_once 'include/Webservices/Utils.php';
		
		$jhostid = $request->get('jhostid');
		$contactid = $request->get('contactid');
		
		// Check the Joomlabridge instance to this Contact record
		$wshostid = vtws_getWebserviceEntityId('Joomlahosts', $jhostid);
		$wsconid = vtws_getWebserviceEntityId('Contacts', $contactid);
		$JBInstance = Joomlabridge_Joomlauser_Model::getJoomlabridgebyContactID($wshostid, $wsconid);
//		$log->debug("@@@ Joomlabridge_JUserSave_Action::CheckDuplicatedJHost() ".print_r($JBInstance, true) );
		if ( $JBInstance ) {
			//a Joomlabridge already set
			$parts = explode('x', $JBInstance[0]['jhostid']);
			$jhostid_used = $parts[1];
			if( $jhostid == $jhostid_used ) {
				$values = array( 'duplicated' => 1 );
			} else {
				//no duplicates
				$values = array( 'duplicated' => 0 );
			}
		} else {
			//no duplicates
			$values = array( 'duplicated' => 0 );
		}

		$response = new Vtiger_Response();
		$response->setResult($values);
		$response->emit();

	}

}
