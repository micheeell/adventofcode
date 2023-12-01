<?php
// Starting clock time in seconds
$start = microtime(true);

// Enable error logging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function displayHelp()
{
    $trace = debug_backtrace();
    $script = basename($trace[1]['file']);

    echo "Usage: php {$script} -- [options]"
        . PHP_EOL . "Options:"
        . PHP_EOL . "  --help, -h      Display this help message"
        . PHP_EOL . "  --verbose, -v   Enable verbose mode"
        . PHP_EOL . "  --test, -t      Use test mode (with sample data)"
        . PHP_EOL . "  --silent, -s    Use silent mode (only shows result)"
        . PHP_EOL;
}

// Script options
$shortOptions = "vfitsh";
$longOptions = ["verbose", "file", "input", "test", "silent", "help"];

$options = getopt($shortOptions, $longOptions);

if (isset($options["h"]) || isset($options["help"])) {
    displayHelp();
    exit(0);
}

define('VERBOSE', (isset($options["v"]) || isset($options["verbose"])));
define('TEST_MODE', (isset($options["t"]) || isset($options["test"])));
define('SILENT', (isset($options["s"]) || isset($options["silent"])));

if (VERBOSE) {
	echo 'starting...'
		. PHP_EOL . sprintf("%20s %s", 'verbose mode is:', 'ON')
		. PHP_EOL . sprintf("%20s %s", 'test mode is:', (TEST_MODE ? 'ON' : 'off'))
		. PHP_EOL;
    if (SILENT) {
        die(
            'FATAL ERROR'
            . PHP_EOL . 'Script cannot be verbose and silent at the same time.' . PHP_EOL
        );
    }
}
