<?php

include __DIR__ . DIRECTORY_SEPARATOR . 'template_start.php';

$input = TEST_MODE
	? <<<STR
seeds: 79 14 55 13

seed-to-soil map:
50 98 2
52 50 48

soil-to-fertilizer map:
0 15 37
37 52 2
39 0 15

fertilizer-to-water map:
49 53 8
0 11 42
42 0 7
57 7 4

water-to-light map:
88 18 7
18 25 70

light-to-temperature map:
45 77 23
81 45 19
68 64 13

temperature-to-humidity map:
0 69 1
1 0 69

humidity-to-location map:
60 56 37
56 93 4
STR
	: file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'day-5.txt');

// remove leading and trailing spaces or blank lines
$input = trim($input);

const MAPPINGS = [
	'seed' => 'soil',
	'soil' => 'fertilizer',
	'fertilizer' => 'water',
	'water' => 'light',
	'light' => 'temperature',
	'temperature' => 'humidity',
	'humidity' => 'location',
];

function getSeeds($firstLine): false|array
{
	if (!preg_match('/^seeds: ([\d ]+)$/', $firstLine, $matches)) {
		if (!SILENT) {
			echo 'ERROR: misformed first line, no seeds found in ' . var_export($firstLine, true) . PHP_EOL;
		}

		return false;
	}

	$seeds = [];
	foreach (explode(' ', $matches[1]) as $seed) {
		$seeds[] = (int)$seed;
	}

	return $seeds;
}

function getRange($input): int
{
	$max = 0;
	foreach (explode("\n", $input) as $line) {
		if (preg_match('/^(\d+)\s+(\d+)\s+(\d+)$/', $line, $matches)) {
			$max = max([$max, (int)$matches[1] + (int)$matches[3], (int)$matches[2] + (int)$matches[3]]);
		}
	}

	return $max;
}

function parseMaps($block): false|array
{
	$lines = explode("\n", $block);
	$firstLine = array_shift($lines);
	if (!preg_match('/^([a-z]+)-to-([a-z]+) map:$/', $firstLine, $matches)) {
		if (!SILENT) {
			echo 'ERROR: misformed map declaration: ' . $firstLine . PHP_EOL;
		}

		return false;
	}
	$source = $matches[1];
	$dest = $matches[2];
	if (VERBOSE) {
		echo 'Map: ' . $source . ' --> ' . $dest . PHP_EOL;
	}

	$map = [
		'source' => $source,
		'dest' => $dest,
		'map' => [],
	];
	foreach ($lines as $k => $line) {
		if (!preg_match('/^(\d+)\s+(\d+)\s+(\d+)$/', $line, $matches)) {
			if (!SILENT) {
				echo 'ERROR: unsupported mapping ' . $line . '(' . $source . '>>' . $dest . ')' . PHP_EOL;
			}

			return $map;
		}
		$map['map'][] = ['orig' => (int)$matches[2], 'range' => (int)$matches[3], 'target' => (int)$matches[1]];
	}

	return $map;
}

function getMappedNumber($number, $map): int
{
	foreach ($map['map'] as $translation) {
		if ($number >= $translation['orig'] && $number < ($translation['orig'] + $translation['range'])) {
			return ($translation['target'] + $number - $translation['orig']);
		}
	}

	return $number;
}

function getMap($source, $dest, $maps): false|array
{
	foreach ($maps as $_map) {
		if ($_map['dest'] == $dest) {
			if ($_map['source'] !== $source) {
				if (!SILENT) {
					echo 'ERROR: Incorrect map request:' . PHP_EOL
						. $dest . ' can only be mapped from ' . $_map['source'] . ' (' . $source . ' requested)' . PHP_EOL;
				}
				return false;
			}

			return $_map;
		}
	}

	return false;
}

function getLocationNumberFromSeedNumber($seedNumber, $maps): int
{
	if (DEBUG_MODE && $seedNumber == 14) {
		echo 'Seed ' . $seedNumber . PHP_EOL;
	}
	$mappedNumber = $seedNumber;
	foreach (MAPPINGS as $source => $dest) {
		$mappedNumber = getMappedNumber($mappedNumber, getMap($source, $dest, $maps));
		if (DEBUG_MODE && $seedNumber == 14) {
			echo $dest . ' = ' . $mappedNumber . PHP_EOL;
		}
	}

	return $mappedNumber;
}

// get number range from input
$range = getRange($input);

$blocks = explode("\n\n", $input);

// get seeds from first line
$firstLine = trim(array_shift($blocks));
$seeds = getSeeds($firstLine);

$range = max(array_merge([$range], $seeds));
if (VERBOSE || DEBUG_MODE) {
	echo 'computed range is ' . $range . PHP_EOL;
}

// getMaps
$maps = [];
foreach ($blocks as $block) {
	$maps[] = parseMaps(trim($block));
}

$min = $range;
foreach ($seeds as $seed) {
	$location = getLocationNumberFromSeedNumber($seed, $maps);
	if (TEST_MODE && (VERBOSE || DEBUG_MODE)) {
		echo 'seed ' . $seed . ' has computed location ' . $location . PHP_EOL;
	}
	if ($location < $min) {
		if (VERBOSE && DEBUG_MODE) {
			echo 'new lowest location found!' . PHP_EOL
				. 'seed ' . $seed . ' with location ' . $location . PHP_EOL;
		}
		$min = $location;
	}
}

if (SILENT) {
	echo $min . PHP_EOL;
} else {
	echo 'lowest location is ' . $min . PHP_EOL;
}

include __DIR__ . DIRECTORY_SEPARATOR . 'template_exit.php';
