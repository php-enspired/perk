<?php
/**
 * @package    at.perk
 * @author     Adrian <adrian@enspi.red>
 * @copyright  2018
 * @license    GPL-3.0 (only)
 *
 *  This program is free software: you can redistribute it and/or modify it
 *  under the terms of the GNU General Public License, version 3.
 *  The right to apply the terms of later versions of the GPL is RESERVED.
 *
 *  This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 *  without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *  See the GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License along with this program.
 *  If not, see <http://www.gnu.org/licenses/gpl-3.0.txt>.
 */
declare(strict_types = 1);

namespace at\perk\Logic;

use at\perk\ {
  FilterException,
  Filter\Logic\LogicException,
  LogicFilter
};

/**
 * Passes if all of the given filters pass.
 *
 * @example <code>
 *  <?php
 *  use at\perk\Perk;
 *
 *  $to = Perk::createFilter([Perk::ALL, Perk::ALWAYS, Prek::ALWAYS]);
 *  $to->apply(1);   // 1
 *  $to->invert(1);  // null
 *
 *  $to = Perk::createFilter([Perk::ALL, Perk::ALWAYS, Prek::NEVER]);
 *  $to->apply(1);   // null
 *  $to->invert(1);  // 1
 * </code>
 */
class All extends LogicFilter {

  /**
   * {@inheritDoc}
   */
  protected function _applyFilter($value) {
    try {
      foreach ($this->_filters as $filter) {
        $value = $filter->apply($filter, true);
      }
      return $value;
    } catch (FilterException $e) {
      throw new LogicException(
        LogicException::NOT_ALL,
        $e,
        ['filter' => $this->_filter, 'value' => $value]
      );
    }
  }

  /**
   * {@inheritDoc}
   */
  protected function _getInvertException($value) : FilterException {
    return new LogicException(
      LogicException::ALL,
      ['filters' => $this->_filters, 'value' => $value]
    );
  }
}
