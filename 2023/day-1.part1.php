<?php

include __DIR__ . DIRECTORY_SEPARATOR . 'template_start.php';

$input = TEST_MODE
	? <<<STR
1abc2
pqr3stu8vwx
a1b2c3d4e5f
treb7uchet
STR
	: file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'day-1.txt');

// remove leading and trailing spaces or blank lines
$input = trim($input);

function getFirstDigit($str): false|string
{
	// regex is: "from the beginning of the string, skip the non-digit characters and get the first digit you find"
	if (!preg_match('/^[\D]*(\d)/', $str, $matches)) {
		if (VERBOSE) {
			echo 'Error in string: no match found for ' . __METHOD__ . PHP_EOL;
		}
		return false;
	}

	return $matches[1];
}

function getLastDigit($str): false|string
{
	// regex is: "get a unique digit that is only followed by non-digit characters until the end of the string"
	if (!preg_match('/(\d)[\D]*$/', $str, $matches)) {
		if (!SILENT) {
			echo 'Error in string: no match found for ' . __METHOD__ . PHP_EOL;
		}

		return false;
	}

	return $matches[1];
}

$sum = 0;
foreach (explode("\n", $input) as $k => $line) {
	$caliber = getFirstDigit($line) . getLastDigit($line);
	$sum += (int)$caliber;
	if (VERBOSE) {
		echo 'caliber #' . $k . ' : ' . $caliber
			. PHP_EOL . 'sum : ' . $sum . PHP_EOL;
	}
}

if (SILENT) {
	echo $sum . PHP_EOL;
} else {
	echo 'Entire calibration is ' . $sum . '.' . PHP_EOL;
}

include __DIR__ . DIRECTORY_SEPARATOR . 'template_exit.php';
