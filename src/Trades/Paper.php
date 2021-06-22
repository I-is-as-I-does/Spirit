<?php
/* This file is part of Spirit | SSITU | (c) 2021 I-is-as-I-does */
namespace SSITU\Spirit\Trades;

class Paper
{
    private $Printer;

    public function __construct($Printer)
    {
        $this->Printer = $Printer;
    }

    public function getRscImg()
    {
        if ($this->Printer->useBgImg) {
            return $this->loadImg();
        }
        return $this->makeImg();
    }

    private function drawFrame($rscImg, $bgcolor)
    {
        $linecolor = imagecolorallocate($rscImg, ...$this->Printer->lineColorCodes);
        # full frame 
        imagerectangle($rscImg, $this->Printer->margin, $this->Printer->margin, $this->Printer->width - $this->Printer->margin, $this->Printer->height - $this->Printer->margin, $linecolor);
        # frame horizontal "eraser" (to keep only angles) 
        imagefilledrectangle($rscImg, 0, $this->Printer->margin * 3, $this->Printer->width, $this->Printer->height - $this->Printer->margin * 3, $bgcolor);
        # top frame eraser, width based on header width  
        imagefilledrectangle($rscImg, $this->Printer->headx["maskx1"], 0, $this->Printer->headx["maskx2"], $this->Printer->heady, $bgcolor);
        # bottom frame eraser, width based on footer width  
        imagefilledrectangle($rscImg, $this->Printer->endx["maskx1"], $this->Printer->maskendy, $this->Printer->endx["maskx2"], $this->Printer->height, $bgcolor);
        return $rscImg;
    }

    private function makeImg()
    {
        $rscImg = imagecreatetruecolor($this->Printer->width, $this->Printer->height);
        $bgcolor = imagecolorallocate($rscImg, ...$this->Printer->bgColorCodes);
        imagefilledrectangle($rscImg, 0, 0, $this->Printer->width, $this->Printer->height, $bgcolor);
        if ($this->Printer->drawFrame) {
            return $this->drawFrame($rscImg, $bgcolor);
        }
        return $rscImg;
    }

    private function loadImg()
    {
        $img = Utils::pathToRsc($this->Printer->bgImgPath);
        if ($img === false) {
            return false;
        }
        return imagescale($img, $this->Printer->width, $this->Printer->height);
    }

}
