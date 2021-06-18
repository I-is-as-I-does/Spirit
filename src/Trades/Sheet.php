<?php
/* This file is part of Spirit | SSITU | (c) 2021 I-is-as-I-does */
namespace SSITU\Spirit\Trades;

use \SSITU\Jack\Jack;

class Sheet
{

    private $Spirit;
    private $Plate;
    private $config;

    # create-specific set values
    private $lineHeight;
    private $maxWidth;
    private $maxLines;

    private $textcolor;

    private $baseDiff;
    private $lure1;
    private $lure2;
    private $minfiller_len;
    private $y_adjust;

    # create-specific defaults
    private $adtlines = 4; //@doc: 4 = header, footer, and line spacers
    private $minfiller_bytes = 3;
    private $fontFile = "../samples/sourcessproxtrlight.ttf";

    private $fontSize = 11;
    private $margin = 9;
    private $lineSpacer = 2;
    private $angle = 0;
    private $textColorCodes = [160, 160, 160];

    public function __construct($Spirit)
    {
        $this->Spirit = $Spirit;

    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
    }

    public function setConfig($config)
    {
        $this->Plate = $this->Spirit->Plate();

        $this->config = $config;

        $this->prepDrawConfig();
        $this->prepDrawValues();

    }

    private function prepDrawConfig()
    {

        if (!empty($this->config['fontFilePath']) && $this->checkFontFile($this->config['fontFilePath'])) {
            $this->fontFile = $this->config['fontFilePath'];
        }

        foreach (['fontSize', 'margin', 'lineSpacer', 'minfiller_bytes'] as $param) {
            if (!empty($this->config[$param]) && Jack::Help()->isPostvInt($this->config[$param])) {
                $this->$param = $this->config[$param];
            }
        }

        if (!empty($this->config['angle']) && is_int($this->config['angle'])) {
            $this->angle = $this->config['angle'];
        }

        if (!empty($this->config['adtlines']) && is_int($this->config['adtlines']) && $this->config['adtlines'] >= 2) {
            $this->adtlines = $this->config['adtlines'];
        }
        if (!empty($this->config['textColorCodes'])) {
            $textColorCodes = $this->config['textColorCodes'];
            if (is_array($textColorCodes) && count($textColorCodes) === 3 && array_filter($textColorCodes, 'is_int') === $textColorCodes) {
                $this->textColorCodes = $textColorCodes;
            }
        }

    }

    private function checkFontFile($path)
    {
        if (!file_exists($path)) {
            $this->Spirit->record('invalid font file path');
            return false;
        }

        if (Jack::File()->getExt($path) !== 'ttf') {
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

        $this->minfiller_len = Utils::b64length($this->minfiller_bytes);

        $this->lineHeight = $this->fontSize + $this->lineSpacer;
        $this->maxWidth = $this->Plate->width - $this->margin * 2;

        $this->maxLines = floor(($this->Plate->height - $this->margin * 2) / $this->lineHeight);

        $this->y_adjust = $this->lineSpacer + $this->lineHeight / 2;

        $this->baseDiff = $this->maxLines - $this->adtlines;

        $this->lure1 = Utils::randFill($this->Plate->lure1_bytes);
        $this->lure2 = Utils::randFill($this->Plate->lure2_bytes);
    }

}
