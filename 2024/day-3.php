<?php

include __DIR__ . DIRECTORY_SEPARATOR . 'template_start.php';

$input = TEST_MODE
	? <<<STR
xmul(2,4)&mul[3,7]!^don't()_mul(5,5)+mul(32,64](mul(11,8)undo()?mul(8,5))
STR
	: file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'day-3.txt');

// remove leading and trailing spaces or blank lines
$input = trim($input);

// part 1:
$sum = 0;
if (preg_match_all('/mul\((\d+),(\d+)\)/', $input, $matches)) {
    for ($i = 0; $i < count($matches[0]); $i++) {
        $sum += $matches[1][$i] * $matches[2][$i];
    }
}

$conditionalSum = 0;
$chunks = explode(
    'do',
    str_replace("\n", '', $input) // make sure to process all the lines of the input
);
$ignore = false;
foreach ($chunks as $chunk) {
    if (preg_match('/^n\'t\(\).*$/', $chunk)) {
        // if it starts with "[do]n't()" instructions should be ignored
        $ignore = true;
    } elseif (preg_match('/^\(\).*$/', $chunk)) {
        // if it starts with "[do]()" instructions should be re-enabled
        $ignore = false;
    }
    if ($ignore) {
        continue;
    }
    if (preg_match_all('/mul\((\d+),(\d+)\)/', $chunk, $matches)) {
        for ($i = 0; $i < count($matches[0]); $i++) {
            $conditionalSum += $matches[1][$i] * $matches[2][$i];
        }
    }
}

if (SILENT) {
	echo PART2 ? $conditionalSum : $sum . PHP_EOL;
} else {
	echo (PART2
        ? 'The result of conditional multiplications is ' . $conditionalSum
        : 'The result of multiplications is ' . $sum
        ) . '.' . PHP_EOL;
}

include __DIR__ . DIRECTORY_SEPARATOR . 'template_exit.php';
