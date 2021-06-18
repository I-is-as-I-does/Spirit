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

    private $texts = [];
    private $bgColorCodes = [255, 255, 255];
    private $lineColorCodes = [200, 200, 200];
    private $drawFrame = true;

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

        foreach (['headerText', 'footerText', 'mainText'] as $param) {

            if (!empty($config[$param]) && is_string($config[$param])) {
                $this->texts[$param] = $config[$param];
            }
        }

        if (!empty($config['addtTexts']) && is_array($config['addtTexts'])) {
            $this->texts['addtTexts'] = $config['addtTexts'];
        }

        foreach (['bgColorCodes', 'lineColorCodes'] as $colorparam) {

            if (is_array($config[$colorparam]) && count($config[$colorparam]) === 3 && array_filter($config[$colorparam], 'is_int') === $config[$colorparam]) {
                $this->$colorparam = $config[$colorparam];
            }
        }

        if (is_bool($config['drawFrame'])) {
            $this->drawFrame = $config['drawFrame'];
        }

        $this->heady = $this->Sheet->margin + $this->Sheet->fontSize - $this->Sheet->lineSpacer / 2;

        $this->endy = $this->Plate->height - $this->Sheet->margin + $this->Sheet->lineSpacer / 2;
        $this->maskendy = $this->endy - $this->Sheet->fontSize;

    }

    private function drawFrame($rscImg, $endx, $headx)
    {
        $bgcolor = imagecolorallocate($rscImg, ...$this->bgColorCodes);
        $linecolor = imagecolorallocate($rscImg, ...$this->lineColorCodes);
        /* bg */
        imagefilledrectangle($rscImg, 0, 0, $this->Plate->width, $this->Plate->height, $bgcolor);
        /* frame */
        imagerectangle($rscImg, $this->Sheet->margin, $this->Sheet->margin, $this->Plate->width - $this->Sheet->margin, $this->Plate->height - $this->Sheet->margin, $linecolor);
        /* frame horizontal "eraser" (to keep only angles) */
        imagefilledrectangle($rscImg, 0, $this->Sheet->margin * 3, $this->Plate->width, $this->Plate->height - $this->Sheet->margin * 3, $bgcolor);
        /* top frame eraser, width based on header width  */
        imagefilledrectangle($rscImg, $headx["maskx1"], 0, $headx["maskx2"], $this->Sheet->heady, $bgcolor);
        /* bottom frame eraser, width based on footer width  */
        imagefilledrectangle($rscImg, $endx["maskx1"], $this->Sheet->maskendy, $endx["maskx2"], $this->Plate->height, $bgcolor);
        return $rscImg;
    }

    public function getRscImage()
    {
        $rscImg = imagecreatetruecolor($this->Plate->width, $this->Plate->height);

        $endx = $this->xPoz($this->footerText);
        $headx = $this->xPoz($this->headerText);
        if ($this->drawFrame) {
            $rscImg = $this->dawFrame($rscImg, $endx, $headx);
        }

        if(!empty($this->texts) || !empty($this->addtTexts)){
            $rscImg = $this->Stylus->writeTexts($rscImg, $headx, $endx,$this->texts);
        }

        return $rscImg;

    }

}
