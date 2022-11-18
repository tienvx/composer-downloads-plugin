# Automated Tests

The `tests/` folder includes unit-tests and integration-tests written with
PHPUnit.  Each integration-test generates a new folder/project with a
plausible, representative `composer.json` file and executes `composer
install`.  It checks the output has the expected files.

To run the tests, you will need `composer` and `phpunit` in the `PATH`.

```
[~/src/composer-downloads-plugin] which composer
/Users/myuser/bin/composer

[~/src/composer-downloads-plugin] which phpunit
/Users/myuser/bin/phpunit

[~/src/composer-downloads-plugin] phpunit
PHPUnit 5.7.27 by Sebastian Bergmann and contributors.

.....                                                               5 / 5 (100%)

Time: 40.35 seconds, Memory: 10.00MB

OK (5 tests, 7 assertions)
```

The integration tests can be a bit large/slow. To monitor the tests more
closesly, set the `DEBUG` variable, as in:

```
[~/src/composer-downloads-plugin] env DEBUG=2 phpunit
```
