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
 * Passes if the given value compares as equal to one of the filter's values.
 * Does not cooerce data types.
 *
 * Comparison is loose (==) by default; pass $strict = true to make comparison strict (===).
 */
class OneOf extends Filter {

  /** @type array  value to compare against. */
  protected $_compare = [];

  /** @type bool  should comparison be strict? */
  protected $_strict = false;

  /**
   * @param array $compare  values to compare against
   * @param bool  $strict   should comparison be strict?
   */
  public function __construct(array $compare, bool $strict = false) {
    $this->_compare = $compare;
    $this->_strict = $strict;
  }

  /**
   * {@inheritDoc}
   */
  protected function _applyFilter($value) {
    if (is_iterable($value)) {
      foreach ($this->_compare as $compare) {
        if ($this->_strict && $compare === $value) {
          return $value;
        }

        if (! $this->_strict && $compare == $value) {
          return $value;
        }
      }
    }

    throw new ComparisonException(
      ComparisonException::NOT_ONE_OF,
      ['compare' => $this->_compare, 'value' => $value, 'strict' => $this->_strict]
    );
  }

  /**
   * {@inheritDoc}
   */
  protected function _getInvertException($value) : FilterException {
    return new ComparisonException(
      ComparisonException::IS_ONE_OF,
      ['compare' => $this->_compare, 'value' => $value, 'strict' => $this->_strict]
    );
  }
}
