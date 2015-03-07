<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.  Vtiger_Base_UIType
 *************************************************************************************/

class CustomModule_Multipicklist_UIType extends Vtiger_Multipicklist_UIType {

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return <String> - Template Name
	 */
	public function getTemplateName() {
		global $log;
		$log->debug("ENTERING --> CustomModule_Multipicklist_UIType::getTemplateName() : uitypes/MyMultiPicklist.tpl");
		return 'uitypes/MyMultiPicklist.tpl';
	}
	
    public function getListSearchTemplateName() {
		global $log;
		$fieldName = $this->get('field')->get('name');
		$log->debug("ENTERING --> CustomModule_Multipicklist_UIType::getListSearchTemplateName( $fieldName ) : uitypes/MyMultiSelectFieldSearchView.tpl");
        return 'uitypes/MyMultiSelectFieldSearchView.tpl';
    }
   

}