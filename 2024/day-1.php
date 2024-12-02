<?php

include __DIR__ . DIRECTORY_SEPARATOR . 'template_start.php';

$input = TEST_MODE
	? <<<STR
3   4
4   3
2   5
1   3
3   9
3   3
STR
	: file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'day-1.txt');

// remove leading and trailing spaces or blank lines
$input = trim($input);

function parseInput($input): array {
    $parsed = [
        'left' => [],
        'right' => []
    ];
    foreach (explode("\n", $input) as $k => $line) {
        if (!preg_match_all('/(\d+)\s+(\d+)/', $line, $matches,)) {
            if (VERBOSE) {
                echo 'No number found in line #' . $k . ' : ' . $line . PHP_EOL;
            }
            continue;
        }

        $parsed['left'][] = $matches[1][0];
        $parsed['right'][] = $matches[2][0];
    }

    return $parsed;
}
function parseSimilarityCoeff($rightList): array {
    $coeff = [];
    foreach ($rightList as $value) {
        if (!isset($coeff[$value])) {
            $coeff[$value] = 0;
        }
        $coeff[$value]++;
    }

    return $coeff;
}

$parsed = parseInput($input);
$list1 = $parsed['left'];
$list2 = $parsed['right'];
sort($list1);
sort($list2);
$distance = 0;
foreach ($list1 as $k => $position) {
    $distance += ($list2[$k] - $list1[$k]) > 0
        ? ($list2[$k] - $list1[$k])
        : ($list1[$k] - $list2[$k]);
}
$similarity = 0;
$coeffArray = parseSimilarityCoeff($list2);
foreach ($list1 as $position) {
    $similarity += $position * ($coeffArray[$position] ?? 0);
}

if (SILENT) {
	echo (PART2 ? $similarity : $distance) . PHP_EOL;
} else {
	echo 'Total distance between 2 lists is ' . $distance . '.' . PHP_EOL
	    . 'Similarity is ' . $similarity . '.' . PHP_EOL;
}

include __DIR__ . DIRECTORY_SEPARATOR . 'template_exit.php';
