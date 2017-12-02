# Changelog
This file contains highlights of what changes on each version of the [Coveralls for PHP](https://github.com/cedx/coveralls.php) package.

## Version [7.0.0](https://github.com/cedx/coveralls.php/compare/v6.0.0...v7.0.0)
- Breaking change: raised the required [PHP](https://secure.php.net) version.
- Breaking change: using PHP 7.1 features, like nullable types and void functions.
- Updated the package dependencies.

## Version [6.0.0](https://github.com/cedx/coveralls.php/compare/v5.0.0...v6.0.0)
- Breaking change: changed the signature of most class constructors.
- Breaking change: most class properties are now read-only.
- Breaking change: the `Configuration::fromYaml()` method now throws an `InvalidArgumentException` if the document is invalid.
- Updated the package dependencies.

## Version [5.0.0](https://github.com/cedx/coveralls.php/compare/v4.0.0...v5.0.0)
- Breaking change: moved the `Observable` API to a synchronous one.
- Breaking change: moved the `Subject` event API to the `EventEmitter` one.
- Changed licensing for the [MIT License](https://opensource.org/licenses/MIT).
- Restored the [Guzzle](http://docs.guzzlephp.org) HTTP client.

## Version [4.0.0](https://github.com/cedx/coveralls.php/compare/v3.0.0...v4.0.0)
- Breaking change: properties representing URLs as strings now use instances of the [`Psr\Http\Message\UriInterface`](http://www.php-fig.org/psr/psr-7/#35-psrhttpmessageuriinterface) interface.
- Added new unit tests.
- Replaced the [Guzzle](http://docs.guzzlephp.org) HTTP client by an `Observable`-based one.

## Version [3.0.0](https://github.com/cedx/coveralls.php/compare/v2.0.1...v3.0.0)
- Breaking change: renamed the `coveralls` namespace to `Coveralls`.

## Version [2.0.1](https://github.com/cedx/coveralls.php/compare/v2.0.0...v2.0.1)
- Merged [pull request #1](https://github.com/cedx/coveralls.php/pull/1): fixes an issue with usages of the `array_filter()` function.

## Version [2.0.0](https://github.com/cedx/coveralls.php/compare/v1.0.0...v2.0.0)
- Breaking change: ported some APIs to [Observables](http://reactivex.io/intro.html).
- Breaking change: replaced the `-f|--file` named argument of the CLI script by an anonymous argument (e.g. `coveralls coverage.xml` instead of `coveralls -f coverage.xml`)
- Enabled the strict typing.
- Replaced [phpDocumentor](https://www.phpdoc.org) documentation generator by [ApiGen](https://github.com/ApiGen/ApiGen).
- Updated the package dependencies.

## Version [1.0.0](https://github.com/cedx/coveralls.php/compare/v0.4.0...v1.0.0)
- First stable release.
- Updated the package dependencies.

## Version [0.4.0](https://github.com/cedx/coveralls.php/compare/v0.3.0...v0.4.0)
- Breaking change: dropped the dependency on [Observables](http://reactivex.io/intro.html).
- Breaking change: the `Client` class is now an `EventEmitter`.
- Ported the unit test assertions from [TDD](https://en.wikipedia.org/wiki/Test-driven_development) to [BDD](https://en.wikipedia.org/wiki/Behavior-driven_development).
- Updated the package dependencies.

## Version [0.3.0](https://github.com/cedx/coveralls.php/compare/v0.2.0...v0.3.0)
- Updated the package dependencies.

## Version [0.2.0](https://github.com/cedx/coveralls.php/compare/v0.1.0...v0.2.0)
- Updated the package dependencies.

## Version 0.1.0
- Initial release.
