<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
require_once 'data/VTEntityDelta.php';
require_once 'include/Webservices/Utils.php';

class JoomlabridgeHandler extends VTEventHandler {

	function handleEvent($eventName, $entityData) {
		global $log, $current_user;
	
		//Event: vtiger.entity.beforesave
		if($eventName == 'vtiger.entity.beforesave') {
			// Entity is about to be saved, take required action
			$moduleName = $entityData->getModuleName();

			if ($moduleName == 'Joomlabridge') {
			
				$log->debug('ENTERING --> Joomlabridge_Event_Handler: vtiger.entity.beforesave - $JdataPull : '.$JdataPull );				
				
/*				$entityId = $entityData->getId();
				$entityDelta = new VTEntityDelta();
				$BlockHasChanged = $entityDelta->hasChanged($moduleName, $entityId, 'juser_block');
				$PasswordHasChanged = $entityDelta->hasChanged($moduleName, $entityId, 'juser_password');
				$ULevelsHasChanged = $entityDelta->hasChanged($moduleName, $entityId, 'joomla_userlevels');			*/	

				//Check this is a New vtiger created Joomlabridge ( juser_id = 0 in this case)
				$JUserId = (int) $entityData->get('juser_id');
				$JHostId = $entityData->get('jhostid');
				$wsid = vtws_getWebserviceEntityId('Joomlahosts', $JHostId);
					
				if ( $JUserId ) { 
					//existing JUser data to Update in Joomla SQL from the JBridge - it is possible in after.save event
					//Note: the VTEntityDelta() data is not available before save, so we do not know what was changed
				
				} else { //new JUser data to push into Joomla
				
					$NewJUser = Joomlabridge_JUser_Model::getInstance( $JUserId, $wsid);
					
					//Prepare data for the first save of the new Joomla User
					$NewJUser->name 			= $entityData->get('juser_name');
					$NewJUser->username 		= $entityData->get('juser_username');
					$NewJUser->email 			= $entityData->get('juser_email');
					$NewJUser->password 		= $entityData->get('juser_password');
					$NewJUser->block 			= $entityData->get('juser_block');
					$NewJUser->registerdate 	= $entityData->get('juser_registerdate');
					//get the User Level (User Groups) array:
					$NewJUser->groups 			= Joomlabridge_FieldMap_Model::ConvertVTULevelsToJoomla( $entityData->get('joomla_userlevels') );
					//Save the New User and get back the UserId				
					$NewUserId = $NewJUser->save();				
//					$log->debug('@@@ Joomlabridge entitydata before save --- NewUserId : '.print_r($NewUserId, true) );

					//Callback to the vtiger record the saved new User Id:
					$entityData->set('juser_id', $NewUserId);
					//get last-push-date real date-time and set for the record
					$vt_now = new DateTimeField(null);
					$lpdate = $vt_now->getDisplayDate($current_user);  
					$lptime = $vt_now->getDisplayTime($current_user);
					$lpdatetime = $lpdate." ".$lptime;					
					$entityData->set('last_push_date', $lpdatetime);

				}  //Finish the New Joomla User save action.
			}
		}
		if($eventName == 'vtiger.entity.aftersave') {
			// Entity has been saved, take next action
			
			$moduleName = $entityData->getModuleName();

			if ($moduleName == 'Joomlabridge') {
			
				$log->debug('ENTERING --> Joomlabridge_Event_Handler: vtiger.entity.aftersave - $JdataPull : '.$JdataPull );				
				
				$entityId = $entityData->getId();
				$entityDelta = new VTEntityDelta();
				$BlockHasChanged = $entityDelta->hasChanged($moduleName, $entityId, 'juser_block');
				$PasswordHasChanged = $entityDelta->hasChanged($moduleName, $entityId, 'juser_password');
				$ULevelsHasChanged = $entityDelta->hasChanged($moduleName, $entityId, 'joomla_userlevels');				

				//Check this is a New vtiger created Joomlabridge ( juser_id = 0 in this case)
				$JUserId = (int) $entityData->get('juser_id');
				$JHostId = $entityData->get('jhostid');
				$wsid = vtws_getWebserviceEntityId('Joomlahosts', $JHostId);
					
				if ( $JUserId ) { //existing JUser data to Update in Joomla SQL from the JBridge
				
					if( $BlockHasChanged || $PasswordHasChanged || $ULevelsHasChanged ) { //Update if there was a change
					
						$log->debug('ENTERING --> Joomlabridge_Event_Handler: vtiger.entity.aftersave - Update Joomla SQL Changes > $BlockHasChanged : '.$BlockHasChanged.' , $PasswordHasChanged : '.$PasswordHasChanged.' , $ULevelsHasChanged : '.$ULevelsHasChanged );

						$JUser = Joomlabridge_JUser_Helper::getJUser( $JUserId, $wsid );
						
						//Only 3 field for change in this case: User level, Block? & Password if it is necessary - all other field are controlled from other place
						$JUser->block 		= $entityData->get('juser_block');
						$JUser->password 	= $entityData->get('juser_password');
						$JUser->groups 		= Joomlabridge_FieldMap_Model::ConvertVTULevelsToJoomla( $entityData->get('joomla_userlevels') );
											
						$save_reasult = $JUser->save();	//Update only
						
						//To-DO: save the callback datetime ?????????????
						
						//get last-push-date real date-time and set for the record
						$vt_now = new DateTimeField(null);
						$lpdate = $vt_now->getDisplayDate($current_user);  
						$lptime = $vt_now->getDisplayTime($current_user);
						$lpdatetime = $lpdate." ".$lptime;					

						global $adb;
						$adb->pquery("UPDATE vtiger_joomlabridge SET last_push_date = ? WHERE joomlabridgeid = ?", array($lpdatetime, $entityId) );
				
						
						//To-DO: send email to the Joomla User, if the password, or username was changed.
						//The email will be sent out by workflow.
						
					}
				
				} else { 
					//with new JUser Joomlabridge data nothing to do here
				
				}
			}
		}
	}
}
?>