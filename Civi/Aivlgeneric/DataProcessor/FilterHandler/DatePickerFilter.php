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

use Civi\DataProcessor\FilterHandler\SimpleSqlFilter;
use CRM_Aivlgeneric_ExtensionUtil as E;
use DateTime;

/**
 * A specific filter which adds the following options to the date picker filter:
 * - at least 12 months ago
 * - at least 10 months ago
 * - at least 8 months ago
 */
class DatePickerFilter extends SimpleSqlFilter {

  protected function getFieldTitle(): string {
    return E::ts('Date Field');
  }

  /**
   * When this filter type has configuration specify the template file name
   * for the configuration form.
   *
   * @return false|string
   */
  public function getConfigurationTemplateFileName() {
    return "CRM/Aivlgeneric/Dataprocessor/Form/Filter/Configuration/DatePickerFilter.tpl";
  }


  /**
   * Add the elements to the filter form.
   *
   * @param \CRM_Core_Form $form
   * @param array $defaultFilterValue
   * @param string $size
   *   Possible values: full or compact
   * @return array
   *   Return variables belonging to this filter.
   */
  public function addToFilterForm(\CRM_Core_Form $form, $defaultFilterValue, $size='full') {
    $fieldSpec = $this->getFieldSpecification();
    $defaults = array();

    $title = $fieldSpec->title;
    $alias = $fieldSpec->alias;
    if ($this->isRequired()) {
      $title .= ' <span class="crm-marker">*</span>';
    }

    $additionalOptions['null'] = E::ts('Not set');
    $additionalOptions['at_least_12_months_ago'] = E::ts('At least 12 months ago');
    $additionalOptions['at_least_10_months_ago'] = E::ts('At least 10 months ago');
    $additionalOptions['at_least_8_months_ago'] = E::ts('At least 8 months ago');
    \CRM_Dataprocessor_Utils_Form::addDatePickerRange($form, $alias, $title, FALSE, FALSE, E::ts('From'), E::ts('To'), $additionalOptions, '_high', '_low');

    $options = [
        '' => ts('- any -'),
        0 => ts('Choose Date Range'),
      ] + \CRM_Core_OptionGroup::values('relative_date_filters');

    if ($additionalOptions) {
      foreach ($additionalOptions as $key => $optionLabel) {
        $options[$key] = $optionLabel;
      }
    }

    $form->add('select',
      $alias."_relative",
      $title,
      $options,
      FALSE,
      ['class' => 'crm-select2']
    );
    $attributes = ['formatType' => 'searchDate'];
    $extra = ['time' => FALSE];
    $form->add('datepicker', $alias . '_low', E::ts('From'), $attributes, false, $extra);
    $form->add('datepicker', $alias . '_high', E::ts('To'), $attributes, false, $extra);

    if (isset($defaultFilterValue['op'])) {
      $defaults[$alias . '_op'] = $defaultFilterValue['op'];
    }
    if (isset($defaultFilterValue['value'])) {
      $defaults[$alias.'_value'] = $defaultFilterValue['value'];
    }
    if (isset($defaultFilterValue['relative'])) {
      $defaults[$alias.'_relative'] = $defaultFilterValue['relative'];
    }
    if (isset($defaultFilterValue['from'])) {
      $defaults[$alias.'_from'] = $defaultFilterValue['from'];
    }
    if (isset($defaultFilterValue['to'])) {
      $defaults[$alias.'_to'] = $defaultFilterValue['to'];
    }
    if (isset($defaultFilterValue['from_time'])) {
      $defaults[$alias.'_from_time'] = $defaultFilterValue['from_time'];
    }
    if (isset($defaultFilterValue['to_time'])) {
      $defaults[$alias.'_to_time'] = $defaultFilterValue['to_time'];
    }

    $filter['type'] = $fieldSpec->type;
    $filter['alias'] = $fieldSpec->alias;
    $filter['title'] = $title;
    $filter['size'] = $size;

    if (count($defaults)) {
      $form->setDefaults($defaults);
    }

    return $filter;
  }

