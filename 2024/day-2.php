<?php

include __DIR__ . DIRECTORY_SEPARATOR . 'template_start.php';

$input = TEST_MODE
	? <<<STR
7 6 4 2 1
1 2 7 8 9
9 7 6 2 1
1 3 2 4 5
8 6 4 4 1
1 3 6 7 9
STR
	: file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'day-2.txt');

// remove leading and trailing spaces or blank lines
$input = trim($input);

function parseLine($line): array {
    $line = trim($line);
    return explode(' ', $line);
}
function isSafe($sequence): bool {
    $direction = false;
    $previous = null;
    $isSafe = true;
    foreach ($sequence as $number) {
        if ($previous === null) {
            $previous = $number;
            continue;
        }
        if ($direction === false) {
            $direction = ($number > $previous) ? 'up' : 'down';
        }
        $diff = $direction === 'up' ? ($number - $previous) : ($previous - $number);
        if ($diff < 1 || $diff > 3) {
            return false;
        }

        $previous = $number;
    }

    return $isSafe;
}

function isReallySafe($sequence): bool {
    $direction = false;
    $previous = null;
    $isSafe = true;
    foreach ($sequence as $k => $number) {
        if ($previous === null) {
            $previous = $number;
            continue;
        }
        if ($direction === false) {
            $direction = ($number > $previous) ? 'up' : 'down';
        }
        $diff = $direction === 'up' ? ($number - $previous) : ($previous - $number);
        if ($diff < 1 || $diff > 3) {
            // sequence looks unsafe at this point.
            // test if it's safe removing previous or current value:
            $sequence1 = $sequence;
            array_splice($sequence1, $k, 1);
            if (isSafe($sequence1)) {
                return true;
            }
            $sequence2 = $sequence;
            array_splice($sequence2, $k-1, 1);

            if (isSafe($sequence2)){
                return true;
            }
            // or maybe it was the first value the problem?
            array_shift($sequence);
            return isSafe($sequence);
        }

        $previous = $number;
    }

    return $isSafe;
}
$count = $realCount = 0;
foreach (explode("\n", $input) as $k => $line) {
    $numbers = parseLine($line);
    if (isSafe($numbers)) {
        $count++;
        $realCount++;
    } elseif (isReallySafe($numbers)) {
        $realCount++;
    }
}

if (SILENT) {
	echo PART2 ? $count : $realCount . PHP_EOL;
} else {
	echo 'Total of safe reports is ' . $count . '.' . PHP_EOL
	. 'Total of really safe reports is ' . $realCount . '.' . PHP_EOL;
}

include __DIR__ . DIRECTORY_SEPARATOR . 'template_exit.php';
