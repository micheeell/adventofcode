<?php

/**
 * GPT solution
 *
 * function chineseRemainderTheorem($remainders, $moduli)
 * {
 * $n = count($moduli);
 * $x = gmp_init('0');
 * $N = gmp_init('1');
 *
 * // Calculate N
 * foreach ($moduli as $m) {
 * $N = gmp_mul($N, $m);
 * }
 *
 * // Calculate x
 * for ($i = 0; $i < $n; $i++) {
 * $Ni = gmp_div($N, $moduli[$i]);
 * $xi = gmp_invert($Ni, $moduli[$i]);
 * $term = gmp_mul(gmp_mul($remainders[$i], $Ni), $xi);
 * $x = gmp_add($x, $term);
 * }
 *
 * return gmp_strval(gmp_mod($x, $N));
 * }
 *
 * // Example usage:
 * $initialOccurrences = ['11567', '21251', '12643', '16409', '19099', '14257'];
 * $periods = ['11569', '21253', '12645', '16413', '19101', '14259'];
 *
 * $firstSimultaneousOccurrence = chineseRemainderTheorem($initialOccurrences, $periods);
 *
 * echo "The first simultaneous occurrence is at step: " . $firstSimultaneousOccurrence . "\n";
 * exit(1);
 * end of GPT solution
 */
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
		if (isExit($position)) {
			$points[] = $position;
		}
	}

	return $points;
}

function isExit($position): bool
{
	return str_ends_with($position, 'Z');
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

function checkLoopExit($exit, $instructions, $network, $period): bool
{
	if ($period % count($instructions) != 0) {
		// cannot be a loop if number of steps is not a multiple of the number of instructions
		return false;
	}

	if (!isExit($exit)) {
		return false;
	}

	$position = $exit;
	for ($i = 0; $i < $period; $i++) {
		$position = walk($position, $instructions[$i % count($instructions)], $network);
	}

	// it's a loop if it exits on the same exit point.
	return ($position == $exit);
}

function computeExits($position, $instructions, $network): false|int
{
	$length = count($instructions);
	$visited = [];
	$step = 0;
	$coordinate = $position;
	while (true) {
		$reducedStep = $step % $length;
		$visited[] = $coordinate . '|' . $reducedStep;
		$direction = $instructions[$reducedStep];
		$coordinate = walk($coordinate, $direction, $network);
		$step++;

		if (isExit($coordinate)) {
			if (VERBOSE && DEBUG_MODE) {
				echo $position . ' exits on ' . $coordinate . ' after ' . $step . ' steps.' . PHP_EOL;
			}

			// check the exit loops with the same number of steps (sufficient condition)
			return checkLoopExit($coordinate, $instructions, $network, $step) ? $step : false;
		}
		$visitedKey = $coordinate . '|' . ($step % $length);
		if (in_array($visitedKey, $visited)) {
			if (VERBOSE && DEBUG_MODE) {
				echo $step . ' > Coordinate is repeated: [' . $visitedKey . '].' . PHP_EOL;
				echo 'EXITING LOOP' . PHP_EOL;
			}

			break;
		}
	}

	return false;
}

function walk($coordinate, $direction, $network): string
{
	return $network[$coordinate][DIRECTIONS[$direction]];
}

[$instructions, $network] = parseInput($input);
$length = count($instructions);
$loop = $found = false;
$steps = 0;
$visited = [];
$startingPoints = getStartingPoints($network);
if (VERBOSE) {
	echo 'Found ' . count($startingPoints) . ' starting points.' . PHP_EOL;
	if (DEBUG_MODE) {
		echo '..and ' . count(getEndingPoints($network)) . ' ending points.' . PHP_EOL;
		echo 'Instructions are looping on ' . $length . ' directions.' . PHP_EOL;
		echo 'initial position is: (' . PHP_EOL
			. "\t" . implode(",\n\t", $startingPoints)
			. PHP_EOL . ')' . PHP_EOL;
	}
}
$answer = 1;
foreach ($startingPoints as $startingPoint) {
	if ($steps = computeExits($startingPoint, $instructions, $network)) {
		$answer = gmp_lcm($answer, $steps);
	} else {
		if (!SILENT) {
			echo 'Starting point ' . $startingPoint . ' never exits "correctly"... Is this a bug???' . PHP_EOL;
		}

		$answer = 0;
		break;
	}
}


if (SILENT) {
	echo $answer . PHP_EOL;
} else {
	echo 'Sand storm is exited after ' . $answer . ' steps.' . PHP_EOL;
}


include __DIR__ . DIRECTORY_SEPARATOR . 'template_exit.php';
