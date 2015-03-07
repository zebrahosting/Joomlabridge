<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_Joomlabridge_Module_Model extends Settings_Vtiger_Module_Model {

	var $baseTable = 'vtiger_joomla_fieldmaps';
	var $baseIndex = 'id';
	var $listFields = array(
			'id' 				=> 'LBL_F_ID'		,	//'FieldMap ID'
			'jfunction' 		=> 'LBL_SYNC_FUNC'	,	//'Sync Function'
			'jtable' 			=> 'LBL_J_TABLENAME',	//'J-Table name'
			'jfield' 			=> 'LBL_J_FIELDNAME',	//'J-Field name'
			'vt_module' 		=> 'LBL_VT_MODULE_N',	//'VT Module name'
			'vt_table' 			=> 'LBL_VT_TABLE_N'	,	//'VT Table name'
			'vt_field' 			=> 'LBL_VT_FIELD_N'	,	//'VT Field name'
			'push_to_joomla' 	=> 'LBL_PUSH_TO'	,	//'Push To Joomla'
			'pull_from_joomla' 	=> 'LBL_PULL_FROM'	,	//'Pull From Joomla'
			'description' 		=> 'LBL_DESC'		,	//'Description'
			'isactive' 			=> 'LBL_ACTIVE'		,	//'Active'
		);
	var $nameFields = array('');
	var $name = 'Joomlabridge';

	/**
	 * Function to get editable fields from this module
	 * @return <Array> list of editable fields
	 */
	public function getEditableFields() {
		$fieldsList = array(
			array('name' => 'jfunction',		'label' => 'LBL_SYNC_FUNC',		'type' => 'text'),
			array('name' => 'jtable', 			'label' => 'LBL_J_TABLENAME',	'type' => 'text'),
			array('name' => 'jfield',			'label' => 'LBL_J_FIELDNAME',	'type' => 'text'),
			array('name' => 'vt_module',		'label' => 'LBL_VT_MODULE_N',	'type' => 'text'),
			array('name' => 'vt_table',			'label' => 'LBL_VT_TABLE_N',	'type' => 'text'),
			array('name' => 'vt_field',			'label' => 'LBL_VT_FIELD_N',	'type' => 'text'),
			array('name' => 'push_to_joomla',	'label' => 'LBL_PUSH_TO',		'type' => 'radio'),
			array('name' => 'pull_from_joomla',	'label' => 'LBL_PULL_FROM',		'type' => 'radio'),
			array('name' => 'isactive',			'label' => 'LBL_ACTIVE',		'type' => 'radio'),
			array('name' => 'description',		'label' => 'LBL_DESC',			'type' => 'text'),
		);

		$fieldModelsList = array();
		foreach ($fieldsList as $fieldInfo) {
			$fieldModelsList[$fieldInfo['name']] = Settings_Joomlabridge_Field_Model::getInstanceByRow($fieldInfo);
		}
		return $fieldModelsList;
	}

	/**
	 * Function to get Create view url
	 * @return <String> Url
	 */
	public function getCreateRecordUrl() {
		return 'javascript:Settings_Joomlabridge_List_Js.triggerEdit(event, "index.php?module='.$this->getName().'&parent='.$this->getParentName().'&view=Edit")';
	}

	/**
	 * Function to get List view url
	 * @return <String> Url
	 */
	public function getListViewUrl() {
		return "index.php?module=".$this->getName()."&parent=".$this->getParentName()."&view=List";
	}


	/**
	 * Function to delete records
	 * @param <Array> $recordIdsList
	 * @return <Boolean> true/false
	 */
	public static function deleteRecords($recordIdsList = array()) {
		if ($recordIdsList) {
			$db = PearDatabase::getInstance();
			$query = 'DELETE FROM vtiger_joomla_fieldmaps WHERE id IN (' . generateQuestionMarks($recordIdsList). ')';
			$db->pquery($query, $recordIdsList);
			return true;
		}
		return false;
	}
}