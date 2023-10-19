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

use Civi\DataProcessor\DataSpecification\FieldSpecification;
use Civi\DataProcessor\FieldOutputHandler\AbstractSimpleFieldOutputHandler;
use Civi\DataProcessor\FieldOutputHandler\FieldOutput;
use CRM_Aivlgeneric_ExtensionUtil as E;
use DateTime;

class DateRangeSegmentation extends AbstractSimpleFieldOutputHandler {

  /**
   * Returns the label of the field for selecting a field.
   *
   * This could be override in a child class.
   *
   * @return string
   */
  protected function getFieldTitle() {
    return E::ts('Date Field');
  }

  /**
   * Callback function for determining whether this field could be handled by this output handler.
   *
   * @param \Civi\DataProcessor\DataSpecification\FieldSpecification $field
   * @return bool
   */
  public function isFieldValid(FieldSpecification $field) {
    if ($field->type == 'Date' || $field->type == 'Timestamp') {
      return TRUE;
    }
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
    $rawValue = $rawRecord[$this->inputFieldSpec->alias] ?? '';
    $formattedValue = '';
    if (strlen($rawValue)) {
      try {
        $date = new DateTime($rawValue);
        if ($date) {
          $today = new DateTime();
          $dateDiff =  $today->diff($date);
          $months = (($dateDiff->y) * 12) + ($dateDiff->m);
          if ($months <= 6) {
            $formattedValue = E::ts('0 - 6 months');
          } elseif ($months <= 12) {
            $formattedValue = E::ts('6 - 12 months');
          } elseif ($months <= 24) {
            $formattedValue = E::ts('12 - 24 months');
          } elseif ($months <= 36) {
            $formattedValue = E::ts('24 - 36 months');
          } elseif ($months <= 48) {
            $formattedValue = E::ts('36 - 48 months');
          } elseif ($months <= 60) {
            $formattedValue = E::ts('48 - 60 months');
          } else {
            $formattedValue = E::ts('+60 months');
          }
        }
      } catch (\Exception $e) {
      }
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

}
