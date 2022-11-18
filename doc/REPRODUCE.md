# Local Dev Harness

What if you want to produce an environment which uses the current plugin
code - one where you can quickly re-run `composer` commands while
iterating on code?

You may use any of the integration-tests to initialize a baseline
environment:

```
env USE_TEST_PROJECT=$HOME/src/myprj DEBUG=2 phpunit tests/Integration/SniffTest.php
```
