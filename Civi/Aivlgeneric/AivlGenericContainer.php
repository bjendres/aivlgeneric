<?php
/**
 * @author Erik Hommel <erik.hommel@civicoop.org>
 * @date 8 Jun 2020
 * @license AGPL-3.0
 */
namespace Civi\Aivlgeneric;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use CRM_Aivlgeneric_ExtensionUtil as E;

class AivlGenericContainer implements CompilerPassInterface {

  private $_aivlLegalName = "Amnesty International Vlaanderen VZW";
  private $_aivlContactId = NULL;

  /**
   * You can modify the container here before it is dumped to PHP code.
   */
  public function process(ContainerBuilder $container) {
    $definition = new Definition('CRM_Aivlgeneric_AivlGenericConfig');
    $definition->setFactory(['CRM_Aivlgeneric_AivlGenericConfig', 'getInstance']);
    $this->setActivityTypes($definition);
    $this->setActivityContacts($definition);
    $this->setGroupTypes($definition);
    $this->setAivlContactId($definition);
    $this->setAivlEmployees($definition);
    $this->setMembershipStatusId($definition);
    $this->setOptionGroupIds($definition);
    $this->setGenderIds($definition);
    $this->setCustomData($definition);
    $this->setActivityStatus($definition);
    $this->setCampaignTypeId($definition);
    $this->setDefaultLocationType($definition);
    $this->setPhoneTypes($definition);
    $this->setRelationshipTypes($definition);
    $this->setWelkomstPakketTypeCustomField($definition);
    $container->setDefinition('aivlgeneric', $definition);
  }

  /**
   * Method to set the relationship type ids
   *
   * @param Definition $definition
   * @return void
   */
  private function setRelationshipTypes(Definition &$definition) {
    $query = "SELECT id FROM civicrm_relationship_type WHERE name_a_b = %1";
    $id = \CRM_Core_DAO::singleValueQuery($query, [1 => ["Employee of", "String"]]);
    if ($id) {
      $definition->addMethodCall('setEmployeeRelationshipTypeId', [(int) $id]);
    }
  }

  /**
   * Method to set the mobile and phone phone type ids
   *
   * @param Definition $definition
   * @return void
   */
  private function setPhoneTypes(Definition &$definition) {
    $query = "SELECT cov.name, cov.value
        FROM civicrm_option_value cov JOIN civicrm_option_group cog ON cog.id = cov.option_group_id
        WHERE cog.name = %1 AND cov.name IN (%2, %3)";
    $dao = \CRM_Core_DAO::executeQuery($query, [
      1 => ["phone_type", "String"],
      2 => ["Mobile", "String"],
      3 => ["Phone", "String"],
      ]);
    while ($dao->fetch()) {
      switch ($dao->name) {
        case "Mobile":
          $definition->addMethodCall('setMobilePhoneTypeId', [(int) $dao->value]);
          break;
        case "Phone":
          $definition->addMethodCall('setPhonePhoneTypeId', [(int) $dao->value]);
          break;
      }
    }
  }

  /**
   * Method to set the default location type id
   *
   * @param Definition $definition
   * @return void
   */
  private function setDefaultLocationType(Definition &$definition) {
    $locationTypeId = \CRM_Core_DAO::singleValueQuery("SELECT id FROM civicrm_location_type WHERE is_default = TRUE");
    if ($locationTypeId) {
      $definition->addMethodCall('setDefaultLocationTypeId', [(int) $locationTypeId]);
    }
  }

  /**
   * Method to set the type welkomst pakket custom field properties
   *
   * @param $definition
   */
  private function setWelkomstPakketTypeCustomField(&$definition) {
    $query = "SELECT cf.id
        FROM civicrm_custom_group AS cg JOIN civicrm_custom_field AS cf ON cg.id = cf.custom_group_id
        WHERE cg.name = %1 AND cf.name = %2";
    $id = \CRM_Core_DAO::singleValueQuery($query, [
      1 => ["aivl_welkomstpakket", "String"],
      2 => ["type_pakket", "String"],
    ]);
    if ($id) {
      $definition->addMethodCall('setWelkomstPakketTypeCustomFieldId', [(int) $id]);
    }
  }

