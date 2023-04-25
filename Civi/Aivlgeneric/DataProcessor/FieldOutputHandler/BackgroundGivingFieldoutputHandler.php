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

namespace Civi\Aivlgeneric\DataProcessor\FieldOutputHandler;

use Civi\DataProcessor\FieldOutputHandler\AbstractSimpleFieldOutputHandler;
use Civi\DataProcessor\FieldOutputHandler\FieldOutput;
use CRM_Aivlgeneric_ExtensionUtil as E;
use CRM_Core_Form;
use DateTime;

class BackgroundGivingFieldoutputHandler extends AbstractSimpleFieldOutputHandler {

  protected $financialTypeOptions = [];

  protected $statusOptions = [];

  protected $statusIds = [];

  protected $financialTypeIds = [];

  /**
   * Initialize the processor
   *
   * @param String $alias
   * @param String $title
   * @param array $configuration
   */
  public function initialize($alias, $title, $configuration): void {
    parent::initialize($alias, $title, $configuration);
    $this->statusIds = $configuration['status_ids'];
    $this->financialTypeIds = $configuration['financial_type_ids'];
  }


  /**
   * Returns the formatted value
   *
   * @param $rawRecord
   * @param $formattedRecord
   *
   * @return \Civi\DataProcessor\FieldOutputHandler\FieldOutput
   */
  public function formatField($rawRecord, $formattedRecord) {
    $contactId = $rawRecord[$this->inputFieldSpec->alias] ?? '';
    $formattedValue = '';
    if (!empty($contactId)) {
      $sql = "SELECT
        MIN(`receive_date`) as `min_receive_date`,
        MAX(`receive_date`) as `max_receive_date`,
        SUM(`total_amount`) as `total_amount`
        FROM `civicrm_contribution`
        WHERE `contact_id` = %1
      ";
      $lastGiftSql = "SELECT
        `receive_date`,
        `total_amount`,
        `financial_type_id`
        FROM `civicrm_contribution`
        WHERE `contact_id` = %1
      ";
      if (count($this->statusIds)) {
        $sql .= " AND `contribution_status_id` IN (" . implode(", ", $this->statusIds) . ")";
        $lastGiftSql .= " AND `contribution_status_id` IN (" . implode(", ", $this->statusIds) . ")";
      }
      if (count($this->financialTypeIds)) {
        $sql .= " AND `financial_type_id` IN (" . implode(", ", $this->financialTypeIds) . ")";
        $lastGiftSql .= " AND `financial_type_id` IN (" . implode(", ", $this->financialTypeIds) . ")";
      }
      $sql .= " GROUP BY `contact_id`";
      $lastGiftSql .= " ORDER BY `receive_date` DESC LIMIT 0, 1";
      $dao = \CRM_Core_DAO::executeQuery($sql, [1=>[$contactId, 'Integer']]);
      $texts = [];
      if ($dao->fetch()) {
        $today = new DateTime();
        $firstGift = new DateTime($dao->min_receive_date);
        $lastGift = new DateTime($dao->max_receive_date);
        $totalAmount = number_format((float) $dao->total_amount, 2, '.', ',');
        $duration = date_diff($today, $firstGift);
        $durationInYears = $duration->format('%y');
        $givingDuration = date_diff($lastGift, $firstGift);
        $givingDurationInYears = $givingDuration->format('%y');
        $texts[] = E::ts('Reeds %1 jaar schenker', [1=>$durationInYears]);
        $texts[] = E::ts('Eerste schenking %1, laatste schenking %2', [1=>$firstGift->format('d/m/Y'), 2=>$lastGift->format('d/m/Y')]);
        $texts[] = E::ts('Gaf in totaal reeds %1 euro, de afgelopen %2 jaar', [1=>$totalAmount, 2=>$givingDurationInYears]);
      }
      $lastGiftDao = \CRM_Core_DAO::executeQuery($lastGiftSql, [1=>[$contactId, 'Integer']]);
      if ($lastGiftDao->fetch()) {
        $totalAmount = number_format((float) $dao->total_amount, 2, '.', ',');
        $financialTypeOptions = $this->getFinancialTypeOptions();
        $financialType = '';
        if (isset($financialTypeOptions[$lastGiftDao->financial_type_id])) {
          $financialType = $financialTypeOptions[$lastGiftDao->financial_type_id];
        }
        $texts[] = E::ts('Laatste gift was %1 euro, van het type %2', [1=>$totalAmount, 2=>$financialType]);
      }
      $formattedValue = implode(" // ", $texts);
    }
    $output = new FieldOutput($formattedValue);
    $output->formattedValue = $formattedValue;
    return $output;
  }

