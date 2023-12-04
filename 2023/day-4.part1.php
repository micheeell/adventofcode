<?php

include __DIR__ . DIRECTORY_SEPARATOR . 'template_start.php';

$input = TEST_MODE
	? <<<STR
Card 1: 41 48 83 86 17 | 83 86  6 31 17  9 48 53
Card 2: 13 32 20 16 61 | 61 30 68 82 17 32 24 19
Card 3:  1 21 53 59 44 | 69 82 63 72 16 21 14  1
Card 4: 41 92 73 84 69 | 59 84 76 51 58  5 54 83
Card 5: 87 83 26 28 32 | 88 30 70 12 93 22 82 36
Card 6: 31 18 13 56 72 | 74 77 10 23 35 67 36 11
STR
	: file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'day-4.txt');

// remove leading and trailing spaces or blank lines
$input = trim($input);

function parseInput($fileContent): array
{
	$lines = explode("\n", $fileContent);
	$scratchcards = [];
	foreach ($lines as $x => $line) {
		[$cardId, $winningNumbers, $numbers] = parseLine($line);
		$scratchcards[$cardId] = [
			'winningNumbers' => $winningNumbers,
			'numbers' => $numbers,
		];
		if (VERBOSE && DEBUG_MODE) {
			echo 'ScratchCard #' . $cardId . PHP_EOL . 'winnings: ' . count($winningNumbers)
				. PHP_EOL . 'numbers: ' . count($numbers) . PHP_EOL;
		}
	}

	return $scratchcards;
}

function parseLine($line): array
{
	if (!preg_match('/^Card[ ]+(\d+): ([ \d]+) \| ([ \d]+)$/', $line, $matches)) {
		if (!SILENT) {
			echo 'Parse error: misformed line ' . $line . PHP_EOL;
		}

		return [
			false,
			[],
			[]
		];
	}

	$cardId = $matches[1];
	$winningString = trim($matches[2]);
	$winningArray = [];
	foreach (explode(' ', $winningString) as $number) {
		if ($number && $number == (int)$number) {
			$winningArray[] = (int)$number;
		}
	}
	$numbersString = trim($matches[3]);
	$numbersArray = [];
	foreach (explode(' ', $numbersString) as $number) {
		if ($number && $number == (int)$number) {
			$numbersArray[] = (int)$number;
		}
	}
	if (VERBOSE && DEBUG_MODE) {
		echo 'Card #' . $cardId . PHP_EOL . 'with winning nb: ' . $winningString . ' (' . count($winningArray) . ')'
			. PHP_EOL . 'numbers ' . $numbersString . ' (' . count($numbersArray) . ')' . PHP_EOL;
	}

	return [
		$cardId,
		$winningArray,
		$numbersArray
	];
}

function computeScore($scratchCard): int
{
	$score = 0;
	foreach ($scratchCard['numbers'] as $sorted) {
		if (isWinning($sorted, $scratchCard['winningNumbers'])) {
			$score = ($score == 0) ? 1 : ($score * 2);
		}
	}

	return $score;
}

function isWinning($number, $winning): bool
{
	return in_array($number, $winning);
}


$parsed = parseInput($input);

$sum = 0;
foreach ($parsed as $cardId => $scratchCard) {
	$score = computeScore($scratchCard);
	$sum += $score;
	if (VERBOSE && DEBUG_MODE) {
		echo 'Card #' . $cardId . ' is worth ' . $score . ' points.'
			. PHP_EOL . 'Partial sum is ' . $sum . PHP_EOL;
	}
}

if (SILENT) {
	echo $sum . PHP_EOL;
} else {
	echo 'The scratchcards are worth ' . $sum . ' points.' . PHP_EOL;
}

include __DIR__ . DIRECTORY_SEPARATOR . 'template_exit.php';
