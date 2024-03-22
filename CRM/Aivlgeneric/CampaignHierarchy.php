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
  public static function addCampaignParentField(&$form)
  {
    // dynamically insert a template block in the page
    $templatePath = E::path('templates/campaign_parent_id.tpl');
    CRM_Core_Region::instance('page-body')->add(['template' => $templatePath]);
    $form->addElement('text', 'campaign_parent_id', ts('Parent Campaign'));

    // prefill for existing campaigns
    $campaign_id = (int) CRM_Utils_Request::retrieve('id', 'Positive');
    if ($campaign_id) {
      // get current parent_id ID
      $campaign = civicrm_api3('Campaign', 'getsingle', ['id' => $campaign_id, 'return' => 'parent_id']);
      if (!empty($campaign['parent_id'])) {
        // setDefaults doesn't seem to work on injected field:
        // $form->setDefaults(['campaign_parent_id' => $campaign['parent_id']]);
        // ... use JS variable instead:
        CRM_Core_Resources::singleton()->addVars('aivlgeneric', ['parent_campaign_id' => $campaign['parent_id']]);
      } else {
        CRM_Core_Resources::singleton()->addVars('aivlgeneric', ['parent_campaign_id' => '']);
      }
    }
  }

  /**
   * Validate the proposed parent_id field value:
   *   1) it has to be an existing campaign
   *   2) it cannot be the campaign's own ID or any of its children
   */
  /**
   * @param CRM_Core_Form $form
   * @throws CRM_Core_Exception
   * @throws CiviCRM_API3_Exception
   */
  public static function validateCampaignParentField( &$form, &$fields, &$errors) {
    $campaign_id = (int) CRM_Utils_Request::retrieve('id', 'Positive');
    $proposed_campaign_parent_id = (int) $fields['campaign_parent_id'];

    // make sure you're not your own father
    if ($campaign_id == $proposed_campaign_parent_id) {
      $form->setElementError('campaign_parent_id', E::ts("Campaign cannot be parent of itself!"));
      return;
    }

    // newly created campaigns don't have to undergo further scrutiny!
    if (empty($campaign_id)) return;

    // first: check if the campaign exists (unless empty)
    if (!empty($proposed_campaign_parent_id)) {
      try {
        $current_campaign = civicrm_api3('Campaign', 'getsingle', [
          'id' => $proposed_campaign_parent_id, 'return' => 'id,parent_id']);
        $current_campaign_parent_id = $current_campaign['parent_id'] ?? '';
        if ($current_campaign_parent_id == $proposed_campaign_parent_id) {
          return;
        }
      } catch (CiviCRM_API3_Exception $ex) {
        /** CRM_Core_Form $form */
        $form->setElementError('campaign_parent_id', E::ts("Invalid Campaign ID"));
        return;
      }
    }

    // check if the $proposed_parent_campaign would cause a loop
    $loop_check = [$proposed_campaign_parent_id, $campaign_id];
    $campaign_node_pointer = $proposed_campaign_parent_id;
    do {
      // go up to the next parent
      $parent_node_query    = civicrm_api3('Campaign', 'getsingle', [
        'id'           => $campaign_node_pointer,
        'option.limit' => 0,
        'return'       => 'id,parent_id'
      ]);
      $parent_loop_check_id = (int)$parent_node_query['parent_id'] ?? 0;
      if ($parent_loop_check_id) {
        if (in_array($parent_loop_check_id, $loop_check)) {
          // we've found the node in the proposed parent node's ancestors, this would be a cycle
          $form->setElementError('campaign_parent_id',
                                 E::ts("This would create a cycle in the parent relationship: " . implode('->', $loop_check) . '->' . $loop_check[0]));
          return;
        } else {
          $campaign_node_pointer = $parent_loop_check_id;
          $loop_check[]          = $parent_loop_check_id;
        }
      }
    } while ($parent_loop_check_id);
  }

  /**
   * Update the campaign's parent.
   *
   * Note that the value should have already been checked by the validation above
   *
   * @var $form CRM_Core_Form
   */
  public static function updateCampaignParentField(&$form)
  {
    $campaign_id = (int) CRM_Utils_Request::retrieve('id', 'Positive');
    $proposed_campaign_parent_id = $form->getSubmitValue('campaign_parent_id');
    $current_campaign = civicrm_api3('Campaign', 'getsingle', ['id' => $campaign_id, 'return' => 'parent_id']);
    $current_campaign_parent_id = $current_campaign['parent_id'] ?? '';
    if ($current_campaign_parent_id != $proposed_campaign_parent_id) {
      civicrm_api3('Campaign', 'create', ['id' => $campaign_id, 'parent_id' => $proposed_campaign_parent_id]);
    }
  }
}
