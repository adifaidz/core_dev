<?php
/**
 * $Id$
 *
 * Functions for validating credit card numbers
 *
 * References:
 *	http://en.wikipedia.org/wiki/Credit_card_number
 *	http://www.beachnet.com/~hstiles/cardtype.html
 *
 * Status: Feature complete. Works with my VISA number, needs some real world testing
 *
 * Functions:
 *	CCstripNumber: helper function, cleans up user inputted number (complete!)
 *	CCgetType: Returns a constant for the identifed card type, or false on failure (may be incomplete, based on info from '97)
 *	CCvalidateMod10: Validates number using the mod 10 algorithm (complete!)
 *
 * @author Martin Lindhe, 2007-2008 <martin@startwars.org>
 */

define("CC_INVALID",	0);
define("CC_VISA",		1);
define("CC_MASTERCARD",	2);
define("CC_AMEX",		3);
define("CC_DINERS",		4);
define("CC_DISCOVER",	5);
define("CC_JCB",		6);

$cc_name[CC_INVALID]	= "Invalid";
$cc_name[CC_VISA]		= "Visa";
$cc_name[CC_MASTERCARD]	= "Mastercard";
$cc_name[CC_AMEX]		= "American Express";
$cc_name[CC_DINERS]		= "Diners Club / Carte Blanche";
$cc_name[CC_DISCOVER]	= "Discover";
$cc_name[CC_JCB]		= "JCB";

/**
 *  Cleans up a cc number entered by a user
 *
 * @param $number credit card number
 * @return false on invalid number
 */
function CCstripNumber($number)
{
	$number = str_replace(' ', '', $number);
	$number = str_replace('-', '', $number);
	$number = str_replace('.', '', $number);

	if (!is_numeric($number)) return false;
	return $number;
}

/**
 * Used to format the card number with spaces between every 4:th digit so it's easier to read
 *
 * @param $number credit card number
 * @return formatted credit card number
 */
function CCprintNumber($number)
{
	$number = CCstripNumber($number);
	if (!$number) return false;

	$result = '';
	for ($i=0; $i<strlen($number); $i+=4) {
		$result .= substr($number, $i, 4).' ';
	}
	return trim($result);
}

/**
 * Used to format the card number with spaces between every 4:th digit so it's easier to read
 * Masks out the middle part of the number, showing the first 4 and last 4 digits.
 *
 * @param $number credit card number
 * @return formatted credit card number
 */
function CCmaskNumber($number)
{
	$number = CCstripNumber($number);
	if (!$number) return false;

	$result = '';
	for ($i=0; $i<strlen($number); $i+=4) {
		if ($i==0 || $i+4 == strlen($number)) {
			$result .= substr($number, $i, 4).' ';
		} else {
			$result .= '**** ';
		}
	}
	return trim($result);
}

/**
 * Returns type of credit card for supplied cc number
 *
 * @param $number credit card number
 */
function CCgetTypeName($number)
{
	global $cc_name;
	return $cc_name[CCgetType($number)];
}

/**
 * Tries to figure out the card type
 *
 * @param $number credit card number
 * @return type of cc number
 */
function CCgetType($number)
{
	$number = CCstripNumber($number);
	if ($number === false) return CC_INVALID;

	$len = strlen($number);
	if ($len < 13) return CC_INVALID;

	$pref1 = substr($number, 0, 1);
	if (($pref1 == 4) && ($len == 13 || $len == 16)) {
		return CC_VISA;
	}

	if (($pref1 == 3) && ($len == 16)) {
		return CC_JCB;
	}

	$pref2 = substr($number,0,2);
	if (($pref2 >= 51) && ($pref2 <= 55) && $len == 16) {
		return CC_MASTERCARD;
	}

	if ((($pref2 == 34) || ($pref2 == 37)) && $len == 15) {
		return CC_AMEX;
	}

	$pref3 = substr($number,0,3);
	if (((($pref3 >= 300) && ($pref3 <= 305)) || ($pref2 == 36) || ($pref2 = 38)) && $len == 14) {
		return CC_DINERS;
	}

	$pref4 = substr($number,0,4);
	if (($pref4 == 6011) && $len == 16) {
		return CC_DISCOVER;
	}

	if ((($pref4 == 2131) || ($pref4 == 1800)) && $len == 15) {
		return CC_JCB;
	}

	return CC_INVALID;
}

/**
 * Performs MOD 10 calculation of credit card number
 *
 * @return true if checksums is correct
 */
function CCvalidateMod10($number)
{
	$number = CCstripNumber($number);
	if ($number === false || CCgetType($number) == CC_INVALID) return false;

	$tot=0;
	for ($i = strlen($number)-1; $i>=0; $i--) {
		$char = substr($number, $i, 1);
		if (!((strlen($number)-$i) % 2)) { //Even numbers
			$char *= 2;
			$d1 = substr($char,0,1);
			$d2 = substr($char,1,1);
			$tot += $d1 + $d2;
		} else {
			$tot += $char;
		}
	}

	if (substr($tot, -1) == '0') return true;
	return false;
}
?>