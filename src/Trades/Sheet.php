<?php
/* This file is part of Spirit | SSITU | (c) 2021 I-is-as-I-does */
namespace SSITU\Spirit\Trades;

use \SSITU\Jack\Jack;

class Sheet
{

    private $Spirit;
    private $Plate;

    # create-specific set values
    private $lineHeight;
    private $maxWidth;
    private $maxLines;
    private $heady;
    private $bgcolor;
    private $linecolor;
    private $textcolor;
    private $endy;
    private $maskendy;
    private $baseDiff;
    private $lure1;
    private $lure2;
    private $minfiller_len;
    private $y_adjust;

    # create-specific defaults
    private $adtlines = 4; //@doc: 4 = header, footer, and line spacers
    private $minfiller_bytes = 3;
    private $fontFile = "../samples/sourcessproxtrlight.ttf";
    private $passTitle = 'PASS';
    private $fontSize = 11;
    private $margin = 9;
    private $lineSpacer = 2;
    private $angle = 0;
    private $bgColorCodes = [255, 255, 255];
    private $lineColorCodes = [200, 200, 200];
    private $textColorCodes = [160, 160, 160];

    public function __construct($Spirit)
    {
        $this->Spirit = $Spirit;
        $this->Plate = $Spirit->Plate();

        $this->prepDrawConfig();
        $this->prepDrawValues();
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
    }

    private function prepDrawConfig()
    {
        if (!empty($this->Spirit->config('passTitle') && is_string($this->Spirit->config('passTitle')))) {
            $this->passTitle = $this->Spirit->config('passTitle');
        }

        if (!empty($this->Spirit->config('fontFilePath')) && $this->checkFontFile($this->Spirit->config('fontFilePath'))) {
            $this->fontFile = $this->Spirit->config('fontFilePath');
        }

        foreach (['fontSize', 'margin', 'lineSpacer', 'minfiller_bytes'] as $param) {
            if (Jack::Help()->isPostvInt($this->Spirit->config($param))) {
                $this->$param = $this->Spirit->config($param);
            }
        }

        if (is_int($this->Spirit->config('angle'))) {
            $this->angle = $this->Spirit->config('angle');
        }

        if (is_int($this->Spirit->config('adtlines')) && $this->Spirit->config('adtlines') >= 2) {
            $this->adtlines = $this->Spirit->config('adtlines');
        }

        foreach (['bgColorCodes', 'lineColorCodes', 'textColorCodes'] as $colorparam) {
            if (is_array($this->Spirit->config($colorparam)) && count($this->Spirit->config($colorparam)) === 3 && array_filter($this->Spirit->config($colorparam), 'is_int') === $this->Spirit->config($colorparam)) {
                $this->$colorparam = $this->Spirit->config($colorparam);
            }
        }

    }

    private function checkFontFile($path)
    {
        if (!file_exists($path)) {
            $this->Spirit->addToLog('invalid font file path');
            return false;
        }

        if (Jack::File()->getExt($path) !== 'ttf') {
            $this->Spirit->addToLog('invalid font file type');
            return false;
        }
        if (!empty(preg_match('/[^a-z]+/', basename($path, '.ttf')))) {
            $this->Spirit->addToLog('invalid font file name: [a-z] only');
            //@doc: for weird unknown reasons, font filename must be lowercase a-z only
            return false;
        }

        return true;
    }

    private function prepDrawValues()
    {

        $this->minfiller_len = Utils::b64length($this->minfiller_bytes);

        $this->lineHeight = $this->fontSize + $this->lineSpacer;
        $this->maxWidth = $this->Plate->width - $this->margin * 2;

        $this->maxLines = floor(($this->Plate->height - $this->margin * 2) / $this->lineHeight);

        $this->heady = $this->margin + $this->fontSize - $this->lineSpacer / 2;

        $this->endy = $this->Plate->height - $this->margin + $this->lineSpacer / 2;
        $this->maskendy = $this->endy - $this->fontSize;

        $this->y_adjust = $this->lineSpacer + $this->lineHeight / 2;

        $this->baseDiff = $this->maxLines - $this->adtlines;

        $this->lure1 = Utils::randFill($this->Plate->lure1_bytes);
        $this->lure2 = Utils::randFill($this->Plate->lure2_bytes);
    }

}
