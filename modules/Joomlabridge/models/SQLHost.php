<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
require_once 'include/database/PearDatabase.php';
require_once 'include/Webservices/Query.php';

class Joomlabridge_SQLHost_Model extends Vtiger_Base_Model {

	const tableName = 'vtiger_joomlahosts';
	
	public function __construct() {
		$this->set('hika_installed', self::getHikaState() );
		$this->set('acym_installed', self::getAcymState() );
	}

	/**
	 * Function to get record instance by using record id
	 * @param <mixed> $recordId
	 * @return <Array> of <Joomlabridge_SQLHost_Model> hostdata by given id
	 */
	public static function getHostInstanceById($recordId) {
		global $current_user;
		global $log;
		$host = array();
		$records = array();
//		$log->debug("ENTERING --> Joomlabridge_SQLHost_Model::getHostInstanceById( $recordId )");
		$modulename = 'Joomlahosts';
		try {
			$q = "SELECT * FROM $modulename WHERE id = $recordId AND enabled = 1;"; // NOTE: Make sure to terminate query with ;
			$records = vtws_query($q, $current_user);			
//			$log->debug('Joomlabridge_SQLHost_Model::getHostInstanceById() '.print_r($records, true) );

		} catch (WebServiceException $ex) {
			$log->fatal('@@@ vtws_query -- Joomlahosts -- failed: '.print_r($ex->getMessage(), true) );
			return false;
		}
		if ( count($records) ) {
			foreach ( $records as $record) {
				foreach ( $record as $key => $value ) {
					$host[$key] = $value;
				}
			}
			return $host;
		} else {
			return false;	
		}
	}
	
	/**
	 * Function to get Host identification name by using record id
	 * @param <mixed> $recordId
	 * @return <Array> of <Joomlabridge_SQLHost_Model> hostdata by given id
	 */
	public static function getHostNameById($recordId) {
		global $current_user;
		global $log;
		$host = array();
		$records = array();
//		$log->debug("ENTERING --> Joomlabridge_SQLHost_Model::getHostNameById( $recordId )");
		$modulename = 'Joomlahosts';
		try {
			$q = "SELECT iname FROM $modulename WHERE id = $recordId AND enabled = 1;"; // NOTE: Make sure to terminate query with ;
			$records = vtws_query($q, $current_user);

			$iname = $records[0]['iname'];	
			return $iname;			
//			$log->debug('Joomlabridge_SQLHost_Model::getHostNameById() '.print_r($records, true) );

		} catch (WebServiceException $ex) {
			$log->fatal('@@@ vtws_query -- Joomlahosts -- failed: '.print_r($ex->getMessage(), true) );
			return false;
		}
	}
	
	/**
	 * Function to get Host identification Number by using record id
	 * @param <mixed> $recordId (ws)
	 * @return <Array> of <Joomlabridge_SQLHost_Model> hostdata by given id
	 */
	public static function getHostNoById($recordId) {
		global $current_user;
		global $log;
		$host = array();
		$records = array();
//		$log->debug("ENTERING --> Joomlabridge_SQLHost_Model::getHostNameById( $recordId )");
		$modulename = 'Joomlahosts';
		try {
			$q = "SELECT host_no FROM $modulename WHERE id = $recordId AND enabled = 1;"; // NOTE: Make sure to terminate query with ;
			$records = vtws_query($q, $current_user);

			$host_no = $records[0]['host_no'];	
			return $host_no;			
//			$log->debug('Joomlabridge_SQLHost_Model::getHostNameById() '.print_r($records, true) );

		} catch (WebServiceException $ex) {
			$log->fatal('@@@ vtws_query -- Joomlahosts -- failed: '.print_r($ex->getMessage(), true) );
			return false;
		}
	}
	
