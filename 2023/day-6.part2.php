<?php

include __DIR__ . DIRECTORY_SEPARATOR . 'template_start.php';

$input = TEST_MODE
	? <<<STR
Time:      7  15   30
Distance:  9  40  200
STR
	: <<<STR
Time:      7  15   31
Distance:  9  40  201
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
	$raceTime = str_replace(' ', '', $matches[1]);

	if (!preg_match('/^Distance:\s+([\d ]+)$/', $distanceLine, $matches)) {
		if (!SILENT) {
			echo 'ERROR: misformed DISTANCE line ' . var_export($distanceLine, true) . PHP_EOL;
		}

		return false;
	}
	$distance = str_replace(' ', '', $matches[1]);

	return [
		'time' => (int)$raceTime,
		'dist' => (int)$distance,
	];
}

$race = parseInput($input);
$raceTime = (int)$race['time'];
$distance = (int)$race['dist'];
// 1) a winning way is when your boat travels more than the record distance
// 2) the distance you travel, is a function of the time holding the button:
// it's the formula "speed gathered x time left"
// but "speed gathered" (in mm/ms) is equal to the time (in ms) holding the button
// and "time left" (in ms) is equal to "total time - time holding the button"
// so really, the distance traveled is given by:
// `$holdingTime * (RACETIME - $holdingTime)`
// 3) Now a winning way is when `$holdingTime * (RACETIME - $holdingTime) > RECORD DISTANCE`
// 4) this function (of the variable holdingTime) is a parabole pointing up: y = A·x - x^2
// it reaches its peak for x = A / 2;
// furthermore, solving `holdingTime * (RACETIME - holdingTime) > DISTANCE`
// is equivalent to solving `holdingTime^2 - holdingTime·RACETIME < -DISTANCE`
// <=> `(holdingTime - RACETIME/2)^2 < RACETIME^2 /4 - DISTANCE`
// NOTE: [there are no real solutions if RACETIME^2 /4 - DISTANCE is negative or if DISTANCE is greater than RACETIME^2/4]
if ($distance > ($raceTime * $raceTime / 4)) {
	if (!SILENT) {
		echo 'there is no way to win the race' . PHP_EOL;
		if (VERBOSE) {
			echo 'best you can do is press the button for ' . ($raceTime / 2) . 'ms' . PHP_EOL
				. 'and travel ' . ($raceTime / 2) ** 2 . 'mm' . PHP_EOL;
		}
	}
	$winningWays = 0;
} else {
// <=> `-sqrt(RACETIME^2 /4 - DISTANCE) < (holdingTime - RACETIME/2) < sqrt(RACETIME^2 /4 - DISTANCE)`
// <=> `RACETIME/2 - sqrt(RACETIME^2 /4 - DISTANCE) < holdingTime < RACETIME/2 + sqrt(RACETIME^2 /4 - DISTANCE)`
// (given that times are all integers:) `ceil(RACETIME/2 - sqrt(RACETIME^2 /4 - DISTANCE)) <= holdingTime <= floor(RACETIME/2 + sqrt(RACETIME^2 /4 - DISTANCE))`
	$winningWays = floor($raceTime / 2 + sqrt($raceTime * $raceTime / 4 - $distance))
		- ceil($raceTime / 2 - sqrt($raceTime * $raceTime / 4 - $distance))
		+ 1;
	if (
		ceil($raceTime / 2 - sqrt($raceTime * $raceTime / 4 - $distance))
		==
		($raceTime / 2 - sqrt($raceTime * $raceTime / 4 - $distance))
	) {
		if (!SILENT) {
			echo 'this case is a tie, not a clear victory' . PHP_EOL;
		}
		$winningWays--;
	}
	if (
		floor($raceTime / 2 + sqrt($raceTime * $raceTime / 4 - $distance))
		==
		($raceTime / 2 + sqrt($raceTime * $raceTime / 4 - $distance))
	) {
		if (!SILENT) {
			echo 'this case is a tie not a clear victory' . PHP_EOL;
		}
		$winningWays--;
	}
}

if (SILENT) {
	echo $winningWays . PHP_EOL;
} else {
	echo 'Number of ways you can beat the record: ' . $winningWays . PHP_EOL;
}

include __DIR__ . DIRECTORY_SEPARATOR . 'template_exit.php';
