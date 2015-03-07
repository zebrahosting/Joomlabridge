/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Vtiger_Edit_Js("Joomlabridge_Edit_Js",{},{
	
	//Will have the mapping of address fields based on the modules
	ContactFieldsMapping : {'Contacts' :
									{'juser_email'			: 'email',  
									'juser_username'		: 'email',
									'juser_name'			: '*',
									'juser_password'		: '*',
									'juser_passwordc'		: '*',
									'juser_block'			: '*',
									'joomla_userlevels[]'	: '*',
									'juser_id'				: '*',
									'juser_registerdate'	: '*',
									'juser_lastvisitdate'	: '*',
									'last_pull_date'		: '*',
									'last_push_date'		: '*',
									'description'			: '*'
									}
							},
							
	NewJUser : {
				'juser_block' 			: 0,
				'juser_id' 				: 0,
				'juser_lastvisitdate' 	: '0000-00-00 00:00:00',
				'last_pull_date' 		: '0000-00-00 00:00:00',
				'description' 			: 'Created by vtiger',
			},
			
	JReadonly : {
				'juser_id' 			: 1,
				'juser_email' 		: 1,
				'juser_username' 	: 1,
				'juser_name' 		: 1,
				'juser_password' 	: 1,
			},
					
	/**
	 * Function which will register event for Reference Fields Selection 
	 */
	registerReferenceSelectionEvent : function(container) {
		var thisInstance = this;
		
		jQuery('input[name="contactid"]', container).on(Vtiger_Edit_Js.referenceSelectionEvent, function(e, data){
			thisInstance.referenceSelectionEventHandler(data, container);
		});
	},
	
	/**
	 * Function which will register event for Reference Fields Selection of Joomla HOST ID
	 */
	registerJHostSelectionEvent : function(container) {
		var thisInstance = this;
		
		jQuery('input[name="jhostid"]', container).on(Vtiger_Edit_Js.referenceSelectionEvent, function(e, data){
			thisInstance.JHostSelectionEventHandler(data, container);
		});
	},
		
	/**
	 * Reference Fields Selection Event Handler
	 * On Confirmation It will copy the address details
	 */
	referenceSelectionEventHandler :  function(data, container) {
		var thisInstance = this;
		thisInstance.AutoPopulateContactDetails(data, container);
	},
	
	/**
	 * Autopopulate data if the Quickcreate was used 
	 */
	QuickCreateAutopopulate :  function(container) {
		var thisInstance = this;
			
		var ContactIdSelected = (jQuery('input[name="contactid"]').val() > 0);
		var JHostIdSelected = (jQuery('input[name="jhostid"]').val() > 0);
		var JUserNameFilled = (jQuery('input[name="juser_name"]').val() > 0);
		if ( ContactIdSelected && !JHostIdSelected ) {
			var infoMessage = app.vtranslate('Autopopulate start, Record ID = ' + jQuery('input[name="contactid"]').val() + ' Host : ' + JHostIdSelected );
			var params = {
				text: infoMessage,
				type: 'info'
			};		
			Vtiger_Helper_Js.showMessage(params);		
		
			var thisInstance = this;
			var data = {
				'source_module' : 'Contacts',
				'record' 		: jQuery('input[name="contactid"]').val(),
						};
			var sourceModule = data['source_module'];
			thisInstance.getRecordDetails(data).then(
				function(data){
					var response = data['result'];
					thisInstance.mapContactDetails(thisInstance.ContactFieldsMapping[sourceModule], response['data'], container);
				},
				function(error, err){
					//TODO : handle the error case
				});
		}
	},
	
	/**
	 * Joomla Host ID Fields Selection Event Handler
	 * It will check and prevent to choose duplicated JHostId for the record
	 */
	JHostSelectionEventHandler :  function(data, container) {
		var thisInstance = this;
		//Check if the ContactID was selected first
		var ContactIdSelected = (jQuery('input[name="contactid"]', container).val() > 0);
		if ( !ContactIdSelected ) { 
			//Force to Contact Name select first
			var errorMessage = app.vtranslate('JS_SELECT_CONTACT_NAME_FIRST');
			var params = {
				text: errorMessage,
				type: 'error'
			};
			Vtiger_Helper_Js.showMessage(params);		
		
			jQuery('input[name="jhostid"]', container).removeAttr('value').trigger('change');
			jQuery('input[name="jhostid_display"]', container).removeAttr('value').trigger('change');
			jQuery('#Joomlabridge_editView_fieldName_jhostid_clear', container).click().trigger('change');
			
			jQuery('#Joomlabridge_editView_fieldName_jhostid_clear', container).on('click', function() {
				jQuery('input[name="jhostid_display"]', container).blur().trigger('change');
				jQuery('input[name="contactid_display"]', container).focus().trigger('change');
			});			
			
		} else {
			//Check the duplicated JHostId			
			var HParams = {
			'module'    : "Joomlabridge",
			'action'    : "JUserSave",
			'mode'      : "CheckDuplicatedJHost",
			'contactid' : jQuery('input[name="contactid"]', container).val(),
			'jhostid'   : jQuery('input[name="jhostid"]', container).val(),
			}			
			AppConnector.request(HParams).then(
				function(data){
					var responseData = data.result;
					if ( responseData.duplicated ) {
					
						var errorMessage = app.vtranslate('JS_DUPLICATED_JOOMLA_ACCESS');
						var params = {
							text: errorMessage,
							type: 'error'
						};
						Vtiger_Helper_Js.showMessage(params);		
					
						jQuery('input[name="jhostid"]', container).removeAttr('value').trigger('change');
						jQuery('input[name="jhostid_display"]', container).removeAttr('value').trigger('change');
						jQuery('#Joomlabridge_editView_fieldName_jhostid_clear', container).click().trigger('change');
						
						jQuery('#Joomlabridge_editView_fieldName_jhostid_clear', container).on('click', function() {
							jQuery('input[name="jhostid_display"]', container).blur().trigger('change');
							jQuery('input[name="contactid_display"]', container).focus().trigger('change');
						});
			
					} else {
						var infoMessage = app.vtranslate('JS_OK_TO_JOOMLA_ACCESS');
						var params = {
							text: infoMessage,
							type: 'info'
						};		
						Vtiger_Helper_Js.showMessage(params);
					}
				},
				function(error,err){
					//TODO : handle the error case
				}
			);	
			
		}


	},
	
	/**
	 * Function which will copy the Contact details - without Confirmation
	 */
	AutoPopulateContactDetails : function(data, container) {
		var thisInstance = this;
		var sourceModule = data['source_module'];
		thisInstance.getRecordDetails(data).then(
			function(data){
				var response = data['result'];
				thisInstance.mapContactDetails(thisInstance.ContactFieldsMapping[sourceModule], response['data'], container);
			},
			function(error, err){

			});
	},
	
	/**
	 * Function which will map the address details of the selected record
	 */
	mapContactDetails : function(ContactDetails, result, container) {
		var thisInstance = this;
		var recordId = thisInstance.getRecordId();
		
			var infoMessage = app.vtranslate('Edit / Create RecordID = ' + recordId );
			var params = {
				text: infoMessage,
				type: 'info'
			};		
			Vtiger_Helper_Js.showMessage(params);
		
		
		for(var key in ContactDetails) {
            if(container.find('[name="'+key+'"]').length == 0 && key != 'joomla_userlevels') {
                var create = container.append("<input type='hidden' name='"+key+"'>");
            }
			if( ContactDetails[key] != '*') {
				container.find('[name="'+key+'"]').val(result[ContactDetails[key]]).trigger('change');
			} else {
				if( key == 'juser_name' ) {
					container.find('[name="'+key+'"]').val(result['firstname']+' '+result['lastname']).trigger('change');			
				}
				if ( !recordId ) {
				//For the New JUser only
					if( key == 'joomla_userlevels[]' ) {
						container.find('[name="'+key+'"]').val('Registered').trigger('change');					
					}
					if( key in thisInstance.NewJUser ) {
						container.find('[name="'+key+'"]').val( thisInstance.NewJUser[key] ).trigger('change');			
					}
				}
			}
		}
		if ( !recordId ) {
			var NewJUserParams = {
			'module' : "Joomlabridge",
			'action' : "JUserSave",
			'mode'   : "JUserNew",
			}			
			AppConnector.request(NewJUserParams).then(
				function(data){
					var responseData = data.result;
					
					$( "input[name='juser_passwordc']" ).val( responseData.juser_passwordc ).trigger('change');
					$( "input[name='juser_password']" ).val( responseData.juser_password ).trigger('change');
					$( "input[name='juser_registerdate']" ).val( responseData.juser_registerdate ).trigger('change');
					$( "input[name='last_push_date']" ).val( responseData.juser_registerdate ).trigger('change');
											
				},
				function(error,err){
					//TODO : handle the error case
				}
			);
		}
	},
	
	/**
	 * This function will return the current RecordId
	 */
	getRecordId : function(container){
		return jQuery('input[name="record"]',container).val();
	},
	
	setReadonly : function(container) {
		var thisInstance = this;
		for(var key in thisInstance.JReadonly) {
			container.find('[name="'+key+'"]').attr('readonly', true).trigger('change');
		}
	},
	
	checkPasswordField : function(container) {
		$( "input[name='juser_passwordc']" ).focusout(function() {
			var passw = $( "input[name='juser_passwordc']" ).val();
			if ( passw.length > 0 && 8 > passw.length ) {
				$('.btn-success').attr('disabled',true);
				var infoMessage = app.vtranslate('JS_J_PASSWORD_SECURE');
				var params = {
					text: infoMessage,
					type: 'error'
				};		
				Vtiger_Helper_Js.showMessage(params);
			} else if ( passw.length > 7 || passw.length == 0 ) {
				$('.btn-success').attr('disabled',false);
			}

		});
	},
	
	registerBasicEvents : function(container){
		this._super(container);
		this.registerReferenceSelectionEvent(container);
		this.registerJHostSelectionEvent(container);
		this.QuickCreateAutopopulate(container);
		this.checkPasswordField(container);
		this.setReadonly(container);
	}	

});
/*
jQuery(document).ready(function() {
	Joomlabridge_Edit_Js.QuickCreateAutopopulate();
});
*/