  /**
   * Method to set the group types
   *
   * @param $definition
   */
  private function setGroupTypes(&$definition) {
    $mailingListName = "Mailing List";
    $definition->addMethodCall('setMailingListGroupTypeName', [$mailingListName]);
    $query = "SELECT cov.value
        FROM civicrm_option_group AS cog JOIN civicrm_option_value AS cov ON cog.id = cov.option_group_id
        WHERE cog.name = %1 AND cov.name = %2";
    $groupTypeId = \CRM_Core_DAO::singleValueQuery($query, [
      1 => ["group_type", "String"],
      2 => ["Mailing List", "String"],
      ]);
    if ($groupTypeId) {
      $definition->addMethodCall('setMailingListGroupTypeId', [(int) $groupTypeId]);
    }
  }

  /**
   * Method to set campaign type(s)
   *
   * @param $definition
   */
  private function setCampaignTypeId(&$definition) {
    $query = "SELECT cov.value
        FROM civicrm_option_group AS cog JOIN civicrm_option_value AS cov ON cog.id = cov.option_group_id
        WHERE cog.name = %1 AND cov.name = %2";
    $campaignTypeId = \CRM_Core_DAO::singleValueQuery($query, [
      1 => ["campaign_type", "String"],
      2 => ["Petitie", "String"],
    ]);
    if ($campaignTypeId) {
      $definition->addMethodCall('setPetitionCampaignTypeId', [(int) $campaignTypeId]);
    }
  }

  /**
   * Method to set the activity contact record types
   *
   * @param $definition
   */
  private function setActivityContacts(&$definition) {
    $query = "SELECT cov.name, cov.value
        FROM civicrm_option_value cov JOIN civicrm_option_group cog ON cov.option_group_id = cog.id
        WHERE cog.name = %1";
    $dao = \CRM_Core_DAO::executeQuery($query, [1 => ["activity_contacts", "String"]]);
    while ($dao->fetch()) {
      switch ($dao->name) {
        case "Activity Assignees":
          $definition->addMethodCall('setAssigneeRecordTypeId', [(int) $dao->value]);
          break;
        case "Activity Source":
          $definition->addMethodCall('setSourceRecordTypeId', [(int) $dao->value]);
          break;
        case "Activity Targets":
          $definition->addMethodCall('setTargetRecordTypeId', [(int) $dao->value]);
          break;
      }
    }

  }
  /**
   * Method to set the activity status
   *
   * @param $definition
   */
  private function setActivityStatus(&$definition) {
    $query = "SELECT cov.value, cov.name FROM civicrm_option_value cov
        JOIN civicrm_option_group cog ON cov.option_group_id = cog.id
        WHERE cog.name = %1 AND cov.name IN(%2, %3)";
    $queryParams = [
      1 => ["activity_status", "String"],
      2 => ["Completed", "String"],
      3 => ["Scheduled", "String"],
    ];
    $dao = \CRM_Core_DAO::executeQuery($query, $queryParams);
    while ($dao->fetch()) {
      switch ($dao->name) {
        case "Completed":
          $definition->addMethodCall('setCompletedActivityStatusId', [(int) $dao->value]);
          break;
        case "Scheduled":
          $definition->addMethodCall('setScheduledActivityStatusId', [(int) $dao->value]);
          break;
      }
    }
  }

  /**
   * Method to set the custom data
   *
   * @param $definition
   */
  private function setCustomData(&$definition) {
    $customGroupName = "FWTM_call_assignment_info";
    $query = "SELECT id, table_name FROM civicrm_custom_group WHERE name = %1";
    $dao = \CRM_Core_DAO::executeQuery($query, [1 => [$customGroupName, "String"]]);
    if ($dao->fetch()) {
      $definition->addMethodCall("setTmCallAssignmentTable", [$dao->table_name]);
      $this->addCallAssignmentCustomFields((int) $dao->id, $definition);
    }
  }

