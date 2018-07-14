# Included Filters

Perk includes a number of basic filter classes for general value comparison,
text handling, time, datatypes, and logical filter composition.
The goal is to

## Comparison

### Between

Passes if the given value compares as between minimum and maximum values.
Does not check or cooerce data types.

Comparison is inclusive (min <= value <= max) by default;
pass $inclusive = false to make comparison exclusive (min < value < max).

#### `Between::__construct(mixed $min, mixed $max [, bool $inclusive = true])`

```php
<?php
use at\perk\Perk;

$between = Perk::createFilter([Perk::BETWEEN, 1, 5]);
var_dump(
  $between->apply(2),  // 2
  $between->apply(5),  // 5 (inclusive by default)
  $between->apply(0),  // null
  $between->invert(10) // 10 ("not between")
);

$between = Perk::createFilter([Perk::BETWEEN, 1, 5, false]);
var_dump(
  $between->apply(2),  // 2
  $between->apply(5)   // null (comparison is now exclusive of min/max)
);
```

### Equal

Passes if the given value compares as equal to filter's value.
Does not cooerce data types.

Comparison is loose (==) by default; pass $strict = true to make comparison strict (===).

#### `Equal::__construct(mixed $compare [, bool $strict])`

```php
<?php
use at\perk\Perk;

$equal = Perk::createFilter([Perk::EQUAL, 1]);
var_dump(
  $equal->apply(1),   // 1
  $equal->apply("1"), // "1" (loose comparison by default)
  $equal->apply(2)    // null
  $equal->invert(2)   // 2 ("not equal")
);

$equal = Perk::createFilter([Perk::EQUAL, 1, true]);
var_dump(
  $equal->apply(1),   // 1
  $equal->apply("1"), // null (comparison is now strict)
);
```
