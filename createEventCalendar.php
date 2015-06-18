<?php
/*
Params
    &startDate: start date of the event
    &endDate: end date of the event
    &address: comma delimited list of the addres as street,housenumber,zipcode,city,country
    &link: add link to the event
    &attachment: add an attachment to the event
	&street: streetname
	&housenumber: housenumber
	&zipcode: zipcode
	&city: city name
	&country: country name
	&coordinates: latitude,longitude
	&geocode: 0 defaults to 1

*/
$eventsDir 				= $modx->getOption('assets_path').'events/';

$eventID                = $modx->resource->get('id');
$eventName 				= $modx->resource->get('longtitle') ? $modx->resource->get('longtitle') : $modx->resource->get('pagetitle');
$eventSummary 			= wordwrap($summary, 50, "\n ", true);
$eventStartDate         = strtotime($startDate);
$eventEndDate           = strtotime($endDate);

$prodid 				= mt_rand(1000000000, 9999999999) . $eventID;

$addressArray           = explode(",", $address);
$street                 = $addressArray[0];
$housenumber            = $addressArray[1];
$zipcode                = $addressArray[2];
$city                   = $addressArray[3];
$country                = $addressArray[4];

$eventLocation 			= $street . ' ' . $housenumber . ',' . $zipcode . ' ' . $city . ';' . $country;

$geocode = ($geocode == 0) ? 0 : 1;
//check if a file with this name already exists
$file = $modx->resource->get('pagetitle') . ".ics";
$eventsFile = fopen($eventsDir . $file, "w");

if(!$coordinates && $geocode == 1){

	$reverseGeocodeUrl = strip_tags('https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($eventLocation));
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $reverseGeocodeUrl);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	$response = json_decode(curl_exec($ch), true);
	
	if ($response['status'] != 'OK') {
		//failed
		continue;
	}

	$geoCodeResults = array(
		'street' 		=> $response['results'][0]['address_components'][1]['long_name'],
		'housenumber'	=> $response['results'][0]['address_components'][0]['long_name'],
		'zipcode'		=> $response['results'][0]['address_components'][6]['long_name'],
		'city'			=> $response['results'][0]['address_components'][2]['long_name'],
		'state'			=> $response['results'][0]['address_components'][4]['long_name'],
		'country'		=> $response['results'][0]['address_components'][5]['long_name'],
		'latitude'		=> str_replace(",", ".", $response['results'][0]['geometry']['location']['lat']),
		'longitude'		=> str_replace(",",".", $response['results'][0]['geometry']['location']['lng'])
	);
	
	$coordinates = $geoCodeResults['latitude'].','.$geoCodeResults['longitude'];
}
$eventLocationMap	 	= str_replace(",", "\n ", $eventLocation);
$eventLocationMap	 	= str_replace(";", "\\n\n ", $eventLocationMap);

$eventLocation 			= str_replace(",", "\\n", $eventLocation);
$eventLocation 			= str_replace(";", "\\n", $eventLocation);

function dateToCal($timestamp) {
  return date('Ymd\THis\Z', $timestamp);
}

function escapeString($string) {
  return preg_replace('/([\,;])/','\\\$1', $string);
}

$vCalendar = "BEGIN:VCALENDAR\n";
$vCalendar .= "VERSION:2.0\n";
$vCalendar .= "PRODID:" . $prodid . "\n";
$vCalendar .= "CALSCALE:GREGORIAN\n";
$vCalendar .= "BEGIN:VEVENT\n";
$vCalendar .= "DTEND:" . dateToCal($eventEndDate) . "\n";
$vCalendar .= "UID:" . $eventID . "\n";
$vCalendar .= "DTSTAMP:" . dateToCal(time()) . "\n";

$vCalendar .= "LOCATION:" . $eventLocation . "\n";
$vCalendar .= "DESCRIPTION:" . $eventSummary . "\n";
if($link){
    $vCalendar .= "URL;VALUE=URI:" . $link . "\n";    
}

$vCalendar .= "SUMMARY:" . $eventName . "\n";

if($attachment){
	$getAttachment	= file_get_contents($attachment);
	$getFileName	= pathinfo($attachment);
	$fileName 		= $getFileName['basename'];

	$vCalendar .= "ATTACH;ENCODING=BASE64;VALUE=BINARY;X-APPLE-FILENAME=" .  $fileName . ":";
	$b64vcard = base64_encode($getAttachment);
	$b64mline = chunk_split($b64vcard,74,"\n");
	$b64final = preg_replace('/(.+)/', ' $1', $b64mline);

	$vCalendar .= $b64final;
}

$vCalendar .= "DTSTART:" . dateToCal($eventStartDate) ."\n";

if($coordinates && $geocode == 1){
$vCalendar .= "X-APPLE-PROXIMITY:DEPART\n";
    
$vCalendar .= "X-APPLE-STRUCTURED-LOCATION;VALUE=URI;
 X-ADDRESS=" . $eventLocationMap . ";
 X-APPLE-RADIUS=49.91307587029686;X-TITLE=Zoom 1:geo:
 ".$coordinates."\n";
}
$vCalendar .= "END:VEVENT\n";
$vCalendar .= "END:VCALENDAR\n";

$eventsFile = fopen($eventsDir . $file, "w");
fwrite($eventsFile, $vCalendar);
fclose($eventsFile);
