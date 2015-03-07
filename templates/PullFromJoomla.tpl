{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/
-->*}
{strip}
<div class="container-fluid">
	<div class="widget_header row-fluid">
		<h2>{vtranslate('LBL_JOOMLABRIDGE', $QUALIFIED_MODULE)}</h2>
		<p>{vtranslate('LBL_JB_SUBTITLE', $QUALIFIED_MODULE)}</p>
	</div>
	<hr><br>
	<div class="row-fluid">
		<p>{vtranslate('LBL_JB_NUMBER_OF_USERS', $QUALIFIED_MODULE)}&nbsp;<span id="NumberOfUsers"></span></p>
		<p>{vtranslate('LBL_JB_NUMBER_OF_HOSTS', $QUALIFIED_MODULE)}&nbsp;<span id="NumberOfHosts"></span></p>
<hr><br>
		<div id="StartBtn" class="span10" style="display:none;">
			<button class="btn btn-info">{vtranslate('LBL_JB_START_BUTTON', $QUALIFIED_MODULE)}</button><br>
		</div>
		<div id="Finish2Btn" class="span10" style="display:none;">		
			<p><a class="btn btn-info" href="?module=Joomlabridge&view=List">{vtranslate('LBL_JB_END_BUTTON', $QUALIFIED_MODULE)}</a></p><br>
		</div>	
	</div>
	<div class="row-fluid" id="Processing" style="display:none;">	
		<div class="span10"><p>{vtranslate('LBL_JB_PROCESSING', $QUALIFIED_MODULE)}</p><br></div>
		<div class="span10">
			<div id="progressbar"></div>
		</div>
		<div class="span10"><p><span id="Ready"></span>&nbsp;{vtranslate('LBL_JB_PERCENT', $QUALIFIED_MODULE)}<span id="Step"></span></p></div>

		<div class="span10">
			<p>{vtranslate('LBL_JB_HANDLED', $QUALIFIED_MODULE)}&nbsp;<span id="Handled"></span></p>
			<p>{vtranslate('LBL_JB_CREATED', $QUALIFIED_MODULE)}&nbsp;<span id="Created"></span></p>
			<p>{vtranslate('LBL_JB_UPDATED', $QUALIFIED_MODULE)}&nbsp;<span id="Updated"></span></p>
			<p>{vtranslate('LBL_JB_SKIPPED', $QUALIFIED_MODULE)}&nbsp;<span id="Skipped"></span></p>
			<p>{vtranslate('LBL_JB_FAILED', $QUALIFIED_MODULE)}&nbsp;<span id="Failed"></span></p>
		</div>
		<div class="span10">
			<p>{vtranslate('LBL_JB_PROCESSED', $QUALIFIED_MODULE)}&nbsp;<span id="Processed"></span></p><br>
		</div>
		<div id="ErrorMessageContainer" class="span10 alert alert-error"></div>
		<div id="FinishBtn" class="span10" style="display:none;">		
			<p><a class="btn btn-info" href="?module=Joomlabridge&view=List">{vtranslate('LBL_JB_END_BUTTON', $QUALIFIED_MODULE)}</a></p><br>
		</div>		
		<div id="Pinfo" class="span10 alert alert-info container-fluid">
		<b>{vtranslate('LBL_NOTE', $QUALIFIED_MODULE)}:</b><br>
		<p>{vtranslate('LBL_JB_PROCESSING', $QUALIFIED_MODULE)}</p>
		</div>
{*
		<div id="CancelBtn" class="span10">
			<p><a class="btn btn-alert" href="?module=Joomlabridge&view=List">{vtranslate('LBL_JB_CANCEL_BUTTON', $QUALIFIED_MODULE)}</a></p><br>
		</div>
*}
	</div>
</div>
{/strip}