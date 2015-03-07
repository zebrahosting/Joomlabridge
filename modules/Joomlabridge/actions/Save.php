<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Joomlabridge_Save_Action extends Vtiger_Save_Action {

	public function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$record = $request->get('record');

		if(!Users_Privileges_Model::isPermitted($moduleName, 'Save', $record)) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request) {
		$recordModel = $this->saveRecord($request);
		if($request->get('relationOperation')) {
			$parentModuleName = $request->get('sourceModule');
			$parentRecordId = $request->get('sourceRecord');
			$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentRecordId, $parentModuleName);
			//TODO : Url should load the related list instead of detail view of record
			$loadUrl = $parentRecordModel->getDetailViewUrl();
		} else if ($request->get('returnToList')) {
			$loadUrl = $recordModel->getModule()->getListViewUrl();
		} else {
			$loadUrl = $recordModel->getDetailViewUrl();
		}
		header("Location: $loadUrl");
	}

	/**
	 * Function to save record
	 * @param <Vtiger_Request> $request - values of the record
	 * @return <RecordModel> - record Model of saved record
	 */
	public function saveRecord($request) {
		global $log;
		$log->debug("ENTERING --> Joomlabridge_Save_Action::saveRecord() RECORD_ID = ".print_r($request->get('record'), true) );
		
		$recordModel = $this->getRecordModelFromRequest($request);
		$recordModel->save();
/*		
		$testdata = $request->getAll();
		$log->debug("RUN --> Joomlabridge_Save_Action::saveRecord() REQUEST = ".print_r($testdata, true) );
*/		
		//set relation
		$parentModuleName = 'Contacts';
		$parentModuleModel = Vtiger_Module_Model::getInstance($parentModuleName);
		$parentRecordId = $request->get('contactid');
		$relatedModule = $recordModel->getModule();
		$relatedRecordId = $request->get('record');

		$relationModel = Vtiger_Relation_Model::getInstance($parentModuleModel, $relatedModule);
		$relationModel->addRelation($parentRecordId, $relatedRecordId);

		return $recordModel;
	}
	

	/**
	 * Function to get the record model based on the request parameters
	 * @param Vtiger_Request $request
	 * @return Vtiger_Record_Model or Module specific Record Model instance
	 */
	protected function getRecordModelFromRequest(Vtiger_Request $request) {
		global $log;
		$log->debug("ENTERING --> Joomlabridge_Save_Action::getRecordModelFromRequest() RECORD_ID = ".print_r($request->get('record'), true) );

		$moduleName = $request->getModule();
		$recordId = $request->get('record');

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$pw_is_changed = false;
		if(!empty($recordId)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
			$modelData = $recordModel->getData();
			$recordModel->set('id', $recordId);
			$recordModel->set('mode', 'edit');
			
			$oldpassword = trim($recordModel->get('juser_passwordc'));
			$newpassword = trim($request->get('juser_passwordc'));
			if ( $oldpassword !== $newpassword ) {
				//password was changed need new hash for the store
				$pw_is_changed = true; //flag
			} else {
				//nothing to do with Joomla password
			}
			
		} else {
			$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
			$modelData = $recordModel->getData();
			$recordModel->set('mode', '');
		}
		
		

		$fieldModelList = $moduleModel->getFields();
		foreach ($fieldModelList as $fieldName => $fieldModel) {
			$fieldValue = $request->get($fieldName, null);
			$fieldDataType = $fieldModel->getFieldDataType();
			if($fieldDataType == 'time'){
				$fieldValue = Vtiger_Time_UIType::getTimeValueWithSeconds($fieldValue);
			}
			if($fieldDataType == 'multipicklist'){
				$fieldValue = $fieldModel->getDBInsertValue($fieldValue);
			}
			if($fieldName == 'juser_password' && $pw_is_changed ){
				$fieldValue = Joomlabridge_JUser_Helper::hashPassword( $request->get('juser_passwordc', null) );
			}
			if($fieldValue !== null) {
				if(!is_array($fieldValue)) {
					$fieldValue = trim($fieldValue);
				}
				$recordModel->set($fieldName, $fieldValue);
			}
		}
		return $recordModel;
	}
}
