<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

require_once 'modules/Vtiger/CRMEntity.php';
require_once 'vtlib/Vtiger/Link.php';
require_once 'vtlib/Vtiger/Module.php';
require_once 'vtlib/Vtiger/Menu.php';
require_once 'vtlib/Vtiger/Event.php';

class Joomlabridge extends CRMEntity {
	var $log;
	var $db;
	
	/**
	 * Base module table and index.
	 */
	var $table_name = 'vtiger_joomlabridge';
	var $table_index= 'joomlabridgeid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_joomlabridgecf', 'joomlabridgeid');
	var $related_tables = Array('vtiger_joomlabridgecf'=>array('joomlabridgeid','vtiger_joomlabridge', 'joomlabridgeid'));

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('vtiger_crmentity', 'vtiger_joomlabridge', 'vtiger_joomlabridgecf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
			'vtiger_crmentity' => 'crmid',
			'vtiger_joomlabridge' => 'joomlabridgeid',
			'vtiger_joomlabridgecf'=>'joomlabridgeid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = Array (
			/* Format: Field Label => Array(tablename, columnname) */
			// tablename should not have prefix 'vtiger_'
			'Bridge ID' 			=> Array('joomlabridge', 'jb_no')				,
			'Contact Name' 			=> Array('joomlabridge', 'contactid')			,
			'Joomla Instance' 		=> Array('joomlabridge', 'jhostid')				,
			'J-User ID' 			=> Array('joomlabridge', 'juser_id')			,
			'J-User Username' 		=> Array('joomlabridge', 'juser_username')		,
			'J-User Email' 			=> Array('joomlabridge', 'juser_email')			,
			'Blocked?' 				=> Array('joomlabridge', 'juser_block')			,
			'J-User Levels' 		=> Array('joomlabridge', 'joomla_userlevels')	,
			'Last Visit Date' 		=> Array('joomlabridge', 'juser_lastvisitdate')	,
	);
	var $list_fields_name = Array (
			/* Format: Field Label => fieldname */
			'Bridge ID' 			=> 'jb_no'				,
			'Contact Name' 			=> 'contactid'			,
			'Joomla Instance' 		=> 'jhostid'			,
			'J-User ID' 			=> 'juser_id'			,
			'J-User Username' 		=> 'juser_username'		,
			'J-User Email' 			=> 'juser_email'		,
			'Blocked?' 				=> 'juser_block'		,
			'J-User Levels' 		=> 'joomla_userlevels'	,
			'Last Visit Date' 		=> 'juser_lastvisitdate',
	);

	// Make the field link to detail view
	var $list_link_field = 'jb_no';

	// For Popup listview and UI type support
	var $search_fields = Array(
			/* Format: Field Label => Array(tablename, columnname) */
			// tablename should not have prefix 'vtiger_'
			'Bridge ID' 		=> Array('joomlabridge', 'jb_no')				,
			'Contact Name' 		=> Array('joomlabridge', 'contactid')			,
			'Joomla Instance' 	=> Array('joomlabridge', 'jhostid')				,
			'Last Visit Date' 	=> Array('joomlabridge', 'juser_lastvisitdate')	,
			'Assigned To' 		=> Array('vtiger_crmentity', 'assigned_user_id'),
	);
	var $search_fields_name = Array (
			/* Format: Field Label => fieldname */
			'Bridge ID' 		=> 'jb_no'				,
			'Contact Name' 		=> 'contactid'			,
			'Joomla Instance' 	=> 'jhostid'			,
			'Last Visit Date' 	=> 'juser_lastvisitdate',
			'Assigned To' 		=> 'assigned_user_id'	,
	);

	// For Popup window record selection
	var $popup_fields = Array ('jb_no');

	// For Alphabetical search
	var $def_basicsearch_col = 'jb_no';

	// Column value to use on detail view record text display
	var $def_detailview_recname = 'jb_no';

	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array(
			'jb_no'				, 
			'contactid'			,
			'jhostid'			,
			'juser_id'			,
			'juser_name'		,
			'juser_username'	,
			'juser_email'		,
			'juser_password'	,
			'joomla_userlevels'	,
			'assigned_user_id'	,
			'createdtime'		,
			'modifiedtime'		,
		);

	var $default_order_by = 'jb_no';
	var $default_sort_order='ASC';
	
	function Joomlabridge(){
		$this->log = LoggerManager::getLogger('joomlabridge');  //TODO Check it
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('Joomlabridge');
    }

    /**
     * Invoked when special actions are performed on the module.
     * @param String Module name
     * @param String Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
     */

    function vtlib_handler($modulename, $event_type) {
		global $log;
		global $adb;
		
		require_once 'vtlib/Vtiger/Module.php';
		require_once 'vtlib/Vtiger/Event.php';
		require_once 'modules/ModTracker/ModTracker.php';
		$moduleInstance = Vtiger_Module::getInstance($modulename);
		$tabid = $moduleInstance->getId();
			
		if ($event_type == 'module.postinstall') {
			// TODO Handle actions after this module is installed.
			
			// Add Joomlabridge module to the related list of Contacts module
			$contactsModuleInstance = Vtiger_Module::getInstance('Contacts');
			$contactsModuleInstance->setRelatedList($moduleInstance, 'Joomlabridge', Array('ADD'), 'get_dependents_list');

			// Add Comments widget to Joomlabridge module
			$modcommentsModuleInstance = Vtiger_Module::getInstance('ModComments');
			if($modcommentsModuleInstance && file_exists('modules/ModComments/ModComments.php')) {
				include_once 'modules/ModComments/ModComments.php';
				if(class_exists('ModComments')) ModComments::addWidgetTo(array('Joomlabridge'));
			}

			//Check the existing module sequence numbering
			$result = $adb->pquery("SELECT 1 FROM vtiger_modentity_num WHERE semodule = ? AND active = 1", array($modulename));
			if (!($adb->num_rows($result))) {
				//Initialize module sequence for the module
				$adb->pquery("INSERT INTO vtiger_modentity_num values(?,?,?,?,?,?)", array($adb->getUniqueId("vtiger_modentity_num"), $modulename, 'JBridge', 1, 1, 1));
			}

			$this->addJoomlaFieldMapsTable(); // bootstrapp added
			$this->addSettingsLinks();
            $this->addActionMapping();
            $this->addLinksForJoomlabridge();
			
			//Check the existing EventHandler settings
			$events = $adb->pquery("SELECT handler_class FROM vtiger_eventhandlers WHERE handler_class = ?", array('JoomlabridgeHandler'));
			if (!($adb->num_rows($events))) {
				$this->AddEventHandler($moduleInstance);
			}
			
			$c_events = $adb->pquery("SELECT handler_class FROM vtiger_eventhandlers WHERE handler_class = ?", array('ContactsJBHandler'));
			if (!($adb->num_rows($c_events))) {			
				$this->AddContactsEventHandler();
			}
			
			ModTracker::enableTrackingForModule($tabid);
			
			//Add the module default workflow to the database with check the existing workflow settings 
			$summary = 'Send Email to Joomla User when the Joomla Password was reset';
			$result = $adb->pquery("SELECT module_name FROM com_vtiger_workflows WHERE module_name = ? AND summary = ?", array($modulename, $summary));
			if (!($adb->num_rows($result))) {
				//Add the module default workflow to the database if it is missing
				$this->populateJoomlabridgeWorkflows($adb);
			}
						
        } else if ($event_type == 'module.disabled') {
			// TODO Handle actions after this module is disabled.
			
            $this->removeLinksForJoomlabridge();
			$this->removeSettingsLinks();
			$this->removeActionMapping();
			
			$em = new VTEventsManager($adb);
			$em->setHandlerInActive('Joomlabridge');
			
			ModTracker::disableTrackingForModule($tabid);

        } else if ($event_type == 'module.enabled') {
			// TODO Handle actions after this module is enabled.
			
			//Check the existing EventHandler settings
			$events = $adb->pquery("SELECT module_name FROM vtiger_eventhandler_module WHERE module_name = ?", array($modulename));
			if (!($adb->num_rows($events))) {
				$this->AddEventHandler($moduleInstance);
			} else {
			$em = new VTEventsManager($adb);
			$em->setHandlerActive('Joomlabridge');			
			}
			
			ModTracker::enableTrackingForModule($tabid);
			
			// Add Joomlabridge module to the related list of Contacts module
			$contactsModuleInstance = Vtiger_Module::getInstance('Contacts');
			$contactsModuleInstance->setRelatedList($moduleInstance, 'Joomlabridge', Array('ADD'), 'get_dependents_list');

			// Add Comments widget to Joomlabridge module
			$modcommentsModuleInstance = Vtiger_Module::getInstance('ModComments');
			if($modcommentsModuleInstance && file_exists('modules/ModComments/ModComments.php')) {
				include_once 'modules/ModComments/ModComments.php';
				if(class_exists('ModComments')) ModComments::addWidgetTo(array('Joomlabridge'));
			}

			//Check the existing module sequence numbering
			$result = $adb->pquery("SELECT 1 FROM vtiger_modentity_num WHERE semodule = ? AND active = 1", array($modulename));
			if (!($adb->num_rows($result))) {
				//Initialize module sequence for the module
				$adb->pquery("INSERT INTO vtiger_modentity_num values(?,?,?,?,?,?)", array($adb->getUniqueId("vtiger_modentity_num"), $modulename, 'JBridge', 1, 1, 1));
			}
			
            $this->addLinksForJoomlabridge();
			$this->addSettingsLinks();
			$this->addActionMapping();

        } else if ($event_type == 'module.preuninstall') {
			// TODO Handle actions before this module is uninstalled.
            $this->removeLinksForJoomlabridge();
			$this->removeSettingsLinks();
			$this->removeActionMapping();

        } else if ($event_type == 'module.preupdate') {
            // TODO Handle actions before this module is updated.

        } else if ($event_type == 'module.postupdate') {
            // TODO Handle actions after this module is updated.

			// Add Comments widget to Joomlabridge module
			$modcommentsModuleInstance = Vtiger_Module::getInstance('ModComments');
			if($modcommentsModuleInstance && file_exists('modules/ModComments/ModComments.php')) {
				include_once 'modules/ModComments/ModComments.php';
				if(class_exists('ModComments')) ModComments::addWidgetTo(array('Joomlabridge'));
			}
			//Check the existing module sequence numbering
			$result = $adb->pquery("SELECT 1 FROM vtiger_modentity_num WHERE semodule = ? AND active = 1", array($modulename));
			if (!($adb->num_rows($result))) {
				//Initialize module sequence for the module
				$adb->pquery("INSERT INTO vtiger_modentity_num values(?,?,?,?,?,?)", array($adb->getUniqueId("vtiger_modentity_num"), $modulename, 'JBridge', 1, 1, 1));
			}
			//Check the existing EventHandler settings
			$events = $adb->pquery("SELECT handler_class FROM vtiger_eventhandlers WHERE handler_class = ?", array('JoomlabridgeHandler'));
			if (!($adb->num_rows($events))) {
				$this->AddEventHandler($moduleInstance);
			}
			
			$c_events = $adb->pquery("SELECT handler_class FROM vtiger_eventhandlers WHERE handler_class = ?", array('ContactsJBHandler'));
			if (!($adb->num_rows($c_events))) {			
				$this->AddContactsEventHandler();
			}
			
			ModTracker::enableTrackingForModule($tabid);
			
			//Check the existing workflow settings 
			$summary = 'Send Email to Joomla User when the Joomla Password was reset';
			$result = $adb->pquery("SELECT module_name FROM com_vtiger_workflows WHERE module_name = ? AND summary = ?", array($modulename, $summary));
			if (!($adb->num_rows($result))) {
				//Add the module default workflow to the database if it is missing
				$this->populateJoomlabridgeWorkflows($adb);
			}
			
			$this->removeOldSettingsLinks(); //it will remove all old
			$this->removeOldActionMapping(); //it will remove unused old
			$this->removeOldLinksForJoomlabridge(); //it will remove unused old
			
			$this->addSettingsLinks();	//it will add new for th update
        }
    }

    /** Function to handle module specific operations when saving a entity
	 */
	function save_module($module){
	}



	/**
	 * Create Joomla JUserSync basic fieldmaps Table
	 * @param String tablename to create 'vtiger_joomla_fieldmaps'
	 * @param String table creation criteria like '(columnname columntype, ....)'
	 */
	function addJoomlaFieldMapsTable(){
		global $log;
		require_once 'vtlib/Vtiger/Utils.php';		
		$log->debug('ENTERING --> addJoomlaFieldMapsTable() --- Joomla FieldMaps Table adding started');

		if ( !Vtiger_Utils::CheckTable('vtiger_joomla_fieldmaps') ) {
			Vtiger_Utils::CreateTable(
				'vtiger_joomla_fieldmaps', 
					'(id int(11) NOT NULL AUTO_INCREMENT, '.	//1
					'jfunction varchar(100) NOT NULL, '.		//2
					'jtable varchar(100) NOT NULL, '.			//3
					'jfield varchar(100) NOT NULL, '.			//4
					'vt_module varchar(100) NOT NULL, '.		//5
					'vt_table varchar(100) NOT NULL, '.			//6
					'vt_field varchar(100) NOT NULL, '.			//7
					'push_to_joomla tinyint(4), '.				//8
					'pull_from_joomla tinyint(4), '.			//9
					'isactive tinyint(4), '.					//10
					'description text, '.						//11
				'primary key (id))');

			if ( Vtiger_Utils::CheckTable('vtiger_joomla_fieldmaps') ) {
				$fquery = "INSERT INTO vtiger_joomla_fieldmaps (id, jfunction, jtable, jfield, vt_module, vt_table, vt_field, push_to_joomla, pull_from_joomla, isactive, description) VALUES".
			"(1, 'JUserSync', '#__users', 'id', 'Joomlabridge', 'vtiger_joomlabridge', 'juser_id', 0, 1, 1, 'J-User ID'),".
			"(2, 'JUserSync', '#__users', 'name', 'Joomlabridge', 'vtiger_joomlabridge', 'juser_name', 1, 1, 1, 'J-User Name'),".
			"(3, 'JUserSync', '#__users', 'username', 'Joomlabridge', 'vtiger_joomlabridge', 'juser_username', 1, 1, 1, 'J-User Username'),".
			"(4, 'JUserSync', '#__users', 'email', 'Joomlabridge', 'vtiger_joomlabridge', 'juser_email', 1, 1, 1, 'J-User Email'),".
			"(5, 'JUserSync', '#__users', 'password', 'Joomlabridge', 'vtiger_joomlabridge', 'juser_password', 1, 1, 1, 'J-User Password'),".
			"(6, 'JUserSync', '#__users', 'block', 'Joomlabridge', 'vtiger_joomlabridge', 'juser_block', 1, 1, 1, 'Blocked user?'),".
			"(7, 'JUserSync', '#__users', 'registerdate', 'Joomlabridge', 'vtiger_joomlabridge', 'juser_registerdate', 0, 1, 1, 'Register Date'),".
			"(8, 'JUserSync', '#__users', 'lastvisitdate', 'Joomlabridge', 'vtiger_joomlabridge', 'juser_lastvisitdate', 0, 1, 1, 'Last Visit Date'),".
			"(9, 'JUserSync', '#__users', 'name', 'Contacts', 'vtiger_contactdetails', 'lastname', 1, 1, 1, 'First + Last name'),".
			"(10, 'JUserSync', '#__users', 'email', 'Contacts', 'vtiger_contactdetails', 'email', 1, 1, 1, 'Email'),".
			"(11, 'JUserLevel', '#__user_usergroup_map', 'group_id', 'Joomlabridge', 'vtiger_joomlabridge', 'joomla_userlevels', 1, 1, 1, 'J-User Level');";

				Vtiger_Utils::ExecuteQuery($fquery);
			}
		}

		$log->debug('EXIT FROM: addJoomlaFieldMapsTable()');
        $log->fatal('Joomla FieldMaps Table added');
    }	
	
    /**
     * To add Integration->Joomlabridge block in Settings page
     */
    function addSettingsLinks(){
        global $log;
        $adb = PearDatabase::getInstance();
        $integrationBlock = $adb->pquery('SELECT * FROM vtiger_settings_blocks WHERE label=?',array('LBL_INTEGRATION'));
        $integrationBlockCount = $adb->num_rows($integrationBlock);
        
        // To add Block
        if($integrationBlockCount > 0){
            $blockid = $adb->query_result($integrationBlock, 0, 'blockid');
        }else{
            $blockid = $adb->getUniqueID('vtiger_settings_blocks');
            $sequenceResult = $adb->pquery("SELECT max(sequence) as sequence FROM vtiger_settings_blocks", array());
            if($adb->num_rows($sequenceResult)) {
                $sequence = $adb->query_result($sequenceResult, 0, 'sequence');
            }
            $adb->pquery("INSERT INTO vtiger_settings_blocks(blockid, label, sequence) VALUES(?,?,?)", array($blockid, 'LBL_INTEGRATION', ++$sequence));
        }
        
        // To add a Field
        $fieldid = $adb->getUniqueID('vtiger_settings_field');
        $adb->pquery("INSERT INTO vtiger_settings_field(fieldid, blockid, name, iconpath, description, linkto, sequence, active)
            VALUES(?,?,?,?,?,?,?,?)", array($fieldid, $blockid, 'LBL_SYNC_FIELDS', '','LBL_JOOMLABRIDGE_CONFIGURATION', 'index.php?module=Joomlabridge&parent=Settings&view=List', 2, 0));
        $log->fatal('Joomlabridge Settings Block and Field added');
    }
    
    /**
     * To delete Integration->Joomlabridge block in Settings page
     */
    function removeSettingsLinks(){
        global $log;
        $adb = PearDatabase::getInstance();
        $adb->pquery('DELETE FROM vtiger_settings_field WHERE name=?', array('LBL_SYNC_FIELDS'));
        $log->fatal('Joomlabridge Settings Field Removed');
    }
	
    /**
     * To delete Integration->Joomlabridge links in Settings page at update
    */
    function removeOldSettingsLinks(){
        global $log;
        $adb = PearDatabase::getInstance();
        $adb->pquery("DELETE FROM vtiger_settings_field WHERE linkto LIKE '%Joomlabridge%'", array());
        $log->fatal('OLD Joomlabridge Settings Field Removed'); 
    }

	/**
	 * To enable(PushToJoomla & PullFromJoomla) tool in profile
	 */
	 function addActionMapping() {
		global $log;
		$adb = PearDatabase::getInstance();
		$module = new Vtiger_Module();
		$moduleInstance = $module->getInstance('Joomlabridge');

/*		//To add actionname as PushToJoomla
		$maxActionIdresult = $adb->pquery('SELECT max(actionid+1) AS actionid FROM vtiger_actionmapping',array());
		if($adb->num_rows($maxActionIdresult)) {
			$actionId = $adb->query_result($maxActionIdresult, 0, 'actionid');
		}
		$adb->pquery('INSERT INTO vtiger_actionmapping
					 (actionid, actionname, securitycheck) VALUES(?,?,?)',array($actionId,'PushToJoomla',0));
		$moduleInstance->enableTools('PushToJoomla');
		$log->fatal('PushToJoomla ActionName Added');															*/
		
		//To add actionname as PullFromJoomla
		$maxActionIdresult = $adb->pquery('SELECT max(actionid+1) AS actionid FROM vtiger_actionmapping',array());
		if($adb->num_rows($maxActionIdresult)) {
			$actionId = $adb->query_result($maxActionIdresult, 0, 'actionid');
		}
		$adb->pquery('INSERT INTO vtiger_actionmapping
					 (actionid, actionname, securitycheck) VALUES(?,?,?)',array($actionId,'PullFromJoomla',0));
		$moduleInstance->enableTools('PullFromJoomla');
		$log->fatal('PullFromJoomla ActionName Added');
	}

	/**
	 * To remove(PushToJoomla & PullFromJoomla) tool from profile
	 */
	function removeActionMapping() {
		global $log;
		$adb = PearDatabase::getInstance();
		$module = new Vtiger_Module();
		$moduleInstance = $module->getInstance('Joomlabridge');
		
/*		$moduleInstance->disableTools('PushToJoomla');
		$adb->pquery('DELETE FROM vtiger_actionmapping 
					 WHERE actionname=?', array('PushToJoomla'));
		$log->fatal('PushToJoomla ActionName Removed');						*/
		
		$moduleInstance->disableTools('PullFromJoomla');
		$adb->pquery('DELETE FROM vtiger_actionmapping 
					  WHERE actionname=?', array('PullFromJoomla'));
		$log->fatal('PullFromJoomla ActionName Removed');
	}
	
	/**
	 * To remove(PushToJoomla) tool from profile
	 */
	function removeOldActionMapping() {
		global $log;
		$adb = PearDatabase::getInstance();
		$module = new Vtiger_Module();
		$moduleInstance = $module->getInstance('Joomlabridge');
		
		$moduleInstance->disableTools('PushToJoomla');
		$adb->pquery('DELETE FROM vtiger_actionmapping 
					 WHERE actionname=?', array('PushToJoomla'));
		$log->fatal('PushToJoomla ActionName Removed');	
	}
	
     /**
     * To add a link in vtiger_links 
	 * Add custom link for a module page
	 * @param String Type can be like 'DETAILVIEW', 'LISTVIEW' etc..
 	 * @param String Label to use for display
	 * @param String HREF value to use for generated link
	 * @param String Path to the image file (relative or absolute)
	 * @param Integer Sequence of appearance
	 *
	 * NOTE: $url can have variables like $MODULE (module for which link is associated),
	 * $RECORD (record on which link is dispalyed)
	 */
     function addLinksForJoomlabridge() {
		global $log;
		$moduleInstance = Vtiger_Module::getInstance('Joomlabridge');

//		$moduleInstance->addLink('LISTVIEW', 'Push to Joomla', 'index.php?module=Joomlabridge&view=PushToJoomla');
		$moduleInstance->addLink('LISTVIEW', 'Pull from Joomla', 'index.php?module=Joomlabridge&view=PullFromJoomla');
		$log->fatal('Links -- Pull from Joomla -- added');
    }
	
    /**
     * To remove link for Joomlabridge from vtiger_links
     */
    function removeLinksForJoomlabridge() {
		global $log;
		$moduleInstance = Vtiger_Module::getInstance('Joomlabridge');
		//Deleting Headerscripts links
//		$moduleInstance->deleteLink('LISTVIEW', 'Push to Joomla', 'index.php?module=Joomlabridge&view=PushToJoomla');
		$moduleInstance->deleteLink('LISTVIEW', 'Pull from Joomla', 'index.php?module=Joomlabridge&view=PullFromJoomla');
		$log->fatal('Links -- Push to Joomla, Pull from Joomla -- Removed');
	}
	
    /**
     * To remove old link for Joomlabridge from vtiger_links
     */
    function removeOldLinksForJoomlabridge() {
		global $log;
		$moduleInstance = Vtiger_Module::getInstance('Joomlabridge');
		//Deleting Headerscripts links
		$moduleInstance->deleteLink('LISTVIEW', 'Push to Joomla', 'index.php?module=Joomlabridge&view=PushToJoomla');
		$log->fatal('Links -- Push to Joomla -- Removed');
	}

	function checkLinkPermission($linkData){
		$module = new Vtiger_Module();
		$moduleInstance = $module->getInstance('Joomlabridge');
		
		if($moduleInstance) {
			return true;
		}else {
			return false;
		}
	}
	
    /**
     * Add event handler to the module
     * @param joomlabridge Module instance
     */	
	function AddEventHandler($JBModuleInstance) {
		global $log;
		require_once 'vtlib/Vtiger/Event.php';
		$log->debug('ENTERING --> AddEventHandler() method to the Joomlabridge');
		
		if ( empty($JBModuleInstance) ) {
			$module = new Vtiger_Module();
			$JBModuleInstance = $module->getInstance('Joomlabridge');		
		}

		if(Vtiger_Event::hasSupport()) {

			//Register Events for Joomlabridge record Callback
			Vtiger_Event::register(
				$JBModuleInstance, 'vtiger.entity.aftersave',
				'JoomlabridgeHandler','modules/Joomlabridge/JoomlabridgeHandler.php'
			);
			Vtiger_Event::register(
				$JBModuleInstance, 'vtiger.entity.beforesave',
				'JoomlabridgeHandler','modules/Joomlabridge/JoomlabridgeHandler.php'
			);
			$log->fatal('Joomlabridge Events are added.');
		}
	}
	
    /**
     * Add Contacts event handler to the module
     * @param joomlabridge Module instance
     */	
	function AddContactsEventHandler() {
		global $log;
		require_once 'vtlib/Vtiger/Event.php';
		$log->debug('ENTERING --> AddContactsEventHandler() method to the Contacts / Joomlabridge');
		
		if(Vtiger_Event::hasSupport()) {
		
			//Register Events for the Contacts record Callback of Joomlabridge
			$contactsModuleInstance = Vtiger_Module::getInstance('Contacts');		
			Vtiger_Event::register(
				$contactsModuleInstance, 'vtiger.entity.aftersave.final',
				'ContactsJBHandler', 'modules/Joomlabridge/ContactsJBHandler.php'
			);
			Vtiger_Event::register(
				$contactsModuleInstance, 'vtiger.entity.beforesave',
				'ContactsJBHandler', 'modules/Joomlabridge/ContactsJBHandler.php'
			);		

			$log->fatal('Contacts / Joomlabridge Events are added.');
		}
	}
	
	/**
	 * Function adds default Joomlabridge workflow
	 * @param <PearDatabase> $adb
	 */
	function populateJoomlabridgeWorkflows($adb) {
		vimport("~~modules/com_vtiger_workflow/include.inc");
		vimport("~~modules/com_vtiger_workflow/tasks/VTEntityMethodTask.inc");
		vimport("~~modules/com_vtiger_workflow/VTEntityMethodManager.inc");
		vimport("~~modules/com_vtiger_workflow/VTTaskManager.inc");	
		global $log;
		$log->debug('ENTERING --> populateJoomlabridgeWorkflows()');
		
		// Creating Workflow for Joomlabridge when the Joomla User passwords was reset
		$vtJBWorkFlow = new VTWorkflowManager($adb);
		$jbpasswWorkFlow = $vtJBWorkFlow->newWorkFlow("Joomlabridge");
		$jbpasswWorkFlow->test = '[{"fieldname":"juser_password","operation":"has changed","value":null,"valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"juser_passwordc","operation":"is not empty","value":null,"valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]';
		$jbpasswWorkFlow->description = "Send Email to Joomla User when the Joomla Password was reset";
		$jbpasswWorkFlow->executionCondition = 4;
		$jbpasswWorkFlow->defaultworkflow = 1;
		$vtJBWorkFlow->save($jbpasswWorkFlow);
		$id1 = $jbpasswWorkFlow->id;
		
		$tm = new VTTaskManager($adb);
		$task = $tm->createTask('VTEmailTask',$jbpasswWorkFlow->id);

		$task->active = true;
		$task->executeImmediately = 0;
		$task->recepient = ",$(contactid : (Contacts) email)";
		$task->emailcc = '';
		$task->emailbcc = ",$(assigned_user_id : (Users) email1)";
		$task->fromEmail = "$(general : (__VtigerMeta__) supportName)&lt;$(general : (__VtigerMeta__) supportEmailId)&gt;";
		$task->subject = "Your new Joomla access data at $(jhostid : (Joomlahosts) iurl)";
		$task->content = "Dear&nbsp;".'$(contactid : (Contacts) firstname)'."&nbsp;".'$(contactid : (Contacts) lastname)'.",<br /><br />".
		"An administrator has just changed you password and login credentials to the Joomla website at&nbsp;".'$(jhostid : (Joomlahosts) iurl)'."<br /><br />".
		"Your new login name:&nbsp;".'<b>$juser_username</b>'."<br />".
		"Your new password:&nbsp;".'<b>$juser_passwordc​</b>'."<br />".
		"Your email:&nbsp;".'<b>$juser_email</b>'."<br />".
		"Site URL:&nbsp;<a href=".'"http://$(jhostid : (Joomlahosts) iurl)"'."><b>".'$(jhostid : (Joomlahosts) iurl)'."</b></a><br /><br />".
		"Kindest regards:<br />Help Desk<br />At: ".'$last_push_date​';
		$task->summary = "Email Joomla Login Credentials";
		$tm->saveTask($task);
		$adb->pquery("update com_vtiger_workflows set defaultworkflow=?, filtersavedinnew=? where workflow_id=?",array(1,6,$id1));
	
		$log->fatal('Joomlabridge default workflow for password utility is added');
	}
	
	/**
	 * Function correct $summaryfield 
	 * @param --
	 * @note - this function should be removed if the vtiger import/export package will support the $summaryfield property
	 */
/*
	function correctsummaryfields() { 
		global $log;
		require_once 'vtlib/Vtiger/Utils.php';		
		$log->debug('ENTERING --> correctsummaryfields() --- for Joomlabridge fields');
		
		$jbsummaries = array(
			'jb_no', 'contactid', 'jhostid', 'juser_id', 'juser_username', 'joomla_userlevels', 'juser_lastvisitdate',
		);
		
		foreach( $jbsummaries as $key => $value) {
			$query = "UPDATE vtiger_field SET summaryfield = '1' WHERE tablename = 'vtiger_joomlabridge' AND fieldname = '$value';";
			Vtiger_Utils::ExecuteQuery($query);
		}
		
		$log->fatal('Joomlabridge summary field settings corrected.');
	}
*/
}