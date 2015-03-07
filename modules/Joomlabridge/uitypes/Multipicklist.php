<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.  Vtiger_Base_UIType
 *************************************************************************************/

class Joomlabridge_Multipicklist_UIType extends Vtiger_Multipicklist_UIType {

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return <String> - Template Name
	 */
	public function getTemplateName() {
		global $log;
		$log->debug("ENTERING --> Joomlabridge_Multipicklist_UIType::getTemplateName() : uitypes/Multipicklist.tpl");
		return 'uitypes/MultiPicklist.tpl';
	}
	
    public function getListSearchTemplateName() {
		global $log;
		
		$fieldName = $this->get('field')->get('name');
		
		$log->debug("ENTERING --> Joomlabridge_Multipicklist_UIType::getListSearchTemplateName( $fieldName ) : uitypes/JBMultiSelectFieldSearchView.tpl");
        return 'uitypes/JBMultiSelectFieldSearchView.tpl';
    }

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getDisplayValue($value, $jhostid = NULL) {
		global $log;
		require_once 'include/Webservices/Utils.php';
		$log->debug("ENTERING --> Joomlabridge_Multipicklist_UIType::getDisplayValue( ".print_r($value, true).", ".print_r($jhostid, true)." )" );
		$retstring = '';
		$JUserLevels = explode( ' |##| ', $value );
		if( is_numeric($JUserLevels[0]) ) {
		
			if( isset($jhostid) && !empty($jhostid) ) { 
				//individual field display value
				$wsid = vtws_getWebserviceEntityId('Joomlahosts', $jhostid);
				$JHostname = Joomlabridge_SQLHost_Model::getHostNoById($wsid);
				$picklists = Joomlabridge_Util_Helper::getPickListValues( $JHostname );
				
				$log->debug("RUN --> Joomlabridge_Multipicklist_UIType ==> Joomlabridge_Util_Helper::getPickListValues( $JHostname ) ".print_r($picklists, true) );

			} else {
				//list field display value			
				$picklists = Joomlabridge_Util_Helper::getAllPickListValues();
				$log->debug("RUN --> Joomlabridge_Multipicklist_UIType ==> Joomlabridge_Util_Helper::getAllPickListValues() ".print_r($picklists, true) );

			}
			$jDisplayValue = array();
			foreach ($JUserLevels as $jkey => $jindex ) {
				$jDisplayValue[] = $picklists[$jindex];
			}
			$retstring = implode(', ', $jDisplayValue);
			
			//$log->debug("EXITING --> Joomlabridge_Multipicklist_UIType::getDisplayValue( ".print_r($value, true).", ".print_r($jhostid, true)." ) :: $retstring" );
		} else {
			$retstring = implode(', ', $JUserLevels);
		}
		return $retstring;
	}
    
    public function getDBInsertValue($value) {
		global $log;
		$log->debug("ENTERING --> Joomlabridge_Multipicklist_UIType::getDBInsertValue( $value ) ");
		
		if(is_array($value)){
            $value = implode(' |##| ', $value);
        } else {
			$value = explode( ',', $value );
			$value = implode(' |##| ', $value);
		}
        return $value;
	}
    
    

}