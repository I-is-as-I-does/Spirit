<?php

use SSITU\Sod\Sod;
use SSITU\Spirit\Spirit;

$autoloadPath = dirname(__DIR__, 5) . '/autoload.php';
if (!file_exists($autoloadPath)) {
    exit('Please edit autoloader path');
}
require_once $autoloadPath;

function outSodLogs()
{
    global $Spirit;
    $logs = $Spirit->getLogs();
    exit(json_encode($logs, JSON_PRETTY_PRINT));
}

// Sod config:
$sodConfig["cryptKey"] = '703af4dd03ebe11e35167157a8a697d8a2cb545a907a38289f8a7ba19432a342';
$sodConfig["flavour"] = "Sugar"; # prefer Sodium if installed

// Sod init:
$Sod = new Sod($sodConfig);

// Spirit init:
$Spirit = new Spirit($Sod);

//You can also pass Sod like so:
# $Spirit->setSod($Sod);

// Loading default print config (could also be a php array):
$printConfig = json_decode(file_get_contents(__DIR__ . '/Spirit-printConfig.json'), true);

// Some changes to config:
$modifiers = [
    "useBgImg" => true,
    "footerText" => date('Y-m-d'),
    "mainText" => "",
    "addtTexts" => []];
$printConfig = array_merge($printConfig, $modifiers);

// Some data to inject (must be a string):
$dataToInject = 'such Secret much Hidden wow';

// Printing image:
// returns 'image' (b64 format) and 'key';
$printRslt = $Spirit->printImg($dataToInject, $printConfig);
if ($printRslt === false) {
    outSodLogs();
}

// Reading image (either a filepath, or a base64 image can be passed)
$readRslt = $Spirit->readImg($printRslt['image'], $printRslt['key']);
if ($readRslt === false) {
    outSodLogs();
}

// Reading previously stored image
$safelyStoredSpiritKey = 'nCLz32iG2hyu67lWCKBSFHeZw2qh1cFx';
$fileReadRslt = $Spirit->readImg(__DIR__ . '/spirit-image.png', $safelyStoredSpiritKey);
if ($fileReadRslt === false) {
    outSodLogs();
}

// If providing a key: it must consist of b64 characters only; 
// and length must match with the one specified in config 
$givenKey = 'Hf/mRnoh3mJDl8w+7DuhelTdHIWuj4V4';
$otherDataToInject = "blah blah blah";
$cstmPrintRslt = $Spirit->printImg($otherDataToInject, $printConfig, $givenKey);

if ($cstmPrintRslt === false) {
    outSodLogs();
}

// reading it
$cstmReadRslt = $Spirit->readImg($cstmPrintRslt['image'], $givenKey);
if ($cstmReadRslt === false) {
    outSodLogs();
}

include __DIR__ . '/demo_html.php';
