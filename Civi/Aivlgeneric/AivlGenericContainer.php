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

  private $_aivlLegalName = "Amnesty International Vlaanderen vzw";

  /**
   * You can modify the container here before it is dumped to PHP code.
   */
  public function process(ContainerBuilder $container) {
    $definition = new Definition('CRM_Aivlgeneric_AivlGenericConfig');
    $definition->setFactory(['CRM_Aivlgeneric_AivlGenericConfig', 'getInstance']);
    $this->setActivityTypes($definition);
    $this->setAivlContactId($definition);
    $this->setAivlEmployees($definition);
    $container->setDefinition('aivlgeneric', $definition);
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
    }
  }

  /**
   * Method to get all the active AIVL employees (id and display_name)
   *
   * @param $definition
   */
  private function setAivlEmployees(&$definition) {
    $employees = [];
    $query = "SELECT cr.contact_id_a AS contact_id, cca.display_name
      FROM civicrm_relationship AS cr
          JOIN civicrm_relationship_type AS crt ON cr.relationship_type_id = crt.id
          JOIN civicrm_contact AS cca ON cr.contact_id_a = cca.id
          JOIN civicrm_contact AS ccb ON cr.contact_id_b = ccb.id
      WHERE (crt.name_a_b = %1 AND crt.name_b_a = %2) AND cr.is_active = %3 AND cca.contact_type = %4
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

}


