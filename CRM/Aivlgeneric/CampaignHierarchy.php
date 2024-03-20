<?php
use CRM_Aivlgeneric_ExtensionUtil as E;

/**
 * Logic for the Campaign Hierarchy (via the campaign's parent_id)
 *
 * @author Bjoern Endres (SYSTOPIA) <endres@systopia.de>
 * @license AGPL-3.0
 */
class CRM_Aivlgeneric_CampaignHierarchy {

  /**
   * Add the campaign parent field to the campaign edit form
   *
   * @param CRM_Core_Form $form
   *  the campaign edit form
   *
   * @return void
   */
  public static function addCampaignParentField($form)
  {
    // dynamically insert a template block in the page
    $templatePath = E::path('templates/campaign_parent_id.tpl');
    CRM_Core_Region::instance('page-body')->add(['template' => $templatePath]);
    $form->add('text', 'campaign_parent_id', ts('Parent Campaign'));

    // prefill for existing campaigns
    $campaign_id = (int) CRM_Utils_Request::retrieve('id', 'Positive');
    if ($campaign_id) {
      // get current parent_id ID
      $campaign = civicrm_api3('Campaign', 'getsingle', ['id' => $campaign_id, 'return' => 'parent_id']);
      if (!empty($campaign['parent_id'])) {
        $form->setDefaults(['campaign_parent_id' => $campaign['parent_id']]);
      }
    }
  }

  /**
   * Validate the proposed parent_id field value:
   *   1) it has to be an existing campaign
   *   2) it cannot be the campaign's own ID or any of its children
   */
  public static function validateCampaignParentField( &$form, &$fields, &$errors) {
    $campaign_id = (int) CRM_Utils_Request::retrieve('id', 'Positive');
    $proposed_parent_campaign_id = (int) $fields['campaign_parent_id'];
    if ($proposed_parent_campaign_id) {
      // first: check if the campaign exists:
      try {
        civicrm_api3('Campaign', 'getsingle', [
          'id' => $proposed_parent_campaign_id, 'return' => ['id', 'parent_id']]);
      } catch (CiviCRM_API3_Exception $ex) {
        $errors['parent_campaign_id'] = E::ts("Invalid Campaign ID");
        return;
      }

      // then: check if the $proposed_parent_campaign is one of the campaign's children (would create loop)
      $children_generation = [$proposed_parent_campaign_id]; // init with the proposed campaign
      while ($children_generation) {
        if (in_array($campaign_id, $children_generation)) {
          //$form->setElementError('parent_campaign_id', E::ts("Campaign cannot be its own parent!"));
          $errors['parent_campaign_id'] = E::ts("Campaign cannot be its own parent!");
          return;
        } else {
          // go to the next generation
          $next_generation = [];
          $next_generation_query = civicrm_api3('Campaign', 'get', [
            'parent_id' => ['IN' => [1,2]],
            'option.limit' => 0,
          ]);
          foreach ($next_generation_query['values'] as $child) {
            $next_generation[] = $child['id'];
          }
          if (empty($next_generation)) break; // no more descendants
        }
      }
    }
  }

  /**
   * Update the campaign's parent.
   *
   * Note that the value should have already been checked by the validation above
   *
   * @var $form CRM_Core_Form
   */
  public static function setCampaignParentField(&$form)
  {
    $campaign_id = (int) CRM_Utils_Request::retrieve('id', 'Positive');
    $proposed_parent_campaign_id = $form->getSubmitValue('campaign_parent_id');
    $current_campaign = civicrm_api3('Campaign', 'getsingle', ['id' => $campaign_id, 'return' => 'parent_id']);
    $current_parent_campaign_id = $current_campaign['parent_id'] ?? '';
    if ($current_parent_campaign_id != $proposed_parent_campaign_id) {
      civicrm_api3('Campaign', 'create', ['id' => $campaign_id, 'parent_id' => $proposed_parent_campaign_id]);
    }
  }
}
