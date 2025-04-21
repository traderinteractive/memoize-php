# Memoize
A PHP library for memoizing repeated function calls.

[![Build Status](http://img.shields.io/travis/traderinteractive/memoize-php.svg?style=flat)](https://travis-ci.org/traderinteractive/memoize-php)
[![Scrutinizer Code Quality](http://img.shields.io/scrutinizer/g/traderinteractive/memoize-php.svg?style=flat)](https://scrutinizer-ci.com/g/traderinteractive/memoize-php/)
[![Code Coverage](http://img.shields.io/coveralls/traderinteractive/memoize-php.svg?style=flat)](https://coveralls.io/r/traderinteractive/memoize-php)

[![Latest Stable Version](http://img.shields.io/packagist/v/traderinteractive/memoize.svg?style=flat)](https://packagist.org/packages/traderinteractive/memoize)
[![Total Downloads](http://img.shields.io/packagist/dt/traderinteractive/memoize.svg?style=flat)](https://packagist.org/packages/traderinteractive/memoize)
[![License](http://img.shields.io/packagist/l/traderinteractive/memoize.svg?style=flat)](https://packagist.org/packages/traderinteractive/memoize)

## Requirements
This library requires PHP 7.0, or newer.

## Installation
This package uses [composer](https://getcomposer.org) so you can just add
`traderinteractive/memoize` as a dependency to your `composer.json` file.

## Memoization
[Memoization](http://en.wikipedia.org/wiki/Memoization) is a way of optimizing
a function that is called repeatedly by caching the results of a function call.

## Memoization Providers
This library includes several built-in providers for memoization.  Each one
implements the `\TraderInteractive\Memoize\Memoize` interface:
```php
interface Memoize
{
    /**
     * Gets the value stored in the cache or uses the passed function to
     * compute the value and save to cache.
     *
     * @param string $key The key to fetch
     * @param callable $compute A function to run if the value was not cached
     *     that will return the result.
     * @param int $cacheTime The number of seconds to cache the response for,
     *     or null to not expire it ever.
     * @return mixed The data requested, optionally pulled from cache
     */
    public function memoizeCallable($key, $compute, $cacheTime = null);
}
```

The `$compute` callable must not take any parameters - if you need parameters,
consider wrapping your function in a closure that pulls the required parameters
into scope.  For example, given the function:
```php
$getUser = function($database, $userId) {
  $query = $database->select('*')->from('user')->where(['id' => $userId]);
  return $query->fetchOne();
};
```

You could wrap this in a closure like so:
```php
$getLoggedInUser = function() use($database, $loggedInUserId, $getUser) {
    return $getUser($database, $loggedInUserId);
};

$memoize->memoizeCallable("getUser-{$loggedInUserId}", $getLoggedInUser);
```

Alternatively, you could invert this and return the closure instead, like so:

```php
$getUserLocator = function($database, $userId) use($getUser) {
    return function() use($database, $userId, $getUser) {
        return $getUser($database, $userId);
    };
};

$getLoggedInUser = $getUserLocator($database, $loggedInUserId);
$memoize->memoizeCallable("getUser-{$loggedInUserId}", $getLoggedInUser);
```

Future versions of this library may add support for parameters, as it can be a
common usecase (especially when it comes to recursive functions.

Also worth noting, is that you need to make sure you define your cache keys
uniquely for anything using the memoizer.

### Predis
The predis provider uses the [predis](https://github.com/nrk/predis) library to
cache the results in Redis.  It supports the `$cacheTime` parameter so that
results can be recomputed after the time expires.

This memoizer can be used in a way that makes it persistent between processes
rather than only caching computation for the current process.

#### Example
```php
$predis = new \Predis\Client($redisUrl);
$memoize = new \TraderInteractive\Memoize\Predis($predis);

$compute = function() {
    // Perform some long operation that you want to memoize
};

// Cache he results of $compute for 1 hour.
$result = $memoize->memoizeCallable('myLongOperation', $compute, 3600);
```

### Memcache
The memcache provider uses the [memcache](https://www.php.net/manual/en/book.memcache.php) library to
cache the results in Memcache.  It supports the `$cacheTime` parameter so that
results can be recomputed after the time expires.

This memoizer can be used in a way that makes it persistent between processes
rather than only caching computation for the current process.

#### Example
```php
$memcache = new Memcache;
$memcacheInstance = $memcache->connect('127.0.0.1', 11211);
$memoize = new \TraderInteractive\Memoize\Memcache($memcacheInstance);

$compute = function() {
    // Perform some long operation that you want to memoize
};

// Cache he results of $compute for 1 hour.
$result = $memoize->memoizeCallable('myLongOperation', $compute, 3600);
```

### Memory
This is a standard in-memory memoizer.  It does not support `$cacheTime` at the
moment and only keeps the results around as long as the memoizer is in memory.

#### Example
```php
$memoize = new \TraderInteractive\Memoize\Memory();

$compute = function() {
    // Perform some long operation that you want to memoize
};

$result = $memoize->memoizeCallable('myLongOperation', $compute);
```

### None
This memoizer does not actually memoize anything - it always calls the
`$compute` function.  It is useful for testing and can also be used when you
disable memoization for debugging, etc. because you can swap your real memoizer
out for this one and everything will still work.

#### Example
```php
$memoize = new \TraderInteractive\Memoize\None();

$compute = function() {
    // Perform some long operation that you want to memoize
};

// This will never actually memoize the results - they will be recomputed every
// time.
$result = $memoize->memoizeCallable('myLongOperation', $compute);
```
