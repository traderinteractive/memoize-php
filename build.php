#!/usr/bin/env php
<?php
chdir(__DIR__);

$returnStatus = null;
passthru('composer install --dev', $returnStatus);
if ($returnStatus !== 0) {
    exit(1);
}

passthru('./vendor/bin/phpcs --standard=PSR1 -n src tests *.php', $returnStatus);
if ($returnStatus !== 0) {
    exit(1);
}

passthru('./vendor/bin/phpunit --coverage-html coverage --strict tests', $returnStatus);
if ($returnStatus !== 0) {
    exit(1);
}
