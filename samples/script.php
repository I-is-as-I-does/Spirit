<?php
use SSITU\Jack\Jack;
use SSITU\Spirit\Spirit;

require_once dirname(__DIR__, 3) . '/app/vendor/autoload.php';

// Initiating Spirit:
$Spirit = new Spirit();

// Loading default print config (could also be a php array; or loaded from a .env file; as preferred):
$printConfig = Jack::File()->readJson(__DIR__ . '/Spirit-printConfig.json');

// Completing or modifying config:
$printConfig["cryptKey"] = '703af4dd03ebe11e35167157a8a697d8a2cb545a907a38289f8a7ba19432a342';
$printConfig["flavour"] = "Sugar"; # prefer Sodium if installed


// Data to inject must be a string,
// and its encrypted copy must fit in specified image dimensions;
// Spirit returns false and log error if that's not the case
$dataToInject = 'While doubting thus his dogs espied him there:
first Blackfoot and the sharp nosed Tracer raised
the signal: Tracer of the Gnossian breed,
and Blackfoot of the Spartan: swift as wind
the others followed. Glutton, Quicksight, Surefoot,
three dogs of Arcady; then valiant Killbuck,
Tempest, fierce Hunter, and the rapid Wingfoot;
sharp-scented Chaser, and Woodranger wounded
so lately by a wild boar; savage Wildwood,
the wolf-begot with Shepherdess the cow-dog;
and ravenous Harpy followed by her twin whelps;
and thin-girt Ladon chosen from Sicyonia;
racer and Barker, brindled Spot and Tiger;
sturdy old Stout and white haired Blanche and black Smut
lusty big Lacon, trusty Storm and Quickfoot;
active young Wolfet and her Cyprian brother
black headed Snap, blazed with a patch of white hair
from forehead to his muzzle; swarthy Blackcoat
and shaggy Bristle, Towser and Wildtooth,
his sire of Dicte and his dam of Lacon;
and yelping Babbler: these and others, more
than patience leads us to recount or name.';

// $modifiers = ["useBgImg" => true, "addtTexts" => [], "mainText" => "", "headerText" => "", "footerText" => date('Y-m-d')];

// Printing image:
$printRslt = $Spirit->printImg($dataToInject, $printConfig);
// returns 'image' (b64 format) and 'signature'; 
echo '<h2>Fresh Print</h2>';
echo '<a href="'.$printRslt['image'].'" download="spirit-image.png"><img src="'.$printRslt['image'].'"></a>';
echo '<h2>Signature</h2>';
echo '<p>Signature is REQUIRED for future decryption;<br>';
echo 'it is only given at this one occasion;<br>';
echo 'STORE IT in a safe place; DO NOT display it publicly</p>';
echo '<p>'.$printRslt['signature'].'</p>';

// Reading image ($img can be either a filepath, or a base64 data image)
echo '<h2>Extracting Data from Fresh Print</h2>';
//echo $Spirit->readImg($printRslt['image'], $printRslt['signature']);

echo '<h2>Stored Print</h2>';
echo '<img src="./pass.png">';
echo '<h2>Extracting Data from Stored Print</h2>';
//$Spirit->readImg(__DIR__ . '/pass.png');

$logs = $Spirit->getLogs();
if (!empty($logs)) {
    echo '<h2>Logs</h2>';
    echo json_encode($logs, JSON_PRETTY_PRINT);
}
