<?php
/* This file is part of Spirit | SSITU | (c) 2021 I-is-as-I-does */
namespace SSITU\Spirit\Trades;

use \SSITU\Jack\Jack;

class Printer
{

    private $Spirit;
    private $Plate;
    private $Sheet;

    public function __construct($Spirit)
    {
        $this->Spirit = $Spirit;

    }

    public function printImg($dataToInject)
    {

        $Sod = $this->Spirit->getSod();
        $cryptdata = $Sod->encrypt($dataToInject);
        if ($cryptdata === false) {
            $this->Spirit->record($Sod->getLogs()[0]);
            return false;
        }

        $this->Plate =$this->Spirit->Plate();
        if (!$this->Plate->setConfig()) {
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

        if (!Jack::Arrays()->allItemsAreEmpty($this->Sheet->texts)) {
            $rscImg = $this->Spirit->Stylus()->writeTexts($rscImg);
        }

        return $this->StegaData($rscImg, $cryptdata);
    }

    private function StegaData($rscImg, $cryptdata)
    {

        $adtchar = '';
        $padlen = 0;
        $datalen = strlen($cryptdata);
        $pads = strstr($cryptdata, '=');
        if ($pads !== false) {
            $cryptdata = rtrim($cryptdata, '=');
            $padlen = strlen($pads);
            for ($p = 0; $p < $padlen; $p++) {
                $adtchar .= Jack::Help()->multRandLetters($padlen);
            }
        }
        
        $realdata_len = $this->Plate->extradata_len + $datalen;

        $file_bytes = Utils::b64bytes(($this->Plate->encod_limits / 8) - $realdata_len);
        $file_filler = Utils::randFill($file_bytes);

        $maxpoz = strlen($file_filler) - $datalen - $this->Sheet->minfiller_len;
        $randpoz = rand($this->Sheet->minfiller_len, $maxpoz);
        $startpoz = $randpoz + $this->Plate->extradata_len;

        $hiddendata = substr_replace($file_filler, $cryptdata . $adtchar, $randpoz, 0);

        $lure1 = Utils::randFill($this->Plate->lure1Bytes);
        $lure2 = Utils::randFill($this->Plate->lure2Bytes);

        $frmt_startpoz = Utils::b64pad($startpoz, Jack::Help()->randLetter());
        $frmt_lengtpoz = Utils::b64pad($datalen, Jack::Help()->randLetter());

        $toinject = $this->Sheet->lure1 . $frmt_startpoz . $this->Sheet->lure2 . $frmt_lengtpoz . $hiddendata;

        // @doc: * 8 bits
        if (strlen($toinject) * 8 > $this->Plate->encod_limits) {
            $this->Spirit->record('too much data to inject in a too small img');
            return false;
        }
        $cryptdataBin = Utils::toBin($toinject);
        
        $rscImg = $this->rewritePx($rscImg, $cryptdataBin);

        $out_b64img = Jack::Images()->rsrcTob64png($rscImg);
        imagedestroy($rscImg);

        return ['image' => $out_b64img, 'signature' => $this->getSignature()];
    }


    private function getSignature()
    {

        $signature = [$this->Spirit->config('cryptKey')];
        if ($this->Spirit->config('flavour') === 'Sodium') {
            $signature[] = 1;
        } else {
            $signature[] = 2;
        }
        foreach (['lure1Bytes', 'lure2Bytes'] as $b) {
            $signature[] = $this->Plate->$b;
        }
        $signature = implode('$', $signature);
        return base64_encode($signature);

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
