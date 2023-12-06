<?php

include __DIR__ . DIRECTORY_SEPARATOR . 'template_start.php';

$input = TEST_MODE
	? <<<STR
Time:      7  15   30
Distance:  9  40  200
STR
	: <<<STR
Time:        48     93     85     95
Distance:   296   1928   1236   1391
STR;

// remove leading and trailing spaces or blank lines
$input = trim($input);

function parseInput($input): false|array
{
	$lines = explode("\n", $input);
	if (count($lines) !== 2) {
		if (!SILENT) {
			echo 'ERROR: misformed input - expected 2 lines ' . var_export($input, true) . PHP_EOL;
		}

		return false;
	}
	[$timeLine, $distanceLine] = $lines;
	if (!preg_match('/^Time:\s+([\d ]+)$/', $timeLine, $matches)) {
		if (!SILENT) {
			echo 'ERROR: misformed TIME line ' . var_export($timeLine, true) . PHP_EOL;
		}

		return false;
	}
	if (!preg_match_all('/(\d+)/', $matches[1], $times)) {
		if (!SILENT) {
			echo 'ERROR: no times found!!! ' . var_export($matches[1], true) . PHP_EOL;
		}

		return false;
	}
	$timesArr = $times[1];
	if (!preg_match('/^Distance:\s+([\d ]+)$/', $distanceLine, $matches)) {
		if (!SILENT) {
			echo 'ERROR: misformed DISTANCE line ' . var_export($distanceLine, true) . PHP_EOL;
		}

		return false;
	}
	if (!preg_match_all('/(\d+)/', $matches[1], $distances)) {
		if (!SILENT) {
			echo 'ERROR: no distances found!!! ' . var_export($matches[1], true) . PHP_EOL;
		}

		return false;
	}
	$distancesArr = $distances[1];
	if (count($distancesArr) !== count($timesArr)) {
		if (!SILENT) {
			echo 'ERROR: times and distances do not match: ' . count($timesArr) . ' times vs ' . count(
					$distancesArr
				) . ' times' . PHP_EOL;
		}

		return false;
	}
	$parsed = [];
	foreach ($distancesArr as $_k => $record) {
		$parsed[] = [
			'time' => $timesArr[$_k],
			'dist' => $record,
		];
	}

	return $parsed;
}

function computeDistance($speedTime, $raceTime): int
{
	return $speedTime * ($raceTime - $speedTime);
}

$races = parseInput($input);
$product = 1;
foreach ($races as $race) {
	$raceTime = (int)$race['time'];
	$winningWays = 0;
	for ($i = 1; $i < $raceTime; $i++) {
		$dist = computeDistance($i, $raceTime);
		if ($dist > (int)$race['dist']) {
			$winningWays++;
		}
	}
	$product *= $winningWays;
}

if (SILENT) {
	echo $product . PHP_EOL;
} else {
	echo 'Number of ways you can beat the record: ' . $product . PHP_EOL;
}

include __DIR__ . DIRECTORY_SEPARATOR . 'template_exit.php';
