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
use Civi\DataProcessor\Source\Contribution\AggregatedContributionSource;
use CRM_Aivlgeneric_ExtensionUtil as E;

class OptimizedAggregatedContributionSource extends AggregatedContributionSource {

  /**
   * @return \Civi\DataProcessor\DataFlow\SqlDataFlow
   */
  protected function getEntityDataFlow() {
    $needToSetIndex = false;
    if (empty($this->entityDataFlow)) {
      $needToSetIndex = true;
    }
    $return = parent::getEntityDataFlow();
    if ($needToSetIndex && $this->entityDataFlow instanceof SqlTableDataFlow) {
      $this->entityDataFlow->setIndexStatement("IGNORE INDEX (`index_contribution_status`)");
    }
    return $return;
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


}
