<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Joomlabridge_Util_Helper {

	/**
     * Function which will give the picklist values for the Joomlabridge JUserLevels multiselect fields depending from the JoomlaHosts 
     * @param type $host_no -- string (JoomlaHost string unique identifier
     * @return type -- array of values
     */
    public static function getPickListValues($host_no) {
		global $log;	
		$log->debug("ENTERING --> Joomlabridge_Util_Helper::getPickListValues( $host_no )");
		
        $db = PearDatabase::getInstance();
			
		$VT_UserLTablename = 'vtiger_joomla_userlevels_'.strtolower($host_no);
        $query = "SELECT joomla_userlevelsid, joomla_userlevels FROM $VT_UserLTablename WHERE presence = 1 ORDER BY sortorderid ASC";
		
        $values = array();
        $result = $db->pquery($query, array());

		if ( $db->num_rows($result) ) {
			while ( $row = $db->fetch_array($result) ) { 
				$values[$row['joomla_userlevelsid']] = $row['joomla_userlevels'];
			}
			//$log->debug("EXITING --> Joomlabridge_Util_Helper::getPickListValues( $host_no )". print_r($values, true) );
			return $values;
		}
		return false;
    }
	
	/**
     * Function which will give the union all picklist values for the Joomlabridge JUserLevels multiselect fields 
     * @param type --- none
     * @return type -- array of values
     */
    public static function getAllPickListValues() {
		global $log;	
		$log->debug("ENTERING --> Joomlabridge_Util_Helper::getAllPickListValues()");
		
        $db = PearDatabase::getInstance();
		
		$hdata = Joomlabridge_SQLHost_Model::getAllHostNo();
//		$log->debug("RUN --> Joomlabridge_Util_Helper::getAllPickListValues())". print_r($hdata, true) );
		
		$picklist_union = array();
		
		foreach ($hdata as $host_nos) {
			
			$VT_UserLTablename = 'vtiger_joomla_userlevels_'.strtolower($host_nos['host_no']);
			$query = "SELECT joomla_userlevelsid, joomla_userlevels FROM $VT_UserLTablename WHERE presence = 1 ORDER BY sortorderid ASC";
			
			$values = array();
			$result = $db->pquery($query, array());

			if ( $db->num_rows($result) ) {
				while ( $row = $db->fetch_array($result) ) { 
					$values[$row['joomla_userlevelsid']] = $row['joomla_userlevels'];
				}
				//$log->debug("EXITING --> Joomlabridge_Util_Helper::getPickListValues( $host_no )". print_r($values, true) );
				$picklist_union = $picklist_union + $values;
			}
		}
		if ( count($picklist_union) ) {
			return $picklist_union;
		} else {
			return false;
		}
    }
	
	/**
     * Function which will give the picklist values for the Joomlabridge JUserLevels multiselect fields depending from the JoomlaHosts 
     * @param type $host_no -- string (JoomlaHost string unique identifier
     * @return type -- array of values
     */
    public static function getJHostID($recordId) {
		global $log;	
		$log->debug("ENTERING --> Joomlabridge_Util_Helper::getJHostID( $recordId )");
		
        $db = PearDatabase::getInstance();
			
        $query = "SELECT jhostid FROM vtiger_joomlabridge WHERE joomlabridgeid = ? ";
		
        $returnvalue = '';
        $result = $db->pquery($query, array($recordId));

		if ( $db->num_rows($result) ) {
			while ( $row = $db->fetch_array($result) ) { 
				$returnvalue = $row['jhostid'];
			}
			//$log->debug("EXITING --> Joomlabridge_Util_Helper::getPickListValues( $host_no )". print_r($values, true) );
			return $returnvalue;
		}
		return false;
    }


}
