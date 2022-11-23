# Automated Tests

To run the tests, you will need `composer` and `phpunit` in the `PATH`.

```
[~/composer-downloads-plugin] which composer
/usr/local/bin/composer

[~/composer-downloads-plugin] which phpunit
/usr/local/bin/phpunit

[~/src/composer-downloads-plugin] phpunit
PHPUnit 9.5.26 by Sebastian Bergmann and contributors.

....................................                              36 / 36 (100%)

Time: 00:06.560, Memory: 30.38 MB

OK (36 tests, 172 assertions)

Generating code coverage report in Clover XML format ... done [00:00.035]
```

To debug, set project dir so it will not be removed after running:

```
env USE_TEST_PROJECT=$HOME/my-project DEBUG_COMPOSER=1 phpunit tests/Integration/DownloadTest.php
```
