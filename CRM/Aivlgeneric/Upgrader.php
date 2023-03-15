<?php
use CRM_Aivlgeneric_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_Aivlgeneric_Upgrader extends CRM_Aivlgeneric_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Example: Run an external SQL script when the module is installed.
   */
  public function install() {
    $this->createWelkomstPakketIfNotExists();
    $this->createFirstYearGroupsIfNotExists();
    $this->createPrimaryPetitionField();
    $this->createDatabaseIndexOnContributionTable();
  }

  /**
   * Upgrade 1000: create welkomst pakket activity type id if not exists
   *
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_1000() {
    $this->ctx->log->info('Applying update 1000 - add welkomst pakket activity type if not exists');
    $this->createWelkomstPakketIfNotExists();
    return TRUE;
  }

  /**
   * Upgrade 1010: create first year groups if not exists
   *
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_1010() {
    $this->ctx->log->info('Applying update 1010 - create first year groups if not exists');
    $this->createFirstYearGroupsIfNotExists();
    return TRUE;
  }

  /**
   * Upgrade 1020 - add custom field aivl primary petition
   * @return bool
   */
  public function upgrade_1020() {
    $this->ctx->log->info("Applying update 1020 - add primary petition custom field");
    $this->createPrimaryPetitionField();
    return TRUE;
  }

  public function upgrade_1021() {
    $this->ctx->log->info("Applying update 1021 - add database index on contribution table");
    $this->createDatabaseIndexOnContributionTable();
    return TRUE;
  }

  /**
   * Method to create aivl primary petition field
   */
  private function createPrimaryPetitionField() {
    $customData = new CRM_Aivlgeneric_CustomData();
    $file = $customData->getJsonFile('aivl_primary_petition_field.json');
    if ($file) {
      $customGroupId = $customData->getCustomGroupId('Petities_metadata', 'Activity');
      if ($customGroupId) {
        foreach ($file as $key => $values) {
          $customFieldId = $customData->getCustomFieldId($customGroupId, $key);
          if (!$customFieldId) {
            $values['custom_group_id'] = $customGroupId;
            $customData->createCustomField($values);
          }
        }
      }
    }
  }

  /**
   * Method to create first year groups if not exists
   */
  private function createFirstYearGroupsIfNotExists() {
    $groups = [
      'aivl_first_year_donor' => [
        'title' => "First Year Donor",
        'description' => "Groep voor eerste jaar donors (nieuwe donors die in deze groep komen na hun eerste non-recurrente donatie)"
      ],
      'aivl_first_year_40+' => [
        'title' => "First Year Donor 40+",
        'description' => "Groep voor eerste jaar donors die 40 euro of meer doneren als eerste donatie (nieuwe donors die in deze groep komen na hun eerste non-recurrente donatie als die 40 euro of meer is)"
      ],
    ];
    foreach ($groups as $groupName => $groupData) {
      if (function_exists('civicrm_api4')) {
        $this->createFirstYearGroupApi4($groupName, $groupData['title'], $groupData['description']);
      }
      else {
        $this->createFirstYearGroupApi3($groupName, $groupData['title'], $groupData['description']);
      }
    }
  }

  /**
   * Method to create a first year donor group if it does not exist yet with api4
   *
   * @param $name
   * @param $title
   * @param $description
   */
  private function createFirstYearGroupApi4($name, $title, $description) {
    try {
      $action = "get";
      $groups = \Civi\Api4\Group::$action()
        ->addSelect('COUNT(*) AS count')
        ->addWhere('name', '=', $name)
        ->execute();
      $group = $groups->first();
      if ($group['count'] == 0) {
        $action = "create";
        \Civi\Api4\Group::$action()
          ->addValue('name', $name)
          ->addValue('title', $title)
          ->addValue('description', $description)
          ->addValue('is_active', TRUE)
          ->addValue('group_type', [Civi::service('aivlgeneric')->getMailingListGroupTypeId()])
          ->addValue('is_reserved', TRUE)
          ->execute();
      }
    }
    catch (API_Exception $ex) {
      Civi::log()->error(E::ts("Error creating group in ") . __METHOD__ . E::ts(", error from API4 Group ") . $action
        . $ex->getMessage());
    }
  }

  /**
   * Method to create a first year donor group if it does not exist yet with api3
   *
   * @param $name
   * @param $title
   * @param $description
   */
  private function createFirstYearGroupApi3($name, $title, $description) {
    try {
      $action = "getcount";
      $groupCount = civicrm_api3('Group', $action, ['name' => $name]);
      if ($groupCount == 0) {
        $action = "create";
        civicrm_api3('Group', $action, [
          'name' => $name,
          'title' => $title,
          'description' => $description,
          'is_active' => TRUE,
          'is_reserved' => TRUE,
          'group_type' => Civi::service('aivlgeneric')->getMailingListGroupTypeName(),
        ]);
      }
    }
    catch (CiviCRM_API3_Exception $ex) {
      Civi::log()->error(E::ts("Error creating group in ") . __METHOD__ . E::ts(", error from API3 Group ") . $action
        . $ex->getMessage());
    }
  }

  /**
   * Method to create welkomstpakket activity type if not exists else make sure it is active
   */
  private function createWelkomstPakketIfNotExists() {
    $this->createWelkomstPakketActivity();
    $this->createWelkomstPakketCustomData();
  }

  /**
   * Method to create the welkomstpakket custom group if not exists
   */
  private function createWelkomstPakketCustomData() {
    $query = "SELECT cov.value
        FROM civicrm_option_group AS cog
            JOIN civicrm_option_value AS cov ON cog.id = cov.option_group_id
        WHERE cog.name = %1 AND cov.name = %2";
    $optionValue = CRM_Core_DAO::singleValueQuery($query, [
      1 => ["activity_type", "String"],
      2 => ["aivl_welkomstpakket", "String"],
    ]);
    if ($optionValue) {
      try {
        $customGroupId = civicrm_api3('CustomGroup', 'getvalue', [
          'name' => 'aivl_welkomstpakket',
          'extends' => 'Activity',
          'extends_entity_column_value' => $optionValue,
          'return' => 'id'
        ]);
        if ($customGroupId) {
          $this->createTypeCustomField((int)$customGroupId);
        }
      }
      catch (CiviCRM_API3_Exception $ex) {
        $customGroupParams = [
          'name' => 'aivl_welkomstpakket',
          'table_name' => 'civicrm_value_welkomstpakket_data',
          'title' => 'Welkomstpakket gegevens',
          'extends' => 'Activity',
          'extends_entity_column_value' => $optionValue,
          'is_active' => 1,
          'is_reserved' => 1,
        ];
        try {
          $customGroup = civicrm_api3('CustomGroup', 'create', $customGroupParams);
          $this->createTypeCustomField((int)$customGroup['id']);
        }
        catch (CiviCRM_API3_Exception $ex) {
          Civi::log()->debug('Could not creat a custom group for welkomstpakket in ' . __METHOD__
            . ', error message from API CustomGroup create: ' . $ex->getMessage());
        }
      }
    }
  }

  /**
   * Method to create type welkomstpakket custom field if not exists
   *
   * @param int $customGroupId
   * @throws CiviCRM_API3_Exception
   */
  private function createTypeCustomField(int $customGroupId) {
    $exists = CRM_Core_BAO_SchemaHandler::checkIfFieldExists('civicrm_value_welkomstpakket_data', 'type_pakket');
    if (!$exists) {
      // make sure option group exists or is created
      $optionGroupId = $this->createTypeOptionGroup();
      try {
        civicrm_api3('CustomField', 'create', [
          'custom_group_id' => $customGroupId,
          'name' => 'type_pakket',
          'column_name' => 'type_pakket',
          'in_selector' => TRUE,
          'label' => 'Type welkomstpakket',
          'data_type' => 'String',
          'html_type' => 'Select',
          'option_group_id' => $optionGroupId,
          'is_searchable' => TRUE,
          'is_active' => TRUE,
        ]);
      }
      catch (CiviCRM_API3_Exception $ex) {

      }
    }

  }

  /**
   * Method to create welkomstpakket option group
   *
   * @return int
   * @throws CiviCRM_API3_Exception
   */
  private function createTypeOptionGroup() {
    try {
      $optionGroupId = civicrm_api3('OptionGroup', 'getvalue', [
        'return' => 'id',
        'name' => 'type_welkomstpakket',
      ]);
      if ($optionGroupId) {
        $this->createTypeOptionValues((int) $optionGroupId);
        return (int) $optionGroupId;
      }
    } catch (CiviCRM_API3_Exception $ex) {
      $optionGroup = civicrm_api3('OptionGroup', 'create' , [
        'name' => 'type_welkomstpakket',
        'title' => 'Type Welkomstpakket',
        'data_type' => 'String',
        'is_active' => 1,
        'is_reserved' => 1,
      ]);
      if ($optionGroup) {
        $this->createTypeOptionValues((int) $optionGroup['id']);
        return (int) $optionGroup['id'];
      }
    }
  }

  /**
   * Method to create welkomstpakket type option values
   *
   * @param int $optionGroupId
   */
  private function createTypeOptionValues(int $optionGroupId) {
    $values = [
      'recurring' => 'Recurrente donor',
      'once_over40' => 'Eenmalige donor 40 euro of meer',
    ];
    foreach ($values as $valueName => $valueLabel) {
      $query = "SELECT COUNT(*) FROM civicrm_option_value WHERE option_group_id = %1 AND name = %2";
      $count = CRM_Core_DAO::singleValueQuery($query, [
        1 => [$optionGroupId, "Integer"],
        2 => [$valueName, "String"],
      ]);
      if ($count == 0) {
        try {
          civicrm_api3('OptionValue', 'create', [
            'option_group_id' => $optionGroupId,
            'name' => $valueName,
            'value' => $valueName,
            'label' => $valueLabel,
            'is_active' => 1,
          ]);
        }
        catch (CiviCRM_API3_Exception $ex) {
          Civi::log()->error('Could not create option value with name ' . $valueName . ' in option group with ID '
            . $optionGroupId . ' in ' . __METHOD__ . ', message from API OptionValue create: ' . $ex->getMessage());
        }
      }
    }
  }

  /**
   * Method to create the welkomstpakket activity type
   */
  private function createWelkomstPakketActivity() {
    $activityTypeName = "aivl_welkomstpakket";
    try {
      $optionValueId = civicrm_api3('OptionValue', 'getvalue', [
        'option_group_id' => 'activity_type',
        'name' => $activityTypeName,
        'return' => 'id',
      ]);
      // make sure it is active and reserved if exists
      civicrm_api3('OptionValue', 'create', [
        'id' => (int) $optionValueId,
        'is_active' => TRUE,
        'is_reserved' => TRUE,
      ]);
    }
    catch (CiviCRM_API3_Exception $ex) {
      try {
        civicrm_api3('OptionValue', 'create' , [
          'option_group_id' => 'activity_type',
          'name' => $activityTypeName,
          'label' => "Welkomstpakket",
          'is_active' => TRUE,
          'is_reserved' => TRUE,
        ]);
      }
      catch (CiviCRM_API3_Exception $ex) {
        Civi::log()->error(E::ts('Could not create Welkomstpakket activity type id in ') . __METHOD__);
      }
    }
  }

  /**
   * Create database index on contribution table
   * on contact_id, is_test financial_type_id, status_id and receive date.
   *
   * This index optimized the dataprocessor for Direct Mailing.
   * See https://issues.civicoop.org/issues/10202
   *
   * @return void
   */
  private function createDatabaseIndexOnContributionTable() {
    try {
      \CRM_Core_DAO::executeQuery("ALTER TABLE `civicrm_contribution` ADD INDEX aivl_contact_id_type_status_date_istest (`contact_id`, `is_test`, `financial_type_id`, `contribution_status_id`, `receive_date`);");
    } catch (\PEAR_Exception $e) {
      // Do nothing. The index probably already exists.
    }
  }

  /**
   * Example: Work with entities usually not available during the install step.
   *
   * This method can be used for any post-install tasks. For example, if a step
   * of your installation depends on accessing an entity that is itself
   * created during the installation (e.g., a setting or a managed entity), do
   * so here to avoid order of operation problems.
   */
  // public function postInstall() {
  //  $customFieldId = civicrm_api3('CustomField', 'getvalue', array(
  //    'return' => array("id"),
  //    'name' => "customFieldCreatedViaManagedHook",
  //  ));
  //  civicrm_api3('Setting', 'create', array(
  //    'myWeirdFieldSetting' => array('id' => $customFieldId, 'weirdness' => 1),
  //  ));
  // }

  /**
   * Example: Run an external SQL script when the module is uninstalled.
   */
  // public function uninstall() {
  //  $this->executeSqlFile('sql/myuninstall.sql');
  // }

  /**
   * Example: Run a simple query when a module is enabled.
   */
  // public function enable() {
  //  CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 1 WHERE bar = "whiz"');
  // }

  /**
   * Example: Run a simple query when a module is disabled.
   */
  // public function disable() {
  //   CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 0 WHERE bar = "whiz"');
  // }

  /**
   * Example: Run an external SQL script.
   *
   * @return TRUE on success
   * @throws Exception
   */
  // public function upgrade_4201() {
  //   $this->ctx->log->info('Applying update 4201');
  //   // this path is relative to the extension base dir
  //   $this->executeSqlFile('sql/upgrade_4201.sql');
  //   return TRUE;
  // }


  /**
   * Example: Run a slow upgrade process by breaking it up into smaller chunk.
   *
   * @return TRUE on success
   * @throws Exception
   */
  // public function upgrade_4202() {
  //   $this->ctx->log->info('Planning update 4202'); // PEAR Log interface

  //   $this->addTask(E::ts('Process first step'), 'processPart1', $arg1, $arg2);
  //   $this->addTask(E::ts('Process second step'), 'processPart2', $arg3, $arg4);
  //   $this->addTask(E::ts('Process second step'), 'processPart3', $arg5);
  //   return TRUE;
  // }
  // public function processPart1($arg1, $arg2) { sleep(10); return TRUE; }
  // public function processPart2($arg3, $arg4) { sleep(10); return TRUE; }
  // public function processPart3($arg5) { sleep(10); return TRUE; }

  /**
   * Example: Run an upgrade with a query that touches many (potentially
   * millions) of records by breaking it up into smaller chunks.
   *
   * @return TRUE on success
   * @throws Exception
   */
  // public function upgrade_4203() {
  //   $this->ctx->log->info('Planning update 4203'); // PEAR Log interface

  //   $minId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(min(id),0) FROM civicrm_contribution');
  //   $maxId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(max(id),0) FROM civicrm_contribution');
  //   for ($startId = $minId; $startId <= $maxId; $startId += self::BATCH_SIZE) {
  //     $endId = $startId + self::BATCH_SIZE - 1;
  //     $title = E::ts('Upgrade Batch (%1 => %2)', array(
  //       1 => $startId,
  //       2 => $endId,
  //     ));
  //     $sql = '
  //       UPDATE civicrm_contribution SET foobar = whiz(wonky()+wanker)
  //       WHERE id BETWEEN %1 and %2
  //     ';
  //     $params = array(
  //       1 => array($startId, 'Integer'),
  //       2 => array($endId, 'Integer'),
  //     );
  //     $this->addTask($title, 'executeSql', $sql, $params);
  //   }
  //   return TRUE;
  // }

}
