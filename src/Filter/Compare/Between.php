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
 * Passes if the given value compares as between minimum and maximum values.
 * Does not check or cooerce data types.
 *
 * Comparison is inclusive (min <= value <= max) by default;
 * pass $inclusive = false to make comparison exclusive (min < value < max).
 */
class Between extends Filter {

  /** @type bool  should comparison be inclusive? */
  protected $_inclusive = true;

  /** @type mixed  minimum value. */
  protected $_min;

  /** @type mixed  maximum value. */
  protected $_max;

  /**
   * @param mixed $min        minimum value
   * @param mixed $max        maximum value
   * @param bool  $inclusive  should comparison be inclusive?
   */
  public function __construct($min, $max, bool $inclusive = true) {
    $this->_min = $min;
    $this->_max = $max;
    $this->_inclusive = $inclusive;
  }

  /**
   * {@inheritDoc}
   */
  protected function _applyFilter($value) {
    if ($this->_inclusive && $this->_min <= $value && $value <= $this->_max) {
      return $value;
    }

    if (! $this->_inclusive && $this->_min < $value && $value < $this->_max) {
      return $value;
    }

    throw new ComparisonException(
      ComparisonException::NOT_BETWEEN,
      [
        'min' => $this->_min,
        'max' => $this->_max,
        'value' => $value,
        'inclusive' => $this->_inclusive
      ]
    );
  }

  /**
   * {@inheritDoc}
   */
  protected function _getInvertException($value) : FilterException {
    return new ComparisonException(
      ComparisonException::IS_BETWEEN,
      [
        'min' => $this->_min,
        'max' => $this->_max,
        'value' => $value,
        'inclusive' => $this->_inclusive
      ]
    );
  }
}