	/**
	 * Function to get all Host identification Number
	 * @param -- none
	 * @return <Array> of <Joomlabridge_SQLHost_Model> hostdata by given id
	 */
	public static function getAllHostNo() {
		global $current_user;
		global $log;
		$host = array();
		$records = array();
		$log->debug("ENTERING --> Joomlabridge_SQLHost_Model::getAllHostNo()");
		$modulename = 'Joomlahosts';
		try {
			$q = "SELECT host_no FROM $modulename WHERE enabled = 1;"; // NOTE: Make sure to terminate query with ;
			$records = vtws_query($q, $current_user);

			return $records;			
//			$log->debug('Joomlabridge_SQLHost_Model::getAllHostNo() '.print_r($records, true) );

		} catch (WebServiceException $ex) {
			$log->fatal('@@@ vtws_query -- Joomlahosts -- failed: '.print_r($ex->getMessage(), true) );
			return false;
		}
	}
	
	/**
	 * Function to get state of Hikashop Component
	 * @param -- none
	 * @return <Array> of <Joomlabridge_SQLHost_Model> 
	 */
	public static function getHikaState() {
		global $current_user;
		global $log, $adb;
		$HikaState = array();
		$log->debug("ENTERING --> Joomlabridge_SQLHost_Model::getHikaState()");
		
		$results = $adb->pquery("SELECT joomlahostsid, hikashop FROM vtiger_joomlahosts WHERE enabled = 1", array() );
		if ( $adb->num_rows($results) ) {
			while ( $row = $adb->fetch_array($results) ) { 
				$HikaState[ $row['joomlahostsid'] ] = $row['hikashop'];
			}
			// $log->debug('Joomlabridge_SQLHost_Model::getHikaState() '.print_r($HikaState, true) );
			return $HikaState;
		} else {
			$log->fatal('@@@ Joomlabridge_SQLHost_Model::getHikaState() -- SQL query failed. ');
			return false;
		}
	}
	
	/**
	 * Function to get state of Acymailing Component
	 * @param -- none
	 * @return <Array> of <Joomlabridge_SQLHost_Model> 
	 */
	public static function getAcymState() {
		global $current_user;
		global $log, $adb;
		$AcymState = array();
		$log->debug("ENTERING --> Joomlabridge_SQLHost_Model::getAcymState()");
		
		$results = $adb->pquery("SELECT joomlahostsid, acymailing FROM vtiger_joomlahosts WHERE enabled = 1", array() );
		if ( $adb->num_rows($results) ) {
			while ( $row = $adb->fetch_array($results) ) { 
				$AcymState[ $row['joomlahostsid'] ] = $row['acymailing'];
			}
			// $log->debug('Joomlabridge_SQLHost_Model::getAcymState() '.print_r($AcymState, true) );
			return $AcymState;
		} else {
			$log->fatal('@@@ Joomlabridge_SQLHost_Model::getAcymState() -- SQL query failed. ');
			return false;
		}
	}

	/**
	 * Function to get All instances of Joomla SQL host data
	 * @return <Array> list of all hosts <Joomlabridge_SQLHost_Model>
	 */	
	public static function getAllSQLHosts() {
		global $current_user;
//		global $log;
		
		$records = array();
		$modulename = 'Joomlahosts';
		try {
			$q = "SELECT * FROM $modulename WHERE enabled = 1;"; // NOTE: Make sure to terminate query with ;
			$records = vtws_query($q, $current_user);			

		} catch (WebServiceException $ex) {
			$log->fatal('@@@ vtws_query -- Joomlahosts -- failed: '.print_r($ex->getMessage(), true) );
			return false;
		}
		return $records;
	}
	
	/**
	 * Function to get all Joomla database prefixes from SQL host settings
	 * @return <Array> list of available prefixes in the format of simple array( "hostid1" => "prefix_1", "hostid2" => "prefix_2", etc. )
	 */	
	public static function getJDBprefixes() {
		global $current_user;
		global $log;
		$prefixes = array();
		$instances = array();
//		$log->debug('ENTERING --> Joomlabridge_SQLHost_Model::getJDBprefixes()');
		$modulename = 'Joomlahosts';
		try {
			$q = "SELECT id, j_dbprefix FROM ".$modulename." WHERE enabled = 1;"; // NOTE: Make sure to terminate query with ;
			$instances = vtws_query($q, $current_user);			

		} catch (WebServiceException $ex) {
			$log->fatal('@@@ vtws_query -- Joomlahosts -- failed: '.print_r($ex->getMessage(), true) );
			return false;
		}
		if ( count($instances) ) {
			foreach ( $instances as $instance) {
				$prefixes[$instance['id']] = $instance['j_dbprefix'];
			}
			return $prefixes;
		} else {
			return false;	
		}
	}
	
