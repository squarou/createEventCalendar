<?php
/*
Params
    &resource: the id of the resource targeted ; default to current
    &toPlaceholder: theplaceholder you want to use for the output ; default to calendarLink
    &filePath: Path where the calendar file will be saved, defaults to assets/events directory (define path like /example/)
    &fileName: defaults to pagetitle
    &tpl: name of chunk to use. Defaults to eventCalendar
    &summary: to add a summary of the event
    &startDate: start date of the event, format like 2015-05-14 15:53:00 (default output of date TV)
    &endDate: end date of the event, format like 2015-05-14 15:53:00 (default output of date TV)
    &address: comma delimited list of the addres as street,housenumber,zipcode,city,country
    &link: add link to the event
    &attachment: add an attachment to the event
	&coordinates: latitude,longitude
	&geocode: 0 defaults to 1
	
	use the placeholder "calendarLink" to output the download link to the calendar file
*/

$toPlaceholder = $modx->getOption('toPlaceholder', $scriptProperties, 'calendarLink', true);

$resId  = $modx->getOption('resource', $scriptProperties, $modx->resource->get('id'), true);

$here = $modx->getObject('modResource', $resId);

$startDate = $here->getTVValue('DateStart');;
$endDate = $here->getTVValue('DateEnd');
$address = $here->getTVValue('adresse');


$eventsDir 				= $filePath ?  $_SERVER['DOCUMENT_ROOT'] . $filePath : $modx->getOption('assets_path').'events/';
$tpl                    = $tpl ? $tpl : 'eventCalendar';
$eventID                = $resId;
$eventName 				= $here->get('longtitle') ? $here->get('longtitle') : $here->get('pagetitle');

$summary = $here->get('introtext');
$eventSummary 			= wordwrap($summary, 50, "\n ", true);

$eventStartDate         = strtotime($startDate);
$eventEndDate           = strtotime($endDate);

$prodid 				= mt_rand(1000000000, 9999999999) . $eventID;

$addressArray           = explode(",", $address);

$housenumber            = trim($addressArray[0]);
$street                 = trim($addressArray[1]);
$zipcode                = trim($addressArray[2]);
$city                   = trim($addressArray[3]);

$eventLocation 			= $street . ' ' . $housenumber . ',' . $zipcode . ' ' . $city;

$eventLocation = $address;

$geocode = ($geocode == 0) ? 0 : 1;
//check if a file with this name already exists
$file = $fileName ? $resId . "-" . $fileName . ".ics" : $resId . "-" . $here->get('pagetitle') . ".ics";
$eventsFile = fopen($eventsDir . $file, "w");



$eventLocation 			= str_replace(",", "\\n", $eventLocation);
$eventLocation 			= str_replace(";", "\\n", $eventLocation);




$link = $modx->makeUrl($resId,'','','https');


function dateToCal($timestamp) {
  return date('Ymd\THis', $timestamp);
}

function escapeString($string) {
  return preg_replace('/([\,;])/','\\\$1', $string);
}


if($attachment){
	$getAttachment	= file_get_contents($attachment);
	$getFileName	= pathinfo($attachment);
	$fileName 		= $getFileName['basename'];

	$eventAttachment .= "ATTACH;ENCODING=BASE64;VALUE=BINARY;X-APPLE-FILENAME=" .  $fileName . ":";
	$b64vcard = base64_encode($getAttachment);
	$b64mline = chunk_split($b64vcard,74,"\n");
	$b64final = preg_replace('/(.+)/', ' $1', $b64mline);

	$eventAttachment .= $b64final;
}
else {
    $eventAttachment = '';
}

if($coordinates && $geocode == 1){
$coordinatesOutput .= "X-APPLE-PROXIMITY:DEPART\n";
    
$coordinatesOutput .= "X-APPLE-STRUCTURED-LOCATION;VALUE=URI;
 X-ADDRESS=" . $eventLocation . ";
 X-APPLE-RADIUS=49.91307587029686;X-TITLE=Zoom 1:geo:
 ".$coordinates;
}

$vCalendar = $modx->getChunk($tpl, array(
                                        'id'                => $prodid,
                                        'eventStartDate'    => dateToCal($eventStartDate),
                                        'eventEndDate'      => dateToCal($eventEndDate),
                                        'eventID'           => $eventID,
                                        'attachment'        => $eventAttachment,
                                        'dtstamp'           => dateToCal(time()),
                                        'location'          => $eventLocation,
                                        'description'       => $eventSummary,
                                        'url'               => $link,
                                        'name'              => $eventName,
                                        'coordinates'       => $coordinatesOutput
                                    )
                                );
                                
if (!file_exists($eventsDir)) {
    mkdir($eventsDir, 0777, true);
}

$eventsFile = fopen($eventsDir . $file, "w");
fwrite($eventsFile, $vCalendar);
fclose($eventsFile);

$modx->toPlaceholder($toPlaceholder, $filePath . $file,'');
