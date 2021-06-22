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

