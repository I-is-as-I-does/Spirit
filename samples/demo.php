<?php

use SSITU\Spirit\Spirit;
use \SSITU\Sod\Sod;

require_once dirname(__DIR__, 3) . '/app/vendor/autoload.php';

function outSodLogs()
{
    global $Spirit;
    $logs = $Spirit->getLogs();
    return json_encode($logs, JSON_PRETTY_PRINT);
}

// Initiating Spirit:
$Spirit = new Spirit();

// Loading default print config (could also be a php array; or loaded from a .env file; as preferred):
$printConfig = json_decode(file_get_contents(__DIR__ . '/Spirit-printConfig.json'), true);

// Updating print config:
$modifiers = [
"useBgImg"=> true,
"footerText"=> date('Y-m-d'),
"mainText"=> "",
"addtTexts"=> []];

$printConfig = array_merge($printConfig, $modifiers);

// Independrntly setting Sod config:
$sodConfig["cryptKey"] = '703af4dd03ebe11e35167157a8a697d8a2cb545a907a38289f8a7ba19432a342';
$sodConfig["flavour"] = "Sugar"; # prefer Sodium if installed

// Initiating Sod:
$Sod = new Sod($sodConfig);
$settingSod = $Spirit->setSod($Sod);
if (!$settingSod) {
    $settingSod = outSodLogs();
}

// Data to inject must be a string,
// and its encrypted copy must fit in specified image dimensions;
// Spirit returns false and log error if that's not the case
$dataToInject = 'such Secret much Hidden wow';

// Printing image:
// returns 'image' (b64 format) and 'key';
$printRslt = $Spirit->printImg($dataToInject, $printConfig);
$readRslt;
if ($printRslt === false) {
    $printRslt = outSodLogs();
} else {

// Reading image ($img can be either a filepath, or a base64 data image)
$readRslt = $Spirit->readImg($printRslt['image'], $printRslt['key']);
if ($readRslt === false) {
    $readRslt = outSodLogs();
}
}
// Reading previously stored image
$safelyStoredSpiritKey = 'nCLz32iG2hyu67lWCKBSFHeZw2qh1cFx';
$fileReadRslt = $Spirit->readImg(__DIR__ . '/spirit-image.png', $safelyStoredSpiritKey);
if ($fileReadRslt === false) {
    $fileReadRslt = outSodLogs();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>
<body>
    
<h2>Fresh Print</h2>
<a href="<?= $printRslt['image'] ?>" download="spirit-image.png"><img src="<?= $printRslt['image'] ?>"></a>

<h2>Spirit Key</h2>
<p><?= $printRslt['key'] ?></p>


<h2>Extracting Data from Fresh Print</h2>
<p><?= $readRslt ?></p>

<h2>Stored Print</h2>
<img src="./spirit-image.png">

<h2>Extracting Data from Stored Print</h2>
<p><?= $fileReadRslt ?></p>


<?php include dirname(__DIR__) . '/doc/spirit-doc-en.html'; ?>
</body>
</html>

