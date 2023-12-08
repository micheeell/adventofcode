<?php

include __DIR__ . DIRECTORY_SEPARATOR . 'template_start.php';

$input = TEST_MODE
	? (DEBUG_MODE ? <<<STR
RL

AAA = (BBB, CCC)
BBB = (DDD, EEE)
CCC = (ZZZ, GGG)
DDD = (DDD, DDD)
EEE = (EEE, EEE)
GGG = (GGG, GGG)
ZZZ = (ZZZ, ZZZ)
STR
		: <<<STR
LLR

AAA = (BBB, BBB)
BBB = (AAA, ZZZ)
ZZZ = (ZZZ, ZZZ)
STR
	)
	: file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'day-8.txt');

// remove leading and trailing spaces or blank lines
$input = trim($input);

const DIRECTIONS = ['L' => 0, 'R' => 1];
const START = 'AAA';
const ENDZ = 'ZZZ';

function parseInput($input): array
{
	$lines = explode("\n", $input);
	// first line is for directions
	$instructionsLine = array_shift($lines);
	$instructions = parseInstructions($instructionsLine);
	// second line should be empty:
	$empty = array_shift($lines);
	if (strlen($empty) > 0) {
		if (!SILENT) {
			echo 'ERROR parsing input: line is not empty: ' . var_export($empty, true) . PHP_EOL;
		}
	}
	$network = parseNetwork($lines);

	return [$instructions, $network];
}

function parseInstructions($directions): false|array
{
	if (!preg_match('/^[LR]+$/', $directions)) {
		if (!SILENT) {
			echo 'ERROR Direction line should only contain L (left) or R (right): ' . var_export(
					$directions,
					true
				) . PHP_EOL;
		}

		return false;
	}

	return str_split($directions);
}

function parseNetwork($lines): false|array
{
	$network = [];
	foreach ($lines as $coordinate) {
		if (!preg_match('/^([A-Z]+) = \(([A-Z]+), ([A-Z]+)\)$/', $coordinate, $matches)) {
			if (!SILENT) {
				echo 'ERROR Misformed coordinates - expected "AAA = (BBB, CCC)": ' . var_export(
						$coordinate,
						true
					) . PHP_EOL;
			}

			return false;
		}
		$node = $matches[1];
		$left = $matches[2];
		$right = $matches[3];
		if (isset($network[$node])) {
			if (!SILENT) {
				echo 'ERROR Node ' . $node . ' already defined: ' . var_export($coordinate, true) . PHP_EOL;
			}

			return false;
		}
		$network[$node] = [$left, $right];
	}

	return $network;
}

function walk($coordinate, $direction, $network): string
{
	return $network[$coordinate][DIRECTIONS[$direction]];
}

[$instructions, $network] = parseInput($input);
$length = count($instructions);
$separator = '|';
$loop = $found = false;
$steps = 0;
$visited = [];
$coordinate = START;
while (!$loop && !$found) {
	$reducedStep = $steps % $length;
	$visited[] = $coordinate . $separator . $reducedStep;
	$direction = $instructions[$reducedStep];
	$coordinate = walk($coordinate, $direction, $network);
	$steps++;
	if ($coordinate == ENDZ) {
		if (VERBOSE) {
			echo 'Made it out of the maze on the ' . $direction . '!' . PHP_EOL;
		}

		$found = true;
	}

	$isVisited = in_array($coordinate . $separator . ($steps % $length), $visited);
	if ($isVisited) {
		if (VERBOSE) {
			echo 'Caught in a loop :-(' . PHP_EOL;
		}

		$loop = true;
	}
}


if (SILENT) {
	echo $steps . PHP_EOL;
} else {
	if ($found) {
		echo 'Steps required to reach ZZZ: ' . $steps . PHP_EOL;
	} else {
		echo 'After ' . $steps . ' steps, got in a loop.' . PHP_EOL;
	}
}


include __DIR__ . DIRECTORY_SEPARATOR . 'template_exit.php';
