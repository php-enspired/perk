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

namespace at\perk\Filters;

use at\perk\ {
  Filter,
  Filterable,
  FilterException
};

/**
 * Composite filters which can be mapped (by name) to a collection of values.
 */
class FilterMap extends Filter {

  /**
   * @param array $filterMap  map of name:filter definition pairs.
   *  @see Filter::__construct $filters
   */
  public function __construct(array $filterMap) {
    try {
      foreach ($filterMap as $name => $filters) {
        $this->_addFilters($name, is_array($filters) ? $filters : [$filters]);
      }
    } catch (Throwable $e) {
      throw new FilterException(
        FilterException::INVALID_FILTERMAP_DEFINITION,
        $e,
        ['name' => $name, 'definition' => $filters]
      );
    }
  }

  /**
   * Adds a name:filters pair to the map definition.
   *
   * @param string $name     filter name
   * @param array  $filters  filter definition(s) to add
   */
  protected function _addFilters(string $name, array $filters) {
    foreach ($filters as $filter) {
      if ($filter instanceof Filterable) {
        $this->_filters[$name][] = $filter;
        continue;
      }

      if (! is_array($filter) || is_callable($filter)) {
        $filter = [$filter];
      }
      $this->_filters[$name][] = Perk::createFilter(...$filter);
    }
  }

  /**
   * {@inheritDoc}
   * Maps each of a collection of values to a corresponding filter.
   *
   * The value given is expected to be an array,
   * with keys that match the names of this instance's filters.
   */
  public function apply($values, ...$arguments) : array {
    if (! is_iterable($values)) {
      $values = [$values];
    }

    $filtered = [];
    foreach ($values as $key => $value) {
      if (! isset($filters[$key])) {
        throw new FilterException(FilterException::NO_MATCHING_FILTER, ['key' => $key]);
      }
      if (! $filters[$key] instanceof Filterable) {
        throw new FilterException(
          FilterException::INVALID_FILTER,
          ['filter' => $filter, 'key' => $key]
        );
      }

      $filtered[$key] = $filters[$key]->apply($value);
    }

    return $filtered;
  }
}
