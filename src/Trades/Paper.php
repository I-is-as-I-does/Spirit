<?php
/* This file is part of Spirit | SSITU | (c) 2021 I-is-as-I-does */
namespace SSITU\Spirit\Trades;

use \SSITU\Spirit\Spirit;

class Paper extends Spirit
{

    private $Spirit;
    private $Plate;
    private $Sheet;

    protected function __construct($Spirit)
    {
        $this->Spirit = $Spirit;

    }
    
   public function getRscImg()
    {
        $this->Plate = $this->Spirit->Plate();
        $this->Sheet = $this->Spirit->Sheet();
        if ($this->Sheet->useBgImg) {
            return $this->loadImg();
        }
        return $this->makeImg();

    }

    private function drawFrame($rscImg, $bgcolor)
    {
        $linecolor = imagecolorallocate($rscImg, ...$this->Sheet->lineColorCodes);
        /* frame */
        imagerectangle($rscImg, $this->Sheet->margin, $this->Sheet->margin, $this->Plate->width - $this->Sheet->margin, $this->Plate->height - $this->Sheet->margin, $linecolor);
        /* frame horizontal "eraser" (to keep only angles) */
        imagefilledrectangle($rscImg, 0, $this->Sheet->margin * 3, $this->Plate->width, $this->Plate->height - $this->Sheet->margin * 3, $bgcolor);
        /* top frame eraser, width based on header width  */
        imagefilledrectangle($rscImg, $this->Sheet->headx["maskx1"], 0, $this->Sheet->headx["maskx2"], $this->Sheet->heady, $bgcolor);
        /* bottom frame eraser, width based on footer width  */
        imagefilledrectangle($rscImg, $this->Sheet->endx["maskx1"], $this->Sheet->maskendy, $this->Sheet->endx["maskx2"], $this->Plate->height, $bgcolor);
        return $rscImg;
    }

    private function makeImg()
    {
        $rscImg = imagecreatetruecolor($this->Plate->width, $this->Plate->height);
        $bgcolor = imagecolorallocate($rscImg, ...$this->Sheet->bgColorCodes);
        imagefilledrectangle($rscImg, 0, 0, $this->Plate->width, $this->Plate->height, $bgcolor);
        if ($this->Sheet->drawFrame) {
            return $this->drawFrame($rscImg, $bgcolor);
        }
        return $rscImg;
    }

    private function loadImg()
    {
        $img = Utils::pathToRsc($this->Sheet->bgImgPath);
        if ($img === false) {
            $this->Spirit->record('invalid-background-image');
            return false;
        }
        return imagescale($img, $this->Plate->width, $this->Plate->height);
    }

}
