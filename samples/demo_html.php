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

<h3>Generated Spirit Key</h3>
<p><?= $printRslt['key'] ?></p>

<h3>Extracting Data from Fresh Print</h3>
<p><?= $readRslt ?></p>
<hr>

<h2>Stored Print</h2>
<img src="./spirit-image.png">

<h3>Extracting Data from Stored Print</h3>
<p><?= $fileReadRslt ?></p>
<hr>

<h2>Custom Key Print</h2>

<a href="<?= $cstmPrintRslt['image'] ?>" download="spirit-image.png"><img src="<?= $cstmPrintRslt['image'] ?>"></a>

<h3>Provided key</h3>
<p><?= $givenKey ?></p>

<h3>Extracting Data from Custom Key Print</h3>
<p><?= $cstmReadRslt ?></p>
<hr>

<?php include dirname(__DIR__) . '/doc/spirit-doc-en.html'; ?>
</body>
</html>

