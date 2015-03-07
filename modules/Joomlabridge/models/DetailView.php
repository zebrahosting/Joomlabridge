<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Joomlabridge_DetailView_Model extends Vtiger_DetailView_Model {

	/**
	 * Function to get the detail view links (links and widgets)
	 * @param <array> $linkParams - parameters which will be used to calicaulate the params
	 * @return <array> - array of link models in the format as below
	 *                   array('linktype'=>list of link models);
	 */
	public function getDetailViewLinks($linkParams) {		
		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$JBModuleModel = Vtiger_Module_Model::getInstance('Joomlabridge');
		$recordModel = $this->getRecord();

		$linkModelList = parent::getDetailViewLinks($linkParams);

		if($currentUserModel->hasModulePermission($JBModuleModel->getId())) {
			$basicActionLink = array(
				'linktype' => 'DETAILVIEWBASIC',
				'linklabel' => 'LBL_RESET_PASSWORD',
				'linkurl' => 'javascript:Joomlabridge.triggerResetPassword("index.php?module='.$this->getModule()->getName().'&record='.$recordModel->getId().
								'&action=JUserSave&mode=ResetPassword");',
				'linkicon' => '',
			);
			$linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
		}

		return $linkModelList;
	}
}
