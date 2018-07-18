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
 * Passes if the given value is of the filter's data type, psuedotype, or class/interface.
 * Does not cooerce data types; comparison is strict.
 *
 * @example <code>
 *  <?php
 *  use at\perk\Perk;
 *
 *  $is = Perk::createFilter(Perk::IS_INT);
 *  $is->apply(1);     // 1
 *  $is->apply("1");   // null (comparison is always strict)
 *  $is->invert("1");  // "1" ("is not")
 *
 *  $is = Perk::createFilter(Perk::IS_ITERABLE);
 *  $is->apply([]);                 // []
 *  $is->apply(new ArrayIterator);  // ArrayIterator instance
 *  $is->apply("hello");            // null
 *  $is->invert("hello");           // "hello" ("is not")
 *
 *  class Foo {}
 *  class Fooling extends Foo {}
 *  class Bar {}
 *
 *  $is = Perk::createFilter([Perk::IS, Foo::class]);
 *  $is->apply(new Foo);      // Foo instance
 *  $is->apply(new Fooling);  // Fooling instance (is a Foo instance)
 *  $is->apply(new Bar);      // null
 *  $is->invert(new Bar);     // Bar instance ("is not")
 * </code>
 */
class Is extends Filter {

  /** @type string  type/pseudotype/classname to compare against. */
  protected $_type;

  /**
   * @param mixed $type  type/pseudotype/class to compare against
   */
  public function __construct($type) {
    if (! is_string($type)) {
      $type = Value::type($type);
    }

    $this->_type = $type;
  }

  /**
   * {@inheritDoc}
   */
  protected function _applyFilter($value) {
    if (Value::is($value, $this->_type)) {
      return $value;
    }

    throw new TypeException(
      TypeException::IS_NOT,
      ['compare' => $this->_type, 'value' => $value]
    );
  }

  /**
   * {@inheritDoc}
   */
  protected function _getInvertException($value) : FilterException {
    return new TypeException(
      TypeException::IS,
      ['compare' => $this->_type, 'value' => $value]
    );
  }
}