  protected function getFromTo(string $relative, string $from = null, string $to = null, string $fromTime = null, string $toTime = null) {
    $today = new DateTime();
    switch ($relative) {
      case 'at_least_12_months_ago':
        $from = null;
        $today->modify('-12 months');
        $to = $today->format('Ymd');
        break;
      case 'at_least_10_months_ago':
        $from = null;
        $today->modify('-10 months');
        $to = $today->format('Ymd');
        break;
      case 'at_least_8_months_ago':
        $from = null;
        $today->modify('-8 months');
        $to = $today->format('Ymd');
        break;
      default:
        [$from, $to] = \CRM_Utils_Date::getFromTo($relative, $from, $to, $fromTime, $toTime);
        break;
    }
    return [$from, $to];
  }

  /**
   * Validate the submitted filter parameters.
   *
   * @param $submittedValues
   * @return array
   */
  public function validateSubmittedFilterParams($submittedValues) {
    $errors = array();
    if ($this->isRequired()) {
      $filterSpec = $this->getFieldSpecification();
      $filterName = $filterSpec->alias;
      $processedSubmittedValues = $this->processSubmittedValues($submittedValues);
      if ($filterSpec->type == 'Date' || $filterSpec->type == 'Timestamp') {
        $relative = \CRM_Utils_Array::value("relative", $processedSubmittedValues);
        $from = \CRM_Utils_Array::value("from", $processedSubmittedValues);
        $to = \CRM_Utils_Array::value("to", $processedSubmittedValues);
        $fromTime = \CRM_Utils_Array::value("from_time", $processedSubmittedValues);
        $toTime = \CRM_Utils_Array::value("to_time", $processedSubmittedValues);

        if ($relative != 'null') {
          [$from, $to] = $this->getFromTo($relative, $from, $to, $fromTime, $toTime);
        }
        if (!$from && !$to) {
          $errors[$filterName . '_relative'] = E::ts('Field %1 is required', [1 => $filterSpec->title]);
        }
      }
      elseif (!isset($processedSubmittedValues['op']) || !(isset($processedSubmittedValues['value']) && $processedSubmittedValues['value'])) {
        $errors[$filterName . '_value'] = E::ts('Field %1 is required', [1 => $filterSpec->title]);
      }
    }
    return $errors;
  }

  /**
   * @param array $submittedValues
   * @return string|null
   */
  protected function applyDateFilter($submittedValues) {
    $type = $this->getFieldSpecification()->type;
    $relative = \CRM_Utils_Array::value("relative", $submittedValues);
    $from = \CRM_Utils_Array::value("from", $submittedValues);
    $to = \CRM_Utils_Array::value("to", $submittedValues);
    $fromTime = \CRM_Utils_Array::value("from_time", $submittedValues);
    $toTime = \CRM_Utils_Array::value("to_time", $submittedValues);
    if (!$toTime) {
      $toTime = '235959';
    }

    if ($relative == 'null') {
      $filterParams = [
        'op' => 'IS NULL',
        'value' => '',
      ];
      $filterParams = $this->extendFilterParamsFromSubmittedValues($submittedValues, $filterParams);
      $this->setFilter($filterParams);
      return TRUE;
    } else {
      [$from, $to] = $this->getFromTo($relative, $from, $to, $fromTime, $toTime);
    }
    if ($from && $to) {
      $from = ($type == "Date") ? substr($from, 0, 8) : $from;
      $to = ($type == "Date") ? substr($to, 0, 8) : $to;
      $filterParams = array(
        'op' => 'BETWEEN',
        'value' => array($from, $to),
      );
      $filterParams = $this->extendFilterParamsFromSubmittedValues($submittedValues, $filterParams);
      $this->setFilter($filterParams);
      return TRUE;
    } elseif ($from) {
      $from = ($type == "Date") ? substr($from, 0, 8) : $from;
      $filterParams = array(
        'op' => '>=',
        'value' => $from,
      );
      $filterParams = $this->extendFilterParamsFromSubmittedValues($submittedValues, $filterParams);
      $this->setFilter($filterParams);
      return TRUE;
    } elseif ($to) {
      $to = ($type == "Date") ? substr($to, 0, 8) : $to;
      $filterParams = array(
        'op' => '<=',
        'value' => $to,
      );
      $filterParams = $this->extendFilterParamsFromSubmittedValues($submittedValues, $filterParams);
      $this->setFilter($filterParams);
      return TRUE;
    }
    return FALSE;
  }

}
