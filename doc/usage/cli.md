---
path: src/branch/master
source: bin/coveralls
---

# Command line interface
The easy way. From a command prompt, install the `coveralls` executable:

```shell
composer global require cedx/coveralls
```

!!! tip
    Consider adding the [`composer global`](https://getcomposer.org/doc/03-cli.md#global) executables directory to your system path.

Then use it to upload your coverage reports:

```shell
$ coveralls --help

Description:
  Send a coverage report to the Coveralls service.

Usage:
  coveralls <file>

Arguments:
  file                  The path of the coverage report to upload

Options:
  -h, --help            Display this help message
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi            Force ANSI output
      --no-ansi         Disable ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

For example:

```shell
coveralls build/coverage.xml
```
