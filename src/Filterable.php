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

/**
 * Filterables accept arbitrary values and validate them,
 * optionally performing normalizations in the process.
 *
 * A filter must return the validated+normalized value, or null if the filter fails.
 * If so configured, a filter throws a FilterException on failure instead.
 */
interface Filterable {

  /**
   * array keys for errorInfo tuple.
   *
   * @type int ERROR_TYPE     FilterException classname
   * @type int ERROR_CODE     FilterException code
   * @type int ERROR_MESSAGE  FilterException message
   * @type int ERROR_CONTEXT  map of contextual information
   * @type int ERROR_VALUE    value that failed the filter
   */
  public const ERROR_TYPE = 0;
  public const ERROR_CODE = 1;
  public const ERROR_MESSAGE = 2;
  public const ERROR_CONTEXT = 3;
  public const ERROR_VALUE = 4;

  /**
   * Applies this filter to the given value.
   *
   * @param mixed $value      the value to filter
   * @param bool  $throw      throw on failure?
   * @return ?mixed           filtered value on success; null otherwise
   * @throws FilterException  on failure if $throw
   */
  public function apply($value, bool $throw = false);

  /**
   * Applies this filter to each of the given values.
   * Only values which pass the filter will be returned.
   *
   * @param mixed[] $values   list of values to filter
   * @param bool    $throw    throw on failure?
   * @return mixed[]          (possibly empty) list of filtered values
   * @throws FilterException  on failure, if $throw
   */
  public function applyEach(array $values, bool $throw = false) : array;

  /**
   * Gets information about the last value that failed the filter.
   *
   * @return array  error info:
   *  - string self::ERROR_TYPE     FilterException classname
   *  - int    self::ERROR_CODE     FilterException code
   *  - string self::ERROR_MESSAGE  FilterException message
   *  - array  self::ERROR_CONTEXT  map of contextual information
   *  - mixed  self::ERROR_VALUE    value that failed the filter
   */
  public function errorInfo() : array;

  /**
   * Applies this filter to the given value and inverts (negates) the result.
   *
   * @param mixed $value      the value to filter
   * @param bool  $throw      throw on failure?
   * @return ?mixed           initial value on failure; null otherwise
   * @throws FilterException  on success, if $throw
   */
  public function invert($value, bool $throw = false);

  /**
   * Applies this filter to each of the given values and inverts the result
   * (only values which fail the filter will be returned).
   *
   * @param mixed[] $values   list of values to filter
   * @param bool    $throw    throw on failure?
   * @return mixed[]          (possibly empty) list of initial values
   * @throws FilterException  on success, if $throw
   */
  public function invertEach(array $values, bool $throw = false) : array;
}
