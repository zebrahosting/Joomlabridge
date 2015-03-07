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

class ContactsJBHandler extends VTEventHandler {

	function handleEvent($eventName, $entityData) {
		global $log, $current_user;
		
		if($eventName == 'vtiger.contact.updatefromjoomla') {
			// Entity is about to be saved, take required action
			$log->debug('ENTERING --> Contacts_Event_Handler: vtiger.contact.updatefromjoomla -- in JoomlaBridge directory at $JdataPull : '.print_r($entityData, true) );
		}
	
		if($eventName == 'vtiger.entity.beforesave') {
			// Entity is about to be saved, take required action
			$log->debug('ENTERING --> Contacts_Event_Handler: vtiger.entity.beforesave -- in JoomlaBridge directory at $JdataPull : '.$JdataPull );
		}
		if($eventName == 'vtiger.entity.aftersave.final') {
			// Entity has been saved, take next action
			
			$JdataPull = vglobal('JdataPull'); //JdataPull = true, if this call is a part of Joomla data pull process (otherwise the variable false or not set)
			if( empty($JdataPull) || !isset($JdataPull) || ( $JdataPull === false) ) { $JdataPull = false;}
				
			$moduleName = $entityData->getModuleName();
			if ($moduleName == 'Contacts' && !$JdataPull ) {
			
				$log->debug('ENTERING --> Contacts_Event_Handler: vtiger.entity.aftersave.final -- in JoomlaBridge directory at $JdataPull : '.$JdataPull );
			
				$entityId = $entityData->getId();
				$entityDelta = new VTEntityDelta();
				$EmailHasChanged = $entityDelta->hasChanged($moduleName, $entityId, 'email');
				$FnameHasChanged = $entityDelta->hasChanged($moduleName, $entityId, 'firstname');
				$LnameHasChanged = $entityDelta->hasChanged($moduleName, $entityId, 'lastname');
				
				//Check the sync field change
				if ( $EmailHasChanged || $FnameHasChanged || $LnameHasChanged ) {
					// Email or Lastname or Firstname has changed

					// Check the Joomlabridge instance to this Contact record
					$wsid = vtws_getWebserviceEntityId('Contacts', $entityId);
					$JBInstances = Joomlabridge_Joomlauser_Model::getJoomlabridgesByConID($wsid);
									
					if ( $JBInstances ) {
					
						foreach( $JBInstances as $JBInstance ) {
					
							//Update the Joomlabridge instance to this Contact if it exists

							// Get Joomlabridge data to perform Update (else the not provided fields will be deleted)
							$jbwsid = $JBInstance['id'];
							try {
								$thisJBInstance = vtws_retrieve($jbwsid, $current_user);

							} catch (WebServiceException $ex) {
								$log->fatal('@@@ vtws_retrieve - Joomlabridge - failed: '.print_r($ex->getMessage(), true) );
								exit();
							}
							
							// Joomlabridge record Update
							try {
								// Data mapping	for the update

								$thisJBInstance['juser_name'] 	= $entityData->get('firstname')." ".$entityData->get('lastname');
								$thisJBInstance['juser_email'] 	= $entityData->get('email');
								
								//get sync real date-time
								$vt_now=new DateTimeField(null);
								$syncdate = $vt_now->getDisplayDate($current_user);  
								$synctime = $vt_now->getDisplayTime($current_user);
								
								$thisJBInstance['last_push_date'] 	= $syncdate." ".$synctime;
								
						
								$updateJB = vtws_update($thisJBInstance, $current_user);
								
								//Push data to back the Joomla instance (2 fields update: name, email)							
								$JHostId = $thisJBInstance['jhostid'];
								
								//get the Joomla database prefix
								$prefix = Joomlabridge_SQLHost_Model::getJDBprefix($JHostId);
								$jusertablename = implode("", array($prefix, 'users'));
								//Open the Joomla SQL by JHostId		
								$joomlaDB = Joomlabridge_SQLHost_Model::getJoomlaSQLaccess($JHostId);
								$joomlaDB->pquery("UPDATE ".$jusertablename." SET name = ?, email = ? WHERE id = ?", array($thisJBInstance['juser_name'], $thisJBInstance['juser_email'], $thisJBInstance['juser_id']) );
								$joomlaDB->disconnect();
								$log->fatal("@@@ Joomla Instance Updated (name, email): InstanceID = ".$thisJBInstance['jhostid'].", JUserID = ".$thisJBInstance['juser_id'] );
								
							} catch (WebServiceException $ex) {
								$log->fatal('@@@ vtws_update - Joomlabridge - failed: '.print_r($ex->getMessage(), true) );
								exit();
							}
							// set JS success message - some action call
						
						}

					} else {
						//To-Do: Do something if there is not a Joomlabridge record related to this Contact
					}
				}	//endif -- No action if the sync field were not changed
			}	//endif -- No action if the current module other then Contacts				
		}
	}
}
?>