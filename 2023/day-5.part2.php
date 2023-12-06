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

	if (!preg_match_all('/(\d+)\s+(\d+)/', $matches[1], $pairs)) {
		if (!SILENT) {
			echo 'ERROR: misformed seeds in ' . var_export($matches[1], true) . PHP_EOL;
		}

		return false;
	}

	foreach (array_keys($pairs[1]) as $i) {
		$seeds[] = [(int)$pairs[1][$i], (int)$pairs[1][$i] + (int)$pairs[2][$i] - 1];
	}

	return rearrangeSegments($seeds);
}

function rearrangeSegments($segments): array
{
	// given an array of segments [ [a, b], [c, d], [e, f] ]
	// return an ordered array of disjointed segments.

	// first sort segments by starting points:
	$startingPoints = array_column($segments, 0);
	array_multisort($startingPoints, SORT_ASC, $segments);

	// now join segments that overlap
	$rearranged = [];
	$lastEnding = false;
	foreach ($segments as $segment) {
		if ($lastEnding === false || $segment[0] > $lastEnding) {
			// new segment is disjoint from previous: add it to the list
			$rearranged[] = $segment;
			$lastEnding = $segment[1];
		} else {
			// merge new segment by changing ending
			if ($segment[1] > $lastEnding) {
				$rearranged[count($rearranged) - 1][1] = $segment[1];
				$lastEnding = $segment[1];
			}
		}
	}

	return $rearranged;
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
	if (VERBOSE && DEBUG_MODE) {
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
		// $map['map'][] = ['orig' => (int)$matches[2], 'range' => (int)$matches[3], 'target' => (int)$matches[1]];
		$map['map'][] = [
			'from' => (int)$matches[2],
			'to' => (int)$matches[2] + (int)$matches[3] - 1,
			'move' => (int)$matches[1] - (int)$matches[2]
		];
	}

	return $map;
}

