<?php
/* This file is part of Spirit | SSITU | (c) 2021 I-is-as-I-does */
namespace SSITU\Spirit\Trades;

trait Sheet
{
    private $pxLimit;
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
    private $minlen;

    # defaults
    private $texts = ['headerText' => '', 'footerText' => '', 'mainText' => '', 'addtTexts' => []];
    private $minfillerLen = 32;
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

    public function boxDim($input)
    {
        //@doc: beware, imagettfbbox is often buggy, depending on server running GD
        $box = imagettfbbox($this->fontSize, $this->angle, $this->fontFilePath, $input);
        $inputwidth = abs($box[2] - $box[0]);
        $inputheight = abs($box[7] - $box[1]);
        return ["w" => $inputwidth, "h" => $inputheight];
    }

    public function xPoz($input)
    {
        $indim = $this->boxDim($input);
        $xmid = ($this->width - $indim["w"]) / 2;
        $out = ["xmid" => $xmid];
        $out["maskx1"] = $xmid - $this->margin;
        $out["maskx2"] = $xmid + $indim["w"] + $this->margin;
        return $out;
    }

    private function setSheetParam()
    {
        $this->prepPrintParam();
        $this->prepPrintValues();

        if ($this->minlen + 1 >  $this->pxLimit) {
            $this->Spirit->record('image-is-too-small');
            return false;
        }
        return true;
    }

    private function prepPrintParam()
    {

        $imgExtension = $this->config['imgExtension'];
        if (in_array($imgExtension, $this->validExt)) {
            $this->imgExtension = $imgExtension;
        }

        foreach (['fontFilePath', 'bgImgPath'] as $pathParam) {
            $method = 'check' . ucfirst($pathParam);
            $path = $this->config[$pathParam];
            if ($this->$method($path)) {
                $this->$pathParam = $path;
            }
        }

        foreach (['fontSize', 'margin', 'lineSpacer', 'minfillerLen'] as $param) {
            $numbr = $this->config[$param];
            if (Utils::isPostvInt($numbr)) {
                $this->$param = $numbr;
            }
        }

        $angle = $this->config['angle'];
        if (is_int($angle)) {
            $this->angle = $angle;
        }

        $adtlines = $this->config['adtlines'];
        if (is_int($adtlines) && $adtlines >= 2) {
            $this->adtlines = $adtlines;
        }

        foreach (['headerText', 'footerText', 'mainText'] as $tparam) {
            $text = $this->config[$tparam];
            if (is_string($text)) {
                $this->texts[$tparam] = $text;
            }
        }

        $adtTexts = $this->config['addtTexts'];
        if (is_array($adtTexts)) {
            $this->texts['addtTexts'] = $adtTexts;
        }

        foreach (['bgColorCodes', 'lineColorCodes', 'textColorCodes'] as $colorparam) {
            $colors = $this->config[$colorparam];
            if (is_array($colors) && count($colors) === 3 && array_filter($colors, 'is_int') === $colors) {
                $this->$colorparam = $colors;
            }
        }

        foreach (['drawFrame', 'printTexts', 'useBgImg'] as $boolParam) {
            $bool = $this->config[$boolParam];
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
            $this->Spirit->record('invalid bg img type');
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

    private function prepPrintValues()
    {
        $this->pxLimit = ceil($this->encod_limits/8);
        $this->minlen = $this->poslen + ($this->minfillerLen * 2);
        $this->fillerLimit = $this->pxLimit - $this->minlen;
        $this->lineHeight = $this->fontSize + $this->lineSpacer;
        $this->maxWidth = $this->width - $this->margin * 2;
        $this->maxLines = floor(($this->height - $this->margin * 2) / $this->lineHeight);
        $this->y_adjust = $this->lineSpacer + $this->lineHeight / 2;
        $this->baseDiff = $this->maxLines - $this->adtlines;
        $this->heady = $this->margin + $this->fontSize - $this->lineSpacer / 2;
        $this->endy = $this->height - $this->margin + $this->lineSpacer / 2;
        $this->maskendy = $this->endy - $this->fontSize;
        $this->headx = $this->xPoz($this->texts['headerText']);
        $this->endx = $this->xPoz($this->texts['footerText']);
    }

}
