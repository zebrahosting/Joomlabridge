<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */
		
class Joomlabridge_PullFromJoomla_View extends Vtiger_Index_View {
    
    public function process(Vtiger_Request $request) {
		global $log;
		$log->debug('Joomlabridge_PullFromJoomla_View::process() --- STARTED');
		
		if (class_exists('Joomlabridge_SQLHost_Model')) {
			$log->debug('RUN --> Joomlabridge_PullFromJoomla_View::process() EXIST: Joomlabridge_SQLHost_Model' );	
		}
		if (class_exists('Joomlabridge_HikaShopUser_Model')) {
			$log->debug('RUN --> Joomlabridge_PullFromJoomla_View::process() EXIST: Joomlabridge_HikaShopUser_Model' );
		} else {
			$log->debug('RUN --> Joomlabridge_PullFromJoomla_View::process() NOT EXIST: Joomlabridge_HikaShopUser_Model' );	
		}
		
//		$jsql = new Joomlabridge_SQLHost_Model;
		
//		$HikaState = $jsql->get('hika_installed');
//		$AcyState = $jsql->get('acym_installed');
		
//		$log->debug('RUN --> Joomlabridge_PullFromJoomla_View::process() HIKA : '.print_r($HikaState, true));
		
//		Joomlabridge_Joomlauser_Model::getJSQLUserData();

//		Joomlabridge_Joomlauser_Model::getJSQLUserDatabyIDs( '36x5', '279' );
//		Joomlabridge_Joomlauser_Model::getLastPullDateJoomlaBridgebyJUserID( '36x5', '279' );
//		Joomlabridge_Joomlauser_Model::syncJoomlaUsertoVTbyIDs( '36x5', '279' );
		
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		
//		$viewer->assign('SYNCSTAT', $sync_stat);	
		
		$viewer->view('PullFromJoomla.tpl', $moduleName);
    }
 
}