<?php
/* This file is part of Spirit | SSITU | (c) 2021 I-is-as-I-does */
namespace SSITU\Spirit\Trades;

use \SSITU\Jack\Jack;

class Printer
{

    private $Spirit;
    private $Plate;
    private $Sheet;
    private $Sod;

    public function __construct($Spirit)
    {
        $this->Spirit = $Spirit;
        $this->Plate = $Spirit->Plate();
        $this->Sheet = $Spirit->Sheet();
        $this->Sod = $this->Spirit->getSod();

    }


    public function printImg($rscImg, $dataToInject)
    {
        $cryptdata = $this->Sod->encrypt($dataToInject);
        if ($cryptdata === false) {
            $this->Spirit->record($this->Sod->getLogs()[0]);
            return false;
        }
      
        $stegaImage = $this->StegaData($rscImg, $cryptdata);

        if ($stegaImage === false) {
            $this->Spirit->record('Too much data to inject in too small image');
            return false;
        }

        return $stegaImage;
    }

    private function StegaData($rscImg, $cryptdata)
    {
        /* @doc: to maintain a small filesize, just a part of the pass is encoded;
        if more data needs to be injected, full potential is:
        - $this->Plate->encod_limits = ($this->Plate->width-1) * ($this->Plate->height-1);
        - $this->Plate->encod_y = 0; */
    
          $adtchar = '';
          $padlen = 0;
          $pads = strstr($cryptdata, '=');
          if ($pads !== false) {
            $cryptdata = rtrim($cryptdata, '=');
            $padlen = strlen($pads);
            for ($p = 0; $p < $padlen; $p++) {
              $adtchar .= Jack::Help()->multRandLetters($padlen);
            }
          }
          $datalen = strlen($cryptdata);   
          $realdata_len = $this->Plate->info_len + $datalen + $padlen;
    
          // @doc: * 8 bits
          if (($realdata_len + $this->Sheet->minfiller_len) * 8 > $this->Plate->encod_limits) {
            $this->Spirit->record('too much data to inject in a too small img');
            return false;
          }
    
          $file_bytes = Utils::b64bytes(($this->Plate->encod_limits / 8) - $realdata_len);
          $file_filler = Utils::randFill($file_bytes);
    
          $maxpoz = strlen($file_filler) - $datalen - $padlen - $this->Sheet->minfiller_len;
          $randpoz = rand($this->Sheet->minfiller_len, $maxpoz);
          $startpoz = $randpoz + $this->Plate->info_len;
    
          $hiddendata = substr_replace($file_filler, $cryptdata . $adtchar, $randpoz, 0);
    
          $lure1 = Utils::randFill($this->Plate->lure1_bytes);
          $lure2 = Utils::randFill($this->Plate->lure2_bytes);
    
          $frmt_startpoz = Utils::b64pad($startpoz, Jack::Help()->randLetter());
          $frmt_lengtpoz = Utils::b64pad($datalen, Jack::Help()->randLetter());
    
          $toinject = $this->Sheet->lure1 . $frmt_startpoz . $this->Sheet->lure2 . $frmt_lengtpoz . $hiddendata;
          $cryptdataBin = Utils::toBin($toinject);
        $rscImg = $this->rewritePx($rscImg, $cryptdataBin);

        $out_b64img = Jack::Images()->rsrcTob64png($rscImg);
        imagedestroy($rscImg);

        if ($this->Spirit->inTestMode()) {
            $this->testOutput($startpoz, $datalen, $out_b64img);
        }
        return $out_b64img;
    }

    
private function rewritePx($rscImg, $cryptdataBin)
{

    $pixelX = 0;
    $pixelY = $this->Plate->encod_y;

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

    private function testOutput($startpoz, $datalen, $out_b64img)
    {
//@doc: for testing purposes:
        echo implode('<br>', ['start position: ' . $startpoz, 'data length: ' . $datalen, 'result: ', '<img src="' . $out_b64img . '">']);
    }

}
