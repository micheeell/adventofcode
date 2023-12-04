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
	$symbols = $numbers = [];
	foreach ($lines as $x => $line) {
		$symbols[$x] = parseLineSymbols($line);
		$numbers[$x] = parseLineNumbers($line);
	}

	return [
		'symbols' => $symbols,
		'numbers' => $numbers,
	];
}

function parseLineSymbols($line): array
{
	if (!preg_match_all('/([*#+$\-%&=\/@])/', $line, $matches, PREG_OFFSET_CAPTURE)) {
		if (VERBOSE) {
			echo 'No symbol found in line ' . $line . PHP_EOL;
		}

		return [];
	}

	$symbols = [];
	foreach ($matches[0] as $match) {
		$symbols[] = $match[1];
	}

	return $symbols;
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
		$numbers[] = [
			'value' => (int)$match[0],
			'start' => $match[1],
			'end' => ($match[1] + strlen($match[0])),
		];
	}

	return $numbers;
}

function isAdjacent($number, $lineIndex, $symbols): bool
{
	$start = $number['start'] > 0 ? $number['start'] - 1 : 0;
	$end = $number['end'];
	if ($lineIndex == 102) {
		echo ' number: ' . $number['value'] . PHP_EOL
			. '  first: ' . $number['start'] . PHP_EOL
			. '  start: ' . $start . PHP_EOL
			. '    end: ' . $end . PHP_EOL
			. ' symbols on line L.' . $lineIndex . ' are at: '
			. PHP_EOL . join(', ', $symbols[$lineIndex]) . PHP_EOL
			. ' symbols on line L.' . ($lineIndex - 1) . ' are at: '
			. PHP_EOL . join(', ', $symbols[$lineIndex - 1]) . PHP_EOL
			. ' symbols on line L.' . ($lineIndex + 1) . ' are at: '
			. PHP_EOL . join(', ', $symbols[$lineIndex + 1]) . PHP_EOL;
	}

	// number is adjacent if there's a symbol on the same line before
	if (in_array($start, $symbols[$lineIndex])) {
		return true;
	}
	// number is adjacent if there's a symbol on the same line after
	if (in_array($end, $symbols[$lineIndex])) {
		return true;
	}
	// number is adjacent if there's a symbol on the line above starting at index-1 and ending at index+1
	if ($lineIndex > 0) {
		for ($i = $start; $i <= $end; $i++) {
			if (in_array($i, $symbols[$lineIndex - 1])) {
				return true;
			}
		}
	}
	// number is adjacent if there's a symbol on the line below starting at index-1 and ending at index+1
	if ($lineIndex < count($symbols) - 1) {
		for ($i = $start; $i <= $end; $i++) {
			if (in_array($i, $symbols[$lineIndex + 1])) {
				return true;
			}
		}
	}

	return false;
}

$parsed = parseInput($input);

$result = 0;
foreach ($parsed['numbers'] as $lineIndex => $numbers) {
	foreach ($numbers as $number) {
		if (isAdjacent($number, $lineIndex, $parsed['symbols'])) {
			$result += $number['value'];
		} elseif (TEST_MODE || VERBOSE) {
			echo 'Number ' . $number['value'] . ' on line L.' . ($lineIndex + 1) . ' is not adjacent to any symbol' . PHP_EOL;
		}
	}
}

if (SILENT) {
	echo $result . PHP_EOL;
} else {
	echo 'The sum of all of the part numbers in the engine schematic is ' . $result . PHP_EOL;
}

include __DIR__ . DIRECTORY_SEPARATOR . 'template_exit.php';
