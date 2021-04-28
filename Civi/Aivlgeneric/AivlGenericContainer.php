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
    $this->setAivlContactId($definition);
    $this->setAivlEmployees($definition);
    $this->setMembershipStatusId($definition);
    $this->setOptionGroupIds($definition);
    $this->setGenderIds($definition);
    $container->setDefinition('aivlgeneric', $definition);
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
    $query = "SELECT cov.value
        FROM civicrm_option_group AS cog JOIN civicrm_option_value AS cov ON cog.id = cov.option_group_id
        WHERE cog.name = %1 AND cov.name = %2";
    $id = \CRM_Core_DAO::singleValueQuery($query, [
      1 => ["activity_type", "String"],
      2 => ["To Check", "String"],
    ]);
    if ($id) {
      $definition->addMethodCall('setToCheckActivityTypeId', [(int) $id]);
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


