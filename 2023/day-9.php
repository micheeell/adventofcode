<?php

include __DIR__ . DIRECTORY_SEPARATOR . 'template_start.php';

$input = TEST_MODE
	? <<<STR
0 3 6 9 12 15
1 3 6 10 15 21
10 13 16 21 30 45
STR
	: file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'day-9.txt');

// remove leading and trailing spaces or blank lines
$input = trim($input);

function parseInput($input): array
{
	$lines = explode("\n", $input);
	$numbers = [];
	foreach ($lines as $_k => $line) {
		if (!preg_match('/^(-?\d+ )+-?\d+$/', $line)) {
			if (!SILENT) {
				echo 'ERROR: misformed line #' . ($_k + 1) . ' in input: ' . var_export($line, true) . PHP_EOL;
			}

			continue;
		}

		$numbers[] = explode(" ", $line);
	}

	return $numbers;
}


function predict($series): int
{
	if (isReduced($series)) {
		return (int)$series[0];
	}

	if (PART2) {
		$firstTerm = (int)$series[0];

		return $firstTerm - predict(reduce($series));
	}

	$lastTerm = (int)end($series);

	return $lastTerm + predict(reduce($series));
}

function isReduced($numbers): bool
{
	$equal = false;
	foreach ($numbers as $number) {
		if ($equal === false) {
			$equal = $number;
		} elseif ($number !== $equal) {
			return false;
		}
	}

	return true;
}

function reduce($numbers): array
{
	if (count($numbers) < 2) {
		if (!SILENT) {
			echo 'ERROR: cannot reduce smallest arrays: (' . implode(' | ', $numbers) . ')' . PHP_EOL
				. 'Parent array should have been reduced before.' . PHP_EOL;
		}

		return [];
	}

	$reduced = [];
	for ($i = 0; $i < (count($numbers) - 1); $i++) {
		$reduced[] = (int)$numbers[$i + 1] - (int)$numbers[$i];
	}

	return $reduced;
}


$lines = parseInput($input);
$sum = 0;
foreach ($lines as $line) {
	$sum += predict($line);
}


if (SILENT) {
	echo $sum . PHP_EOL;
} else {
	echo (PART2 ? 'Part 2: ' : 'Part 1: ') . 'The sum for all extrapolated values is ' . $sum . PHP_EOL;
}


include __DIR__ . DIRECTORY_SEPARATOR . 'template_exit.php';
