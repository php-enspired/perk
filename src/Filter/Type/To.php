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

namespace at\perk\Type;

use Countable,
  DateTimeInterface,
  JsonSerializable,
  stdClass;

use at\perk\ {
  Filter,
  FilterException,
  Filter\Compare\ComparisonException
};

use at\util\Value;

/**
 * Passes if the given value can be converted to the filter's data type.
 *
 * This is only possible between certain data types.
 * Invalid conversions can be forced, but be aware this can often result in meaningless data.
 * Inverting (negating) conversion makes no sense, so will always throw.
 *  - arrays:
 *    - convert to: -
 *    - force to: -
 *  - booleans:
 *    - convert to: -
 *    - force to: array [bool], int 1|0, string "true"|"false"
 *  - floats:
 *    - convert to: int, string
 *    - force to: array [float], bool (true if nonzero), int (truncated)
 *  - integers:
 *    - convert to: float, string
 *    - force to: array [int], bool (true if nonzero)
 *  - strings:
 *    - convert to: bool, float, int
 *    - force to:
 *      array [string], bool, float, int
 *
 * @example <code>
 *  <?php
 *  use at\perk\Perk;
 *
 *  $to = Perk::createFilter(Perk::TO_INT);
 *  $to->apply(3);            // 3
 *  $to->apply("3");          // 3
 *  $to->apply("3 wishes")    // null
 *  $to->invert("3 wishes");  // throws FilterException::NO_INVERSION
 *
 *  $force = Perk::createFilter(Perk::FORCE_INT);
 *  $force->apply("3 wishes");  // 0
 *
 *  $to = Perk::createFilter(Perk::TO_ARRAY);
 *  $to->apply([]);                 // []
 *  $to->apply(new ArrayIterator);  // []
 *  $to->apply("hello");            // null
 *  $to->invert("hello");           // throws FilterException::NO_INVERSION
 *
 *  $force = Perk::createFilter(Perk::FORCE_ARRAY);
 *  $force->apply("hello");  // ["hello"]
 * </code>
 */
class To extends Filter {

  /** @type string  type/pseudotype/classname to compare against. */
  protected $_type;

  /** @type bool  force conversion? */
  protected $_force = false;

  /**
   * @param mixed $type   type/pseudotype/class to convert to
   * @param bool  $force  force conversion?
   */
  public function __construct($type, bool $force = false) {
    if (! method_exists($this, "_to{$type}")) {
      throw new FilterException(
        FilterException::UNSUPPORTED_CONVERSION,
        ['type' => $type]
      );
    }

    $this->_type = $type;
    $this->_force = $force;
  }

  /**
   * {@inheritDoc}
   */
  protected function _applyFilter($value) {
    if (Value::is($value, $this->_type)) {
      return $value;
    }

    return $this->{"_to{$type}"}($value);
  }

  /**
   * {@inheritDoc}
   * Inverting makes no sense.
   */
  public function invert($value, bool $throw = false) : FilterException {
    if ($throw) {
      return new FilterException(FilterException::NO_INVERSION);
    }

    return null;
  }

  /**
   * Converts value to array.
   *
   * @param mixed $value      the value to convert
   * @return array            on success
   * @throws FilterException  on failure
   */
  protected function _toArray($value) : array {
    if (Value::is($value, Value::ITERABLE)) {
      return iterator_to_array($value);
    }

    if ($this->_force) {
      return [$value];
    }

    throw new FilterException(
      FilterException::INCONVERTABLE,
      ['from' => Value::type($value), 'to' => $this->_type, 'force' => $this->_force]
    );
  }

  /**
   * Converts value to boolean.
   *
   * @param mixed $value      the value to convert
   * @return bool             on success
   * @throws FilterException  on failure
   */
  protected function _toBoolean($value) : bool {
    $filtered = filter_var(
      $value,
      FILTER_VALIDATE_BOOLEAN,
      $this->_force ? 0 : FILTER_NULL_ON_FAILURE
    );
    if (is_bool($filtered)) {
      return $filtered;
    }

    throw new FilterException(
      FilterException::INCONVERTABLE,
      ['from' => Value::type($value), 'to' => $this->_type, 'force' => $this->_force]
    );
  }

  /**
   * Converts value to float.
   *
   * @param mixed $value      the value to convert
   * @return float            on success
   * @throws FilterException  on failure
   */
  protected function _toFloat($value) : float {
    $filtered = $value;
    if ($this->_force) {
      $filtered = filter_var($filtered, FILTER_SANITIZE_NUMBER_FLOAT);
    }
    $filtered = filter_var($filtered, FILTER_VALIDATE_FLOAT);
    if (is_float($filtered)) {
      return $filtered;
    }

    if ($this->_force) {
      return 0.0;
    }

    throw new FilterException(
      FilterException::INCONVERTABLE,
      ['from' => Value::type($value), 'to' => $this->_type, 'force' => $this->_force]
    );
  }

  /**
   * Converts value to integer.
   *
   * @param mixed $value      the value to convert
   * @return int              on success
   * @throws FilterException  on failure
   */
  protected function _toInteger($value) : int {
    $filtered = $value;
    if ($this->_force) {
      $filtered = filter_var($filtered, FILTER_SANITIZE_NUMBER_INT);
    }
    $filtered = filter_var($filtered, FILTER_VALIDATE_INT);
    if (is_int($filtered)) {
      return $filtered;
    }

    if ($this->_force) {
      return 0;
    }

    throw new FilterException(
      FilterException::INCONVERTABLE,
      ['from' => Value::type($value), 'to' => $this->_type, 'force' => $this->_force]
    );
  }

  /**
   * Converts value to string.
   *
   * @param mixed $value      the value to convert
   * @return string           on success
   * @throws FilterException  on failure
   */
  protected function _toString($value) : string {
    $type = Value::type($value);
    switch ($type) {
      case Value::BOOL:
        return $value ? 'true' : 'false';
      case Value::FLOAT:
      case Value::INT:
        return "{$value}";
    }

    if (method_exists($value, '__toString')) {
      return $value->__toString();
    }

    throw new FilterException(
      FilterException::INCONVERTABLE,
      ['from' => Value::type($value), 'to' => $this->_type, 'force' => $this->_force]
    );
  }
}
