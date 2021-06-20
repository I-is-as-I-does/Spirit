<?php
/* This file is part of Spirit | SSITU | (c) 2021 I-is-as-I-does */
namespace SSITU\Spirit\Trades;

use \SSITU\Spirit\Spirit;

class Printer extends Spirit
{

    private $Spirit;
    private $Plate;
    private $Sheet;

    public function __construct($Spirit)
    {
        $this->Spirit = $Spirit;
    }

    protected function print($dataToInject)
    {

       $Sod = $this->Spirit->getSod();
        $cryptdata = $Sod->encrypt($dataToInject);
        if ($cryptdata === false) {
            $this->Spirit->record($Sod->getLogs()[0]);
            return false;
        }

        $this->Plate = $this->Spirit->Plate();
        if (!$this->Plate->setPrintConfig()) {
            return false;
        }

        $this->Sheet = $this->Spirit->Sheet();
        if (!$this->Sheet->setConfig()) {
            return false;
        }

        $rscImg = $this->Spirit->Paper()->getRscImg();
        if ($rscImg === false) {
            return false;
        }

        if (array_filter($this->Sheet->texts, function($itm){ return empty($itm);}) !== $this->Sheet->texts) {
            $rscImg = $this->Spirit->Stylus()->writeTexts($rscImg);
        }

        return $this->StegaData($rscImg, $cryptdata);
    }

    private function StegaData($rscImg, $cryptdata)
    {

        $datalen = strlen($cryptdata); 
        if ($this->Plate->minlen + $datalen > $this->Plate->encod_limits) {
            $this->Spirit->record('too much data to inject in a too small img');
            return false;
        }   

        $info = Utils::adjustb64($this->Plate->poslen, $datalen);      
        $cryptdata = Utils::adjustb64($datalen, $cryptdata);
        $fillerlen = floor($this->Sheet->fillerLimit/8) - $datalen;
        $filler = Utils::randFill($fillerlen);
        $randpoz = random_int($this->Plate->minfillerBytes, strlen($filler) - $this->Plate->minfillerBytes);
        $key = substr($filler,$randpoz-$this->Plate->keyLength-1,$this->Plate->keyLength);
        $toinject = substr($filler,0,$randpoz-1).$info.$cryptdata.substr($filler,$randpoz+1);
        $cryptdataBin = Utils::toBin($toinject);
        $rscImg = $this->rewritePx($rscImg, $cryptdataBin);     
        $out_b64img = Utils::rscTob64($rscImg, $this->Sheet->imgExtension);
        imagedestroy($rscImg);
        return ['image' => $out_b64img, 'key' => $key];
    }

    private function rewritePx($rscImg, $cryptdataBin)
    {

        $pixelX = 0;
        $pixelY = 0;

        for ($x = 0; $x < $this->Plate->encod_limits; $x++) { // @doc: loop through pixel by pixel
            if ($pixelX === $this->Plate->width) { // @doc: if this is true, we've reached the end of the row of pixels, start on parent row
                $pixelY++;
                $pixelX = 0;
            }
            $rgb = imagecolorat($rscImg, $pixelX, $pixelY); // @doc: color of the pixel at the x and y positions
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;

            $binBlue = Utils::toBin($b);
            $leastBit = strlen($binBlue) - 1;

            $binBlue[$leastBit] = $cryptdataBin[$x]; // @doc: change least significant bit with the bit from data
            $newBlue = Utils::toHex($binBlue);

            $new_color = imagecolorallocate($rscImg, $r, $g, $newBlue); // @doc: swap pixel with new pixel that has its blue lsb changed (looks the same)
            imagesetpixel($rscImg, $pixelX, $pixelY, $new_color); // @doc: set the color at the x and y positions

            $pixelX++; // @doc: change x coordinates to parent
        }
        return $rscImg;
    }

}
