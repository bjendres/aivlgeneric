{* generate an additional table row *}
<table>
  <tr class="crm-campaign-form-block-campaign_parent_id">
    <td class="label">
      <label for="title">Parent Campaign ID</label>
    </td>
    <td class="view-value"><input maxlength="10" size="10" name="campaign_parent_id" type="text" id="campaign_parent_id" class="crm-form-text"></td>
  </tr>
</table>

{* move the new table row into the campaign edit form *}
<script type="text/javascript">
  cj('tr.crm-campaign-form-block-campaign_parent_id').insertAfter(cj('tr.crm-campaign-form-block-campaign_type_id'));
</script>
