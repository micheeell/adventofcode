<?php

include __DIR__ . DIRECTORY_SEPARATOR . 'template_start.php';

$input = TEST_MODE
	? <<<STR
LR

11A = (11B, XXX)
11B = (XXX, 11Z)
11Z = (11B, XXX)
22A = (22B, XXX)
22B = (22C, 22C)
22C = (22Z, 22Z)
22Z = (22B, 22B)
XXX = (XXX, XXX)
STR
	: file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'day-8.txt');

// remove leading and trailing spaces or blank lines
$input = trim($input);

const DIRECTIONS = ['L' => 0, 'R' => 1];

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

function getStartingPoints($network): array
{
	$points = [];
	foreach (array_keys($network) as $position) {
		if (str_ends_with($position, 'A')) {
			$points[] = $position;
		}
	}

	return $points;
}

function getEndingPoints($network): array
{
	$points = [];
	foreach (array_keys($network) as $position) {
		if (str_ends_with($position, 'Z')) {
			$points[] = $position;
		}
	}

	return $points;
}

function parseNetwork($lines): false|array
{
	$network = [];
	foreach ($lines as $coordinate) {
		if (!preg_match('/^([A-Z0-9]+) = \(([A-Z0-9]+), ([A-Z0-9]+)\)$/', $coordinate, $matches)) {
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

function ghostWalk($coordinates, $direction, $network): array
{
	$nextCoordinates = [];
	foreach ($coordinates as $_k => $position) {
		$nextCoordinates[$_k] = walk($position, $direction, $network);
	}

	return $nextCoordinates;
}

function hasArrived($coordinates): bool
{
	foreach ($coordinates as $position) {
		if (!str_ends_with($position, 'Z')) {
			return false;
		}
	}

	return true;
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
$coordinates = getStartingPoints($network);
if (VERBOSE) {
	echo 'Found ' . count($coordinates) . ' starting points.' . PHP_EOL;
	echo 'For ' . count(getEndingPoints($network)) . ' ending points.' . PHP_EOL;
	if (DEBUG_MODE) {
		echo 'initial position is: (' . PHP_EOL
			. "\t" . implode(",\n\t", $coordinates)
			. PHP_EOL . ')' . PHP_EOL;
	}
}
while (!$loop && !$found) {
	$reducedStep = $steps % $length;
	$visited[] = implode($separator, $coordinates) . $separator . $reducedStep;
	$direction = $instructions[$reducedStep];
	$coordinates = ghostWalk($coordinates, $direction, $network);
	$steps++;
	if (VERBOSE && DEBUG_MODE && ($steps % 100 == 0)) {
		echo 'after 100 steps position is: (' . PHP_EOL
			. "\t" . implode(",\n\t", $coordinates)
			. PHP_EOL . ')' . PHP_EOL;
	}
	if (VERBOSE && DEBUG_MODE && $steps > 999) {
		$found = true; // that's a lie
	}
	if (hasArrived($coordinates)) {
		if (VERBOSE) {
			echo 'Made it out of the maze on the ' . $direction . '!' . PHP_EOL;
		}

		$found = true;
	}

	$isVisited = in_array(implode($separator, $coordinates) . $separator . ($steps % $length), $visited);
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
		echo 'Steps required to reach exit: ' . $steps . PHP_EOL;
	} else {
		echo 'After ' . $steps . ' steps, got in a loop.' . PHP_EOL;
	}
}


include __DIR__ . DIRECTORY_SEPARATOR . 'template_exit.php';
