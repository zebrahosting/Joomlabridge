<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * Joomlabridge Field Model Class
 */
class Joomlabridge_Field_Model extends Vtiger_Field_Model {

	/**
	 * Function to check whether field is ajax editable'
	 * @return <Boolean>
	 */
	public function isAjaxEditable() {
		if(!$this->isEditable() || $this->getName() == 'juser_id' 
								|| $this->getName() == 'juser_name' 
								|| $this->getName() == 'juser_username' 
								|| $this->getName() == 'juser_email' ) {
			return false;
		}
		return true;
	}
	
	/**
	 * Function to check whether the current field is editable
	 * @return <Boolean> - true/false
	 */
	public function isEditable() {
		if( $this->getName() == 'joomla_userlevels' ) {
			return true;
		} else {
			return parent::isEditable();
		}
	}
	
    public function getFieldDataType() {
//		global $log;
//		$log->debug("ENTERING --> Joomlabridge_Field_Model::getFieldDataType(".$this->getName().", ".$this->get('uitype').")");
		
        if($this->getName() == 'joomla_userlevels' || $this->get('uitype') == '133') {
			$this->fieldDataType = 'multipicklist';  //jbmultipicklist
            return 'multipicklist';
        }
        return parent::getFieldDataType();
    }

	/**
	 * Function to get all the available picklist values for the current field depending of the JoomlaHosts
	 * @return <Array> List of picklist values if the field is of type picklist or multipicklist, null otherwise.
	 */
	public function getPickListValues($host_no = NULL) {
		global $log;
		$log->debug("ENTERING --> Joomlabridge_Field_Model::getPickListValues( $host_no )" );
		
        $fieldDataType = $this->getFieldDataType();

        if($fieldDataType == 'multipicklist' || $this->get('uitype') == '133' ) {
		
			if( !isset($host_no) || empty($host_no) ) { 
				$picklistValues = Joomlabridge_Util_Helper::getAllPickListValues();
				$log->debug("EXITING --> Joomlabridge_Field_Model::getPickListValues( $host_no )".print_r($picklistValues, true) );
			} else {
				$picklistValues = Joomlabridge_Util_Helper::getPickListValues($host_no);
				$log->debug("EXITING --> Joomlabridge_Field_Model::getPickListValues( $host_no )".print_r($picklistValues, true) );
			}
			return $picklistValues;
		} else {
			return parent::getPicklistValues();		
		}
    }

	/**
	 * Function to get all the available picklist values for the current field depending of the JoomlaHosts
	 * @return <Array> List of picklist values if the field is of type picklist or multipicklist, null otherwise.
	 */
	public function getHostNo() {
		global $log;
		$log->debug("ENTERING --> Joomlabridge_Field_Model::getHostNo() " );
		
		$jhostid =  vglobal('current_jhostid'); //parameter to get the right display values
		if( isset($jhostid) && !empty($jhostid) ) {
			$wsid = vtws_getWebserviceEntityId('Joomlahosts', $jhostid);
			$JHostname = Joomlabridge_SQLHost_Model::getHostNoById($wsid);
			
			$log->debug("EXITING --> Joomlabridge_Field_Model::getHostNo() -> return : $JHostname" );
			return $JHostname;
		} else {
			return NULL;
		}
    }
	
	/**
	 * Function to get all the available picklist values for the current field depending of the JoomlaHosts
	 * @return <Array> List of picklist values if the field is of type picklist or multipicklist, null otherwise.
	 */
	public function getHostNoByRecordId($RecordId) {
		global $log;
		$log->debug("ENTERING --> Joomlabridge_Field_Model::getHostNoByRecordId( $RecordId ) " );
		
		$jhostid = Joomlabridge_Util_Helper::getJHostID($RecordId);

		if( isset($jhostid) && !empty($jhostid) ) {
			$wsid = vtws_getWebserviceEntityId('Joomlahosts', $jhostid);
			$JHostname = Joomlabridge_SQLHost_Model::getHostNoById($wsid);
			
			$log->debug("EXITING --> Joomlabridge_Field_Model::getHostNoByRecordId( $RecordId ) -> return : $JHostname" );
			return $JHostname;
		} else {
			return NULL;
		}
    }
	
	/**
	 * Function to retieve display value for a value
	 * @param <String> $value - value which need to be converted to display value
	 * @param <String> $record - if it is set, Jommla Host Id of the record which contains the $value
	 * @return <String> - converted display value
	 */
	public function getDisplayValue($value, $record=false, $recordInstance = false) {
		global $log;
		$log->debug("ENTERING --> Joomlabridge_Field_Model::getDisplayValue( Value = $value, Record = $record ) ");
		$MyJhost = new Joomlabridge_Field_Model;
		
		if( $this->getName() == 'jhostid' ) {
			vglobal('current_jhostid', $value);
			$log->debug("RUN --> Joomlabridge_Field_Model:: current_jhostid set, value = ".print_r($value, true) );
		}
		
		if( $this->getName() == 'joomla_userlevels' ) {
			$jhostid =  vglobal('current_jhostid'); //parameter to get the right display values
			if ( isset($jhostid) && !empty($jhostid) ) {
				$record = $jhostid;
			} elseif( $record !== '' ) {
				// $record as string "<a href='?module=Joomlahosts&view=Detail&record=7' title='Joomla SQL Hosts'>J3X Test</a>"
				$pos = strpos($record, 'record=');
				if( $pos > 0) {
					$part = stristr($record, "' title='", true);
					$part2 = stristr($part, "record=");
					$parts = explode( '=', $part2);
					$record = $parts[1];
				}
			}
			//$log->debug("RUN --> Joomlabridge_Field_Model:: current_jhostid get, value = ".print_r($record, true) );
		}
		
		if(!$this->uitype_instance) {
			$this->uitype_instance = Vtiger_Base_UIType::getInstanceFromField($this);
		}
		$uiTypeInstance = $this->uitype_instance;
		
		return $uiTypeInstance->getDisplayValue($value, $record, $recordInstance);
	}
	
    /**
     * Function whcih will get the database insert value format from user format
     * @param type $value in user format
     * @return type
     */
    public function getDBInsertValue($value) {
		global $log;
		$log->debug("ENTERING --> Joomlabridge_Field_Model::getDBInsertValue() Value = ".print_r($value, true) );
		
        if(!$this->uitype_instance) {
			$this->uitype_instance = Vtiger_Base_UIType::getInstanceFromField($this);
		}
		$uiTypeInstance = $this->uitype_instance;
        return $uiTypeInstance->getDBInsertValue($value);
    }
	
	/**
	 * Function to get the field details
	 * @return <Array> - array of field values
	 */
	public function getFieldInfo() {

        $fieldDataType = $this->getFieldDataType();

        if($fieldDataType == 'multipicklist') {
            $pickListValues = $this->getPickListValues();
            if(!empty($pickListValues)) {
                $this->fieldInfo['picklistvalues'] = $pickListValues;
            } else {
				$this->fieldInfo['picklistvalues'] = array();
			}
        }
		return parent::getFieldInfo();
	}

}
