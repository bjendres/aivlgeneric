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

namespace Civi\Aivlgeneric\DataProcessor;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use CRM_Aivlgeneric_ExtensionUtil as E;

class CompilerPass implements CompilerPassInterface {

  /**
   * You can modify the container here before it is dumped to PHP code.
   */
  public function process(ContainerBuilder $container) {
    if ($container->hasDefinition('data_processor_factory')) {
      $factoryDefinition = $container->getDefinition('data_processor_factory');
      $factoryDefinition->addMethodCall('addFilter', [
        'aivlsepadataprocessor_mandate', 'Civi\Aivlgeneric\DataProcessor\FilterHandler\IsAivlEmployeeFilter', E::ts('AIVL: Contact is (not) AIVL Employee')]);
      $factoryDefinition->addMethodCall('addFilter', [
        'aivldatepicker', 'Civi\Aivlgeneric\DataProcessor\FilterHandler\DatePickerFilter', E::ts('AIVL: Date Picker')]);
      $factoryDefinition->addMethodCall('addOutputHandler', [
        'aivl_background_giving', 'Civi\Aivlgeneric\DataProcessor\FieldOutputHandler\BackgroundGivingFieldoutputHandler', E::ts("AIVL: Donor Background Giving")
      ]);
    }
  }


}
