<?php
global $start;

// End clock time in seconds
$end = microtime(true);

// Compute script execution time
$execution = ($end - $start);

$timeUnit = 'sec';
if ($execution < .0005) {
	$execution = 0;
} elseif ($execution < .5) {
	$execution *= 1000;
	$timeUnit = 'ms';
}

if (VERBOSE) {
	echo sprintf("...exiting after %.2f %s", $execution, $timeUnit) . PHP_EOL;
} elseif (!SILENT) {
	echo 'the end' . PHP_EOL;
}
