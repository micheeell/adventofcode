<?php

include __DIR__ . DIRECTORY_SEPARATOR . 'template_start.php';

$input = TEST_MODE
	? <<<STR
Game 1: 3 blue, 4 red; 1 red, 2 green, 6 blue; 2 green
Game 2: 1 blue, 2 green; 3 green, 4 blue, 1 red; 1 green, 1 blue
Game 3: 8 green, 6 blue, 20 red; 5 blue, 4 red, 13 green; 5 green, 1 red
Game 4: 1 green, 3 red, 6 blue; 3 green, 6 red; 3 green, 15 blue, 14 red
Game 5: 6 red, 1 blue, 3 green; 2 blue, 1 red, 2 green
STR
	: file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'day-2.txt');

// remove leading and trailing spaces or blank lines
$input = trim($input);

function parseSubsets($str): false|array
{
	$subset = [];
	$groups = explode(',', $str);
	foreach ($groups as $group) {
		if (!preg_match('/^(\d+) (.*)/', trim($group), $matches)) {
			if (!SILENT) {
				echo 'Error in game parsing: misformed record ' . $str . PHP_EOL;
			}

			return false;
		}
		if (isset($subset[$matches[2]])) {
			$subset[$matches[2]] += (int)$matches[1];
		} else {
			$subset[$matches[2]] = (int)$matches[1];
		}
	}

	return $subset;
}

function parseGame($str, $line = 0): false|array
{
	if (!preg_match('/^Game (\d+)\: (.*)/', $str, $matches)) {
		if (!SILENT) {
			echo 'Error in game parsing (L. ' . $line . ') : misformed record ' . var_export($str, true) . PHP_EOL;
		}

		return false;
	}
	$gameId = $matches[1];
	$subsetsString = $matches[2];

	$game = [
		'ID' => $gameId,
		'sets' => [],
	];
	foreach (explode(';', $subsetsString) as $subset) {
		$game['sets'][] = parseSubsets($subset);
	}

	return $game;
}

const BAG = [
	'red' => 12,
	'green' => 13,
	'blue' => 14,
];

function isPossible($game): bool
{
	foreach ($game['sets'] as $subset) {
		foreach (BAG as $cube => $max) {
			if (!isset($subset[$cube])) {
				continue;
			}

			if ($subset[$cube] > $max) {
				return false;
			}
		}
	}

	if (DEBUG_MODE) {
		echo 'game ' . $game['ID'] . ' is possible.' . PHP_EOL;
	}

	return true;
}

$sum = 0;
foreach (explode("\n", $input) as $k => $line) {
	$game = parseGame($line, $k);
	$isPossible = isPossible($game);
	if ($isPossible) {
		$sum += $game['ID'];
	}
	if (VERBOSE || DEBUG_MODE) {
		echo 'game #' . $game['ID'] . ' - ' . ($isPossible ? 'Possible' : 'Impossible') . ' L.' . __LINE__
			. PHP_EOL . 'sum : ' . $sum . PHP_EOL;
	}
}

if (SILENT) {
	echo $sum . PHP_EOL;
} else {
	echo 'The sum of all possible is ' . $sum . PHP_EOL;
}

include __DIR__ . DIRECTORY_SEPARATOR . 'template_exit.php';
