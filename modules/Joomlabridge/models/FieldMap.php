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

class Joomlabridge_FieldMap_Model extends Vtiger_Base_Model {

	const fieldTableName  = 'vtiger_joomla_fieldmaps';
	const fieldTableIndex = 'id';
	
	/**
	 * Function to convert multiselect vtiger Picklist items possible to store
	 * @param <array> picklist items Ids
	 * @return <string> merged pickilist items to store string format (e.g. '2 |##| 3 |##| 4' )
	 */	
	public static function PicklistItemsToStore($items) {
		global $log;	
		$log->debug('ENTERING --> Joomlabridge_FieldMap_Model::PicklistItemsToStore($items)');
		
		$picklists = array();
		
		if ( is_array($items) ) {
			$PicklistItemsToStore = implode(" |##| ", $items);
		} else {
			$PicklistItemsToStore = $items;
		}
		return $PicklistItemsToStore;
	}
	
	/**
	 * Function to convert multiselect vtiger Picklist Joomla bridge items to index array for Joomla store
	 * @param <array> picklist items Ids
	 * @return <string> merged pickilist items to store string format
	 */	
	public static function ConvertVTULevelsToJoomla($items) {
		global $log;	
		$log->debug('ENTERING --> Joomlabridge_FieldMap_Model::ConvertVTULevelsToJoomla($items)'.print_r($items, true) );
		
		$ToJoomla = array();
		$ToJoomla = explode(' |##| ',$items );
		
		return $ToJoomla;
	}
	
	/**
	 * Function to get vtiger Fields to Pull for the JUserSync function
	 * @param -none-
	 * @return <Array> of <Joomlabridge_FieldMap_Model> vtiger field instances to Pull for the JUserSync function
	 */
	public static function getJUserSyncVTFields() {
		global $log;
		$log->debug('ENTERING --> Joomlabridge_FieldMap_Model::getJUserSyncVTFields()');
		
		$vtfields = array();
	
		$db = PearDatabase::getInstance();
		$instances = array();

		$maps = $db->pquery("SELECT id, vt_field FROM ".self::fieldTableName." WHERE jfunction = 'JUserSync' AND vt_module = 'Joomlabridge' AND isactive = 1 AND pull_from_joomla = 1", array());
		if ($db->num_rows($maps)) {
			while ($data = $db->fetch_array($maps)) {
				$instances[] = new self($data);
			}
			
			foreach ( $instances as $instance) {
				$vtfields[] = strtolower($instance->get('vt_field'));
			}
		}
		return $vtfields;
	}
	
	/**
	 * Function to get Joomla Tables and Fields to Pull by HostId for the JUserSync function
	 * @param <mixed> $HostId
	 * @return <Array> of <Joomlabridge_FieldMap_Model> Joomlab tables and field instances to Pull for the JUserSync function
	 */
	public static function getJUserSyncPullFields($HostId) {
		global $log;
		$log->debug("ENTERING --> Joomlabridge_FieldMap_Model::getJUserSyncPullFields( $HostId )");
		
		$prefix = Joomlabridge_SQLHost_Model::getJDBprefix($HostId);
		$jfields = array();
	
		$db = PearDatabase::getInstance();
		$instances = array();

		$maps = $db->pquery("SELECT id, jtable, jfield, pull_from_joomla FROM ".self::fieldTableName." WHERE jfunction = 'JUserSync' AND vt_module = 'Joomlabridge' AND isactive = 1 AND pull_from_joomla = 1", array());
		if ($db->num_rows($maps)) {
			while ($data = $db->fetch_array($maps)) {
				$instances[] = new self($data);
			}
			
			foreach ( $instances as $instance) {
				$jtablename = explode( '__', $instance->get('jtable') );
				$jtname = implode("", array($prefix, $jtablename[1]));
				$jfields[$jtname][] = strtolower($instance->get('jfield'));
			}
		}
		return $jfields;
	}
	
	/**
	 * Function to get Joomla Tables and Fields to Pull by HostId for the JUserLevel sync function
	 * @param <mixed> $HostId
	 * @return <Array> of <Joomlabridge_FieldMap_Model> Joomlab tables and field instances to Pull for the JUserLevel sync function
	 */
	public static function getJUserLevelPullFields($HostId) {
		global $log;
		$log->debug('ENTERING --> Joomlabridge_FieldMap_Model::getJUserSyncPullFields($HostId)');
		
		$prefix = Joomlabridge_SQLHost_Model::getJDBprefix($HostId);
		$jfields = array();
	
		$db = PearDatabase::getInstance();
		$instances = array();

		$maps = $db->pquery("SELECT id, jtable, jfield, pull_from_joomla FROM ".self::fieldTableName." WHERE jfunction = 'JUserLevel' AND isactive = 1 AND pull_from_joomla = 1", array());
		if ($db->num_rows($maps)) {
			while ($data = $db->fetch_array($maps)) {
				$instances[] = new self($data);
			}
			
			foreach ( $instances as $instance) {
				$jtablename = explode( '__', $instance->get('jtable') );
				$jtname = implode("", array($prefix, $jtablename[1]));
				$jfields[$jtname][] = strtolower($instance->get('jfield'));
			}
		}
		return $jfields;
	}



}
?>
