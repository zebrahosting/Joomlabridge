<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Joomlabridge_Module_Model extends Vtiger_Module_Model {

	/**
	 * Functions tells if the module supports workflow
	 * @return boolean
	 */
	public function isWorkflowSupported() {
		return true;
	}

	public function getSettingLinks() {
		vimport('~~modules/com_vtiger_workflow/VTWorkflowUtils.php');
		
		$layoutEditorImagePath = Vtiger_Theme::getImagePath('LayoutEditor.gif');
		$editWorkflowsImagePath = Vtiger_Theme::getImagePath('EditWorkflows.png'); 
		$settingsLinks = array();
		
		$settingsLinks[] = array( 
					'linktype'	=> 'LISTVIEWSETTING', 
					'linklabel' => 'LBL_SYNC_FIELDS', 
					'linkurl'	=> 'index.php?module=Joomlabridge&parent=Settings&view=List', 
					'linkicon'	=> '', 
		); 

		if(VTWorkflowUtils::checkModuleWorkflow($this->getName())) { 
			$settingsLinks[] = array( 
					'linktype'	=> 'LISTVIEWSETTING', 
					'linklabel' => 'LBL_EDIT_WORKFLOWS', 
					'linkurl'	=> 'index.php?parent=Settings&module=Workflows&view=List&sourceModule='.$this->getName(), 
					'linkicon'	=> $editWorkflowsImagePath 
			); 
		} 
		$settingsLinks[] = array(
					'linktype'	=> 'LISTVIEWSETTING',
					'linklabel' => 'LBL_EDIT_FIELDS',
					'linkurl'	=> 'index.php?parent=Settings&module=LayoutEditor&sourceModule='.$this->getName(),
					'linkicon'	=> $layoutEditorImagePath
		);
		
		$settingsLinks[] = array(
					'linktype'	=> 'LISTVIEWSETTING',
					'linklabel' => 'LBL_EDIT_PICKLIST_VALUES',
					'linkurl'	=> 'index.php?parent=Settings&module=Picklist&source_module='.$this->getName(),
					'linkicon'	=> ''
		);
		
		if($this->hasSequenceNumberField()) {
			$settingsLinks[] = array(
					'linktype'	=> 'LISTVIEWSETTING',
					'linklabel' => 'LBL_MODULE_SEQUENCE_NUMBERING',
					'linkurl'	=> 'index.php?parent=Settings&module=Vtiger&view=CustomRecordNumbering&sourceModule='.$this->getName(),
					'linkicon'	=> ''
				);
		}

		return $settingsLinks;
	}
}
?>