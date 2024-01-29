<?php
use CRM_Aivlgeneric_ExtensionUtil as E;

/**
 * Class for Amnesty International Vlaanderen Custom Data
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 22 Oct 2021
 * @license AGPL-3.0
 */
class CRM_Aivlgeneric_CustomData {
  private $_hasAPI4;
  private $_resourcesPath;

  /**
   *
   */
  public function __construct() {
    if (function_exists('civicrm_api4')) {
      $this->_hasAPI4 = TRUE;
    }
    else {
      $this->_hasAPI4 = FALSE;
    }
    $container = CRM_Extension_System::singleton()->getFullContainer();
    $this->_resourcesPath = $container->getPath('aivlgeneric').'/resources/';
  }

  /**
   * Method to get the custom group id
   *
   * @param string $customGroupName
   * @param string $extends
   * @return bool|int
   */
  public function getCustomGroupId(string $customGroupName, string $extends) {
    if ($this->_hasAPI4) {
      try {
        $customGroup = \Civi\Api4\CustomGroup::get()
          ->addSelect('id')
          ->addWhere('extends', '=', $extends)
          ->addWhere('name', '=', $customGroupName)
          ->setCheckPermissions(FALSE)->execute()->first();
        if ($customGroup['id']) {
          return (int) $customGroup['id'];
        }
      }
      catch (API_Exception $ex) {
        Civi::log()->error(E::ts("Error from API4 CustomGroup get (id) with name") . $customGroupName
          . E::ts(" and extends ") . $extends . E::ts(" in ") . __METHOD__. E::ts(", error message: ") . $ex->getMessage());
      }
    }
    else {
      try {
        $customGroupId = civicrm_api3('CustomGroup', 'getvalue', [
          'name' => $customGroupName,
          'extends' => $extends,
          'return' => 'id'
        ]);
        if ($customGroupId) {
          return (int) $customGroupId;
        }
      }
      catch (CiviCRM_API3_Exception $ex) {
        Civi::log()->error(E::ts("Error from API3 CustomGroup getvalue (id) with name") . $customGroupName
          . E::ts(" and extends ") . $extends . E::ts(" in ") . __METHOD__. E::ts(", error message: ") . $ex->getMessage());
      }
    }
    return FALSE;
  }

  /**
   * Method to get the custom field id
   *
   * @param int $customGroupId
   * @param string $customFieldName
   * @return false|int
   */
  public function getCustomFieldId(int $customGroupId, string $customFieldName) {
    if ($this->_hasAPI4) {
      try {
        $customField = \Civi\Api4\CustomField::get()
          ->addSelect('id')
          ->addWhere('custom_group_id', '=', $customGroupId)
          ->addWhere('name', '=', $customFieldName)
          ->setCheckPermissions(FALSE)->execute()->first();
        if ($customField['id']) {
          return (int) $customField['id'];
        }
      }
      catch (API_Exception $ex) {
        Civi::log()->error(E::ts("Error from API4 CustomField get (id)) with name") . $customFieldName
          . E::ts(" and custom group id ") . $customGroupId . E::ts(" in ") . __METHOD__. E::ts(", error message: ") . $ex->getMessage());
      }
    }
    else {
      try {
        $customFieldId = civicrm_api3('CustomField', 'getvalue', [
          'return' => 'id',
          'name' => $customFieldName,
          'custom_group_id' => $customGroupId,
        ]);
        if ($customFieldId) {
          return (int) $customFieldId;
        }
      }
      catch (CiviCRM_API3_Exception $ex) {
        Civi::log()->error(E::ts("Error from API3 CustomField getvalue (id)) with name") . $customFieldName
          . E::ts(" and custom group id ") . $customGroupId . E::ts(" in ") . __METHOD__. E::ts(", error message: ") . $ex->getMessage());
      }
    }
    return FALSE;
  }

