{* template block that contains the new field *}
<div id="testfield-tr">
  <div>Test field:</div>
  <div>{$form.testfield.html}</div>
</div>
{* reposition the above block after #someOtherBlock *}
<script type="text/javascript">
  cj('#testfield-tr').insertAfter('#crm-campaign-form-block-campaign_type_id');
</script>
