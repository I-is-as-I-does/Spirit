<?php
/* This file is part of Spirit | SSITU | (c) 2021 I-is-as-I-does */
namespace SSITU\Spirit\Trades;

use \SSITU\Spirit\Interfaces\_Template;

class _Paper implements _Template
{

    private $Spirit;
    private $Plate;
    private $Sheet;
    private $Stylus;

    private $headerText = '';
    private $footerText = '';
    private $mainText = '';
    private $addtTexts = [];
    private $bgColorCodes = [255, 255, 255];
    private $lineColorCodes = [200, 200, 200];

    private $heady;
    private $endy;
    private $maskendy;

    public function __construct($Spirit)
    {
        $this->Spirit = $Spirit;
        $this->Plate = $Spirit->Plate();
        $this->Sheet = $Spirit->Sheet();
        $this->Stylus = $Spirit->Stylus();
    }

    public function setConfig($config)
    {
        $this->Sheet->setConfig($config);

        foreach (['headerText', 'footerText', 'addtTexts'] as $param) {

            if (!empty($config[$param]) && is_string($config[$param])) {
                $this->$param = $config[$param];
            }
        }

        if (!empty($config['addtTexts']) && is_array($config['addtTexts'])) {
            $this->addtTexts = $config['addtTexts'];
        }

        foreach (['bgColorCodes', 'lineColorCodes'] as $colorparam) {

            if (is_array($config[$colorparam]) && count($config[$colorparam]) === 3 && array_filter($config[$colorparam], 'is_int') === $config[$colorparam]) {
                $this->$colorparam = $config[$colorparam];
            }
        }

        $this->heady = $this->Sheet->margin + $this->Sheet->fontSize - $this->Sheet->lineSpacer / 2;

        $this->endy = $this->Plate->height - $this->Sheet->margin + $this->Sheet->lineSpacer / 2;
        $this->maskendy = $this->endy - $this->Sheet->fontSize;

    }

    public function getRscImage()
    {
        $image = imagecreatetruecolor($this->Plate->width, $this->Plate->height);
        $textcolor = imagecolorallocate($image, ...$this->Sheet->textColorCodes);

        $bgcolor = imagecolorallocate($image, ...$this->bgColorCodes);
        $linecolor = imagecolorallocate($image, ...$this->lineColorCodes);

        $endx = $this->xPoz($this->footerText);
        $headx = $this->xPoz($this->headerText);
        /* bg */
        imagefilledrectangle($image, 0, 0, $this->Plate->width, $this->Plate->height, $bgcolor);
        /* frame */
        imagerectangle($image, $this->Sheet->margin, $this->Sheet->margin, $this->Plate->width - $this->Sheet->margin, $this->Plate->height - $this->Sheet->margin, $linecolor);
        /* frame horizontal "eraser" (to keep only angles) */
        imagefilledrectangle($image, 0, $this->Sheet->margin * 3, $this->Plate->width, $this->Plate->height - $this->Sheet->margin * 3, $bgcolor);

        /* top frame eraser, width based on header width  */
        imagefilledrectangle($image, $headx["maskx1"], 0, $headx["maskx2"], $this->Sheet->heady, $bgcolor);
        /* header */

        /* bottom frame eraser, width based on footer width  */
        imagefilledrectangle($image, $endx["maskx1"], $this->Sheet->maskendy, $endx["maskx2"], $this->Plate->height, $bgcolor);
        /* footer  */

        $image = $this->Stylus->writeTop($image, $headx, $this->headerText);
        $image = $this->Stylus->writeBottom($image, $endx, $this->footerText);

        return $this->Stylus->writeCenter($image, $this->mainText, $this->addtTexts);

    }

}