function applyTranslation($segments, $from, $to, $move): array
{
	$ultraVerbose = VERBOSE && DEBUG_MODE && TEST_MODE && false;
	$mappedSegments = [];
	$unmappedSegments = [];
	foreach ($segments as $segment) {
		[$segmentStart, $segmentEnd] = $segment;
		echo 'apply translation: ' . $from . '-' . $to . ' -> ' . ($move > 0 ? '+' : '') . $move . PHP_EOL;
		// segment ends after translation starts and segment starts before the translation begins
		if ($segmentEnd >= $from && $segmentStart <= $to) {
			// 1. segment is included in translation
			if ($segmentStart >= $from && $segmentEnd <= $to) {
				$newSegment = [
					$segmentStart + $move,
					$segmentEnd + $move,
				];
				if ($ultraVerbose) {
					echo '1) included: [' . $segmentStart . ',' . $segmentEnd . ']' . PHP_EOL
						. 'mapped: [' . $newSegment[0] . ',' . $newSegment[1] . ']' . PHP_EOL;
				}
				if (computeSegmentLength($newSegment) !== computeSegmentLength($segment)) {
					if (!SILENT) {
						echo 'ERROR: invalid translation: [' . $segmentStart . ', ' . $segmentEnd . '] > [' . $newSegment[0] . ',' . $newSegment[1] . ']' . PHP_EOL;
					}
				}
				$mappedSegments[] = $newSegment;
				// 2. segment starts before, ends before
			} elseif ($segmentStart < $from && $segmentEnd <= $to) {
				$newSegment1 = [
					$segmentStart,
					$from - 1
				];
				$newSegment2 = [
					$from + $move,
					$segmentEnd + $move
				];
				if ((
						computeSegmentLength($newSegment1) + computeSegmentLength($newSegment2)
					) !== computeSegmentLength($segment)) {
					if (!SILENT) {
						echo 'ERROR: invalid translation: [' . $segmentStart . ', ' . $segmentEnd . '] > [' . $newSegment1[0] . ',' . $newSegment1[1] . '] U [' . $newSegment2[0] . ',' . $newSegment2[1] . '] U [' . PHP_EOL;
					}
				}
				if ($ultraVerbose) {
					echo '2) before: [' . $segmentStart . ',' . $segmentEnd . ']' . PHP_EOL
						. 'mapped 1: [' . $newSegment1[0] . ',' . $newSegment1[1] . ']' . PHP_EOL
						. 'mapped 2: [' . $newSegment2[0] . ',' . $newSegment2[1] . ']' . PHP_EOL;
				}
				$unmappedSegments[] = $newSegment1;
				$mappedSegments[] = $newSegment2;
				// 3. segment starts after, ends after
			} elseif ($segmentStart >= $from && $segmentEnd > $to) {
				$newSegment1 = [
					$segmentStart + $move,
					$to + $move
				];
				$newSegment2 = [$to + 1, $segmentEnd];
				if (computeSegmentLength($newSegment1) + computeSegmentLength(
						$newSegment2
					) !== computeSegmentLength($segment)) {
					if (!SILENT) {
						echo 'ERROR: invalid translation: [' . $segmentStart . ', ' . $segmentEnd . '] > [' . $newSegment1[0] . ',' . $newSegment1[1] . '] U [' . $newSegment2[0] . ',' . $newSegment2[1] . ']' . PHP_EOL;
					}
				}
				if ($ultraVerbose) {
					echo '3) after: [' . $segmentStart . ',' . $segmentEnd . ']' . PHP_EOL
						. 'mapped 1: [' . $newSegment1[0] . ',' . $newSegment1[1] . ']' . PHP_EOL
						. 'mapped 2: [' . $newSegment2[0] . ',' . $newSegment2[1] . ']' . PHP_EOL;
				}
				$mappedSegments[] = $newSegment1;
				$unmappedSegments[] = $newSegment2;
				// 4. segment includes translation:
			} else {
				$newSegment1 = [$segmentStart, $from - 1];
				$newSegment2 = [
					$from + $move,
					$to + $move
				];
				$newSegment3 = [$to + 1, $segmentEnd];
				if (
					(
						computeSegmentLength($newSegment1)
						+ computeSegmentLength($newSegment2)
						+ computeSegmentLength($newSegment3)
					) !== computeSegmentLength($segment)) {
					if (!SILENT) {
						echo 'ERROR: invalid translation: [' . $segmentStart . ', ' . $segmentEnd . '] > [' . $newSegment1[0] . ',' . $newSegment1[1] . '] U [' . $newSegment2[0] . ',' . $newSegment2[1] . '] U [' . $newSegment3[0] . ',' . $newSegment3[1] . ']' . PHP_EOL;
					}
				}
				if ($ultraVerbose) {
					echo '4) includes: [' . $segmentStart . ',' . $segmentEnd . ']' . PHP_EOL
						. 'mapped 1: [' . $newSegment1[0] . ',' . $newSegment1[1] . ']' . PHP_EOL
						. 'mapped 2: [' . $newSegment2[0] . ',' . $newSegment2[1] . ']' . PHP_EOL
						. 'mapped 3: [' . $newSegment3[0] . ',' . $newSegment3[1] . ']' . PHP_EOL;
				}
				$unmappedSegments[] = $newSegment1;
				$mappedSegments[] = $newSegment2;
				$unmappedSegments[] = $newSegment3;
			}
		} else {
			if ($ultraVerbose) {
				echo '0) untouched: [' . $segmentStart . ',' . $segmentEnd . ']' . PHP_EOL;
			}
			$unmappedSegments[] = $segment;
		}
	}

	return [
		'mapped' => rearrangeSegments($mappedSegments),
		'unmapped' => rearrangeSegments($unmappedSegments),
	];
}

