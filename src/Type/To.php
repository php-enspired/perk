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
  Filter\Type\TypeException
};

use at\util\Value;

/**
 * Passes if the given value can be converted to the filter's data type.
 *
 * This is only possible between certain data types.
 * Invalid conversions can be forced, but be aware this can often result in meaningless data.
 * Inverting (negating) a conversion makes no sense, and so will always throw.
 *
 * @example <code>
 *  <?php
 *  use at\perk\Perk;
 *
 *  $to = Perk::createFilter(Perk::TO_ARRAY);
 *  $to->apply([]);                 // []
 *  $to->apply(new ArrayIterator);  // []
 *  $to->apply("hello");            // null
 *  $to->invert("hello");           // throws TypeException::INVERT_DISALLOWED
 *
 *  $force = Perk::createFilter(Perk::FORCE_ARRAY);
 *  $force->apply("hello");  // ["hello"]
 *
 *  $to = Perk::createFilter(Perk::TO_BOOL);
 *  $to->apply(true);   // true
 *  $to->apply("yes");  // true
 *  $to->apply(1);      // true
 *  $to->apply("no");   // false
 *  $to->apply(0);      // false
 *  $to->apply(42)      // null
 *  $to->invert("42");  // throws TypeException::INVERT_DISALLOWED
 *
 *  $force = Perk::createFilter(Perk::FORCE_BOOL);
 *  $to->apply(42)  // false
 *
 *  $to = Perk::createFilter(Perk::TO_FLOAT);
 *  $to->apply(3.5);            // 3.5
 *  $to->apply("3.5");          // 3.5
 *  $to->apply("3.5 wishes")    // null
 *  $to->invert("3.5 wishes");  // throws TypeException::INVERT_DISALLOWED
 *
 *  $force = Perk::createFilter(Perk::FORCE_INT);
 *  $force->apply("3.5 wishes");               // 3.5
 *  $force->apply("three and a half wishes");  // 0.0
 *
 *  $to = Perk::createFilter(Perk::TO_INT);
 *  $to->apply(3);            // 3
 *  $to->apply("3");          // 3
 *  $to->apply("3 wishes")    // null
 *  $to->invert("3 wishes");  // throws TypeException::INVERT_DISALLOWED
 *
 *  $force = Perk::createFilter(Perk::FORCE_INT);
 *  $force->apply("3 wishes");      // 3
 *  $force->apply("three wishes");  // 0
 *
 *  $to = Perk::createFilter(Perk::TO_STRING);
 *  $to->apply(3);      // "3"
 *  $to->apply(3.5);    // "3.5"
 *  $to->apply(false);  // "false"
 *  $to->apply([]);     // null
 *  $to->invert([]);    // throws TypeException::INVERT_DISALLOWED
 *
 *  $force = Perk::createFilter(Perk::FORCE_STRING);
 *  $force->apply([]);  // ""
 * </code>
 */
class To extends Filter {

  /** @type string[]  supported type:conversion method map. */
  protected const _TO = [
    Value::ARRAY => '_toArray',
    Value::BOOL => '_toBoolean',
    Value::FLOAT => '_toFloat',
    Value::INT => '_toInteger',
    Value::STRING => '_toString'
  ];

  /** @type string  type/pseudotype/classname to compare against. */
  protected $_type;

  /** @type bool  force conversion? */
  protected $_force = false;

  /**
   * @param mixed $type   type/pseudotype/class to convert to
   * @param bool  $force  force conversion?
   */
  public function __construct($type, bool $force = false) {
    if (! isset(self::_TO[$type])) {
      throw new TypeException(
        TypeException::UNSUPPORTED_CONVERSION,
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

    $filtered = $this->{self::_TO[$this->_type]}($value);
    if ($filtered !== null) {
      return $filtered;
    }

    throw new TypeException(
      TypeException::INCONVERTABLE,
      ['from' => Value::type($value), 'to' => $this->_type, 'force' => $this->_force]
    );
  }

  /**
   * {@inheritDoc}
   * Inverting makes no sense.
   */
  public function invert($value, bool $throw = false) : FilterException {
    if ($throw) {
      return new TypeException(TypeException::INVERT_DISALLOWED);
    }

    return null;
  }

  /**
   * Converts value to array.
   *
   * @param mixed $value      the value to convert
   * @return array            on success
   * @throws TypeException  on failure
   */
  protected function _toArray($value) : array {
    if (Value::is($value, Value::ITERABLE)) {
      return iterator_to_array($value);
    }

    return $this->_force ? [$value] : null;
  }

  /**
   * Converts value to boolean.
   *
   * @param mixed $value      the value to convert
   * @return bool             on success
   * @throws TypeException  on failure
   */
  protected function _toBoolean($value) : bool {
    return filter_var(
      $value,
      FILTER_VALIDATE_BOOLEAN,
      $this->_force ? 0 : FILTER_NULL_ON_FAILURE
    );
  }

  /**
   * Converts value to float.
   *
   * @param mixed $value      the value to convert
   * @return float            on success
   * @throws TypeException  on failure
   */
  protected function _toFloat($value) : float {
    return filter_var(
      $this->_force ? filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT) : $value,
      FILTER_VALIDATE_FLOAT,
      FILTER_NULL_ON_FAILURE
    ) ?? ($this->_force ? 0.0 : null);
  }

  /**
   * Converts value to integer.
   *
   * @param mixed $value      the value to convert
   * @return int              on success
   * @throws TypeException  on failure
   */
  protected function _toInteger($value) : int {
    return filter_var(
      $this->_force ? filter_var($value, FILTER_SANITIZE_NUMBER_INT) : $value,
      FILTER_VALIDATE_INT,
      FILTER_NULL_ON_FAILURE
    ) ?? ($this->_force ? 0 : null);
  }

  /**
   * Converts value to string.
   *
   * @param mixed $value      the value to convert
   * @return string           on success
   * @throws TypeException  on failure
   */
  protected function _toString($value) : string {
    $type = Value::type($value);
    switch ($type) {
      case Value::BOOL:
        return $value ? 'true' : 'false';
      case Value::FLOAT:
      case Value::INT:
        return "{$value}";
      default:
        if (method_exists($value, '__toString')) {
          return $value->__toString();
        }
        return $this->_force ? '' : null;
    }
  }
}
