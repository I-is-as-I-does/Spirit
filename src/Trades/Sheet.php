<?php
/* This file is part of Spirit | SSITU | (c) 2021 I-is-as-I-does */
namespace SSITU\Spirit\Trades;

use \SSITU\Spirit\Spirit;

class Sheet extends Spirit
{

    private $Spirit;
    private $Plate;

    # create-specific set values
    private $fillerLimit;
    private $lineHeight;
    private $maxWidth;
    private $maxLines;
    private $heady;
    private $endy;
    private $maskendy;
    private $baseDiff;
    private $y_adjust;
    private $endx;
    private $headx;

    private $texts = ['headerText' => '', 'footerText' => '', 'mainText' => '', 'addtTexts' => []];

    # create-specific defaults
    private $imgExtension = 'png';
    private $adtlines = 4; //@doc: 4 = header, footer, and line spacers
    private $fontFilePath = "../samples/sourcessproxtrlight.ttf";
    private $bgImgPath = "../samples/baseImage.png";
    private $fontSize = 11;
    private $margin = 9;
    private $lineSpacer = 2;
    private $angle = 0;
    private $textColorCodes = [160, 160, 160];
    private $bgColorCodes = [255, 255, 255];
    private $lineColorCodes = [200, 200, 200];
    private $drawFrame = true;
    private $printTexts = true;
    private $useBgImg = false;

    private $validExt = ['png', 'jpg', 'jpeg', 'gif', 'webp'];

    protected function __construct($Spirit)
    {
        $this->Spirit = $Spirit;
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
    }

    public function setConfig()
    {
        $this->Plate = $this->Spirit->Plate();

        if (!is_null($this->Plate->width)) {

            $this->prepDrawConfig();
            $this->prepDrawValues();
            return true;
        }
        $this->Spirit->record('invalid-Plate');
        return false;

    }
   
    public function boxDim($input)
    {
        //doc: beware, imagettfbbox is often buggy, depending on server running GD
        $box = imagettfbbox($this->fontSize, $this->angle, $this->fontFilePath, $input);
        $inputwidth = abs($box[2] - $box[0]);
        $inputheight = abs($box[7] - $box[1]);
        return ["w" => $inputwidth, "h" => $inputheight];
    }

    public function xPoz($input)
    {
        $indim = $this->boxDim($input);
        $xmid = ($this->Plate->width - $indim["w"]) / 2;
        $out = ["xmid" => $xmid];
        $out["maskx1"] = $xmid - $this->margin;
        $out["maskx2"] = $xmid + $indim["w"] + $this->margin;
        return $out;
    }

    private function prepDrawConfig()
    {
        $imgExtension = $this->Spirit->config('imgExtension');
        if (in_array($imgExtension, $this->validExt)) {
            $this->imgExtension = $imgExtension;
        }

        foreach (['fontFilePath', 'bgImgPath'] as $pathParam) {
            $method = 'check' . ucfirst($pathParam);
            $path = $this->Spirit->config($pathParam);
            if ($this->$method($path)) {
                $this->$pathParam = $path;
            }
        }

        foreach (['fontSize', 'margin', 'lineSpacer'] as $param) {
            $numbr = $this->Spirit->config($param);
            if (Utils::isPostvInt($numbr)) {
                $this->$param = $numbr;
            }
        }

        $angle = $this->Spirit->config('angle');
        if (is_int($angle)) {
            $this->angle = $angle;
        }

        $adtlines = $this->Spirit->config('adtlines');
        if (is_int($adtlines) && $adtlines >= 2) {
            $this->adtlines = $adtlines;
        }

        foreach (['headerText', 'footerText', 'mainText'] as $tparam) {
            $text = $this->Spirit->config($tparam);
            if (is_string($text)) {
                $this->texts[$tparam] = $text;
            }
        }
        $adtTexts = $this->Spirit->config('addtTexts');
        if (is_array($adtTexts)) {
            $this->texts['addtTexts'] = $adtTexts;
        }

        foreach (['bgColorCodes', 'lineColorCodes', 'textColorCodes'] as $colorparam) {
            $colors = $this->Spirit->config($colorparam);
            if (is_array($colors) && count($colors) === 3 && array_filter($colors, 'is_int') === $colors) {
                $this->$colorparam = $colors;
            }
        }

        foreach (['drawFrame', 'printTexts', 'useBgImg'] as $boolParam) {
            $bool = $this->Spirit->config($boolParam);
            if (is_bool($bool)) {
                $this->$boolParam = $bool;
            }
        }
    }

    private function checkBgImgPath($path)
    {
        if (!file_exists($path)) {
            $this->Spirit->record('invalid bg img path');
            return false;
        }

        if (!in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), $this->validExt)) {
            $this->Spirit->record('invalid  bg img type');
            return false;
        }
        return true;
    }

    private function checkFontFilePath($path)
    {
        if (!file_exists($path)) {
            $this->Spirit->record('invalid font file path');
            return false;
        }

        if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'ttf') {
            $this->Spirit->record('invalid font file type');
            return false;
        }
        if (!empty(preg_match('/[^a-z]+/', basename($path, '.ttf')))) {
            $this->Spirit->record('invalid font file name: [a-z] only');
            //@doc: for weird unknown reasons, font filename must be lowercase a-z only
            return false;
        }

        return true;
    }

    private function prepDrawValues()
    {
        $this->fillerLimit = $this->Plate->encod_limits - $this->Plate->minlen;
        $this->lineHeight = $this->fontSize + $this->lineSpacer;
        $this->maxWidth = $this->Plate->width - $this->margin * 2;
        $this->maxLines = floor(($this->Plate->height - $this->margin * 2) / $this->lineHeight);
        $this->y_adjust = $this->lineSpacer + $this->lineHeight / 2;
        $this->baseDiff = $this->maxLines - $this->adtlines;
        $this->heady = $this->margin + $this->fontSize - $this->lineSpacer / 2;
        $this->endy = $this->Plate->height - $this->margin + $this->lineSpacer / 2;
        $this->maskendy = $this->endy - $this->fontSize;
        $this->headx = $this->xPoz($this->texts['headerText']);
        $this->endx = $this->xPoz($this->texts['footerText']);
    }

}
