#!/usr/bin/env php
<?php

if (PHP_SAPI !== 'cli') {
    echo 'Warning: Automate should be invoked via the CLI version of PHP, not the '.PHP_SAPI.' SAPI'.PHP_EOL;
}

Phar::mapPhar('automate.phar');

require_once 'phar://automate.phar/vendor/autoload.php';

use Automate\Automate;

$automate = new Automate;
$automate->run();

__HALT_COMPILER();