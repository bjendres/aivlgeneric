{* template block that contains the new field *}

<tr class="crm-campaign-form-block-parent_id">
  <td class="label"><label for="parent_id"><span class="crm-marker" title="{ts}Parent Campaign{/ts}">*</span></label></td>
  <td class="view-value"><input maxlength="64" size="45" name="parent_id" type="text" id="parent_id" class="huge crm-form-text required"></td>
</tr>

{* reposition the above block after #someOtherBlock *}
{*<script type="text/javascript">*}
{*  //cj("tr.crm-campaign-form-block-campaign_type_id").append(cj('[name=parent_id]').parent());*}
{*</script>*}
<script type="text/javascript">
  cj('#testfield-tr').insertAfter('#crm-campaign-form-block-campaign_type_id');
</script>
