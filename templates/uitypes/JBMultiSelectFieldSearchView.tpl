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
    {assign var="FIELD_INFO" value=Zend_Json::encode($FIELD_MODEL->getFieldInfo())}
	{assign var="HOST_NO" value=$FIELD_MODEL->getHostNo()}
    {assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues($HOST_NO)}
    {assign var=SEARCH_VALUES value=explode(',',$SEARCH_INFO['searchValue'])}
    <div class="row-fluid">
        <select class="select2 listSearchContributor span9" name="{$FIELD_MODEL->get('name')}" multiple style="width:150px;" data-fieldinfo='{$FIELD_INFO|escape}'>
            {foreach $PICKLIST_VALUES as $JGINDEX => $TITLE}
                <option value="{$JGINDEX}" {if in_array($JGINDEX,$SEARCH_VALUES) && ($JGINDEX neq "")} selected{/if}>{$TITLE}</option>
            {/foreach}
        </select>
    </div>
{/strip}
