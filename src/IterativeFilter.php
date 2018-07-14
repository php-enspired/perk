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
  FilterException
};

/**
 * Iterative (multi-step) filter composed at runtime (i.e., from other filters or callables).
 */
class IterativeFilter extends Filter {

  /** @type array[]  list of filter definitions. */
  protected $_filters = [];

  /**
   * @param mixed ...$filters  filter definitions
   * @throws FilterException  on failure
   */
  public function __construct(...$filters) {
    try {
      foreach ($filters as $filter) {
        if (! is_array($filter) || is_callable($filter)) {
          $filter = [$filter];
        }
        $this->_addFilter($filter);
      }
    } catch (Throwable $e) {
      throw new FilterException(
        FilterException::INVALID_FILTER_DEFINITION,
        $e,
        ['fqcn' => static::class, 'definition' => $filters]
      );
    }
  }

  /**
   * Adds a filter to the definition.
   *
   * @param array $filter  definition for the filter to add
   */
  protected function _addFilter(array $filter) {
    if (is_a(reset($filter), Filterable::class, true)) {
      $this->_filters[] = Perk::createFilter(...$filter);
      return;
    }

    if (is_callable(reset($filter)) || is_int(reset($filter))) {
      $this->_filters[] = $filter;
      return;
    }

    throw new FilterException(
      FilterException::INVALID_FILTER_DEFINITION,
      ['fqcn' => static::class, 'definition' => $filter]
    );
  }

  /**
   * {@inheritDoc}
   */
  protected function _applyFilter($value) {
    $initialValue = $value;

    foreach ($this->_getFilters() as $i => $params) {
      $filter = array_shift($params);

      if ($filter instanceof Filterable) {
        $value = $filter->apply($value, true);
      } elseif (is_int($filter)) {
        $value = filter_var(
          $value,
          $filter,
          ['options' => $params, 'flags' => FILTER_NULL_ON_FAILURE]
        );
      } elseif (is_callable($filter)) {
        $value = $filter($value, ...$params);
      }

      if ($value === null) {
        throw new FilterException(
          FilterException::VALUE_FAILED_FILTER,
          ['value' => $initialValue, 'filter' => $i]
        );
      }
    }

    return $value;
  }

  /**
   * Gets the filter definition.
   *
   * @return array[]  list of filter definitions
   */
  protected function _getFilters() : array {
    return $this->_filters;
  }
}
