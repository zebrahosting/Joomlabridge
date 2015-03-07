<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Joomlabridge_PushToJoomla_View extends Vtiger_Index_View {

    public function process(Vtiger_Request $request) {
		global $log;
		$log->debug('ENTERING --> Joomlabridge_PushToJoomla_View::process()');
		
/*
		// $eContacts = Joomlabridge_Joomlauser_Model::getContactbyEmail('test@test.com');
		
		// $eContacts = Joomlabridge_Joomlauser_Model::getContactbyJUserID(851);
		
		// $eContacts = Joomlabridge_FieldMap_Model::getJBMapByHostId(1);
		$tablelist = Joomlabridge_FieldMap_Model::getJBPullFields();
	
		$log->debug('Joomlabridge_FieldMap_Model::getJBPullFields()- results: '.print_r($tablelist, true) );
*/
		Joomlabridge_ScheduleSync_Model::runScheduledSync();
//		$log->debug('############## Joomlabridge joomla Host Querry: '.print_r($jdata, true) );
	
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		
//		$viewer->assign('JTABLES', $tablelist);	
		
		$viewer->view('PushToJoomla.tpl', $moduleName);
    }
	
}

?>