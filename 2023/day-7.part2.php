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

const CARDS = ['A', 'K', 'Q', 'T', '9', '8', '7', '6', '5', '4', '3', '2', 'J'];

const CARD_JOKER = 'J';

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

function getHandType($cards): int
{
	if (!isValidHandCards($cards)) {
		return false;
	}

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

function isValidHandCards($cards): bool
{
	if (count($cards) !== 5) {
		if (!SILENT) {
			echo 'ERROR: a hand must have exactly 5 cards: ' . var_export($cards, true) . PHP_EOL;;
		}

		return false;
	}

	foreach ($cards as $card) {
		if (!in_array($card, CARDS)) {
			if (!SILENT) {
				echo 'ERROR: unsupported card: ' . var_export($card, true) . PHP_EOL;;
				if (VERBOSE) {
					echo 'must be one of ["' . implode('", "', CARDS) . '"]' . PHP_EOL;;
				}
			}

			return false;
		}
	}

	return true;
}

function isFiveOfAKind($cards): bool
{
	$kind = false;
	foreach ($cards as $card) {
		if ($card == CARD_JOKER) {
			// jokers don't count
			continue;
		}

		if ($kind === false) {
			$kind = $card;
			continue;
		}

		if ($card !== $kind) {
			// another kind of card? then it's not "5 of a kind"
			return false;
		}
	}

	return true;
}

function isFourOfAKind($cards): bool
{
	$kinds = [];
	foreach ($cards as $card) {
		if (!isset($kinds[$card])) {
			// first card of this kind
			if ($card == CARD_JOKER) {
				// first joker. increment all other cards by one
				foreach ($kinds as $_card => $_repeats) {
					$kinds[$_card]++;
					if ($kinds[$_card] > 3) {
						// found 4 of a kind!
						return true;
					}
				}

				$kinds[CARD_JOKER] = 1;
				continue;
			}

			// not a joker
			$kinds[$card] = 1 + ($kinds[CARD_JOKER] ?? 0);
			if ($kinds[$card] > 3) {
				// found 4 of a kind!
				return true;
			}

			continue;
		}

		// not the first card of its type
		if ($card == CARD_JOKER) {
			// another joker. increment all other cards by one (including joker)
			foreach ($kinds as $_card => $_repeats) {
				$kinds[$_card]++;
				if ($kinds[$_card] > 3) {
					// found 4 of a kind!
					return true;
				}
			}

			continue;
		}

		// not a joker
		$kinds[$card]++;
		if ($kinds[$card] > 3) {
			// found 4 of a kind!
			return true;
		}
	}

	return false;
}

function isFullHouse($cards): bool
{
	$kinds = [];
	foreach ($cards as $card) {
		if ($card == CARD_JOKER) {
			// jokers don't count
			continue;
		}

		if (!in_array($card, $kinds)) {
			$kinds[] = $card;
		}

		if (count($kinds) > 2) {
			// more than 3 different card kinds in hand => not a full house.
			return false;
		}
	}

	// this includes "5 of a kind" & "4 of a kind" cases, but they should be handled before:
	return count($kinds) < 3;
}

function isThreeOfAKind($cards): bool
{
	$kinds = [];
	foreach ($cards as $card) {
		if (!isset($kinds[$card])) {
			// first card of this kind
			if ($card == CARD_JOKER) {
				// first joker. increment all other cards by one
				foreach ($kinds as $_card => $_repeats) {
					$kinds[$_card]++;
					if ($kinds[$_card] > 2) {
						// found 3 of a kind!
						return true;
					}
				}
				$kinds[CARD_JOKER] = 1;
				continue;
			}

			// not a joker
			$kinds[$card] = 1 + ($kinds[CARD_JOKER] ?? 0);
			if ($kinds[$card] > 2) {
				// found 3 of a kind!
				return true;
			}

			continue;
		}

		// not the first card of its kind
		if ($card == CARD_JOKER) {
			// another joker. 2 jokers ensure that there will AT LEAST be a "3 of a kind"
			return true;
		}

		// not a joker
		$kinds[$card]++;
		if ($kinds[$card] > 2) {
			// found 3 of a kind!
			return true;
		}
	}

	return false;
}

function isTwoPair($cards): bool
{
	$kinds = [];
	$firstPair = false;
	foreach ($cards as $card) {
		if ($card == CARD_JOKER) {
			if ($firstPair) {
				// if there was already a pair, then a joker can be associated to any other single card to make a new pair
				return true;
			}

			$firstPair = true;
			continue;
		}

		if (!in_array($card, $kinds)) {
			// new kind of card: add it
			$kinds[] = $card;
			continue;
		}

		// card type was already included!
		if ($firstPair) {
			// if it's not the first time, then it's a new pair:
			return true;
		}

		// now there will be a pair. keep looking (for the 2nd pair)
		$firstPair = true;
	}

	return false;
}

function isOnePair($cards): bool
{
	$kinds = [];
	foreach ($cards as $card) {
		if ($card == CARD_JOKER) {
			// a joker can be associated to any other single card to make a pair
			return true;
		}

		if (in_array($card, $kinds)) {
			// same card was already included => that's a pair
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
		// lowest type means strongest hand
		return ($hand1['type'] < $hand2['type']) ? 1 : -1;
	}

	for ($i = 1; $i <= 5; $i++) {
		$card1 = $hand1['cards'][$i - 1];
		$card2 = $hand2['cards'][$i - 1];
		if ($card1 != $card2) {
			// lowest index means strongest card
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

function dumpHand($hand): void
{
	echo "\t" . implode('-', $hand['cards'])
		. PHP_EOL . '~~>> (' . $hand['type'] . ') ' . TYPES[$hand['type']]
		. PHP_EOL . 'bid: ' . $hand['bid'] . PHP_EOL;
}

function parseHand($line): false|array
{
	if (!preg_match('/^([AKQT2-9J]{5})\s+(\d+)\s*$/', $line, $matches)) {
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
	$hand = parseHand($line);
//	if (TEST_MODE && VERBOSE && DEBUG_MODE) {
//		dumpHand($hand);
//	}
	$list[] = $hand;
}
usort($list, "compareHands");

$answer = 0;
foreach ($list as $rank => $hand) {
	if (TEST_MODE && VERBOSE && DEBUG_MODE) {
		echo 'RANKED HANDS: ' . $rank + 1 . PHP_EOL;
		dumpHand($hand);
	}

	$answer += ($rank + 1) * $hand['bid'];
}

if (SILENT) {
	echo $answer . PHP_EOL;
} else {
	echo 'The total winnings are ' . $answer . PHP_EOL;
}

include __DIR__ . DIRECTORY_SEPARATOR . 'template_exit.php';
