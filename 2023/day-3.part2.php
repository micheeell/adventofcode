<?php

include __DIR__ . DIRECTORY_SEPARATOR . 'template_start.php';

$input = TEST_MODE
	? <<<STR
467..114..
...*......
..35..633.
......#...
617*......
.....+.58.
..592.....
......755.
...$.*....
.664.598..
STR
	: file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'day-3.txt');

// remove leading and trailing spaces or blank lines
$input = trim($input);

function parseInput($fileContent): array
{
	$lines = explode("\n", $fileContent);
	$gears = $numbers = [];
	foreach ($lines as $x => $line) {
		$gears[$x] = parseLineGears($line);
		$numbers[$x] = parseLineNumbers($line);
	}

	return [
		'gears' => $gears,
		'numbers' => $numbers,
	];
}

function parseLineGears($line): array
{
	if (!preg_match_all('/(\*)/', $line, $matches, PREG_OFFSET_CAPTURE)) {
		if (VERBOSE) {
			echo 'No gear found in line ' . $line . PHP_EOL;
		}

		return [];
	}

	$gears = [];
	foreach ($matches[0] as $match) {
		$gears[] = $match[1];
	}

	return $gears;
}

function parseLineNumbers($line): array
{
	if (!preg_match_all('/(\d+)/', $line, $matches, PREG_OFFSET_CAPTURE)) {
		if (VERBOSE) {
			echo 'No number found in line ' . $line . PHP_EOL;
		}

		return [];
	}

	$numbers = [];
	foreach ($matches[0] as $match) {
		$index = $match[1];
		$number = (int)$match[0];
		for ($i = 0; $i < strlen($match[0]); $i++) {
			$numbers[$index + $i] = $number;
		}
	}

	return $numbers;
}

function getAdjacentNumbers($gearLine, $gearColumn, $numbers): array
{
	$adjacentNumbers = [];
	// number is adjacent if it's on the same line and ends right before the gear
	if ($gearColumn > 0 && isset($numbers[$gearLine][$gearColumn - 1])) {
		$adjacentNumbers[] = $numbers[$gearLine][$gearColumn - 1];
	}
	// number is adjacent if it's on the same line and starts right after the gear
	if (isset($numbers[$gearLine][$gearColumn + 1])) {
		$adjacentNumbers[] = $numbers[$gearLine][$gearColumn + 1];
	}
	// number is adjacent if it's right above the gear
	if ($gearLine > 0 && isset($numbers[$gearLine - 1][$gearColumn])) {
		$adjacentNumbers[] = $numbers[$gearLine - 1][$gearColumn];
	} elseif ($gearLine > 0) {
		// or if there's a number diagonally above on the left
		if ($gearColumn > 0 && isset($numbers[$gearLine - 1][$gearColumn - 1])) {
			$adjacentNumbers[] = $numbers[$gearLine - 1][$gearColumn - 1];
		}
		// or if there's a number diagonally above on the right
		if (isset($numbers[$gearLine - 1][$gearColumn + 1])) {
			$adjacentNumbers[] = $numbers[$gearLine - 1][$gearColumn + 1];
		}
	}

	// number is adjacent if it's right below the gear
	if ($gearLine < count($numbers) - 1 && isset($numbers[$gearLine + 1][$gearColumn])) {
		$adjacentNumbers[] = $numbers[$gearLine + 1][$gearColumn];
	} elseif ($gearLine > 0) {
		// or if there's a number diagonally below on the left
		if ($gearColumn > 0 && isset($numbers[$gearLine + 1][$gearColumn - 1])) {
			$adjacentNumbers[] = $numbers[$gearLine + 1][$gearColumn - 1];
		}
		// or if there's a number diagonally below on the right
		if (isset($numbers[$gearLine + 1][$gearColumn + 1])) {
			$adjacentNumbers[] = $numbers[$gearLine + 1][$gearColumn + 1];
		}
	}

	return $adjacentNumbers;
}

$parsed = parseInput($input);

$result = 0;
foreach ($parsed['gears'] as $lineIndex => $gears) {
	foreach ($gears as $column) {
		$adjacentNumbers = getAdjacentNumbers($lineIndex, $column, $parsed['numbers']);
		if (count($adjacentNumbers) == 2) {
			$power = array_product($adjacentNumbers);
			$result += $power;
		}
	}
}

if (SILENT) {
	echo $result . PHP_EOL;
} else {
	echo 'The sum of all of the powers of all the gears is ' . $result . PHP_EOL;
}

include __DIR__ . DIRECTORY_SEPARATOR . 'template_exit.php';