	/**
	 * Function to get Joomla database prefix from SQL host settings by HostId
	 * @return <String> Joomla database prefix
	 */	
	public static function getJDBprefix($HostId) {
		global $current_user;
		global $log;
		$prefix = string;
		$instances = array();	
//		$log->debug("ENTERING --> Joomlabridge_SQLHost_Model::getJDBprefix( $HostId )");
		$modulename = 'Joomlahosts';
		try {
			$q = "SELECT j_dbprefix FROM ".$modulename." WHERE id = ". $HostId. " AND enabled = 1;"; // NOTE: Make sure to terminate query with ;
			$instances = vtws_query($q, $current_user);			

		} catch (WebServiceException $ex) {
			$log->fatal('@@@ vtws_query -- Joomlahosts -- failed: '.print_r($ex->getMessage(), true) );
			return false;
		}
//		$log->debug('Joomlabridge_SQLHost_Model::getJDBprefix($HostId) ---- '.print_r($instances, true) );
		if ( count($instances) ) {
			foreach ( $instances as $instance) {
				$prefix = $instance['j_dbprefix'];
			}
			return $prefix;
		} else {
			return false;	
		}	
	}
	
	/**
	 * Function to get All active Host IDs
	 * @return <Array> list of all host IDs in the format of simple array( "0" => "hostid1", "1" => "hostid2", etc. )
	 */	
	public static function getHostIds() {
		global $current_user;
		global $log;
		$HostIds = array();
		$instances = array();	
//		$log->debug('ENTERING --> Joomlabridge_SQLHost_Model::getHostIds()');
		$modulename = 'Joomlahosts';
		try {
			$q = "SELECT id FROM ".$modulename." WHERE enabled = 1;";  // NOTE: Make sure to terminate query with ;
			$instances = vtws_query($q, $current_user);			

		} catch (WebServiceException $ex) {
			$log->fatal('@@@ vtws_query -- Joomlahosts -- failed: '.print_r($ex->getMessage(), true) );
			return false;
		}
//		$log->debug('Joomlabridge_SQLHost_Model::getHostIds() ---- '.print_r($instances, true) );
		if ( count($instances) ) {
			foreach ( $instances as $instance) {
				$HostIds[] = $instance['id'];
			}
			return $HostIds;
		} else {
			return false;	
		}
	}
	
	/**
	 * Function to get Number of instances of Joomla SQL host data
	 * @return Number of hosts or False if host data has not found
	 */	
	public static function getNumberofHosts() {
		global $current_user;
		global $log;
		$instances = array();	
//		$log->debug('ENTERING --> Joomlabridge_SQLHost_Model::getNumberofHosts()');
		$modulename = 'Joomlahosts';
		try {
			$q = "SELECT id FROM ".$modulename." WHERE enabled = 1;";  // NOTE: Make sure to terminate query with ;
			$instances = vtws_query($q, $current_user);			

		} catch (WebServiceException $ex) {
			$log->fatal('@@@ vtws_query -- Joomlahosts -- failed: '.print_r($ex->getMessage(), true) );
			return false;
		}	
		return count($instances);
	}

	/**
	 * Function to get access to the SQL host by host_id
	 * @return database object to access
	 */	
	public function getJoomlaSQLaccess($hostid) {
		global $log;
//		$log->debug("ENTERING --> Joomlabridge_SQLHost_Model -- getJoomlaSQLaccess( $hostid )");
		$jhost = Joomlabridge_SQLHost_Model::getHostInstanceById($hostid);
		
		if ($jhost) {
			// params to call: $dbtype, $host, $dbname, $username, $passwd
			try {
				return new PearDatabase(
						$jhost['joomlahost_dbtype'],
						$jhost['j_host'],
						$jhost['j_dbname'],
						$jhost['j_dbuser'],
						$jhost['j_dbpassword']			
					);
			} catch (Exception $e) {
				$log->fatal('@@@ PearDatabase init -- failed: '.print_r($e->getMessage(), true) );
				return false;
			}
			
		} else {
			return false;
		}
	}

}
?>