  /**
   * Method to set the call assignment custom fields
   *
   * @param $customGroupdId
   * @param $definition
   */
  private function addCallAssignmentCustomFields($customGroupdId, &$definition) {
    $query = "SELECT id, name, column_name FROM civicrm_custom_field WHERE custom_group_id = %1";
    $dao = \CRM_Core_DAO::executeQuery($query, [1 => [(int) $customGroupdId, "Integer"]]);
    while ($dao->fetch()) {
      switch ($dao->name) {
        case "TMassignment_result_code":
          $definition->addMethodCall('setTmCaResultCodeCustomFieldId', [(int) $dao->id]);
          break;
        case "TMassignment_result_agent":
          $definition->addMethodCall('setTmCaResultAgentCustomFieldId', [(int) $dao->id]);
          break;
        case "TMassignment_result_reason_no":
          $definition->addMethodCall('setTmCaResultReasonNoCustomFieldId', [(int) $dao->id]);
          break;
        case "TMassignment_calldate":
          $definition->addMethodCall('setTmCaCallDateCustomFieldId', [(int) $dao->id]);
          break;
        case "TMassignment_result_upload_date":
          $definition->addMethodCall('setTmCaResultUploadDateCustomFieldId', [(int) $dao->id]);
          break;
        case "TMassignment_result_sdd_mandatecode":
          $definition->addMethodCall('setTmCaResultMandateCodeColumn', [$dao->column_name]);
          break;
        case "TMassignment_sdd_mandatecode_new":
          $definition->addMethodCall('setTmCaResultMandateCodeNewCustomFieldId', [(int)$dao->id]);
          break;
        case "TMassignment_result_agency_reference":
          $definition->addMethodCall('setTmCaAgencyReferenceCustomFieldId', [(int) $dao->id]);
          break;
        case "TMassignment_result_code_sdd":
          $definition->addMethodCall('setTmCaResultCodeSddCustomFieldId', [(int) $dao->id]);
          break;
        case "TMassignment_result_sdd_amount":
          $definition->addMethodCall('setTmCaResultAmountCustomFieldId', [(int) $dao->id]);
          break;
        case "TMassignment_result_sdd_enddate":
          $definition->addMethodCall('setTmCaResultEndDateCustomFieldId', [(int) $dao->id]);
          break;
        case "TMassignment_result_sdd_frequency_interval":
          $definition->addMethodCall('setTmCaResultFreqIntervalCustomFieldId', [(int) $dao->id]);
          break;
        case "TMassignment_result_sdd_frequency_unit":
          $definition->addMethodCall('setTmCaResultFreqUnitCustomFieldId', [(int) $dao->id]);
          break;
        case "TMassignment_result_sdd_iban":
          $definition->addMethodCall('setTmCaResultIbanCustomFieldId', [(int) $dao->id]);
          break;
        case "TMassignment_result_sdd_startdate":
          $definition->addMethodCall('setTmCaResultStartDateCustomFieldId', [(int) $dao->id]);
          break;

      }

    }
  }

  /**
   * Method to set the gender ids
   *
   * @param $definition
   */
  private function setGenderIds(&$definition) {
    $query = "SELECT cov.value, cov.name
        FROM civicrm_option_value AS cov
            JOIN civicrm_option_group AS cog ON cov.option_group_id = cog.id
        WHERE cog.name = %1 AND cov.name IN(%2, %3)";
    $queryParams = [
      1 => ["gender", "String"],
      2 => ["Male", "String"],
      3 => ["Female", "String"],
    ];
    $dao = \CRM_Core_DAO::executeQuery($query, $queryParams);
    while ($dao->fetch()) {
      switch ($dao->name) {
        case "Female":
          $definition->addMethodCall('setFemaleGenderId', [(int) $dao->value]);
          break;

        case "Male":
          $definition->addMethodCall('setMaleGenderId', [(int) $dao->value]);
          break;
      }
    }
  }

  /**
   * Method to set the option group ids
   *
   * @param $definition
   */
  private function setOptionGroupIds(&$definition) {
    $query = "SELECT id FROM civicrm_option_group WHERE name LIKE %1";
    $optionGroupId = \CRM_Core_DAO::singleValueQuery($query, [1 => ["individual_prefix", "String"]]);
    if ($optionGroupId) {
      $definition->addMethodCall('setPrefixOptionGroupId', [(int) $optionGroupId]);
    }
  }

  /**
   * Method to set the membership status id(s)
   *
   * @param $definition
   */
  private function setMembershipStatusId(&$definition) {
    $query = "SELECT id FROM civicrm_membership_status WHERE name = %1 LIMIT 1";
    $statusId = \CRM_Core_DAO::singleValueQuery($query, [1 => ["Expired", "String"]]);
    if ($statusId) {
      $definition->addMethodCall('setExpiredMembershipStatusId', [(int) $statusId]);
    }
  }

  /**
   * Method to set activity type id(s)
   *
   * @param $definition
   */
  private function setActivityTypes(&$definition) {
    $query = "SELECT cov.value, cov.name
        FROM civicrm_option_group AS cog JOIN civicrm_option_value AS cov ON cog.id = cov.option_group_id
        WHERE cog.name = %1 AND cov.name IN (%2, %3, %4)";
    $dao = \CRM_Core_DAO::executeQuery($query, [
      1 => ["activity_type", "String"],
      2 => ["To Check", "String"],
      3 => ["FWTM call assignment", "String"],
      4 => ["aivl_welkomstpakket", "String"],
    ]);
    while ($dao->fetch()) {
      switch ($dao->name) {
        case "aivl_welkomstpakket":
          $definition->addMethodCall('setWelkomstPakketActivityTypeId', [(int) $dao->value]);
          break;
        case "FWTM call assignment":
          $definition->addMethodCall('setCallAssignmentActivityTypeId', [(int) $dao->value]);
          break;
        case "To Check":
          $definition->addMethodCall('setToCheckActivityTypeId', [(int) $dao->value]);
          break;
      }
    }
  }

