# Features

## Coverage formats
Currently, this package supports two formats of coverage reports:

- [Clover](https://www.atlassian.com/software/clover): the main format used with [PHPUnit](https://phpunit.de).
- [LCOV](http://ltp.sourceforge.net/coverage/lcov.php): the de facto standard.

## CI services
This project has been tested with [Travis CI](https://travis-ci.com) service, but these services should also work with no extra effort:

- [AppVeyor](https://www.appveyor.com)
- [CircleCI](https://circleci.com)
- [Codeship](https://codeship.com)
- [GitHub Actions](https://github.com/features/actions)
- [GitLab CI](https://gitlab.com)
- [Jenkins](https://jenkins.io)
- [Semaphore](https://semaphoreci.com)
- [Solano CI](https://ci.solanolabs.com)
- [Surf](https://github.com/surf-build/surf)
- [Wercker](https://app.wercker.com)

?> You can find an [example workflow for GitHub Actions](https://github.com/cedx/coveralls.php/blob/main/.github/workflows/build.yaml) in the sources of this project.

## Environment variables
If your build system is not supported, you can still use this package.
There are a few environment variables that are necessary for supporting your build system:

- `COVERALLS_SERVICE_NAME` : the name of your build system.
- `COVERALLS_REPO_TOKEN` : the secret repository token from [Coveralls](https://coveralls.io).

There are optional environment variables:

- `COVERALLS_SERVICE_JOB_ID` : a string that uniquely identifies the build job.
- `COVERALLS_RUN_AT` : a date string for the time that the job ran. This defaults to your build system's date/time if you don't set it.

The full list of supported environment variables is available in the source code of the `Coveralls\Configuration` class (see the `fromEnvironment()` static method).

## The ".coveralls.yml" file
This package supports the same configuration sources as the [Coveralls](https://coveralls.io) ones:  
[Coveralls currently supports](http://docs.coveralls.io/supported-ci-services)
