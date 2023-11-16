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

namespace Civi\Aivlgeneric\DataProcessor\Source\Contribution;

use Civi\DataProcessor\DataFlow\SqlTableDataFlow;
use Civi\DataProcessor\DataSpecification\DataSpecification;
use Civi\DataProcessor\DataSpecification\FieldSpecification;
use Civi\DataProcessor\Source\AbstractCivicrmEntitySource;
use CRM_Aivlgeneric_ExtensionUtil as E;

class OptimizedAggregatedContributionSource extends AbstractCivicrmEntitySource {

  /**
   * Returns the entity name
   *
   * @return String
   */
  protected function getEntity() {
    return 'Contribution';
  }

  /**
   * Returns the table name of this entity
   *
   * @return String
   */
  protected function getTable() {
    return 'civicrm_contribution';
  }

  /**
   * Returns the default configuration for this data source
   *
   * @return array
   */
  public function getDefaultConfiguration() {
    return array(
      'filter' => array(
        'is_test' => array (
          'op' => '=',
          'value' => '0',
        )
      )
    );
  }

  /**
   * Returns an array with possible aggregate functions.
   * Return false when aggregation is not possible.
   *
   * This function could be overridden in child classes.
   *
   * @return array|false
   */
  protected function getPossibleAggregateFunctions() {
    return [
      'sum_total_amount' => E::ts('Sum Total Amount'),
    ];
  }

  /**
   * @return \Civi\DataProcessor\DataFlow\SqlDataFlow
   */
  protected function getEntityDataFlow() {
    $needToSetIndex = false;
    if (empty($this->entityDataFlow)) {
      $needToSetIndex = true;
    }

    if (!$this->entityDataFlow) {
      if ($this->isAggregationEnabled()) {
        $this->entityDataFlow = $this->getAggregationDataFlow();
      } else {
        $this->entityDataFlow = new SqlTableDataFlow($this->getTable(), $this->getSourceName());
      }
    }

    if ($needToSetIndex && $this->entityDataFlow instanceof SqlTableDataFlow) {
      $this->entityDataFlow->setIndexStatement("IGNORE INDEX (`index_contribution_status`)");
    }
    return $this->entityDataFlow;
  }

  protected function getAggregationDataFlow() {
    $needToSetIndex = false;
    if (empty($this->aggretated_table_dataflow)) {
      $needToSetIndex = true;
    }
    $return = parent::getAggregationDataFlow();
    if ($needToSetIndex) {
      $this->aggretated_table_dataflow->setIndexStatement("IGNORE INDEX (`index_contribution_status`)");
    }
    return $return;
  }



  /**
   * @return \Civi\DataProcessor\DataSpecification\DataSpecification
   * @throws \Civi\DataProcessor\DataSpecification\FieldExistsException
   */
  public function getAvailableFields() {
    if (!$this->availableFields) {
      $this->availableFields = new DataSpecification();
      $totalAmount = new FieldSpecification('total_amount', 'Float', E::ts('Total amount'), null, $this->getSourceName().'_total_amount');
      $this->availableFields->addFieldSpecification('total_amount', $totalAmount);
      $contactId = new FieldSpecification('contact_id', 'Float', E::ts('Contact ID'), null, $this->getSourceName().'_contact_id');
      $this->availableFields->addFieldSpecification('contact_id', $contactId);
      $count = new FieldSpecification('count', 'Integer', E::ts('Count'), null, $this->getSourceName().'_count');
      $this->availableFields->addFieldSpecification('count', $count);
    }
    return $this->availableFields;
  }

  /**
   * Adds an inidvidual filter to the data source
   *
   * @param $filter_field_alias
   * @param $op
   * @param $values
   *
   * @throws \Exception
   */
  protected function addFilter($filter_field_alias, $op, $values) {
    $spec = NULL;
    if ($this->getAvailableFilterFields()->doesAliasExists($filter_field_alias)) {
      $spec = $this->getAvailableFilterFields()
        ->getFieldSpecificationByAlias($filter_field_alias);
    }
    elseif ($this->getAvailableFilterFields()->doesFieldExist($filter_field_alias)) {
      $spec = $this->getAvailableFilterFields()
        ->getFieldSpecificationByName($filter_field_alias);
    }
    if ($spec) {
      $this->addFilterToAggregationDataFlow($spec, $op, $values);
    }
  }

  /**
   * @param \Civi\DataProcessor\DataSpecification\FieldSpecification $fieldSpecification
   *
   * @return void
   * @throws \Exception
   */
  public function ensureFieldInSource(FieldSpecification $fieldSpecification) {
    parent::ensureFieldInSource($fieldSpecification);
    if ($fieldSpecification->name == 'count' && $this->isAggregationEnabled()) {
      $this->ensureEntity();
      $countField = new FieldSpecification('id', 'Integer', E::ts('Count'), null, 'count');
      $countField->setMySqlFunction('COUNT');
      $this->aggretated_table_dataflow->getDataSpecification()->addFieldSpecification('count', $countField);
    }
  }

}
