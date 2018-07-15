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
 *
 * Child classes must implement their specific filter logic in `_applyFilter()`.
 * On success, this method returns the value that passed the filter
 * (i.e., with any normalizations, type conversions, etc. applied).
 * On failure, this method MUST throw a FilterException.
 * This method MUST NOT throw any other exception, nor return any value to indicate failure.
 *
 * Child classes may optionally override `_getInvertException()`,
 * to provide a more meaningful FilterException for when `invert()` fails.
 */
abstract class Filter implements Filterable {

  /** @type array  info about last filter failure. */
  protected $_lastError = [
    self::ERROR_TYPE => null,
    self::ERROR_CODE => FilterException::ERROR_NONE,
    self::ERROR_MESSAGE => '',
    self::ERROR_CONTEXT => [],
    self::ERROR_VALUE => null
  ];

  /**
   * {@inheritDoc}
   */
  public function apply($value, bool $throw = false) {
    try {
      return $this->_applyFilter($value);
    } catch (FilterException $e) {
      $this->_setErrorInfo($value, $e);
      if ($throw) {
        throw $e;
      }

      return null;
    }
  }

  /**
   * {@inheritDoc}
   */
  public function applyEach(array $values, bool $throw = false) : array {
    $filtered = [];
    foreach ($values as $value) {
      $value = $this->apply($value, $throw);
      if ($value !== null) {
        $filtered[] = $value;
      }
    }

    return $filtered;
  }

  /**
   * {@inheritDoc}
   */
  public function errorInfo() : array {
    return $this->_lastError;
  }

  /**
   * {@inheritDoc}
   */
  public function invert($value, bool $throw = false) {
    try {
      $this->apply($value, true);
    } catch (FilterException $e) {
      return $value;
    }

    $e = $this->_getInvertException($value);
    $this->_setErrorInfo($value, $e);
    if ($throw) {
      throw new $e($e->getCode(), $e->getContext());
    }

    return null;
  }

  /**
   * {@inheritDoc}
   */
  public function invertEach(array $values, bool $throw = false) : array {
    $filtered = [];
    foreach ($values as $value) {
      $value = $this->invert($value, $throw);
      if ($value !== null) {
        $filtered[] = $value;
      }
    }

    return $filtered;
  }

  /**
   * Actual filter logic.
   *
   * @param mixed $value      the value to filter
   * @return mixed            the filtered value on success
   * @throws FilterException  on failure
   */
  abstract protected function _applyFilter($value);

  /**
   * Gets the exception to throw when the negated filter fails.
   *
   * @param mixed $value  the value that failed the filter
   * @return FilterException
   */
  public function _getInvertException($value) : FilterException {
    return new FilterException(FilterException::INVERT_FAILED, ['filter' => static::class]);
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
