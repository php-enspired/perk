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
  Filterable,
  Filter,
  FilterMap,
  Filters\Text,
  Filters\Type
};

/**
 * Convenience class for accessing included filters and building custom filters.
 */
class Perk {

  /**
   * Aliases for composable filters and logical filter structures.
   *
   * @type callable ALL       passes if all rules pass.
   * @type callable ANY       passes if any rule passes.
   * @type callable AT_LEAST  passes if at least N rules pass.
   * @type callable AT_MOST   passes if at most N rules pass.
   * @type callable EXACTLY   passes if exactly N rules pass.
   * @type callable IF        passes if the first rule fails, or if all other rules pass.
   * @type callable NONE      passes if all rules fail.
   * @type callable ONE       passes if exactly one rule passes.
   * @type callable UNLESS    passes if the first rule passes, or if all other rules pass.
   */
  const ALL = [Composable::class, 'all'];
  const ANY = [Composable::class, 'any'];
  const AT_LEAST = [Composable::class, 'atLeast'];
  const AT_MOST = [Composable::class, 'atMost'];
  const IF = [Composable::class, 'if'];
  const NONE = [Composable::class, 'none'];
  const ONE = [Composable::class, 'one'];
  const UNLESS = [Composable::class, 'unless'];

  /**
   * Aliases for php datatype/class/interface filters.
   * @see Type
   *
   * @type callable ARRAY   passes if value can be converted to array.
   * @type int      BOOL    passes if value can be converted to boolean.
   * @type int      FLOAT   passes if value can be converted to float.
   * @type int      INT     passes if value can be converted to integer.
   * @type callable IS      passes if value is of given class, type, or psuedotype.
   * @type int      STRING  passes if value can be converted to string.
   */
  public const ARRAY = [Type::class, 'array'];
  public const BOOL = FILTER_VALIDATE_BOOLEAN;
  public const FLOAT = FILTER_VALIDATE_FLOAT;
  public const INT = FILTER_VALIDATE_INT;
  public const IS = [Type::class, 'is'];
  public const STRING = FILTER_UNSAFE_RAW;

  /**
   * Aliases for text filters.
   * @see Text
   *
   * MATCH  passes if value matches given pcre.
   * UTF8   passes if value can be converted+normalized as utf-8.
   */
  public const MATCH = [Text::class, 'match'];
  public const UTF8 = [Text::class, 'normalizeUtf8'];

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
   * @param mixed ...$filters  filter definitions
   * @retun Filter             on success
   * @throws FilterException   on failure
   */
  public static function createFilter(...$filters) : Filterable {
    $fqcn = is_a(reset($filters), Filterable::class, true) ?
      array_shift($filters) :
      Filter::class;

    try {
      return new $fqcn(...$filters);
    } catch (FilterException $e) {
      throw $e;
    } catch (Throwable $e) {
      throw new FilterException(
        FilterException::INVALID_FILTER_DEFINITION,
        $e,
        ['filter' => $fqcn, 'definition' => $filters]
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
   * @param mixed $value         vlaue to filter
   * @param mixed $filter        filter definition
   * @param mixed ...$arguments  filter args
   * @return mixed               filtered value
   */
  public static function filter($value, $filter, ...$arguments) {
    return self::createFilter($filter)->apply($value, ...$arguments);
  }

  /**
   * Creates a filter map and applies it to given values.
   *
   * @param mixed[] $values      map of values to filter
   * @param mixed[] $filters     map of filter definition
   * @param mixed ...$arguments  filter args
   * @return mixed[]             filtered values
   */
  public static function filterMap(array $values, array $filters, ...$arguments) : array {
    return self::createFilterMap($filters)->apply($values, ...$arguments);
  }
}