function getMappedSegments($segments, $map): array
{
	if (VERBOSE && DEBUG_MODE) {
		echo ' --> BEFORE MAPPING <--' . PHP_EOL;
		dumpSegments($segments);
	}
	$mappedResult = [];
	foreach ($segments as $segment) {
		// for each segment
		$translatingSegments = [$segment];
		// apply single translation
		foreach ($map['map'] as $translation) {
			// but each time only on the unmapped segments left by previous translation
			$newSegments = applyTranslation(
				$translatingSegments,
				$translation['from'],
				$translation['to'],
				$translation['move']
			);
			foreach ($newSegments['mapped'] as $newMappedSegment) {
				$mappedResult[] = $newMappedSegment;
			}
			$translatingSegments = $newSegments['unmapped'];
			if (VERBOSE && DEBUG_MODE) {
				echo '--> AFTER TRANSLATION <--' . PHP_EOL;
				echo 'MAPPED:' . PHP_EOL;
				dumpSegments($mappedResult);
				echo 'STILL UNMAPPED:' . PHP_EOL;
				dumpSegments($translatingSegments);
			}
		}
		foreach ($translatingSegments as $unmapped) {
			$mappedResult[] = $unmapped;
		}
	}

	return rearrangeSegments($mappedResult);
}

function computeSegmentLength($segment): int
{
	if ($segment[1] < $segment[0]) {
		if (!SILENT) {
			echo 'ERROR: invalid segment: [' . $segment[0] . ', ' . $segment[1] . ']' . PHP_EOL;
		}
		return false;
	}

	return ($segment[1] - $segment[0] + 1);
}

function computeTotalLength($segments): int
{
	$totalLength = 0;
	foreach ($segments as $segment) {
		$totalLength += computeSegmentLength($segment);
	}
	return $totalLength;
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

function dumpSegments($segments)
{
	$count = count($segments);
	if ($count == 0) {
		echo '0/0 (no segment)' . PHP_EOL;
	}
	foreach ($segments as $_k => $segment) {
		$length = computeSegmentLength($segment);
		echo ($_k + 1) . '/' . $count . ' [' . $segment[0] . ',' . $segment[1] . '] ' . $length . PHP_EOL;
	}
}

function getLocationSegmentsFromSeedSegments($seedSegments, $maps): array
{
	$mappedSegments = $seedSegments;
	$length = computeTotalLength($mappedSegments);
	if (VERBOSE && DEBUG_MODE) {
		echo 'Initial length of seeds: ' . $length . PHP_EOL;
	}
	foreach (MAPPINGS as $source => $dest) {
		if (VERBOSE && DEBUG_MODE) {
			echo 'Apply: ' . $source . ' --> ' . $dest . PHP_EOL
				. 'total segments: ' . count($mappedSegments) . PHP_EOL;
		}

		$mappedSegments = getMappedSegments($mappedSegments, getMap($source, $dest, $maps));
		if (computeTotalLength($mappedSegments) !== $length) {
			die(
				'FATAL ERROR - lengths do not match' . PHP_EOL
				. 'before: ' . $length . ' --- after: ' . computeTotalLength($mappedSegments) . PHP_EOL
			);
		}
	}

	return $mappedSegments;
}

// get number range from input
$range = getRange($input);

$blocks = explode("\n\n", $input);

// get seeds from first line
$firstLine = trim(array_shift($blocks));
$seeds = getSeeds($firstLine);
if (VERBOSE || DEBUG_MODE) {
	echo 'actual seeds are ' . computeTotalLength($seeds) . PHP_EOL;
}

// getMaps
$maps = [];
foreach ($blocks as $block) {
	$maps[] = parseMaps(trim($block));
}

$location = [];
$location = getLocationSegmentsFromSeedSegments($seeds, $maps);
if (TEST_MODE && (VERBOSE || DEBUG_MODE)) {
	echo 'computed location segment: ' . var_export($location, true) . PHP_EOL;
}

if (SILENT) {
	echo $location[0][0] . PHP_EOL;
} else {
	echo 'lowest location is ' . $location[0][0] . PHP_EOL;
}

include __DIR__ . DIRECTORY_SEPARATOR . 'template_exit.php';
