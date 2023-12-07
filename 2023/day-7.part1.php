<?php

include __DIR__ . DIRECTORY_SEPARATOR . 'template_start.php';

$input = TEST_MODE
	? <<<STR
32T3K 765
T55J5 684
KK677 28
KTJJT 220
QQQJA 483
STR
	: file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'day-7.txt');

// remove leading and trailing spaces or blank lines
$input = trim($input);

const CARDS = ['A', 'K', 'Q', 'J', 'T', '9', '8', '7', '6', '5', '4', '3', '2'];

const TYPES = [
	'Five of a kind',
	// where all five cards have the same label: AAAAA
	'Four of a kind',
	// where four cards have the same label and one card has a different label: AA8AA
	'Full house',
	// where three cards have the same label, and the remaining two cards share a different label: 23332
	'Three of a kind',
	// where three cards have the same label, and the remaining two cards are each different from any other card in the hand: TTT98
	'Two pair',
	// where two cards share one label, two other cards share a second label, and the remaining card has a third label: 23432
	'One pair',
	// where two cards share one label, and the other three cards have a different label from the pair and each other: A23A4
	'High card'
	//where all cards' labels are distinct: 23456
];
const TYPE_FIVE_OF_A_KIND = 0;
const TYPE_FOUR_OF_A_KIND = 1;
const TYPE_FULL_HOUSE = 2;
const TYPE_THREE_OF_A_KIND = 3;
const TYPE_TWO_PAIR = 4;
const TYPE_ONE_PAIR = 5;
const TYPE_HIGH_CARD = 6;

function getHandType($cards): string
{
	if (isFiveOfAKind($cards)) {
		return array_search('Five of a kind', TYPES) ?? TYPE_FIVE_OF_A_KIND;
	} elseif (isFourOfAKind($cards)) {
		return array_search('Four of a kind', TYPES) ?? TYPE_FOUR_OF_A_KIND;
	} elseif (isFullHouse($cards)) {
		return array_search('Full house', TYPES) ?? TYPE_FULL_HOUSE;
	} elseif (isThreeOfAKind($cards)) {
		return array_search('Three of a kind', TYPES) ?? TYPE_THREE_OF_A_KIND;
	} elseif (isTwoPair($cards)) {
		return array_search('Two pair', TYPES) ?? TYPE_TWO_PAIR;
	} elseif (isOnePair($cards)) {
		return array_search('One pair', TYPES) ?? TYPE_ONE_PAIR;
	}
	return array_search('High card', TYPES) ?? TYPE_HIGH_CARD;
}

function isFiveOfAKind($cards): bool
{
	if (count($cards) !== 5) {
		if (!SILENT) {
			echo 'ERROR: not a hand: ' . var_export($cards, true) . PHP_EOL;;
		}

		return false;
	}

	$kind = $cards[0];
	foreach ($cards as $card) {
		if ($card !== $kind) {
			return false;
		}
	}
	return true;
}

function isFourOfAKind($cards): bool
{
	if (count($cards) !== 5) {
		if (!SILENT) {
			echo 'ERROR: not a hand: ' . var_export($cards, true) . PHP_EOL;;
		}

		return false;
	}

	$kinds = [];
	foreach ($cards as $card) {
		if (!isset($kinds[$card])) {
			$kinds[$card] = 1;
		} else {
			$kinds[$card]++;
		}
		if ($kinds[$card] > 3) {
			return true;
		}
	}

	return false;
}

function isFullHouse($cards): bool
{
	if (count($cards) !== 5) {
		if (!SILENT) {
			echo 'ERROR: not a hand: ' . var_export($cards, true) . PHP_EOL;;
		}

		return false;
	}

	$kinds = [];
	foreach ($cards as $card) {
		if (!in_array($card, $kinds)) {
			$kinds[] = $card;
		}
		if (count($kinds) > 2) {
			return false;
		}
	}

	return count($kinds) < 3;
}

function isThreeOfAKind($cards): bool
{
	if (count($cards) !== 5) {
		if (!SILENT) {
			echo 'ERROR: not a hand: ' . var_export($cards, true) . PHP_EOL;;
		}

		return false;
	}

	$kinds = [];
	foreach ($cards as $card) {
		if (!isset($kinds[$card])) {
			$kinds[$card] = 1;
		} else {
			$kinds[$card]++;
		}
		if ($kinds[$card] > 2) {
			return true;
		}
	}

	return false;
}

function isTwoPair($cards): bool
{
	if (count($cards) !== 5) {
		if (!SILENT) {
			echo 'ERROR: not a hand: ' . var_export($cards, true) . PHP_EOL;;
		}

		return false;
	}

	$kinds = [];
	$firstPair = false;
	foreach ($cards as $card) {
		if (!in_array($card, $kinds)) {
			$kinds[] = $card;
			continue;
		}
		if ($firstPair) {
			return true;
		}
		$firstPair = true;
	}

	return false;
}

function isOnePair($cards): bool
{
	if (count($cards) !== 5) {
		if (!SILENT) {
			echo 'ERROR: not a hand: ' . var_export($cards, true) . PHP_EOL;;
		}

		return false;
	}

	$kinds = [];
	foreach ($cards as $card) {
		if (in_array($card, $kinds)) {
			return true;
		}
		$kinds[] = $card;
	}

	return false;
}

/**
 * if hand1 is higher than hand2, return 1
 * return 0 if hands are identical
 * -1 otherwise (hand2 higher than hand1)
 *
 * @param $hand1
 * @param $hand2
 * @return int
 */
function compareHands($hand1, $hand2): int
{
	if ($hand1['type'] != $hand2['type']) {
		return ($hand1['type'] < $hand2['type']) ? 1 : -1;
	}
	foreach ($hand1['cards'] as $_k => $card1) {
		$card2 = $hand2['cards'][$_k];
		if ($card2 != $card1) {
			return (array_search($card1, CARDS) < array_search($card2, CARDS))
				? 1 : -1;
		}
	}
	if (!SILENT) {
		echo 'ERROR: hands are identical' . PHP_EOL;
		dumpHand($hand1);
		dumpHand($hand2);
	}

	return 0;
}

function dumpHand($hand)
{
	echo "\t" . implode('-', $hand['cards'])
		. PHP_EOL . '~~>> (' . $hand['type'] . ') ' . TYPES[$hand['type']]
		. PHP_EOL . 'bid: ' . $hand['bid'] . PHP_EOL;
}

function parseHand($line): false|array
{
	if (!preg_match('/^([AKQJT2-9]{5})\s+(\d+)\s*$/', $line, $matches)) {
		if (!SILENT) {
			echo 'ERROR: misformed hand ' . var_export($line, true) . PHP_EOL;
		}

		return false;
	}
	$cards = str_split($matches[1]);
	$bid = (int)$matches[2];

	return [
		'cards' => $cards,
		'type' => getHandType($cards),
		'bid' => $bid
	];
}

foreach (explode("\n", $input) as $line) {
	$list[] = parseHand($line);
}
usort($list, "compareHands");

$answer = 0;
foreach ($list as $rank => $hand) {
	$answer += ($rank + 1) * $hand['bid'];
}

if (SILENT) {
	echo $answer . PHP_EOL;
} else {
	echo 'The total winnings are ' . $answer . PHP_EOL;
}

include __DIR__ . DIRECTORY_SEPARATOR . 'template_exit.php';