  /**
   * Method to enable the custom group (if it already exists make sure it is active)
   *
   * @param int $customGroupId
   */
  public function enableCustomGroup(int $customGroupId) {
    if ($this->_hasAPI4) {
      try {
        \Civi\Api4\CustomGroup::update()
          ->addWhere('id', '=', $customGroupId)
          ->addValue('is_active', TRUE)
          ->setCheckPermissions(FALSE)->execute();
      }
      catch (API_Exception $ex) {
        Civi::log()->error(E::ts("Could not enable custom group with id ") . $customGroupId
          . E::ts(" in ") . __METHOD__ . E::ts(" using API4 CustomGroup update, error message: ") . $ex->getMessage());
      }
    }
    else {
      $query = "UPDATE civicrm_custom_group SET is_active = %1 WHERE id = %2";
      CRM_Core_DAO::executeQuery($query, [
        1 => [1, "Integer"],
        2 => [$customGroupId, "Integer"]
      ]);
    }
  }

  /**
   * Method to enable the custom field (if it already exists make sure it is active)
   * @param int $customFieldId
   */
  public function enableCustomField(int $customFieldId) {
    if ($this->_hasAPI4) {
      try {
        \Civi\Api4\CustomField::update()
          ->addWhere('id', '=', $customFieldId)
          ->addValue('is_active', TRUE)
          ->setCheckPermissions(FALSE)->execute();
      }
      catch (API_Exception $ex) {
        Civi::log()->error(E::ts("Could not enable custom field with id ") . $customFieldId
          . E::ts(" in ") . __METHOD__ . E::ts(" using API4 CustomField update, error message: ") . $ex->getMessage());
      }
    }
    else {
      $query = "UPDATE civicrm_custom_field SET is_active = %1 WHERE id = %2";
      CRM_Core_DAO::executeQuery($query, [
        1 => [1, "Integer"],
        2 => [$customFieldId, "Integer"]
      ]);
    }
  }

  /**
   * Method to create custom group
   *
   * @param array $customGroupData
   * @return false|int
   */
  public function createCustomGroup(array $customGroupData) {
    if ($this->_hasAPI4) {
      return $this->createCustomGroupAPI4($customGroupData);
    }
    else {
      try {
        $result = civicrm_api3("CustomGroup", "create", $customGroupData);
        if ($result['id']) {
          return (int) $result['id'];
        }
      }
      catch (CiviCRM_API3_Exception $ex) {
        Civi::log()->error(E::ts("Error trying to create custom group with data: ") . json_encode($customGroupData)
        . E::ts(" in ") . __METHOD__ . E::ts(", error from API3 CustomGroup create: ") . $ex->getMessage());
      }
    }
    return FALSE;
  }

  /**
   * Method to create custom group data with api 4
   *
   * @param array $customGroupData
   * @return false|int
   */
  public function createCustomGroupAPI4(array $customGroupData) {
    if (isset($customGroupData['name']) && isset($customGroupData['extends'])) {
      if (!isset($customGroupData['title']) || empty($customGroupData['title'])) {
        $customGroupData['title'] = str_replace("_", " ", $customGroupData['name']);
      }
      if (!isset($customGroupData['table_name']) || empty($customGroupData['table_name'])) {
        $customGroupData['table_name'] = "civicrm_value_" . strtolower($customGroupData['name']);
      }
      if (!isset($customGroupData['is_multiple'])) {
        $customGroupData['is_multiple'] = FALSE;
      }
      try {
        $result = \Civi\Api4\CustomGroup::create()
          ->setCheckPermissions(FALSE)->setValues($customGroupData)
          ->addValue('is_active', TRUE);
        if (isset($customGroupData['extends_entity_column_value']) && !empty($customGroupData['extends_entity_column_value'])) {
          if (is_array($customGroupData['extends_entity_column_value'])) {
            $result->addValue('extends_entity_column_value', $customGroupData['extends_entity_column_value']);
          }
          else {
            $result->addValue('extends_entity_column_value', [$customGroupData['extends_entity_column_value']]);
          }
        }
        $result->execute()->first();
        if (isset($result['name'])) {
          return $result['name'];
        }
      }
      catch (API_Exception $ex) {
        Civi::log()->error(E::ts("Error creating custom group in ") . __METHOD__ . E::ts(" with data")
          . json_encode($customGroupData) . E::ts(", error from API4 CustomGroup create:") . $ex->getMessage());
      }
    }
    return FALSE;
  }

