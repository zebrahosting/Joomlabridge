/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

if (typeof(Joomlabridge) == 'undefined') {
    /*
	 * Namespaced javascript class for Joomlabridge
	 */
    Joomlabridge = {
		
		RequestData : { },
		syncData : { 'datacache' : '' },
		NumberOf : { 
			'users'   : 0,
			'hosts'   : 0,
			'counter' : 1,
		},
		syncStatus : {
			'handled' : 0,
			'created' : 0,
			'updated' : 0,
			'skipped' : 0,
			'failed'  : 0,
			'error_message' : '',
		},
		
		JReadonly : {
			'juser_id' 			: 1,
			'juser_email' 		: 1,
			'juser_username' 	: 1,
			'juser_name' 		: 1,
			'juser_password' 	: 1,
		},

		myProgress: function(progress) {
			jQuery( "#ErrorMessageContainer" ).hide();
			jQuery( "#progressbar" ).progressbar({
				value : 0
			});
		},
		
		loadSyncData : function() {
			var thisview = app.getViewName();
			if ( thisview == 'PullFromJoomla' ) {
				var syncParams = {
				'module' : "Joomlabridge",
				'action' : "ManualPull",
				'mode'   : "getItemsCount",
				}
				var progressInstace = jQuery.progressIndicator();
				AppConnector.request(syncParams).then(
					function(data){

						var responseData = data.result;
						var syncItems = '';
						var syncHosts = '';
						
						syncItems += '<b>'+responseData['items']+'</b>';
						syncHosts += '<b>'+responseData['hosts']+'</b>';					
						jQuery( "#NumberOfUsers" ).html(syncItems);
						jQuery( "#NumberOfHosts" ).html(syncHosts);

						progressInstace.hide();
						
						Joomlabridge.syncData.datacache = responseData;
						Joomlabridge.NumberOf.users = responseData['items'];
						Joomlabridge.NumberOf.hosts = responseData['hosts'];
						if ( Joomlabridge.NumberOf.hosts > 0 ) {
							jQuery("#StartBtn").show("slow");
						} else {
							jQuery("#Finish2Btn").show("slow");
						}
						
					},
					function(error,err){
						//TODO : handle the error case
						var params = {
							text: 'Error: '+ error + ' - ' +err,
							type: 'error'
						};		
						Vtiger_Helper_Js.showMessage(params);
					}
				);
			}
		},
		
		getSyncDataQueue : function( host, item ) {
			var syncParams = {
				'module' : "Joomlabridge",
				'action' : "ManualPull",
				'mode'   : "getQueue",
				'host'   : host,
				'item'   : item,
				};
			var RequestNote = AppConnector.request(syncParams).then(
				function(data){
					var sitems = '';
					var percent = '';
					var created = '';
					var updated = '';
					var skipped = '';
					var step = '';
					var responseData = data.result;

					sitems = '<b>'+responseData['host']+'</b> / <b>'+responseData['item']+'</b>';

					percent = Math.round( Joomlabridge.NumberOf.counter / Joomlabridge.NumberOf.users * 100 );
					percenthtml = '<b>'+percent+'</b>';

					Joomlabridge.syncStatus.handled += responseData['handled'];
					Joomlabridge.syncStatus.created += responseData['created'];
					Joomlabridge.syncStatus.updated += responseData['updated'];
					Joomlabridge.syncStatus.skipped += responseData['skipped'];
					Joomlabridge.syncStatus.failed  += responseData['failed'];

					handled = '<b>'+Joomlabridge.syncStatus.handled+'</b>';
					created = '<b>'+Joomlabridge.syncStatus.created+'</b>';
					updated = '<b>'+Joomlabridge.syncStatus.updated+'</b>';
					skipped = '<b>'+Joomlabridge.syncStatus.skipped+'</b>';
					failed = '<b>'+Joomlabridge.syncStatus.failed+'</b>';
					step = '<b>'+Joomlabridge.NumberOf.counter+'</b>';
					
					jQuery( "#Processed" ).html(sitems);
					jQuery( "#Handled" ).html(handled);					
					jQuery( "#Created" ).html(created);
					jQuery( "#Updated" ).html(updated);
					jQuery( "#Skipped" ).html(skipped);
					jQuery( "#Failed" ).html(failed);
					jQuery( "#Step" ).html(step);
					jQuery( "#Ready" ).html(percenthtml);
					jQuery("#progressbar").progressbar('value', percent);
					
					if( responseData['failed'] > 0 ) {
						jQuery( "#ErrorMessageContainer" ).show();
						jQuery( "#ErrorMessageContainer" ).append( "<p><strong>"+ responseData['error_message'] +"</strong></p>" );
					}
					
					if( Joomlabridge.NumberOf.counter === Joomlabridge.NumberOf.users ) {
						// code to be executed if condition is true
						jQuery("#FinishBtn").show("slow");
						jQuery("#Pinfo").hide("slow");
					}
					
					Joomlabridge.NumberOf.counter++ ;
					
				},
				function(error,err){
					//TODO : handle the error case
					var params = {
						text: 'Error: '+ error + ' - ' +err,
						type: 'error'
					};		
					Vtiger_Helper_Js.showMessage(params);
				}
			);
			Joomlabridge.RequestData = RequestNote;
		},
		
		startSync : function() {
			jQuery("#StartBtn").click(function(){
				var MyNull = 0;
				jQuery( "#StartBtn" ).hide("slow");
				jQuery( "#Ready" ).html('<b>'+MyNull+'</b>');
				jQuery( "#Step" ).html('<b>'+MyNull+'</b>');
				jQuery( "#Created" ).html('<b>'+MyNull+'</b>');
				jQuery( "#Updated" ).html('<b>'+MyNull+'</b>');
				jQuery( "#Skipped" ).html('<b>'+MyNull+'</b>');
				jQuery( "#Handled" ).html('<b>'+MyNull+'</b>');
				jQuery( "#Failed" ).html('<b>'+MyNull+'</b>');
				jQuery( "#Processing" ).show();
				Joomlabridge.runSync();
			});
		},
			
		runSync : function() {
			var i = 0 ;
			syncloop:
			for (i = 0; i < Joomlabridge.NumberOf.users; i++) { 	 
				Joomlabridge.getSyncDataQueue(
					Joomlabridge.syncData.datacache.data[i]['hostid'], 
					Joomlabridge.syncData.datacache.data[i]['juserid']
				);
/*
				var Pressed = jQuery("#CancelBtn").click(function(){ 
					var message = app.vtranslate('JS_ABORT_SYNC');
					var params = {
						text: message,
						type: 'alert'
					};
					
					Vtiger_Helper_Js.showMessage(params);
					jQuery.each( Joomlabridge.RequestData, function() {
						Joomlabridge.RequestData.abort();
					});
					return Joomlabridge.NumberOf.users;
				});
				
				if ( Pressed == Joomlabridge.NumberOf.users ) { 
					break syncloop;
				};
*/				
			}
		},
		
		hideJBMessage : function() {
			jQuery(".alert-info").mouseleave( function() {
				$("#JBEditViewMessage").hide("slow");
			});
		},
		/**
		 * This function will trigger the reset password function
		 */	
		triggerResetPassword : function(urlparams) {
			var message = app.vtranslate('JS_PASSWORD_RESET_ARE_YOU_SURE');
			Vtiger_Helper_Js.showConfirmationBox({'message' : message}).then(
				function(e) {
					AppConnector.request(urlparams).then(
						function(data) {
						   if(data.success) {
								var param = {
									text:app.vtranslate('JS_J_PASSWORD_RESET'),
									type: 'info'
								};
								Vtiger_Helper_Js.showMessage(param);
							} else {
								var  params = {
									text : app.vtranslate('Error'),
									type: 'error'
								}
								Vtiger_Helper_Js.showMessage(params);
							}
						}
					);
				},
				function(error, err) {
					//TODO : handle the error case
					var params = {
						text: 'Error: '+ error + ' - ' +err,
						type: 'error'
					};		
					Vtiger_Helper_Js.showMessage(params);					
				}
			);
		},

    }

	jQuery(document).ready(function() {
		Joomlabridge.myProgress();
		Joomlabridge.loadSyncData();
		Joomlabridge.startSync();
		Joomlabridge.hideJBMessage();
	});
}
