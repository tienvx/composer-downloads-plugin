# Automated Tests

To run the tests:

```
./vendor/bin/phpunit
```

To debug, set project dir so it will not be removed after running:

```
env USE_TEST_PROJECT=$HOME/my-project DEBUG_COMPOSER=1 ./vendor/bin/phpunit tests/Integration/Valid/InstallTest.php
```
