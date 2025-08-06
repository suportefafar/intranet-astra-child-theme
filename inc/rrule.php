<?php
// Prevent direct access to the file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_stylesheet_directory() . '/lib/php-rrule/src/RRuleInterface.php';
require_once get_stylesheet_directory() . '/lib/php-rrule/src/RRuleTrait.php';
require_once get_stylesheet_directory() . '/lib/php-rrule/src/RfcParser.php';
require_once get_stylesheet_directory() . '/lib/php-rrule/src/RSet.php';
require_once get_stylesheet_directory() . '/lib/php-rrule/src/RRule.php';

use RRule\RfcParser; // If you have namespace declarations in your code
use RRule\RRule; // If you have namespace declarations in your code

function intranet_fafar_rrule_get_all_occurrences( $rrule_str, $format = null ) {
	//$a = intranet_fafar_rrule_parse_string('DTSTART:20240201T113000\nRRULE:FREQ=WEEKLY;INTERVAL=1;UNTIL=20241201;BYDAY=MO,FR');
	//$a = intranet_fafar_rrule_parse_string('DTSTART:20241107T113000\nRRULE:FREQ=DAILY;COUNT=1');

	//$rrule = new RRule(RfcParser::parseRRule('RRULE:FREQ=DAILY;COUNT=1', '20241107T113000'));
	//$rrule = new RRule(RfcParser::parseRRule('RRULE:FREQ=WEEKLY;INTERVAL=1;UNTIL=20241201T000000;BYDAY=MO,FR', '20240201T113000'));

	if ( ! $rrule_str )
		return false;

	$rrule_dt_start = intranet_fafar_rrule_get_dt_start( $rrule_str );

	$rrule_rules = intranet_fafar_rrule_get_rules( $rrule_str );

	if ( ! $rrule_dt_start || ! $rrule_rules )
		return false;

	$rrule = new RRule( RfcParser::parseRRule( $rrule_rules, $rrule_dt_start ) );

	if ( ! $rrule )
		return false;

	$occurrences = array();

	foreach ( $rrule as $occurrence ) {

		if ( $format )
			array_push( $occurrences, $occurrence->format( $format ) );
		else
			array_push( $occurrences, $occurrence->getTimestamp() );

	}

	return $occurrences;

}

function intranet_fafar_rrule_get_dt_start( $rrule_str ) {

	$lines = explode( '\\n', $rrule_str );

	if ( str_contains( $rrule_str, '\n' ) )
		$lines = explode( '\n', $rrule_str );

	if ( count( $lines ) !== 2 )
		return false;

	$dt_start_line = array_shift( $lines );

	preg_match( '/DTSTART:(\d{8}T\d{6})/', $dt_start_line, $matches );

	if ( count( $matches ) !== 2 )
		return false;

	return $matches[1];

}

function intranet_fafar_rrule_get_rules( $rrule_str ) {

	$lines = explode( '\\n', $rrule_str );

	if ( str_contains( $rrule_str, '\n' ) )
		$lines = explode( '\n', $rrule_str );

	if ( count( $lines ) !== 2 )
		return false;

	return $lines[1];

}