  /**
   * Method to set the AIVL contact ID
   *
   * @param $definition
   */
  private function setAivlContactId(&$definition) {
    $query = "SELECT id FROM civicrm_contact WHERE contact_type = %1 AND legal_name = %2 LIMIT 1";
    $id = \CRM_Core_DAO::singleValueQuery($query, [
      1 => ["Organization", "String"],
      2 => [$this->_aivlLegalName, "String"],
    ]);
    if ($id) {
      $definition->addMethodCall('setAivlContactId', [(int) $id]);
      $this->_aivlContactId = (int) $id;
    }
  }

  /**
   * Method to get all the active AIVL employees (id and display_name)
   *
   * @param $definition
   */
  private function setAivlEmployees(&$definition) {
    // first make sure that Databeheer (if exists) is added as employee
    $this->setDataBeheerEmployee();
    $employees = [];
    $query = "SELECT cr.contact_id_a AS contact_id, cca.display_name
      FROM civicrm_relationship AS cr
          JOIN civicrm_relationship_type AS crt ON cr.relationship_type_id = crt.id
          JOIN civicrm_contact AS cca ON cr.contact_id_a = cca.id
          JOIN civicrm_contact AS ccb ON cr.contact_id_b = ccb.id
      WHERE (crt.name_a_b = %1 AND crt.name_b_a = %2) AND cr.is_active = %3 AND cca.contact_type IN(%4, %5)
          AND ccb.contact_type = %5 AND ccb.legal_name = %6";
    $queryParams = [
      1 => ["Employee of", "String"],
      2 => ["Employer of", "String"],
      3 => [1, "Integer"],
      4 => ["Individual", "String"],
      5 => ["Organization", "String"],
      6 => [$this->_aivlLegalName, "String"],
    ];
    $dao = \CRM_Core_DAO::executeQuery($query, $queryParams);
    while ($dao->fetch()) {
      $employees[$dao->contact_id] = $dao->display_name;
    }
    $definition->addMethodCall('setAivlEmployees', [$employees]);
  }

  /**
   * Method to check if databeheer is set as employee and add if not
   */
  private function setDataBeheerEmployee() {
    $query = "SELECT id FROM civicrm_relationship_type WHERE name_a_b = %1 AND name_b_a = %2";
    $employeeRelationshipTypeId = \CRM_Core_DAO::singleValueQuery($query, [
      1 => ["Employee of", "String"],
      2 => ["Employer of", "String"],
    ]);
    if ($employeeRelationshipTypeId) {
      $query = "SELECT id FROM civicrm_contact WHERE display_name = %1 AND contact_type = %2";
      $dataBeheerId = \CRM_Core_DAO::singleValueQuery($query, [
        1 => ["Databeheer AIVL", "String"],
        2 => ["Organization", "String"],
      ]);
      if ($dataBeheerId) {
        $query = "SELECT COUNT(*) FROM civicrm_relationship
          WHERE relationship_type_id = %1 AND contact_id_b = %2 AND contact_id_a = %3";
        $countDataBeheer = \CRM_Core_DAO::singleValueQuery($query, [
          1 => [(int) $employeeRelationshipTypeId, "Integer"],
          2 => [$this->_aivlContactId, "Integer"],
          3 => [$dataBeheerId, "Integer"],
        ]);
        if ($countDataBeheer == 0) {
          $insert = "INSERT INTO civicrm_relationship
            (contact_id_a, contact_id_b, relationship_type_id, is_active, start_date, is_permission_a_b, is_permission_b_a)
            VALUES(%1, %2, %3, %4, %5, %6, %6)";
          \CRM_Core_DAO::executeQuery($insert, [
            1 => [(int) $dataBeheerId, "Integer"],
            2 => [(int) $this->_aivlContactId, "Integer"],
            3 => [(int) $employeeRelationshipTypeId, "Integer"],
            4 => [1, "Integer"],
            5 => ["2013-01-01", "String"],
            6 => [0, "Integer"],
          ]);
        }
      }
    }
  }

}


