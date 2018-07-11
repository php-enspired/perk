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
  Filter,
  Filterable,
  FilterException,
  FilterMap,

  Filter\Compare\Between,
  Filter\Compare\Equal,
  Filter\Compare\From,
  Filter\Compare\Greater,
  Filter\Compare\Less,
  Filter\Compare\Within,

  Filter\Logic\All,
  Filter\Logic\AllIf,
  Filter\Logic\AllUnless,
  Filter\Logic\Always,
  Filter\Logic\Any,
  Filter\Logic\AtLeast,
  Filter\Logic\AtMost,
  Filter\Logic\Never,
  Filter\Logic\None,
  Filter\Logic\Not,
  Filter\Logic\One,

  Filter\Number\FromBase,
  Filter\Number\Modulo,
  Filter\Number\Serial,
  Filter\Number\ToBase,

  Filter\Text\Ascii,
  Filter\Text\Email,
  Filter\Text\Length,
  Filter\Text\ByteLength,
  Filter\Text\Match,
  Filter\Text\Printable,
  Filter\Text\Url,
  Filter\Text\Utf8,

  Filter\Time\After,
  Filter\Time\Around,
  Filter\Time\At,
  Filter\Time\Before,
  Filter\Time\During,

  Filter\Type\Is,
  Filter\Type\ToArray,
  Filter\Type\ToBool,
  Filter\Type\ToDateTime,
  Filter\Type\ToFloat,
  Filter\Type\ToInt,
  Filter\Type\ToString
};

/**
 * Convenience class for accessing included filters and building custom filters.
 */
class Perk {

  /**
   * Aliases for logical filter structures.
   *
   * @type string ALL       passes if all rules pass.
   * @type string ALLWAYS   always passes.
   * @type string ANY       passes if any rule passes.
   * @type string AT_LEAST  passes if at least N rules pass.
   * @type string AT_MOST   passes if at most N rules pass.
   * @type string EXACTLY   passes if exactly N rules pass.
   * @type string IF        passes if the first rule fails, or if all other rules pass.
   * @type string NEVER     always fails.
   * @type string NONE      passes if all rules fail.
   * @type string NOT       alias of NONE.
   * @type string ONE       passes if exactly one rule passes.
   * @type string UNLESS    passes if the first rule passes, or if all other rules pass.
   */
  const ALL = All::class;
  const ALLWAYS = Allways::class;
  const ANY = Any::class;
  const AT_LEAST = AtLeast::class;
  const AT_MOST = AtMost::class;
  const IF = AllIf::class;
  const NEVER = Never::class;
  const NONE = None::class;
  const NOT = None::class;
  const ONE = One::class;
  const UNLESS = AllUnless::class;

  /**
   * Aliases for php datatype/class/interface filters.
   *
   * @type string ARRAY     passes if value can be converted to array.
   * @type string BOOL      passes if value can be converted to boolean.
   * @type string FLOAT     passes if value can be converted to float.
   * @type string INT       passes if value can be converted to integer.
   * @type string IS        passes if value is of given class, type, or psuedotype.
   * @type string STRING    passes if value can be converted to string.
   */
  public const ARRAY = ToArray::class;
  public const BOOL = ToBool::class;
  public const FLOAT = ToFloat::class;
  public const INT = ToInt::class;
  public const IS = Is::class;
  public const STRING = ToString::class;

  /**
   * Aliases for text filters.
   *
   * @type string MATCH  passes if value matches given pcre.
   * @type string UTF8   passes if value can be converted+normalized as utf-8.
   */
  public const MATCH = Match::class;
  public const UTF8 = Utf8::class;

  /**
   * Aliases for number filters.
   * @see Number
   */

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
   * @retun Filter            on success
   * @throws FilterException  on failure
   */
  public static function createFilter($filter) : Filterable {
    if (! is_array($filter) || is_callable($filter)) {
      $filter = [$filter];
    }

    $fqcn = is_a(reset($filter), Filterable::class, true) ?
      array_shift($filter) :
      Filter::class;

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
   * @param mixed $value         value to filter
   * @param mixed $filter        filter definition
   * @param mixed ...$arguments  filter args
   * @return mixed               filtered value
   * @throws FilterException     on failure
   */
  public static function filter($value, $filter, ...$arguments) {
    return self::createFilter($filter)->apply($value, ...$arguments);
  }

  /**
   * Creates a filter and applies it to each of a list of values.
   *
   * @param mixed[] $values        list of values to filter
   * @param mixed   $filter        filter definition
   * @param mixed   ...$arguments  filter args
   * @return mixed[]               filtered values
   * @throws FilterException       on failure
   */
  public static function filterEach(array $values, $filter, ...$arguments) : array {
    return self::createFilter($filter)->each($values, ...$arguments);
  }

  /**
   * Creates a filter map and applies it to given values.
   *
   * @param mixed[] $values      map of values to filter
   * @param mixed[] $filters     map of filter definition
   * @param mixed ...$arguments  filter args
   * @return mixed[]             filtered values
   * @throws FilterException     on failure
   */
  public static function filterMap(array $values, array $filters, ...$arguments) : array {
    return self::createFilterMap($filters)->apply($values, ...$arguments);
  }
}
