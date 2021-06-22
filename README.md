
# Spirit

Spirit Printer; steganography for 0 cents a copy!

## Getting started

```bash
$ composer require ssitu/spirit
```

## How to 

Demo available in `samples/`.

```php

use SSITU\Spirit\Spirit;
use SSITU\Sod\Sod;

require_once '/path/to/vendor/autoload.php';

// Sod config:
$sodConfig["cryptKey"] = '703af4dd03ebe11e35167157a8a697d8a2cb545a907a38289f8a7ba19432a342';
$sodConfig["flavour"] = "Sugar"; # prefer "Sodium" if installed

// Sod init:
$Sod = new Sod($sodConfig);

// Spirit init:
$Spirit = new Spirit($Sod);

//You can also pass Sod like so:
# $Spirit->setSod($Sod);

// Print config (could also be a decoded json file, cf. samples/):
$printConfig = [
  'keyLength' => 32,
  'width' => 286,
  'height' => 186,
  'margin' => 9,
  'minfillerLen' => 32,
  'imgExtension' => 'png',
  'printTexts' => true,
  'headerText' => 'PASS',
  'footerText' => 'www.some-domain.com/login',
  'mainText' => 'Bob',
  'addtTexts' => ['guest of Ida'],
  'fontFilePath' => '../samples/sourcessproxtrlight.ttf',
  'textColorCodes' => [160,160,160],
  'fontSize' => 11,
  'angle' => 0,
  'lineSpacer' => 2,
  'adtlines' => 4,
  'bgImgPath' => '../samples/baseImg.png',
  'useBgImg' => false,
  'bgColorCodes' => [255,255,255],
  'drawFrame' => true,
  'lineColorCodes' => [160, 160,160,],
];

// Some data to inject (must be a string):
$dataToInject = 'such Secret much Hidden wow';

// Printing image:
// returns an array with 'image' (b64 format) and 'key'
$printRslt = $Spirit->printImg($dataToInject, $printConfig);

// Reading image (either a filepath, or a b64 image can be passed)
// returns decoded data
$Spirit->readImg($printRslt['image'], $printRslt['key']);

// Reading previously stored image
$safelyStoredSpiritKey = 'nCLz32iG2hyu67lWCKBSFHeZw2qh1cFx';
$Spirit->readImg('../samples/spirit-image.png', $safelyStoredSpiritKey);

// If something went wrong:
$Spirit->getLogs();

```

## Doc

![Spirit Duplicator](Spirit.jpg)

Please note that Spirit has NOT been extensively tested; therefore, consider it a toy.  

SSITU/Sod, or another encryption util – as long as it implements the same interface, is REQUIRED to run Spirit.  

Spirit MUST NOT be used to store sensitive data.  
Permanent data loss WILL occurs, AT LEAST in those scenarii:  

- if either Spirit or Sod key gets lost or corrupted, even partially;  
- if a run-time or an encryption error occurs;  
- if libraries are no longer properly maintained.  

Both specified Sod key AND provided Spirit key are REQUIRED for future decryption;  
Spirit key is only given ONCE, on successful image creation;  
thus, Sod key and Spirit key MUST be stored in a safe place.  
Spirit key SHOULD NOT be displayed publicly;  
Sod key MUST NOT be displayed publicly.  

A Spirit image can be duplicated and renamed at will;  
However, a Spirit image MUST NOT be edited, compressed, resized, or saved in another format.  

## Contributing

Sure! You can take a loot at [CONTRIBUTING](CONTRIBUTING.md).

## License

This project is under the MIT License; cf. [LICENSE](LICENSE) for details.

