/*-------------------------------------------------------+
| Adjustments to the campaign edit form                  |
| Amnesty International Vlaanderen                       |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/

// UPDATE TRIGGERS
cj(document).ready(function() {
  // hide the 'Include Groups' row
  cj("tr.crm-campaign-form-block-includeGroups").hide();

  // hide the 'Campaign Goals' row
  cj("tr.crm-campaign-form-block-goal_general").hide();

  // hide the 'Campaign Revenue Goal' row
  cj("tr.crm-campaign-form-block-goal_revenue").hide();

  // hide the 'Campaign External ID' row?
  //cj("tr.crm-campaign-form-block-external_identifier").hide();
  //
  // // add the campaign block
  // cj(document).ready(function() {
  //   cj("tr.crm-campaign-form-block-campaign_type_id").after('<tr class="crm-activity-form-block-campaign_id"><td class="label"><label for="campaign_id">Campaign</label> <a class="helpicon" title="Campaigns Help" aria-label="Campaigns Help" href="#" onclick="CRM.help(&quot;Campaigns&quot;, {&quot;id&quot;:&quot;id-campaign_id&quot;,&quot;file&quot;:&quot;CRM\/Campaign\/Form\/addCampaignToComponent&quot;}); return false;">&nbsp;</a></td><td class="view-value"><div class="select2-container crm-form-entityref crm-campaign-ref crm-ajax-select" id="s2id_campaign_id" style="width: 314px;"><a href="javascript:void(0)" class="select2-choice select2-default" tabindex="-1">   <span class="select2-chosen" id="select2-chosen-8">- select Campaign -</span><abbr class="select2-search-choice-close"></abbr>   <span class="select2-arrow" role="presentation"><b role="presentation"></b></span></a><label for="s2id_autogen8" class="select2-offscreen">Campaign</label><input class="select2-focusser select2-offscreen" type="text" aria-haspopup="true" role="button" aria-labelledby="select2-chosen-8" id="s2id_autogen8"></div><input class="crm-form-entityref crm-campaign-ref crm-ajax-select" placeholder="- select Campaign -" data-select-params="{&quot;minimumInputLength&quot;:0}" data-api-params="" data-api-entity="Campaign" data-create-links="true" name="campaign_id" type="text" value="" id="campaign_id" tabindex="-1" title="Parent Campaign" style="display: none;"></td></tr>');
  // });

  // set the default value
  cj("#campaign_parent_id").val(CRM.vars.aivlgeneric.parent_campaign_id);
});
