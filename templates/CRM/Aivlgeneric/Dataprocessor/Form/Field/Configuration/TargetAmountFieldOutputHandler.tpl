{crmScope extensionKey='aivlgeneric'}
  {include file="CRM/Dataprocessor/Form/Field/Configuration/NumberFieldOutputHandler.tpl"}
  <div class="crm-section">
    <div class="label">{$form.min_number.label}</div>
    <div class="content">{$form.min_number.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.max_number.label}</div>
    <div class="content">{$form.max_number.html}</div>
    <div class="clear"></div>
  </div>
{/crmScope}
