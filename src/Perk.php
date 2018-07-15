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

namespace at\perk;

use at\perk\ {
  Compare\Between,
  Compare\Equal,
  Compare\Greater,
  Compare\Less,
  Compare\OneOf,

  Filterable,
  FilterException,
  FilterMap,
  IterativeFilter,

  Logic\All,
  Logic\AllIf,
  Logic\AllUnless,
  Logic\Always,
  Logic\Any,
  Logic\AtLeast,
  Logic\AtMost,
  Logic\Never,
  Logic\None,
  Logic\One,

  Number\BaseConvert,
  Number\Modulo,
  Number\Serial,

  Text\Ascii,
  Text\Email,
  Text\Length,
  Text\ByteLength,
  Text\Match,
  Text\Printable,
  Text\Url,
  Text\Utf8,

  //Time\After,
  //Time\Around,
  //Time\At,
  //Time\Before,
  //Time\During,

  Type\Is,
  Type\To
};

use at\util\Value;

/**
 * Convenience class for accessing included filters and building custom filters.
 */
class Perk {

  /**
   * Aliases for comparison filters.
   *
   * @type string BETWEEN  passes if value is between min and max.
   * @type string EQUAL    passes if value is equal to test value.
   * @type string GREATER  passes if value is greater than test value.
   * @type string LESS     passes if value is less than test value.
   * @type string ONE_OF   passes if value is equal to one of a set of test values.
   */
  public const BETWEEN = Between::class;
  public const EQUAL = Equal::class;
  public const GREATER = Greater::class;
  public const LESS = Less::class;
  public const ONE_OF = OneOf::class;

  /**
   * Aliases for logical filter constructs.
   *
   * @type string ALL       passes if all filters pass.
   * @type string ALWAYS    always passes.
   * @type string ANY       passes if any filter passes.
   * @type string AT_LEAST  passes if at least N filters pass.
   * @type string AT_MOST   passes if at most N filters pass.
   * @type string EXACTLY   passes if exactly N filters pass.
   * @type string IF        passes if the first filter fails, or if all other filters pass.
   * @type string NEVER     always fails.
   * @type string NONE      passes if all filters fail.
   * @type string NOT       alias of NONE.
   * @type string ONE       passes if exactly one filter passes.
   * @type string UNLESS    passes if the first filter passes, or if all other filters pass.
   */
  public const ALL = All::class;
  public const ALWAYS = Always::class;
  public const ANY = Any::class;
  public const AT_LEAST = AtLeast::class;
  public const AT_MOST = AtMost::class;
  public const IF = AllIf::class;
  public const NEVER = Never::class;
  public const NONE = None::class;
  public const NOT = None::class;
  public const ONE = One::class;
  public const UNLESS = AllUnless::class;

  /**
   * Aliases for php datatype/class/interface filters.
   *
   * @type array FORCE_ARRAY   always passes; converts value to array.
   * @type array FORCE_BOOL    always passes; converts value to boolean.
   * @type array FORCE_FLOAT   always passes; converts value to float.
   * @type array FORCE_INT     always passes; converts value to integer.
   * @type array FORCE_STRING  always passes; converts value to string.
   *
   * @type string IS  passes if value is of given class, type, or psuedotype.
   *
   * @type array IS_ARRAY         passes if value is an array.
   * @type array IS_BOOL          passes if value is a boolean.
   * @type array IS_CALLABLE      passes if value is callable.
   * @type array IS_COUNTABLE     passes if value is countable.
   * @type array IS_DATETIMEABLE  pass if value can be interpreted as a datetime value.
   * @type array IS_FLOAT         passes if value is a float.
   * @type array IS_INT           passes if value is an integer.
   * @type array IS_ITERABLE      passes if value is iterable.
   * @type array IS_JSONABLE      passes if value can be safely encoded as json.
   * @type array IS_NULL          passes if value is null.
   * @type array IS_OBJECT        passes if value is an object.
   * @type array IS_RESOURCE      passes if value is a resource.
   * @type array IS_SCALAR        passes if value is scalar.
   * @type array IS_STRING        passes if value is a string.
   *
   * @type array TO_ARRAY     passes if value can be converted to array.
   * @type array TO_BOOL      passes if value can be converted to boolean.
   * @type array TO_DATETIME  passes if value can be converted to DateTime.
   * @type array TO_FLOAT     passes if value can be converted to float.
   * @type array TO_INT       passes if value can be converted to integer.
   * @type array TO_STRING    passes if value can be converted to string.
   */
  public const FORCE_ARRAY = [To::class, Value::ARRAY, true];
  public const FORCE_BOOL = [To::class, Value::BOOL, true];
  public const FORCE_FLOAT = [To::class, Value::FLOAT, true];
  public const FORCE_INT = [To::class, Value::INT, true];
  public const FORCE_STRING = [To::class, Value::STRING, true];

  public const IS = Is::class;

