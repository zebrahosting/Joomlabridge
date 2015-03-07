{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
{strip}
{assign var="FIELD_INFO" value=Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($FIELD_MODEL->getFieldInfo()))}
{assign var="HOST_NO" value=$FIELD_MODEL->getHostNoByRecordId($RECORD_ID)}
{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues($HOST_NO)}
{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
{assign var="FIELD_VALUE_LIST" value=explode(' |##| ',$FIELD_MODEL->get('fieldvalue'))}
<input type="hidden" name="{$FIELD_MODEL->getFieldName()}" value="" />
<select id="{$MODULE}_{$smarty.request.view}_fieldName_{$FIELD_MODEL->get('name')}" multiple class="select2" name="{$FIELD_MODEL->getFieldName()}[]" data-fieldinfo='{$FIELD_INFO}' {if $FIELD_MODEL->isMandatory() eq true} data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if} {/if} style="width: 60%">
	{foreach $PICKLIST_VALUES as $JGINDEX => $TITLE}
        <option value="{$JGINDEX}" {if in_array($JGINDEX, $FIELD_VALUE_LIST)} selected {/if}>{$TITLE}</option>
    {/foreach}
</select>
{/strip}