  /**
   * Method to create custom field data with api 4
   *
   * @param array $customFieldData
   * @return false|int
   */
  public function createCustomFieldAPI4(array $customFieldData) {
    if (isset($customFieldData['name']) && isset($customFieldData['data_type']) && isset($customFieldData['html_type'])) {
      if (isset($customFieldData['custom_group_id']) || isset($customFieldData['custom_group_id:name'])) {
        if (!isset($customFieldData['label']) || empty($customFieldData['label'])) {
          $customFieldData['label'] = str_replace("_", " ", $customFieldData['name']);
        }
        if (!isset($customFieldData['column_name']) || empty($customFieldData['column_name'])) {
          $customFieldData['column_name'] = strtolower($customFieldData['column_name']);
        }
        if (!isset($customFieldData['in_selector']) || !$customFieldData['in_selector']) {
          $customFieldData['in_selector'] = FALSE;
        }
        try {
          $result = \Civi\Api4\CustomField::create()
            ->setCheckPermissions(FALSE)->setValues($customFieldData)
            ->addValue('is_searchable', TRUE)
            ->addValue('is_active', TRUE);
          if (isset($customFieldData['is_view'])) {
            $result->addValue('is_view', $customFieldData['is_view']);
          }
          if (isset($customFieldData['option_group_id']) && !empty($customFieldData['option_group_id'])) {
            $result->addValue('option_group_id', $customFieldData['option_group_id']);
          }
          if (isset($customFieldData['end_date_years']) && !empty($customFieldData['end_date_years'])) {
            $result->addValue('end_date_years', $customFieldData['end_date_years']);
          }
          if (isset($customFieldData['start_date_years']) && !empty($customFieldData['start_date_years'])) {
            $result->addValue('start_date_years', $customFieldData['start_date_years']);
          }
          if (isset($customFieldData['date_format']) && !empty($customFieldData['date_format'])) {
            $result->addValue('date_format', $customFieldData['date_format']);
          }
          $result->execute()->first();
          return TRUE;
        } catch (API_Exception $ex) {
          Civi::log()->error(E::ts("Error creating custom field in ") . __METHOD__ . E::ts(" with data")
            . json_encode($customFieldData) . E::ts(", error from API4 CustomField create:") . $ex->getMessage());
        }
      }
    }
    return FALSE;
  }

  /**
   * Method to create custom field
   *
   * @param array $customFieldData
   * @return false|int
   */
  public function createCustomField(array $customFieldData) {
    if ($this->_hasAPI4) {
      return $this->createCustomFieldAPI4($customFieldData);
    }
    else {
      try {
        $result = civicrm_api3("CustomField", "create", $customFieldData);
        if ($result['id']) {
          return (int) $result['id'];
        }
      }
      catch (CiviCRM_API3_Exception $ex) {
        Civi::log()->error(E::ts("Error trying to create custom field with data: ") . json_encode($customFieldData)
          . E::ts(" in ") . __METHOD__ . E::ts(", error from API3 CustomField create: ") . $ex->getMessage());
      }
    }
    return FALSE;
  }

  /**
   * Method to get the JSON file with custom data definitions
   *
   * @param string $fileName
   * @return false|mixed
   */
  public function getJsonFile(string $fileName) {
    $jsonFile = $this->_resourcesPath . $fileName;
    if (!file_exists($jsonFile)) {
      Civi::log()->error(E::ts("Could not load JSON file with name ") . $jsonFile
        . E::ts(" which should contain custom group defintions in ") . __METHOD__);
      return FALSE;
    }
    $jsonData = file_get_contents($jsonFile);
    return json_decode($jsonData, TRUE);
  }

  /**
   * Method om een custom veld te verwijderen
   *
   * @param string $customGroupName
   * @param string $customFieldName
   * @return void
   */
  public function deleteCustomField(string $customGroupName, string $customFieldName): void {
    if ($customGroupName && $customFieldName) {
      try {
        \Civi\Api4\CustomField::delete()
          ->addWhere('custom_group_id:name', '=', $customGroupName)
          ->addWhere('name', '=', $customFieldName)
          ->setCheckPermissions(FALSE)->execute();
      }
      catch (API_Exception $ex) {
      }
    }
  }

}
