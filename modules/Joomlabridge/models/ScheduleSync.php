<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
require_once 'include/database/PearDatabase.php';

class Joomlabridge_ScheduleSync_Model extends Vtiger_Base_Model {


	/**
	 * Function to get all Joomlabridge record instances by HostId
	 * @param <Integer> $HostId
	 * @return Object of <Joomlabridge_FieldMap_Model> Joomlabridge record instances
	 */
	public static function runScheduledSync() {
		global $log, $adb, $current_user;
		$log->debug('ENTERING --> Joomlabridge_ScheduleSync_Model::runScheduledSync()');
		
		vglobal('JdataPull', true);
		
		define('_JB_QUEUE_FIRST', 0);
		define('_JB_QUEUE_ROWS', 200);
		
		require_once 'vtlib/Vtiger/Utils.php';	
		vimport('~~modules/com_vtiger_workflow/VTWorkflowUtils.php');
		$util = new VTWorkflowUtils();
		$util->adminUser();  //$current_user is an admin

		//Check the Queue table if it exists
		if ( !Vtiger_Utils::CheckTable('vtiger_joomlabridge_queue') ) {
			Vtiger_Utils::CreateTable(
				'vtiger_joomlabridge_queue', 
					'(id int(11) NOT NULL AUTO_INCREMENT, '.	//1
					'jhostid varchar(255) NOT NULL, '.			//2
					'offset int(11) NOT NULL, '.				//3
					'rows int(11) NOT NULL, '.					//4
				'primary key (id))');
		}
		//Check the initial Queue
		if ( Vtiger_Utils::CheckTable('vtiger_joomlabridge_queue') ) {
			//Check the table rows if exists
			$r = $adb->pquery("SELECT * FROM vtiger_joomlabridge_queue WHERE id = 1;", array());
			if ( !$adb->num_rows($r) ) { 
				//Get the first active Joomlabridge-JoomlaHost id in vtws format e.g. 36x7
				$allHostIds = Joomlabridge_SQLHost_Model::getHostIds();

				if ( $allHostIds ) {
					$JHostId = $allHostIds[0];
				} else {
					//If not any active Joomlabridge-JoomlaHost then return with FALSE
					$JHostId = 0;
					Vtiger_Utils::ModuleLog("Scheduled Pull from Joomla => ERROR", false);
					return false ;
				}	

				// Insert the first - initial batch data for the sync - if the table is empty
				$newquery = "INSERT INTO vtiger_joomlabridge_queue (id, jhostid, offset, rows) VALUES (1, '".$JHostId."', "._JB_QUEUE_FIRST.", "._JB_QUEUE_ROWS.");";
				Vtiger_Utils::ExecuteQuery($newquery);
				$log->fatal('vtiger_joomlabridge_queue Table added to the database');
			}
		}
	
		//the Queue table is already set
		$result = $adb->pquery("SELECT * FROM vtiger_joomlabridge_queue WHERE id = 1;", array());
		
		if ( $adb->num_rows($result) ) {
			$syncqueue_row = $adb->fetch_row($result, 0);
			
			$JHostId	= $syncqueue_row['jhostid'];
			$offset		= $syncqueue_row['offset'];
			$row_count	= $syncqueue_row['rows'];
			
			$log->debug("RUN --> Joomlabridge_ScheduleSync_Model::runScheduledSync() ---> $JHostId , $offset , $row_count " );
			
			//run the sync for the batch defined by $JHostId, $offset, $row_count
			$sync_stat = Joomlabridge_Joomlauser_Model::syncJoomlaUsertoVT( $JHostId, $offset, $row_count );
			
			$handled_rows = $sync_stat['handled'];
			
			$log->debug("RETURN --> Joomlabridge_ScheduleSync_Model::runScheduledSync() ---> Handled rows : $handled_rows ". print_r($sync_stat, true) );

		
			if ( $handled_rows < _JB_QUEUE_ROWS && !empty($sync_stat) ) {
				//this Jhost is handled -> lets go to the next if it exists

				//Get the first active Joomlabridge-JoomlaHost
				$allHostIds = Joomlabridge_SQLHost_Model::getHostIds();
				$nextJHostId = 0;
				$hostindex = 0;
				
				if ( count($allHostIds) > 0 ) {
					$i = 1;
					while ( $i <= count($allHostIds) ) {
						if ( $allHostIds[ $i-1 ] == $JHostId ) {
							$hostindex = $i;
							$i = count($allHostIds) + 1;
						}
						$i++;
					}
				} else {
					//If not any active Joomlabridge-JoomlaHost then return with FALSE
					Vtiger_Utils::ModuleLog("Scheduled Pull from Joomla => ERROR", false);
					return false ;
				}
				
				if ( $hostindex ) {
					//selection successful
					
					if ( $hostindex < count($allHostIds) ) {
						$nextJHostId = $allHostIds[ $hostindex ];
					} else {
						$nextJHostId = $allHostIds[ 0 ]; //back to the beginning
					}
					//The next active HostId exists or starting from the beginning -> reset the queue for this host
					$result = $adb->pquery("UPDATE vtiger_joomlabridge_queue SET jhostid = ?, offset = ?, rows = ? WHERE id = 1 ;", array( $nextJHostId, _JB_QUEUE_FIRST, _JB_QUEUE_ROWS ) );
					// Vtiger_Utils::ModuleLog("Scheduled Pull from Joomla => JHostId : $nextJHostId, Offset : "._JB_QUEUE_FIRST, true);					

				} else {
					//If not any active Joomlabridge-JoomlaHost then return with FALSE
					Vtiger_Utils::ModuleLog("Scheduled Pull from Joomla => ERROR", false);
					return false ;				
				}
			
			} elseif ( $handled_rows == _JB_QUEUE_ROWS ) {
				//prepare the next batch for this host
				$newoffset = $offset + _JB_QUEUE_ROWS;
				$result = $adb->pquery("UPDATE vtiger_joomlabridge_queue SET offset = ? WHERE id = 1", array($newoffset) );
				// Vtiger_Utils::ModuleLog("Scheduled Pull from Joomla => JHostId : $JHostId, Offset : $newoffset", true);
				
			} else {
				//this is an error path: To-Do, handle how to
				Vtiger_Utils::ModuleLog("Scheduled Pull from Joomla => ERROR", false);
				return false ;
			}
		} else {
			//this is an error path: To-Do, handle how to
			Vtiger_Utils::ModuleLog("Scheduled Pull from Joomla => ERROR", false);
			return false ;		
		}

		return true;
	}


}
?>
