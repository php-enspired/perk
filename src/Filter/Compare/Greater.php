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

namespace at\perk\Compare;

use at\perk\ {
  Filter,
  FilterException,
  Filter\Compare\ComparisonException
};

/**
 * Passes if the given value compares as greater than the filter's value.
 * Does not check or cooerce data types.
 *
 * To compare as greater or equal, use Less::invert().
 */
class Greater extends Filter {

  /** @type mixed  value to compare against. */
  protected $_compare;

  /**
   * @param mixed $compare  value to compare against
   */
  public function __construct($compare) {
    $this->_compare = $compare;
  }

  /**
   * {@inheritDoc}
   */
  protected function _applyFilter($value) {
    if ($this->_compare < $value) {
      return $value;
    }

    throw new ComparisonException(
      ComparisonException::NOT_GREATER,
      ['compare' => $this->_compare, 'value' => $value]
    );
  }

  /**
   * {@inheritDoc}
   */
  protected function _getInvertException($value) : FilterException {
    return new ComparisonException(
      ComparisonException::IS_GREATER,
      ['compare' => $this->_compare, 'value' => $value]
    );
  }
}
