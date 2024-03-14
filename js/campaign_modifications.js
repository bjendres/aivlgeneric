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
  console.log("TEST");
  // hide the 'Include Groups' row
  cj("tr.crm-campaign-form-block-includeGroups").hide();

  // hide the 'Campaign Goals' row
  cj("tr.crm-campaign-form-block-goal_general").hide();

  // hide the 'Campaign Revenue Goal' row
  cj("tr.crm-campaign-form-block-goal_revenue").hide();

  // hide the 'Campaign Extenal ID' row?
  //cj("tr.crm-campaign-form-block-external_identifier").hide();

});
