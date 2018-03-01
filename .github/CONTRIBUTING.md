# Contribution Guidelines
We welcome you to report [issues](/../../issues) or submit [pull requests](/../../pulls).  While the below guidelines are necessary to get code merged, you can
submit pull requests that do not adhere to them and we will try to take care of them in our spare time.  We are a smallish group of developers,
though, so if you can make sure the build is passing 100%, that would be very useful.

We recommend including details of your particular usecase(s) with any issues or pull requests.  We love to hear how our libraries are being used
and we can get things merged in quicker when we understand its expected usage.

## Pull Requests
Code changes should be sent through [GitHub Pull Requests](/../../pulls).  Before submitting the pull request, make sure that phpunit reports success
by running:
```sh
./vendor/bin/phpunit
```
And there are not coding standard violations by running
```sh
./vendor/bin/phpcs
```

## Builds
Our [Travis build](https://travis-ci.org/traderinteractive/memoize-php) executes [PHPUnit](http://www.phpunit.de) and uses [Coveralls](https://coveralls.io/) to enforce code coverage.
While the build does not strictly enforce 100% code coverage, it will not allow coverage to drop below its current percentage.
[Scrutinizer](https://scrutinizer-ci.com/) is used to ensure code quality and enforce the [coding standard](http://www.php-fig.org/psr/psr-2/).

