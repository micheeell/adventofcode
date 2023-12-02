<?php

include __DIR__ . DIRECTORY_SEPARATOR . 'template_start.php';

$input = TEST_MODE ?
    <<<STR
two1nine
eightwothree
abcone2threexyz
xtwone3four
4nineeightseven2
zoneight234
7pqrstsixteen
STR
: file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'day-1.txt');

const INTEGER_ARR = [
    'one' => 1,
    'two' => 2,
    'three' => 3,
    'four' => 4,
    'five' => 5,
    'six' => 6,
    'seven' => 7,
    'eight' => 8,
    'nine' => 9
];

function getFirstDigit($str): false|int
{
    // regex is: "find one of those (digit or word), no matter what lies after"
    if (!preg_match('/(\d|(one|two|three|four|five|six|seven|eight|nine)).*$/', $str, $matches)) {
        if (VERBOSE) {
            echo 'Error in string: no match found for ' . __METHOD__ . PHP_EOL;
        }
        return false;
    }

    if ($matches[1] == (int)$matches[1]) {
        return (int)$matches[1];
    }

    return INTEGER_ARR[$matches[1]];
}

function getLastDigit($str): false|int
{
    // regex is: "find one of those (digit or word), no matter what you may encounter before"
    if (!preg_match('/^.*(\d|(one|two|three|four|five|six|seven|eight|nine))/', $str, $matches)) {
        if (VERBOSE) {
            echo 'Error in string: no match found for ' . __METHOD__ . PHP_EOL;
        }
        return false;
    }

    if ($matches[1] == (int)$matches[1]) {
        return (int)$matches[1];
    }

    return INTEGER_ARR[$matches[1]];
}

$sum = 0;
foreach (explode("\n", $input) as $k => $line) {
    $caliber = getFirstDigit($line) . getLastDigit($line);
    $sum += (int)$caliber;
    if (VERBOSE) {
        echo 'caliber #' . $k . ' : ' . $caliber
            . PHP_EOL . 'sum : ' . $sum . PHP_EOL;
    }
}

if (SILENT) {
    echo $sum . PHP_EOL;
} else {
    echo 'Entire calibration is ' . $sum . '.' . PHP_EOL;
}

include __DIR__ . DIRECTORY_SEPARATOR . 'template_exit.php';
