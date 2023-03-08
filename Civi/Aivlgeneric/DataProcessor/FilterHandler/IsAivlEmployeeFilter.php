<?php
/**
 * Copyright (C) 2023  Jaap Jansma (jaap.jansma@civicoop.org)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Civi\Aivlgeneric\DataProcessor\FilterHandler;

use Civi\DataProcessor\DataFlow\SqlDataFlow;
use Civi\DataProcessor\DataSpecification\FieldSpecification;
use Civi\DataProcessor\Exception\InvalidConfigurationException;
use Civi\DataProcessor\FilterHandler\AbstractFieldFilterHandler;
use CiviCRM_API3_Exception;
use CRM_Aivlgeneric_ExtensionUtil as E;
use CRM_Core_Exception;
use CRM_Core_Form;
use CRM_Dataprocessor_Utils_DataSourceFields;
use Exception;

class IsAivlEmployeeFilter extends AbstractFieldFilterHandler {

  /**
   * Initialize the filter
   *
   * @throws \Civi\DataProcessor\Exception\DataSourceNotFoundException
   * @throws \Civi\DataProcessor\Exception\InvalidConfigurationException
   * @throws \Civi\DataProcessor\Exception\FieldNotFoundException
   */
  protected function doInitialization() {
    if (!isset($this->configuration['datasource']) || !isset($this->configuration['field'])) {
      throw new InvalidConfigurationException(E::ts("Filter %1 requires a field to filter on. None given.", array(1=>$this->title)));
    }
    $this->initializeField($this->configuration['datasource'], $this->configuration['field']);
  }

  /**
   * Returns true when this filter has additional configuration
   *
   * @return bool
   */
  public function hasConfiguration(): bool {
    return true;
  }

  /**
   * When this filter type has additional configuration you can add
   * the fields on the form with this function.
   *
   * @param \CRM_Core_Form $form
   * @param array $filter
   */
  public function buildConfigurationForm(CRM_Core_Form $form, $filter=array()) {
    $fieldSelect = [];
    try {
      $fieldSelect = CRM_Dataprocessor_Utils_DataSourceFields::getAvailableFilterFieldsInDataSources($filter['data_processor_id']);
    } catch (Exception $e) {
    }

    try {
      $form->add('select', 'contact_id_field', E::ts('Contact ID Field'), $fieldSelect, TRUE, [
        'style' => 'min-width:250px',
        'class' => 'crm-select2 huge',
        'placeholder' => E::ts('- select -'),
      ]);
    } catch (CRM_Core_Exception $e) {
    }

    if (isset($filter['configuration'])) {
      $configuration = $filter['configuration'];
      $defaults = array();
      if (isset($configuration['field']) && isset($configuration['datasource'])) {
        try {
          $defaults['contact_id_field'] = CRM_Dataprocessor_Utils_DataSourceFields::getSelectedFieldValue($filter['data_processor_id'], $configuration['datasource'], $configuration['field']);
        } catch (CiviCRM_API3_Exception $e) {
        }
      }
      $form->setDefaults($defaults);
    }
  }

  /**
   * When this filter type has configuration specify the template file name
   * for the configuration form.
   *
   * @return false|string
   */
  public function getConfigurationTemplateFileName():? string {
    return "CRM/Aivlgeneric/Dataprocessor/Form/Filter/Configuration/IsAivlEmployeeFilter.tpl";
  }


  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues
   * @return array
   */
  public function processConfiguration($submittedValues): array {
    [$datasource, $field] = explode('::', $submittedValues['contact_id_field'], 2);
    $configuration['field'] = $field;
    $configuration['datasource'] = $datasource;
    return $configuration;
  }

  protected function getOperatorOptions(FieldSpecification $fieldSpec): array {
    return array(
      'null' => E::ts('Is AIVL Employee'),
      'not null' => E::ts('Is not an AIVL Employee'),
    );
  }

  /**
   * @param array $filter
   *   The filter settings
   */
  public function setFilter($filter) {
    $dataFlow = null;
    try {
      $this->resetFilter();
    } catch (Exception $e) {
    }
    try {
      $dataFlow = $this->dataSource->ensureField($this->inputFieldSpecification);
    } catch (Exception $e) {
    }
    if ($dataFlow instanceof SqlDataFlow) {
      /** @var \CRM_AivlGeneric_AivlGenericConfig $aivlContainer */
      $aivlContainer = \Civi::service('aivlgeneric');
      $tableAlias = $this->getTableAlias($dataFlow);
      $fieldName = $this->inputFieldSpecification->getName();
      $fieldAlias = $this->inputFieldSpecification->alias;
      $aivlEmployeeRelTypeId = $aivlContainer->getEmployeeRelationshipTypeId();
      $aivlContactId = $aivlContainer->getAivlContactId();
      $sqlStatement = "`$tableAlias`.`$fieldName` {$filter['op']} (
        SELECT `contact_id_a`
        FROM `civicrm_relationship` `aivl_employee_$fieldAlias`
        WHERE `is_active` = '1'
        AND `aivl_employee_$fieldAlias`.`relationship_type_id` = {$aivlEmployeeRelTypeId}
        AND `aivl_employee_$fieldAlias`.`contact_id_b` ={$aivlContactId}
        ";
      $this->whereClause = new SqlDataFlow\PureSqlStatementClause($sqlStatement, FALSE);
      $dataFlow->addWhereClause($this->whereClause);
    }
  }


}
