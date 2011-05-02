#!/usr/bin/php
<?php
error_reporting(0);
date_default_timezone_set('America/New_York');

// Make a curl request to backpack, return the XML object.
function getCalendar($cal, $token) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $cal);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml','X-BP-Token: '.$token));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	$xmlstr = curl_exec($ch);
	curl_close($ch);
	
	$xml = new SimpleXMLElement($xmlstr);
	return $xml;
}

// Parse XML object and find items for today. Add them to events array.
$events = array();
function addEvents($xml) {
	$arr = array();
	$m = date('n');
	$d = date('j');
	$y = date('Y');
	$start_day = mktime(0, 0, 0, $m, $d, $y);
	$end_day = mktime(23, 59, 59, $m, $d, $y);
	if($xml) {
		$diff = date("Z")." seconds";
		foreach ($xml->{'calendar-event'} as $e) {
			$event_time = strtotime($diff,strtotime($e->{'occurs-at'}));
			if($start_day <= $event_time && $event_time <= $end_day)
				$arr[] = (string)$e->title;
		}
	}
	return $arr;
}

// Add a list of calendars you want to request and your access token.
$calendars = array();
$token = "";

foreach($calendars as $c) {
	$xml = getCalendar($c, $token);
	$events = array_merge($events,addEvents($xml));
}

if(empty($events))
	echo "Nothing today.\n";
else
	foreach($events as $e)
		echo "@ ".$e."\n";

?>