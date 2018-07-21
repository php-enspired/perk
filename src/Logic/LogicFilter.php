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

namespace at\perk\Logic;

use at\perk\ {
  Filter,
  FilterException
};

/**
 * Logical (multi-step) filter composed from other filters at runtime.
 */
abstract class LogicFilter extends Filter {

  /** @type Filterable[]  list of filters to apply. */
  protected $_filters = [];

  /**
   * @param mixed ...$filters  filter definitions
   * @throws FilterException  on failure
   */
  public function __construct(...$filters) {
    foreach ($filters as $filter) {
      $this->_filters[] = $this->_parseFilter($filter);
    }
  }

  /**
   * Adds a filter to the definition.
   *
   * @param mixed $filter     definition for the filter to add
   * @throws FilterException  if definition cannot be parsed
   * @return Filterable       parsed filter instance
   */
  protected function _parseFilter($filter) : Filterable {
    if ($filter instanceof Filterable) {
      return $filter;
    }

    if (! is_array($filter) || is_callable($filter)) {
      $filter = [$filter];
    }

    if (is_a(reset($filter), Filterable::class, true)) {
      return Perk::createFilter($filter);
    }

    if (is_callable(reset($filter))) {
      return new CallableFilter(...$filter);
    }

    if (is_int(reset($filter))) {
      return new FilterVar(...$filter);
    }

    throw new FilterException(
      FilterException::INVALID_FILTER_DEFINITION,
      ['fqcn' => static::class, 'definition' => $filter]
    );
  }
}
