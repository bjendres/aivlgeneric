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

use Civi\DataProcessor\FieldOutputHandler\AbstractFormattedNumberOutputHandler;
use CRM_Aivlgeneric_ExtensionUtil as E;
use DateTime;

class TargetAmount extends AbstractFormattedNumberOutputHandler {

  protected $min_number;
  protected $max_number;

  /**
   * When this handler has additional configuration you can add
   * the fields on the form with this function.
   *
   * @param \CRM_Core_Form $form
   * @param array $field
   */
  public function buildConfigurationForm(\CRM_Core_Form $form, $field=array()) {
    parent::buildConfigurationForm($form, $field);
    $form->add('text', 'min_number', E::ts('Minimum Number'), [], TRUE);
    $form->add('text', 'max_number', E::ts('Maximum Number'), [], TRUE);

    if (isset($field['configuration'])) {
      $configuration = $field['configuration'];
      if (isset($configuration['min_number'])) {
        $this->defaults['min_number'] = $configuration['min_number'];
      }
      if (isset($configuration['max_number'])) {
        $this->defaults['max_number'] = $configuration['max_number'];
      }
      if (is_array($this->defaults) && count($this->defaults)) {
        $form->setDefaults($this->defaults);
      }
    }
  }

  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues
   * @return array
   */
  public function processConfiguration($submittedValues) {
    $configuration = parent::processConfiguration($submittedValues);
    $configuration['min_number'] = $submittedValues['min_number'];
    $configuration['max_number'] = $submittedValues['max_number'];
    return $configuration;
  }

  /**
   * @param array $configuration
   *
   * @return void
   */
  protected function initializeConfiguration($configuration) {
    parent::initializeConfiguration($configuration);
    if (isset($configuration['min_number'])) {
      $this->min_number = (float) $configuration['min_number'];
    }
    if (isset($configuration['max_number'])) {
      $this->max_number = (float) $configuration['max_number'];
    }
  }

  /**
   * When this handler has configuration specify the template file name
   * for the configuration form.
   *
   * @return string|null
   */
  public function getConfigurationTemplateFileName(): ?string {
    return "CRM/Aivlgeneric/Dataprocessor/Form/Field/Configuration/TargetAmountFieldOutputHandler.tpl";
  }


  /**
   * Returns the label of the field for selecting a field.
   *
   * This could be override in a child class.
   *
   * @return string
   */
  protected function getFieldTitle() {
    return E::ts('Amount Field');
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
    $rawValue = (float) $rawRecord[$this->inputFieldSpec->alias] ?? 0.00;
    if ($rawValue < $this->min_number) {
      $rawValue = $this->min_number;
    } elseif ($rawValue > $this->max_number) {
      $rawValue = $this->max_number;
    } else {
      $rawValue = round($rawValue);
    }
    return $this->formatOutput($rawValue);
  }

  /**
   * Returns the data type of this field
   *
   * @return String
   */
  protected function getType() {
    return 'String';
  }

}
