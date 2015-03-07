<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Joomlabridge_ManualPull_Action extends Vtiger_Action_Controller {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('getItemsCount');
		$this->exposeMethod('getQueue');
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
	 * Function to get numbers of Items to sync.
	 * @param $request
	 */
	public function getItemsCount(Vtiger_Request $request){
		global $log;
		$log->debug('ENTERING --> Joomlabridge_ManualPull_Action::getItemsCount() ');

		$JUserDatas = Joomlabridge_Joomlauser_Model::getJSQLUserInfo();
//		$log->debug("##### Joomlabridge_Joomlauser_Model::getJSQLUserInfo() : ".print_r($JUserDatas, true) );
		
		if ( $JUserDatas ) {
			$items = count($JUserDatas);
			$hostdatas = array();
			foreach ( $JUserDatas as $JUserData ) {
				$hostdatas[] = $JUserData['hostid'];
			}
			$hostdata = array_unique($hostdatas);
			$hosts = count($hostdata);
		} else {
			$items = 0;
			$hosts = 0;
		}

		$values = array( 'items' => $items, 'hosts' => $hosts, 'data' => $JUserDatas );
//		$log->debug("##### Joomlabridge_Joomlauser_Model::getJSQLUserInfo() return data : ".print_r($values, true) );

		$response = new Vtiger_Response();
		$response->setResult($values);
		$response->emit();
		$log->debug('EXITING --> Joomlabridge_ManualPull_Action::getItemsCount() ');
	}
	
	/**
	 * Function to get numbers of Items to sync.
	 * @param $request
	 */
	public function getQueue(Vtiger_Request $request){
		global $log;
		$log->debug('ENTERING --> Joomlabridge_ManualPull_Action::getQueue() ');
		require_once 'includes/runtime/Globals.php';
		vglobal('JdataPull', true); //this is the pull only routin
		
		$host = $request->get('host');
		$item = $request->get('item');

		$syncstat =	Joomlabridge_Joomlauser_Model::syncJoomlaUsertoVTbyIDs( $host, $item );
		$hostname = Joomlabridge_SQLHost_Model::getHostNameById( $host );
		
		if ( $syncstat ) {
			$values = array( 
							'success'		=> 1,
							'handled'		=> $syncstat['handled'],
							'created'		=> $syncstat['created'], 
							'updated'		=> $syncstat['updated'], 
							'skipped' 		=> $syncstat['skipped'],
							'failed'		=> $syncstat['failed'],
							'error_message'	=> $syncstat['error_message'],	
							'host'			=> $hostname, 
							'item'			=> $item );
		} else {
			$values = array( 'success' => 0, 'created' => 0, 'updated' => 0, 'skipped' => 0 , 'host' => $hostname, 'item' => $item );
		}
//		$log->debug('RETURN --> Joomlabridge_ManualPull_Action::getQueue() response values :' .print_r($values, true) );
		$response = new Vtiger_Response();
		$response->setResult($values);
		$response->emit();

	}

}