  public const IS_ARRAY = [Is::class, Value::ARRAY];
  public const IS_BOOL = [Is::class, Value::BOOL];
  public const IS_CALLABLE = [Is::class, Value::CALLABLE];
  public const IS_COUNTABLE = [Is::class, Value::COUNTABLE];
  public const IS_DATETIMEABLE = [Is::class, Value::DATETIMEABLE];
  public const IS_FLOAT = [Is::class, Value::FLOAT];
  public const IS_INT = [Is::class, Value::INT];
  public const IS_ITERABLE = [Is::class, Value::ITERABLE];
  public const IS_JSONABLE = [Is::class, Value::JSONABLE];
  public const IS_NULL = [Is::class, Value::NULL];
  public const IS_OBJECT = [Is::class, Value::OBJECT];
  public const IS_RESOURCE = [Is::class, Value::RESOURCE];
  public const IS_SCALAR = [Is::class, Value::SCALAR];
  public const IS_STRING = [Is::class, Value::STRING];

  public const TO_ARRAY = [To::class, Value::ARRAY];
  public const TO_BOOL = [To::class, Value::BOOL];
  public const TO_FLOAT = [To::class, Value::FLOAT];
  public const TO_INT = [To::class, Value::INT];
  public const TO_STRING = [To::class, Value::STRING];

  /**
   * Aliases for text filters.
   *
   * @type string MATCH  passes if value matches given pcre.
   * @type string UTF8   passes if value can be converted+normalized as utf-8.
   */
  public const JSON = '';
  public const MATCH = Match::class;
  public const UTF8 = Utf8::class;


  /**
   * Builds a filter instance from provided filters.
   *
   * Filter definitions are structured as follows:
   *  - [filter [, ...arguments]]
   *  - filter
   *  where <filter> is one of:
   *  - Filterable instance|classname
   *  - callable ($value [, ...$arguments]) : ?mixed
   *  - string classname|type|pseudotype
   *  - int FILTER_* constant
   *
   * @param mixed $filter     filter definitions
   * @return Filterable       on success
   * @throws FilterException  on failure
   */
  public static function createFilter($filter) : Filterable {
    if (! is_array($filter) || is_callable($filter)) {
      $filter = [$filter];
    }

    $fqcn = is_a(reset($filter), Filterable::class, true) ?
      array_shift($filter) :
      IterativeFilter::class;

    try {
      return new $fqcn(...$filter);
    } catch (FilterException $e) {
      throw $e;
    } catch (Throwable $e) {
      throw new FilterException(
        FilterException::INVALID_FILTER_DEFINITION,
        $e,
        ['filter' => $fqcn, 'definition' => $filter]
      );
    }
  }

  /**
   * Builds a filter map instance from provided filters.
   *
   * @param mixed[] $filters  map of key:filter definition list pairs
   * @return FilterMap        on success
   * @throws FilterException  on failure
   */
  public static function createFilterMap(array $filters) : FilterMap {
    return new FilterMap(...$filters);
  }

  /**
   * Creates and applies a filter.
   *
   * @param mixed $value      value to filter
   * @param mixed $filter     filter definition
   * @param bool  $throw      throw on failure?
   * @return mixed            filtered value
   * @throws FilterException  on failure
   */
  public static function filter($value, $filter, bool $throw = false) {
    return self::createFilter($filter)->apply($value, $throw);
  }

  /**
   * Creates a filter and applies it to each of a list of values.
   *
   * @param mixed[] $values   list of values to filter
   * @param mixed   $filter   filter definition
   * @param bool    $throw    throw on failure?
   * @return mixed[]          filtered values
   * @throws FilterException  on failure
   */
  public static function filterEach(array $values, $filter, bool $throw = false) : array {
    return self::createFilter($filter)->each($values, $throw);
  }

  /**
   * Creates a filter map and applies it to given values.
   *
   * @param mixed[] $values   map of values to filter
   * @param mixed[] $filters  map of filter definition
   * @param bool    $throw    throw on failure?
   * @return mixed[]          filtered values
   * @throws FilterException  on failure
   */
  public static function filterMap(array $values, array $filters, bool $throw = false) : array {
    return self::createFilterMap($filters)->apply($values, $throw);
  }

  /**
   * Creates and applies a filter, negating the result.
   *
   * @param mixed $value      value to filter
   * @param mixed $filter     filter definition
   * @param bool  $throw      throw on failure?
   * @return mixed            filtered value
   * @throws FilterException  on failure
   */
  public static function not($value, $filter, bool $throw = false) {
    return self::createFilter($filter)->invert($value, $throw);
  }

  /**
   * Creates a filter and applies it to each of a list of values, negating the result.
   *
   * @param mixed[] $values   list of values to filter
   * @param mixed   $filter   filter definition
   * @param bool    $throw    throw on failure?
   * @return mixed[]          filtered values
   * @throws FilterException  on failure
   */
  public static function notEach(array $values, $filter, bool $throw = false) : array {
    return self::createFilter($filter)->invertEach($values, $throw);
  }
}
