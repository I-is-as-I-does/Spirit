<?php
/* This file is part of Spirit | SSITU | (c) 2021 I-is-as-I-does */
namespace SSITU\Spirit\Trades;

use \SSITU\Spirit\Spirit;

class Reader extends Spirit
{
    private $Spirit;
    private $Plate;

    protected function __construct($Spirit)
    {
        $this->Spirit = $Spirit;
    }

    protected function read($img, $spiritKey)
    {
        $rscImg = $this->getRscImg($img);
        if ($rscImg === false) {
            $this->Spirit->record('invalid-image');
            return false;
        }

        $config['height'] = imagesy($rscImg);
        $config['width'] = imagesx($rscImg);
        $config['keyLength'] = strlen($spiritKey);

        $this->Plate = $this->Spirit->Plate(); 
        if(!$this->Plate->setReadConfig($config)){
            return false;
        }

        $extrdata = $this->extract($rscImg); 
        $keypos =stripos($extrdata, $spiritKey);
        if($keypos === false){
            $this->Spirit->record('invalid-key-image-pair');
            return false;
        }
        $datalenpos = $keypos + $this->Plate->keyLength;
        $datalen = substr($extrdata,$datalenpos,$this->Plate->poslen);
        $datalen = preg_replace('/[^\d]/', '', $datalen);
        if(empty($datalen)){
           $this->Spirit->record('invalid-encryption');
            return false;
           }
        $outdata = substr($extrdata,$datalenpos + $this->Plate->poslen, $datalen); 
        $valid_outdata = Utils::b64pad($outdata);

        imagedestroy($rscImg);

       $Sod = $this->Spirit->getSod();
        $decrypt = $Sod->decrypt($valid_outdata);
        if ($decrypt === false) {
            $this->Spirit->record($Sod->getLogs()[0]);
            return false;
        }

        return $decrypt;
    }

    
    private function getRscImg($img)
    {
        if (is_file($img)) {
            return Utils::pathToRsc($img);
        }
        return Utils::b64ToRsrc($img);
    }


    private function extract($rscImg)
    {
        $pixelX =0;
        $pixelY = 0;
    
        $extract = '';

        for ($x = 0; $x < $this->Plate->encod_limits; $x++) { // @doc: loop through pixel by pixel
            if ($pixelX === $this->Plate->width) { // @doc: if this is true, we've reached the end of the row of pixels, start on next row
                $pixelY++;
                $pixelX = 0;
            }
            $rgb = imagecolorat($rscImg, $pixelX, $pixelY); // @doc: color of the pixel at the x and y positions
            $b = $rgb & 0xFF;

            $binBlue = Utils::toBin($b);
            $leastBit = strlen($binBlue) - 1;

            $extract .= $binBlue[$leastBit]; // @doc: add the lsb to binary result
            $pixelX++; // @doc: change x coordinates to next
        }
        return Utils::toHex($extract);
    }

}