  /**
   * Returns the data type of this field
   *
   * @return String
   */
  protected function getType() {
    return 'String';
  }

  /**
   * @param \CRM_Core_Form $form
   * @param array $field
   *
   * @return void
   */
  public function buildConfigurationForm(CRM_Core_Form $form, $field=array()): void {
    parent::buildConfigurationForm($form, $field);
    try {
      $form->add('select', "status_ids", E::ts('Contribution Status Ids'), $this->getControbitionStatusOptions(), false, [
        'class' => 'crm-select2 huge',
        'multiple' => TRUE,
        'placeholder' => E::ts('- Any status -'),
      ]);
      $form->add('select', "financial_type_ids", E::ts('Financial Type Ids'), $this->getFinancialTypeOptions(), false, [
        'class' => 'crm-select2 huge',
        'multiple' => TRUE,
        'placeholder' => E::ts('- Any financial type -'),
      ]);
    } catch (CRM_Core_Exception $e) {
    }
    if (isset($field['configuration'])) {
      $configuration = $field['configuration'];
      $defaults = [];
      if (isset($configuration['status_ids'])) {
        $defaults['status_ids'] = $configuration['status_ids'];
      }
      if (isset($configuration['financial_type_ids'])) {
        $defaults['financial_type_ids'] = $configuration['financial_type_ids'];
      }
      $form->setDefaults($defaults);
    }
  }

  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues
   *
   * @return array
   */
  public function processConfiguration($submittedValues): array {
    $configuration = parent::processConfiguration($submittedValues);
    $configuration['status_ids'] = $submittedValues['status_ids'];
    $configuration['financial_type_ids'] = $submittedValues['financial_type_ids'];
    return $configuration;
  }

  /**
   * When this handler has configuration specify the template file name
   * for the configuration form.
   *
   * @return string|null
   */
  public function getConfigurationTemplateFileName():? string {
    return "CRM/Aivlgeneric/Dataprocessor/Form/Field/Configuration/BackgroundGivingFieldOutputHandler.tpl";
  }

  protected function getControbitionStatusOptions(): array {
      if (empty($this->statusOptions)) {
        $this->statusOptions = [];
        try {
          $contributionStatusApi = civicrm_api3('OptionValue', 'get', [
            'option_group_id' => 'contribution_status',
            'options' => ['limit' => 0]
          ]);
          foreach ($contributionStatusApi['values'] as $option) {
            $this->statusOptions[$option['value']] = $option['label'];
          }
        } catch (CiviCRM_API3_Exception $e) {
        }
      }
      return $this->statusOptions;
    }

  protected function getFinancialTypeOptions(): array {
      if (empty($this->financialTypeOptions)) {
        $this->financialTypeOptions = [];
        try {
          $financialTypeApi = civicrm_api3('FinancialType', 'get', [
            'options' => ['limit' => 0],
          ]);
          foreach ($financialTypeApi['values'] as $financialType) {
            $this->financialTypeOptions[$financialType['id']] = $financialType['name'];
          }
        } catch (CiviCRM_API3_Exception $e) {
        }
      }
      return $this->financialTypeOptions;
    }


}
