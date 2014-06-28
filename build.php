#!/usr/bin/env php
<?php
chdir(__DIR__);

$returnStatus = null;
passthru('composer install --dev', $returnStatus);
if ($returnStatus !== 0) {
    exit(1);
}

require 'vendor/autoload.php';

passthru('./vendor/bin/phpcs --standard=PSR1 -n src tests *.php', $returnStatus);
if ($returnStatus !== 0) {
    exit(1);
}

$phpunitConfiguration = PHPUnit_Util_Configuration::getInstance(__DIR__ . '/phpunit.xml');
$phpunitArguments = array('coverageHtml' => __DIR__ . '/coverage', 'configuration' => $phpunitConfiguration);
$testRunner = new PHPUnit_TextUI_TestRunner();
$result = $testRunner->doRun($phpunitConfiguration->getTestSuiteConfiguration(), $phpunitArguments);
if (!$result->wasSuccessful()) {
    exit(1);
}

$coverageFactory = new PHP_CodeCoverage_Report_Factory();
$coverageReport = $coverageFactory->create($result->getCodeCoverage());
if ($coverageReport->getNumExecutedLines() !== $coverageReport->getNumExecutableLines()) {
    file_put_contents('php://stderr', "Code coverage was NOT 100%\n");
    exit(1);
}

echo "Code coverage was 100%\n";
