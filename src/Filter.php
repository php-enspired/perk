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
  FilterException
};

/**
 * Generic implementation.
 * Allows for filters which are composed at runtime (i.e., from other filters or callables).
 */
class Filter implements Filterable {

  /** @type int  option key for throwing exceptions. */
  public const OPT_THROW = 0;

  /** @type array[]  list of filter definitions. */
  protected $_filters = [];

  /** @type array  info about last filter failure. */
  protected $_lastError = [
    self::ERROR_TYPE => null,
    self::ERROR_CODE => FilterException::ERROR_NONE,
    self::ERROR_MESSAGE => '',
    self::ERROR_CONTEXT => [],
    self::ERROR_VALUE => null
  ];

  /**
   * @type array  filter options:
   *  - bool self::OPT_THROW  does this filter throw on failure?
   */
  protected $_options = [self::OPT_THROW =>  false];

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
   * {@inheritDoc}
   */
  public function apply($value, ...$arguments) {
    try {
      foreach ($this->_getFilters() as $params) {
        $filter = array_shift($params);

        if (is_a($filter, Filterable::class, true)) {
          $value = [$filter, 'apply']($value, ...$params);
          continue;
        }
        if (is_callable($filter)) {
          $value = self::callback($value, $filter, ...$params);
          continue;
        }
        if (is_int($filter)) {
          $value = self::filter($value, $filter, ...$params);
          continue;
        }
      }

      return $value;
    } catch (FilterException $e) {
      $this->_setErrorInfo($value, $e);

      if ($this->_throw) {
        throw $e;
      }
      return null;
    }
  }

  /**
   * {@inheritDoc}
   */
  public function each(array $values, ...$arguments) : array {
    return array_filter(
      array_map([$this, 'apply'], $values),
      function ($value) { return $value !== null; }
    );
  }

  /**
   * {@inheritDoc}
   */
  public function errorInfo() : array {
    return $this->_lastError;
  }

  /**
   * Gets the filter's options.
   *
   * @return array  filter options
   */
  public function getOptions() : array {
    return $this->_options;
  }

  /**
   * Sets filter options for this instance.
   *
   * @param string $option    option name
   * @param mixed  $value     option value
   * @throws FilterException  if option does not exist, or if value is not valid
   * @return Filterable       $this
   */
  public function setOption(string $option, $value) : Filterable {
    switch ($option) {
      case self::OPT_THROW:
        if (! is_bool($value)) {
          throw new FilterException(
            FilterException::INVALID_OPTION_VALUE,
            ['option' => $option, 'value' => $value]
          );
        }
        $this->_options[self::OPT_THROW] = $value;
        break;
      default:
        throw new FilterException(FilterException::NO_SUCH_OPTION, ['option' => $option]);
    }

    return $this;
  }

  /**
   * Adds a filter to the definition.
   *
   * @param array $filter  definition for the filter to add
   */
  protected function _addFilter(array $filter) {
    if (is_a(reset($filter), Filterable::class, true)) {
      $filter = Perk::createFilter(...$filter);
    }

    $this->_filters[] = $filter;
  }

  /**
   * Gets the filter definition.
   *
   * @return array[]  list of filter definitions
   */
  protected function _getFilters() : array {
    return $this->_filters;
  }

  /**
   * Sets error info from a filter exception.
   *
   * @param mixed           $value  value that failed the filter
   * @param FilterException $e      exception instance
   */
  protected function _setErrorInfo($value, FilterException $e) {
    $this->_lastError = [
      self::ERROR_TYPE => get_class($e),
      self::ERROR_CODE => $e->getCode(),
      self::ERROR_MESSAGE => $e->getMessage(),
      self::ERROR_CONTEXT => $e->getContext(),
      self::ERROR_VALUE => $value
    ];
  }
}
